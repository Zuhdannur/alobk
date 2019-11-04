<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;

class Sekolah extends Model
{

    use RecordsFeed, LogsActivity;

    protected $fillable = [
       'id', 'nama_sekolah','alamat'
    ];

    protected static $logAttributes = ['nama_sekolah'];

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

}
