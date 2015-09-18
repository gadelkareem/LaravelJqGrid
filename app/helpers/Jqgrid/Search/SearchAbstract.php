<?php


namespace Jqgrid\Search;

use \Illuminate\Support\Facades\DB as DB;

/**
 * Class DatabaseSearchAbstract
 * @package  Jqgrid\Search
 */
abstract class SearchAbstract implements SearchInterface
{
    /**
     * database
     * @var  \Illuminate\Database\Query\Builder or
     *              \Illuminate\Database\Eloquent
     *
     */
    protected $database;

    /**
     * Visible columns
     *
     * @var Array
     *
     */
    public $visibleColumns = [];

    /**
     * Select columns
     *
     * @var Array
     *
     */
    public $select = [];

    /**
     * Relations
     *
     * @var Array
     *
     */
    private $relations = [];

    /**
     * Order By
     *
     * @var array
     *
     */
    protected $orderBy;

    /**
     * Group By
     *
     * @var array
     *
     */
    protected $groupBy;

    /**
     *  Query Builder
     *
     * @var  \Illuminate\Database\Query\Builder
     */
    protected $query;

    /**
     *  Attributes types;
     *
     * @var array
     */
    public $cast;

    /**
     *  Export attributes
     *
     * @var array
     */
    public $export;

    /**
     *  Set required attributes
     */
    public function __construct()
    {
        $this->boot();
        foreach ($this->visibleColumns as $key => $column) {
            if (isset($column['relation'])) {
                $this->relations[$column['relation']] = $key;
                continue;
            }
            if (is_numeric($key)) {
                $this->visibleColumns[$column] = isset($this->cast[$column]) ? ['cast' => $this->cast[$column]] : [];
                unset($this->visibleColumns[$key]);
                $key = $column;
            }
            $this->select[] = $key;
        }
    }


    /**
     * Calculate the number of rows. It's used for paging the result.
     *
     * @param  array $rules
     *         An array of filters, example: [['field'=>'column index/name 1','
     *         op'=>'operator','data'=>'searched string column 1'],
     *         ['field'=>'column index/name 2','op'=>'operator','data'=>'searched string column 2']]
     *  The 'field' key will contain the 'index' column property if is set, otherwise the 'name' column property.
     *         The 'op' key will contain one of the following operators: '=',
     *         '<', '>', '<=', '>=', '<>', '!=','like', 'not like', 'is in', 'is not in'.
     *  when the 'operator' is 'like' the 'data' already contains the '%' character in the appropiate position.
     *  The 'data' key will contain the string searched by the user.
     * @param string $count
     * @return integer
     *  Total number of rows
     */
    public function getTotalNumberOfRows($rules, $count = '*')
    {
        $this->createQuery($rules);
        return intval($this->query->count(DB::raw($count)));
    }


    /**
     * Get the rows from DB.
     *
     * @param  \Jqgrid\Encoders\EncoderSource $encoder
     * @return array
     */
    public function getRows(\Jqgrid\Encoders\EncoderSource $encoder)
    {
        $this->createQuery($encoder->filters['rules']);

        if (!is_null($encoder->orderBy) && !is_null($encoder->order)) {
            $this->orderBy = [[$encoder->orderBy, $encoder->order]];
        }

        if ($this->orderBy) {
            foreach ($this->orderBy as $orderBy) {
                if (isset($this->visibleColumns[$orderBy[0]]['having'])) {
                    $orderBy[0] = DB::raw($this->visibleColumns[$orderBy[0]]['having']);
                }
                $this->query->orderBy($orderBy[0], $orderBy[1]);
            }
        }

        if ($this->groupBy) {
            foreach ($this->groupBy as $groupBy) {
                $this->query->groupBy($groupBy);
            }
        }

        $rows = $this->query->take($encoder->limit)
            ->skip($encoder->offset)
            ->addSelect($this->select)
            ->get();


        if (!is_array($rows)) {
            $rows = $rows->toArray();
        }

        return $rows;
    }

    /**
     * Get the rows data to be shown in the grid.
     *
     * @param  \Jqgrid\Encoders\EncoderSource $encoder
     * @return array
     *  An array of array, each array will have the data of a row.
     *  Example: [['row 1 col 1','row 1 col 2'], ['row 2 col 1','row 2 col 2']]
     */
    public function getGrids(\Jqgrid\Encoders\EncoderSource $encoder)
    {
        $rows = $this->getRows($encoder);
        foreach ($rows as &$row) {
            $newRow = [];
            foreach ($row as $key => $value) {
                if (!isset($this->visibleColumns[$key]) && !isset($this->relations[$key])) {
                    continue;
                }
                $newRow[] = $value;
            }
            $row = $newRow;
        }
        return $rows;
    }

    /**
     * Get the rows data for export
     *
     * @param  \Jqgrid\Encoders\EncoderSource $encoder
     * @return array
     */
    public function getExport(\Jqgrid\Encoders\EncoderSource $encoder)
    {
        $rows = $this->getRows($encoder);
        foreach ($rows as &$row) {
            $newRow = [];
            foreach ($row as $key => $value) {
                if (!isset($this->visibleColumns[$key]) && !isset($this->relations[$key])) {
                    continue;
                }
                $newRow[$key] = is_array($value) ? json_encode($value) : $value;
            }
            $row = $newRow;
        }
        return $rows;
    }


    /**
     *  Create the search Query
     * @param array $rules
     *
     */
    public function createQuery($rules = null)
    {
        if ($this->query) {
            return;
        }

        $this->query = $this->database instanceof \Illuminate\Database\Query\Builder ?
            $this->database : $this->database->newQuery();

        if ($rules && is_array($rules)) {
            foreach ($rules as $rule) {
                $this->processFilter($rule);
            }
        }
    }

    /**
     * Process each filter and add search query
     *
     * @param array $rule
     */
    protected function processFilter(array $rule)
    {
        if (!empty($this->visibleColumns[$rule['field']]['having'])) {
            $this->query->having(
                DB::raw($this->visibleColumns[$rule['field']]['having']),
                $rule['op'],
                $rule['data']
            );
            return;
        }
        if (isset($this->visibleColumns[$rule['field']]['relation'])) {
            $column = $this->visibleColumns[$rule['field']];
            $rule['field'] = $column['searchName'];
            $this->query = $this->query->whereHas(
                $column['relation'],
                function ($query) use ($rule) {
                    switch ($rule['op']) {
                        case 'is in':
                            $query->whereIn($rule['field'], explode(',', $rule['data']));
                            return;
                        case 'is not in':
                            $query->whereNotIn($rule['field'], explode(',', $rule['data']));
                            return;
                        case 'is null':
                            $query->whereNull($rule['field']);
                            return;
                        case 'is not null':
                            $query->whereNotNull($rule['field']);
                            return;
                        default:
                            $query->where($rule['field'], $rule['op'], $rule['data']);
                            return;
                    }
                }
            );
        }

        if (isset($this->visibleColumns[$rule['field']])) {
            switch ($rule['op']) {
                case 'is in':
                    $this->query->whereIn($rule['field'], explode(',', $rule['data']));
                    return;
                case 'is not in':
                    $this->query->whereNotIn($rule['field'], explode(',', $rule['data']));
                    return;
                case 'is null':
                    $this->query->whereNull($rule['field']);
                    return;
                case 'is not null':
                    $this->query->whereNotNull($rule['field']);
                    return;
                default:
                    $this->query->where($rule['field'], $rule['op'], $rule['data']);
                    return;
            }
        }
    }


    /**
     *  remove the search Query
     *
     */
    protected function clearQuery()
    {
        $this->query = null;
    }
}
