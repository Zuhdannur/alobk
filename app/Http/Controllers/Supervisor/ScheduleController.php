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
        $schedule = $this->schedule->where('sekolah_id',Auth::user()->sekolah_id)->count();
        $countDaring = $this->schedule->where('sekolah_id',Auth::user()->sekolah_id)->where('type_schedule','daring')->count();
        $countDirect = $this->schedule->where('sekolah_id',Auth::user()->sekolah_id)->where('type_schedule','direct')->count();
        $countRealtime = $this->schedule->where('sekolah_id',Auth::user()->sekolah_id)->where('type_schedule','realtime')->count();

        return Response::json([
            'total_schedule' => $schedule,
            'total_daring' => $countDaring,
            'total_direct' => $countDirect,
            'total_realtime' => $countRealtime,
        ]);
    }

    public function getTotalToday() {
        $schedule = $this->schedule->where('sekolah_id',Auth::user()->sekolah_id)->where('created_at', Carbon::today())->count();

        $totalPending = $this->where('sekolah_id',Auth::user()->sekolah_id)->schedule->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',0],
            ['start','=',0],
        ])->count();

        $totalActive = $this->where('sekolah_id',Auth::user()->sekolah_id)->schedule->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',1]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $totalSelesai = $this->where('sekolah_id',Auth::user()->sekolah_id)->schedule->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1],
        ])->count();

        $countDaring = $this->where('sekolah_id',Auth::user()->sekolah_id)->schedule->where('type_schedule','daring')->where('created_at',Carbon::today())->count();
        $countDirect = $this->where('sekolah_id',Auth::user()->sekolah_id)->schedule->where('type_schedule','direct')->where('created_at',Carbon::today())->count();
        $countRealtime = $this->where('sekolah_id',Auth::user()->sekolah_id)->schedule->where('type_schedule','realtime')->where('created_at',Carbon::today())->count();

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