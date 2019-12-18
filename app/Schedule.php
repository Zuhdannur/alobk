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
    // }updated_new_time updated_old_time
    
    public function getUpdatedNewTimeAttribute() {
        return $this->attributes['updated_new_time'] == null ? null : \Carbon\Carbon::parse($this->attributes['updated_new_time'])
        ->format('d, M Y: H:i');
    }

    public function getUpdatedOldTimeAttribute() {
        return $this->attributes['updated_old_time'] == null ? null : \Carbon\Carbon::parse($this->attributes['updated_old_time'])
        ->format('d, M Y: H:i');
    }

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

    public function getReadableDateAttribute() {
        return \Carbon\Carbon::parse($this->attributes['time'])
            ->format('d, M Y');
    }

    public function getReadableHoursAttribute() {
        return \Carbon\Carbon::parse($this->attributes['time'])
            ->format('H:i');
    }

    public function getPurePendingAttribute() {
        return $this->attributes['pending'] == 1;
        //  &&
        // $this->attributes['canceled'] == 0 &&
        //  $this->attributes['expired'] == 0 &&
        //   $this->attributes['finish'] == 0 &&
        //    $this->attributes['active'] == 0 &&
            // $this->attributes['start'] == 0 ? 1 : 0;
    }


    public function scopeIsPending($q) {
        return $q->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 0)->where('start', 0)->where('canceled', 0);
    }

    public function scopeCreatedToday($q) {
        return $q->whereDate('created_at', Carbon::today());
    }

    public function scopeIsActive($q) {
        return $q->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 1)->where('canceled', 0);
    }

    public function scopeIsExpired($q) {
        return $q->where('pending', 1)->where('expired', 1)->where('finish', 0)->where('active', 0)->where('start', 0)->where('canceled', 0);
    }

    public function scopeIsFinish($q) {
        return $q->where('pending', 1)->where('expired', 0)->where('finish', 1)->where('active', 1)->where('start', 1)->where('canceled', 0);
    }

    public function scopeIsCanceled($q) {
        return $q->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 0)->where('start', 0)->where('canceled', 1);
    }

    public function scopeIsStart($q) {
        return $q->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 1)->where('start', 1)->where('canceled', 0);
    }

}
