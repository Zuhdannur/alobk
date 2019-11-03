<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model {

    protected $guarded=[];

    protected $table = "feeds";

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function feedable() {
        return $this->morphTo();
    }

//    protected $fillable = [];

//    protected $dates = [];

//    public static $rules = [
        // Validation rules
//    ];

    // Relationships

}
