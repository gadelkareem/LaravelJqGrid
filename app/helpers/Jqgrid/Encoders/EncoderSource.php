<?php

namespace Jqgrid\Encoders;

use Jqgrid\Search\SearchInterface;
use App\Utils;

/**
 * Class EncoderSource
 * @package  Jqgrid\Encoders
 */
class EncoderSource
{
    /**
     *  current page
     * @var int
     */
    public $page = 1;

    /**
     *  limit per page
     * @var int
     */
    public $limit;

    /**
     *  order type asc or desc
     * @var string
     */
    public $order;

    /**
     * Order by column
     * @var string
     */
    public $orderBy;

    /**
     *  offset of the rows
     * @var int
     */
    public $offset;

    /**
     *  search filters
     * @var array
     */
    public $filters;

    /**
     *  Export
     * @var string
     */
    protected $exportType;

    /**
     *  Search repository
     * @var  \Jqgrid\Search\SearchInterface
     */
    protected $search;


    /**
     *  process input data
     *
     * @param array $inputData
     *
     */
    protected function processInput(array $inputData)
    {
        // get the requested exportType
        if (isset($inputData['oper']) && in_array($inputData['oper'], ['xls', 'csv'])) {
            $this->exportType = $inputData['oper'];
        }

        //do not set a limit if it is an exportType
        if (!$this->exportType) {
            // get the requested page
            if (isset($inputData['page']) && is_numeric($inputData['page'])) {
                $this->page = $inputData['page'];
            }

            // get how many rows we want to have into the grid
            if (isset($inputData['rows']) && is_numeric($inputData['rows']) && $inputData['rows'] > 0) {
                $this->limit = $inputData['rows'];
            } else {
                $this->limit = Config::get('grids.rows');
            }
        }

        // get the order
        if (isset($inputData['sord']) && in_array($inputData['sord'], ['asc', 'desc'])) {
            $this->order = $inputData['sord'];
        } else {
            $this->order = Config::get('grids.order');
        }

        // get index row - i.e. user click to sort
        if (isset($inputData['sidx']) && isset($this->search->visibleColumns[$inputData['sidx']])) {
            $this->orderBy = $inputData['sidx'];
        } else {
            $this->orderBy = Config::get('grids.orderBy');
        }

        //set filters input
        if (!empty($inputData['_search'])
            && Utils::cast($inputData['_search'], 'boolean') != false
            && isset($inputData['filters'])
        ) {
            $this->filters = is_array($inputData['filters']) ? $inputData['filters']
                : json_decode(str_replace('\'', '"', $inputData['filters']), true);
        }
    }

    /**
     *  Process search filters
     * @throws \Exception if casting fails
     */
    protected function processFilters()
    {
        if (isset($this->filters['rules']) && is_array($this->filters['rules'])) {
            foreach ($this->filters['rules'] as $k => $filter) {
                //removing fake fields
                if (!isset($this->search->visibleColumns[$filter['field']])) {
                    unset($this->filters['rules'][$k]);
                    continue;
                }
                //type casting values
                if (isset($this->search->visibleColumns[$filter['field']]['cast'])) {
                    $filter['data'] = Utils::cast(
                        $filter['data'],
                        $this->search->visibleColumns[$filter['field']]['cast']
                    );
                }
                switch ($filter['op']) {
                    case 'eq': //equal
                        $filter['op'] = '=';
                        break;
                    case 'ne': //not equal
                        $filter['op'] = '!=';
                        break;
                    case 'lt': //less
                        $filter['op'] = '<';
                        break;
                    case 'le': //less or equal
                        $filter['op'] = '<=';
                        break;
                    case 'gt': //greater
                        $filter['op'] = '>';
                        break;
                    case 'ge': //greater or equal
                        $filter['op'] = '>=';
                        break;
                    case 'bw': //begins with
                        $filter['op'] = 'ilike';
                        $filter['data'] = $filter['data'] . '%';
                        break;
                    case 'bn': //does not begin with
                        $filter['op'] = 'not ilike';
                        $filter['data'] = $filter['data'] . '%';
                        break;
                    case 'in': //is in
                        $filter['op'] = 'is in';
                        break;
                    case 'ni': //is not in
                        $filter['op'] = 'is not in';
                        break;
                    case 'ew': //ends with
                        $filter['op'] = 'ilike';
                        $filter['data'] = '%' . $filter['data'];
                        break;
                    case 'en': //does not end with
                        $filter['op'] = 'not ilike';
                        $filter['data'] = '%' . $filter['data'];
                        break;
                    case 'cn': //contains
                        $filter['op'] = 'ilike';
                        $filter['data'] = '%' . $filter['data'] . '%';
                        break;
                    case 'nc': //does not contains
                        $filter['op'] = 'not ilike';
                        $filter['data'] = '%' . $filter['data'] . '%';
                        break;
                    case 'nu': //is null
                        $filter['op'] = 'is null';
                        $filter['data'] = '';
                        break;
                    case 'nn': //is not null
                        $filter['op'] = 'is not null';
                        $filter['data'] = '';
                        break;
                    default:
                        unset($this->filters['rules'][$k]);
                        break;
                }
                $this->filters['rules'][$k] = $filter;
            }
        } else {
            $this->filters['rules'] = null;
        }
    }

    /**
     * Echo in a jqGrid compatible format the data requested by a grid.
     *
     * @param \Jqgrid\Search\SearchInterface $search
     *    An implementation of the SearchInterface
     * @param  array $inputData
     *    All jqGrid posted data
     * @throws \Exception
     * @return array
     *    Array of a jqGrid results.
     */
    public function get(SearchInterface $search, $inputData)
    {
        $this->search = $search;
        $this->processInput($inputData);
        $this->processFilters();

        $count = $this->search->getTotalNumberOfRows($this->filters['rules']);

        if (!is_int($count)) {
            throw new \Exception("The method getTotalNumberOfRows must return an integer");
        }


        $totalPages = $count > 0 ? ceil($count / $this->limit) : 0;
        if ($this->page > $totalPages) {
            $this->page = $totalPages;
        }

        $this->offset = $this->limit * $this->page - $this->limit;
        if ($this->offset < 0) {
            $this->offset = 0;
        }

        $this->limit = $this->limit * $this->page;

        $rows = $this->search->getGrids($this);

        if (!is_array($rows) || (isset($rows[0]) && !is_array($rows[0]))) {
            throw new \Exception(
                "The method getRows must return an array of arrays, example: " .
                "[['row 1 col 1','row 1 col 2'], ['row 2 col 1','row 2 col 2']]"
            );
        }

        return ['page' => $this->page, 'total' => $totalPages, 'records' => $count, 'rows' => $rows];
    }

    /**
     *  Provides an Excel or CSV export for the grids
     *
     * @param \Jqgrid\Search\SearchInterface $search
     *    An implementation of the SearchInterface
     * @param  array $inputData
     *    All jqGrid posted data
     * @throws \Exception
     */
    public function export(SearchInterface $search, $inputData)
    {
        $this->search = $search;
        $this->processInput($inputData);
        if (!$this->exportType) {
            throw new \Exception(
                "No valid export type requested."
            );
        }
        $this->processFilters();

        $rows = $this->search->getExport($this);

        Excel::create(
            $search->export['title'] . '.' . $this->exportType,
            function ($excel) use ($search, $rows) {
                $excel->setTitle($search->export['title']);
                $excel->sheet(
                    $search->export['title'],
                    function ($Sheet) use ($search, $rows) {
                        $Sheet->fromArray($rows);
                        $Sheet->row(
                            1,
                            function ($Row) {
                                $Row->setFontWeight('bold');
                            }
                        );
                    }
                );
            }
        )->export($this->exportType);
    }
}
