<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{

    use RecordsFeed;

    protected $fillable = [
        'id','img','title','desc','kategori'
    ];

    protected $table = "artikel";

    protected $dates = [];

    protected $appends = ['readable_created_at'];

    public static $rules = [
        // Validation rules
    ];

    public function getReadableCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->diffForHumans();
    }

    // Relationships
    public function logAttribute(): string
    {
        return $this->title;
    }
}
