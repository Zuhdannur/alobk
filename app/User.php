<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, RecordsFeed;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table= "user";

    protected $primaryKey = "id";

    protected $fillable = [
        'username','name','ever_change_password'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    public function favorite()
    {
        return $this->hasMany('\App\Favorite');
    }

    public function sekolah() {
        return $this->belongsTo('\App\Sekolah');
    }

    public function sekolahOnlyName() {
        return $this->sekolah()->select(array('id', 'nama_sekolah'));
    }

    public function diary() {
        return $this->hasMany('\App\Diary');
    }

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
                     ->with([$relation => $constraint]);
    }

    public function logAttribute(): string
    {
        if($this->role == 'admin' || $this->role == 'supervisor') {
            return $this->username;
        } else {
            return $this->name;
        }
    }
}
