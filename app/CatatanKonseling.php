<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CatatanKonseling extends Model
{
    protected $fillable = [
        'id',
        'schedule_id',
        'komentar',
        'rating'
    ];

    protected $dates = [];

    protected $table = "catatan_konseling";

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function schedule()
    {
        return $this->hasOne('\App\Schedule');
    }

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->diffForHumans();
    }

}
