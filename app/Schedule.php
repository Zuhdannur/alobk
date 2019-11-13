<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'requester_id',
        'consultant_id',
        'title',
        'desc',
        'tgl_pengajuan',
        'type_schedule',
        'channel_url',
        'time',
        'location',

        'expired',
        'canceled',
        'pending',
        'finish',
        'active',
        'start'
    ];

    protected $dates = [
        'time'
    ];

    protected $table = "schedule";

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function requester()
    {
        return $this->hasOne('\App\User', 'id', 'requester_id');
    }

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }

    public function consultant()
    {
        return $this->hasOne('\App\User', 'id', 'consultant_id');
    }

    public function getTimeAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['time'])
            ->format('d, M Y H:i');
    }


    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->diffForHumans();
    }

}
