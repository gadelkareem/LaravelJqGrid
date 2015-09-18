<?php


namespace App\Controllers;

use \App\Models\Users;
use \App\Models\UsersSearch;

/**
 * Class UsersController
 */
class UsersController extends \Controller
{
    /**
     * List of Users
     *
     */
    public function getIndex()
    {
        $this->layout = \View::make('users.index');
        $this->layout->title = 'Users List';
    }


    /**
     *  Jqgrid handler for listing and CRUD
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postGridData()
    {
        $operation = \Input::get('oper');

        if (in_array($operation, ['edit', 'add', 'del'])) {
            return $this->processUser($operation);
        }
        return \Response::json(\Encoder::get(new UsersSearch, \Input::all()));
    }


    /**
     * Process User CRUD
     *
     * @param $operation
     * @return \Illuminate\Http\JsonResponse|mixed|string
     */
    private function processUser($operation)
    {
        $user = null;
        $postData = \Input::all();
        switch ($operation) {
            case 'del':
                return !Users::destroy(explode(',', $postData['id'])) ?
                    \Response::json(
                        ['errors' => (Users::$lastErrors ?: [['Delete Failed']])],
                        400
                    ) : '';
            case 'edit':
                $user = Users::findOrFail($postData['id']);
                break;
            case 'add':
                $user = new Users;
                break;
        }
        if (isset($postData['password']) && trim($postData['password']) == '') {
            unset($postData['password']);
        }
        $user->fill($postData);
        if (!$user->withValidation()->save()) {
            return \Response::json(['errors' => array_flatten($user->validationErrors)], 400);
        }
        return \Response::json(array_only($user->toArray(), array('id', 'groups_id')));
    }

    /**
     *  Jqgrid handler for Export
     *
     * @return \Response
     */
    public function getExportData()
    {
        return \Response::make(\Encoder::export(new UsersSearch, \Input::all()));
    }
}
