<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogsActivityInterface;
use Spatie\Activitylog\Traits\LogsActivity;

class Sekolah extends Model implements LogsActivityInterface
{

    use RecordsFeed;

    protected $fillable = [
       'id', 'nama_sekolah','alamat'
    ];

    public function logAttribute()
    {
        return $this->nama_sekolah;
    }

    protected $dates = [];

    protected $table = "sekolah";

    protected $hidden = [
        'created_at','updated_at'
    ];

    public static $rules = [
        // Validation rules
    ];

    public function scopeWithAndWhereHas($query, $relation, $constraint){
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }

    // Relationships
    public function user() {
        return $this->hasMany('\App\User');
    }

    public function firstAdmin() {
        return $this->hasOne('\App\User')->where('role','admin');
    }

    /**
     * Get the message that needs to be logged for the given event.
     *
     * @param string $eventName
     *
     * @return string
     */
    public function getActivityDescriptionForEvent($eventName)
    {
        if ($eventName == 'created')
        {
            return 'Sekolah "' . $this->name . '" berhasil dibuat.';
        }

        if ($eventName == 'updated')
        {
            return 'Sekolah "' . $this->name . '" berhasil disunting.';
        }

        if ($eventName == 'deleted')
        {
            return 'Sekolah "' . $this->name . '" berhasil dihapus.';
        }

        return '';
    }
}
