<?php


namespace App\Http\Controllers\Supervisor;


use App\Schedule;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ScheduleController extends Controller
{

    private $schedule;

    /**
     * DiaryController constructor.
     * @param $schedule
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }


    public function getTotalSchedule() {
        $schedule = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->count();
        $countDaring = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where('type_schedule','daring')->count();
        $countDirect = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where('type_schedule','direct')->count();
        $countRealtime = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where('type_schedule','realtime')->count();

        return Response::json([
            'total_schedule' => $schedule,
            'total_daring' => $countDaring,
            'total_direct' => $countDirect,
            'total_realtime' => $countRealtime,
        ]);
    }

    public function getTotalToday() {
        $total = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where('created_at', Carbon::today());

        $schedule = $total->count();

        $totalPending = $total->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',0],
            ['start','=',0],
        ])->count();

        $totalActive = $total->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',1]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $totalSelesai = $total->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1],
        ])->count();

        $countDaring = $total->where('type_schedule','daring')->count();
        $countDirect = $total->where('type_schedule','direct')->count();
        $countRealtime = $total->where('type_schedule','realtime')->count();

        return Response::json([
            'total_schedule' => $schedule,
            'total_daring' => $countDaring,
            'total_direct' => $countDirect,
            'total_realtime' => $countRealtime,

            'total_pending' => $totalPending,
            'total_aktif' => $totalActive,
            'total_selesai' => $totalSelesai
        ]);
    }


    public function lastFeed(Request $request) {
        $schedule = $this->schedule->where('sekolah_id',Auth::user()->sekolah_id)->orderBy('created_at','desc');

        if($request->has('take')) {
            $schedule = $schedule->take($request->take)->get();
            return Response::json($schedule, 200);
        }

        $schedule = $schedule->paginate($request->per_page);

        return Response::json($schedule, 200);
    }

}
