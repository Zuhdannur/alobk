<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model {

    protected $table = "fav_artikel";

    protected $primaryKey = "id_favorit";

    protected $fillable = [
        'id','id_artikel','user_id'
    ];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function artikel(){
        return $this->hasOne('\App\Artikel','id','id_artikel')->select(array('id','title','desc','created_at'));
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
