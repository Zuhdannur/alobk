<?php
namespace App;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Cron extends Model
{
    protected $primaryKey = 'command';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['command', 'next_run', 'last_run'];

    public static function shouldIRun($command, $minutes) {
        $cron = Cron::find('command:updateschedule');
        $now  = Carbon::now();
        if ($cron && $cron->next_run > $now->timestamp) {
            return false;
        }
        Cron::updateOrCreate(
            ['command'  => 'command:updateschedule'],
            ['next_run' => Carbon::now()->addMinutes($minutes)->timestamp,
                'last_run' => Carbon::now()->timestamp]
        );
        return true;
    }

}
