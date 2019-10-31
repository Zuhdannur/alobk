<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CatatanKonseling extends Model
{
    protected $fillable = [
        'schedule_id'
    ];

    protected $dates = [];

    protected $table = "catatan_konseling";

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function schedule()
    {
        return $this->hasOne('\App\Schedule', 'id', 'schedule_id');
    }
}
