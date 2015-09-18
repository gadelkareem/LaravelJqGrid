<?php

namespace Jqgrid\Search;

/**
 * Class DatabaseSearchAbstract
 * @package  Jqgrid\Search
 */
class EloquentSearch extends SearchAbstract
{
    /**
     * database
     * @var  \Illuminate\Database\Eloquent\Model
     *
     */
    protected $database;
}
