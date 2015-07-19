<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

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
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * [hasRole description]
     * 
     * @param  [type]  $role [description]
     * @return boolean       [description]
     */
    public function hasRole($role = null)
    {
        return isset($this->hasRole) ? $this->hasRole : false;
    }

    /**
     * [can description]
     * 
     * @param  [type] $permission [description]
     * @return [type]             [description]
     */
    public function can($permission = null)
    {
        return isset($this->can) ? $this->can : false;
    }
}
