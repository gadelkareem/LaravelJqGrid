<?php
namespace App\Models;

use Jqgrid\Search\EloquentSearch;

class UsersSearch extends EloquentSearch
{
    public function boot()
    {
        $this->database = new Users;
        $this->visibleColumns = [
            'id',
            'name',
            'username',
            'email',
            'active',
            'last_activity',
        ];
        $this->orderBy = [['id', 'asc']];
        $this->cast = $this->database->cast;
        $this->export = [
            'title' => 'Users',
        ];
    }
}
