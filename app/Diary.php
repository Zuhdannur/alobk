<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Diary extends Model
{
    protected $fillable = [
        'user_id','body','title','tgl'
    ];

    public $timestamps = true;

    protected $dates = [];

    protected $table = "diary";

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function user()
    {
       return $this->belongsTo('\App\User');
    }

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->diffForHumans();
    }

}
