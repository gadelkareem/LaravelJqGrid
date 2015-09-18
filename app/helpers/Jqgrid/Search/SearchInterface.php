<?php

namespace Jqgrid\Search;

/**
 * Interface SearchInterface
 * @package Jqgrid\Search
 */
interface SearchInterface
{
    /**
     * Calculate the number of rows. It's used for paging the result
     *
     * @param  array $rules
     *           An array of filters, example: [['field'=>'column index/name 1','op'=>'operator',
     *           'data'=>'searched string column 1'], ['field'=>'column index/name 2','
     *           op'=>'operator','data'=>'searched string column 2']]
     *           The 'field' key will contain the 'index' column property if is set,
     *           otherwise the 'name' column property.
     *           The 'op' key will contain one of the following operators: '=', '<', '>',
     *           '<=', '>=', '<>', '!=','like', 'not like', 'is in', 'is not in'.
     *    when the 'operator' is 'like' the 'data' already contains the '%' character in the appropriate position.
     *    The 'data' key will contain the string searched by the user.
     * @param string $count
     * @return integer
     *    Total number of rows
     */
    public function getTotalNumberOfRows($rules, $count = '*');


    /**
     * Get the rows data to be shown in the grid.
     *
     * @param  \Jqgrid\Encoders\EncoderSource $encoder
     * @return array
     */
    public function getRows(\Jqgrid\Encoders\EncoderSource $encoder);

    /**
     * Get the rows data to be shown in the grid.
     *
     * @param  \Jqgrid\Encoders\EncoderSource $encoder
     * @return array
     *  An array of array, each array will have the data of a row.
     *  Example: [['row 1 col 1','row 1 col 2'], ['row 2 col 1','row 2 col 2']]
     */
    public function getGrids(\Jqgrid\Encoders\EncoderSource $encoder);

    /**
     * Get the rows data for export
     *
     * @param  \Jqgrid\Encoders\EncoderSource $encoder
     * @return array
     */
    public function getExport(\Jqgrid\Encoders\EncoderSource $encoder);

    /**
     *  Create the search Query
     *
     * @param array $rules
     */
    public function createQuery($rules);
}
