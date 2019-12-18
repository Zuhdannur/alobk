<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Diary extends Model
{
    protected $fillable = [
        'user_id','body','title','tgl'
    ];

    public $timestamps = true;

    protected $dates = ['tgl'];

    protected $casts = [
        'tgl' => 'date:l, d F Y'
    ];

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

    public function dateCreatedAt() {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->translatedFormat('l, d F Y, H:m');
    }

    public function getTglAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['tgl'])->translatedFormat('l, d F Y');
    }

    public function scopeUserSameSchool($q) {
        // Schedule::whereHas('requester', function($query) {
        //     $query->sameSchool();
        // })->isDirect()->isActive()->count();
        return $q->whereHas('user', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        });
    }

}
