<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{

    use RecordsFeed;

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

    public function favorite() {
        return $this->belongsTo('\App\Favorite');
    }

    // Relationships
    public function logAttribute(): string
    {
        return $this->title;
    }
}
