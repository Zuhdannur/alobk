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
        'start',
        'overtime'
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

    public function scopeIsHistory($q) {
        if(Auth::user()->role === 'siswa') {
            return $q->where('expired', 1)
                ->orWhere('canceled', 1)
                ->orWhere('finish', 1);
        } else {
            return $q->where('canceled', 1)
                ->orWhere('finish', 1);
        }
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

    public function scopeUpdatedToday($q) {
        return $q->whereDate('updated_at', Carbon::today());
    }

    public function scopeIsActive($q) {
        return $q->where(function($query){
            $query->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 1)->where('canceled', 0);
        });
    }

    public function scopeJustActive($q) {
        return $q->where('active', 1);
    }

    public function scopeIsExpired($q) {
        return $q->where(function($query){
            $query->where('pending', 1)->where('expired', 1)->where('finish', 0)->where('active', 0)->where('start', 0)->where('canceled', 0);
        });
    }

    public function scopeJustExpired($q) {
        return $q->where('expired', 1);
    }

    public function scopeIsFinish($q) {
        return $q->where(function($query){
            $query->where('pending', 1)->where('expired', 0)->where('finish', 1)->where('active', 1)->where('canceled', 0);
        });
    }

    public function scopeJustFinish($q) {
        return $q->where('finish', 1);
    }

    public function scopeIsCanceled($q) {
        return $q->where(function($query){
            $query->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 0)->where('start', 0)->where('canceled', 1);
        });
    }

    public function scopeJustCanceled($q) {
        return $q->where('canceled', 1);
    }

    public function scopeIsStart($q) {
        return $q->where(function($query){
            $query->where('pending', 1)->where('expired', 0)->where('finish', 0)->where('active', 1)->where('start', 1)->where('canceled', 0);
        });
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

    public function scopeIsOnline($q) {
        return $q->where('type_schedule', '!=', 'direct');
    }

    public function scopeRequesterSameSchool($q) {
        // Schedule::whereHas('requester', function($query) {
        //     $query->sameSchool();
        // })->isDirect()->isActive()->count();
        return $q->whereHas('requester', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        });
    }

    public function scopeOrRequesterSameSchool($q) {
        // Schedule::whereHas('requester', function($query) {
        //     $query->sameSchool();
        // })->isDirect()->isActive()->count();
        return $q->orWhereHas('requester', function($query) {
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

    public function scopeOrConsultantSameSchool($q) {
        // Schedule::whereHas('requester', function($query) {
        //     $query->sameSchool();
        // })->isDirect()->isActive()->count();
        return $q->orWhereHas('consultant', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        });
    }

    public function scopeMe($q) {
        if(Auth::user()->role == 'siswa') {
            return $q->requesterIsMe();
        } else if(Auth::user()->role == 'guru') {
            return $q->consultantIsMe();
        } else {
            throw new \Exception('User role not found');
        }
    }

    public function scopeRequesterSchedule($q) {
        return $q->whereHas('requester', function ($query) {
            $query->where('role', 'siswa')
                ->where('requester_id', Auth::user()->id)
                ->where('sekolah_id', Auth::user()->sekolah_id);
        });
    }

    public function scopeConsultantSchedule($q) {
        return $q->whereHas('requester', function ($query) {
            $query->where('role', 'siswa')
                ->where('sekolah_id', Auth::user()->sekolah_id);
        });
    }

    public function scopeWithConsultant($q) {
        return $q->with('consultant');
    }

    public function scopeWithRequester($q) {
        return $q->with('requester');
    }

    public function scopeWithFeedback($q) {
        return $q->with('feedback');
    }

    public function scopeOrderDescCreated($q) {
        return $q->orderBy('created_at', 'desc');
    }

    public function scopeOrderDescUpdated($q) {
        return $q->orderBy('updated_at', 'desc');
    }

    public function scopeRequesterIsMe($q) {
        return $q->where('requester_id', Auth::user()->id);
    }

    public function scopeConsultantIsMe($q) {
        return $q->where('consultant_id', Auth::user()->id);
    }

}
