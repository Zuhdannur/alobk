<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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

    public function scopeIsPending($q) {
        return $q->where(function($query){
            $query->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 0)->where('start', 0)->where('canceled', 0);
        });
    }

    public function scopeJustPending($q) {
        return $q->where('pending', 1);
    }

    public function scopeCreatedToday($q) {
        return $q->whereDate('created_at', Carbon::today());
    }

    public function scopeIsActive($q) {
        return $q->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 1)->where('canceled', 0);
    }

    public function scopeJustActive($q) {
        return $q->where('active', 1);
    }

    public function scopeIsExpired($q) {
        return $q->where('pending', 1)->where('expired', 1)->where('finish', 0)->where('active', 0)->where('start', 0)->where('canceled', 0);
    }

    public function scopeJustExpired($q) {
        return $q->where('expired', 1);
    }

    public function scopeIsFinish($q) {
        return $q->where('pending', 1)->where('expired', 0)->where('finish', 1)->where('active', 1)->where('start', 1)->where('canceled', 0);
    }

    public function scopeJustFinish($q) {
        return $q->where('finish', 1);
    }

    public function scopeIsCanceled($q) {
        return $q->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 0)->where('start', 0)->where('canceled', 1);
    }

    public function scopeJustCanceled($q) {
        return $q->where('canceled', 1);
    }

    public function scopeIsStart($q) {
        return $q->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 1)->where('start', 1)->where('canceled', 0);
    }

    public function scopeJustStart($q) {
        return $q->where('start', 1);
    }

    public function scopeIsDaring($q) {
        return $q->where('type_schedule', 'daring');
    }

    public function scopeIsRealtime($q) {
        return $q->where('type_schedule', 'realtime');
    }

    public function scopeIsDirect($q) {
        return $q->where('type_schedule', 'direct');
    }

    public function scopeRequesterSameSchool($q) {
        // Schedule::whereHas('requester', function($query) {
        //     $query->sameSchool();
        // })->isDirect()->isActive()->count();
        return $q->whereHas('requester', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        });
    }

    public function scopeConsultantSameSchool($q) {
        // Schedule::whereHas('requester', function($query) {
        //     $query->sameSchool();
        // })->isDirect()->isActive()->count();
        return $q->whereHas('consultant', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        });
    }


}
