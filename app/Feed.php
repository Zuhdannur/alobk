<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model {

    protected $guarded=[];

    public function feedable() {
        return $this->morphTo();
    }

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->diffForHumans();
    }

//    protected $fillable = [];

//    protected $dates = [];

//    public static $rules = [
        // Validation rules
//    ];

    // Relationships

}
