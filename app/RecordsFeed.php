<?php


namespace App;


use Illuminate\Support\Facades\Auth;

trait RecordsFeed
{
    protected static function bootRecordsFeed()
    {
        static::created(function($model){
            $model->recordFeed('created', $model);
        });
        static::updated(function($model){
            $model->recordFeed('updated', $model);
        });
        static::deleted(function($model){
            $model->recordFeed('deleted', $model);
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
