<?php


namespace App;


use Illuminate\Support\Facades\Auth;

trait RecordsFeed
{
    protected static function bootRecordsFeed()
    {
        static::created(function($model){
            $model->recordFeed('create', $model);
        });
        static::updated(function($model){
            $model->recordFeed('update', $model);
        });
        static::deleted(function($model){
            $model->recordFeed('delete', $model);
        });
    }

    abstract public function logAttribute(): string;

    public function feeds()
    {
        return $this->morphMany(Feed::class, 'feedable');
    }

    protected function recordFeed($type, $event)
    {
        $this->feeds()->create([
            'user_id' => Auth::user()->id,
            'type'    => $type,
            'description' => $event->logAttribute()
        ]);
    }
}
