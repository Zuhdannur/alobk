<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model {

    protected $table = "fav_artikel";

    protected $primaryKey = "id";

    protected $fillable = [
        'id','artikel_id','user_id'
    ];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function artikel(){
        return $this->hasMany('\App\Artikel')->select(array('id','title','desc','created_at'));
    }

    public function user(){
        return $this->belongsTo('\App\User')->select(array('id', 'name'));
    }

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['created_at'])
            ->diffForHumans();
    }

}
