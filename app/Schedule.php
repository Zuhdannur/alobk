<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'id',
        'requester_id',
        'consultant_id',
        'title',
        'desc',
        'type_schedule',
        'time',
        'location',
        'catatan',
        'updated_old_time',
        'updated_new_time',

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

    protected $appends = ['readable_created_at', 'readable_updated_at', 'readable_time', 'readable_date', 'readable_hours'];

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

    public function feedback()
    {
        return $this->hasOne('\App\Feedback');
    }

    // public function getTimeAttribute()
    // {
    //     return \Carbon\Carbon::parse($this->attributes['time'])
    //         ->format('d, M Y: H:i');
    // }

    public function getReadableCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->diffForHumans();
    }

    public function getReadableTimeAttribute() {
        return \Carbon\Carbon::parse($this->attributes['time'])
            ->format('d, M Y: H:i');
    }

    public function getReadableUpdatedAtAttribute()
    {
       return \Carbon\Carbon::parse($this->attributes['updated_at'])
           ->diffForHumans();
    }

    public function getReadableDate() {
        return \Carbon\Carbon::parse($this->attributes['time'])
            ->format('d, M Y');
    }

    public function getReadableHours() {
        return \Carbon\Carbon::parse($this->attributes['time'])
            ->format('H:i');
    }

}
