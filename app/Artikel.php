<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    protected $fillable = [
        'id','img','title','desc'
    ];

    protected $table = "artikel";

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->diffForHumans();
    }

    // Relationships
}
