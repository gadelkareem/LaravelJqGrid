<?php

namespace App\Models;

use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserTrait;

/**
 * Class Users
 */
class Users extends \Eloquent implements UserInterface, RemindableInterface
{
    use UserTrait, RemindableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'active',
        'last_activity',
        'password',
    ];

    /**
     *  Attributes types;
     *
     * @var array
     */
    public $cast = [
        'id' => 'integer',
        'name' => 'string',
        'username' => 'string',
        'email' => 'string',
        'active' => 'boolean',
        'last_activity' => 'timestamp',
    ];

    /*
     * Login rules
     *
     * @var array
     */
    /**
     * @var array
     */
    public static $loginRules = [
        'username' => 'required|min:3|regex:/^([a-z0-9\-\.])+$/',
        'password' => 'required|min:6|regex:/\pL/|regex:/\pN/',
    ];

    /**
     *  Custom validation messages
     *
     * @var array
     */
    public $customMessages = [
        'username.regex' => "The username may only contain characters, numbers, '.' and ''-'.",
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * @param null $event
     * @return array
     */
    public function rules($event = null)
    {
        $id = is_int($this->id) ? $this->id : null;
        $rules = [
            'username' => 'required|min:3|regex:/^([a-z0-9\-\.])+$/|unique:users'.($id ? ',username,'.$id : ''),
            'email' => 'required|email|unique:users'.($id ? ',email,'.$id : ''),
            'name' => 'required|min:5',
            'last_activity' => 'sometimes|date',
        ];

        if ($event == 'creating'
            || ($event == 'updating' && $this->isDirty('password'))
        ) {
            $rules['password'] = 'required|min:6|regex:/\pL/|regex:/\pN/';
        }

        return $rules;
    }

    /**
     * @param string $username
     */
    public function setUsernameAttribute($username)
    {
        $this->attributes['username'] = strtolower($username);
    }


    /**
     * @param array $email
     */
    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = strtolower($email);
    }

    /**
     * @param null $event
     * @return bool
     */
    public function isValid($event = null)
    {
        $valid = parent::isValid($event);
        if ($valid && $this->isDirty('password')) {
            $this->attributes['password'] = \Hash::make($this->attributes['password']);
        }

        return $valid;
    }


    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', '=', 1);
    }
}
