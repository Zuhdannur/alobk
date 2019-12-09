<?php


namespace App\Http\Controllers\Supervisor;

use App\Diary;
use App\Schedule;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use PDF;

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
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1],
        ])->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1],
        ])->count();
        $countDaring = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where('type_schedule','daring')->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1],
        ])->count();
        $countDirect = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where('type_schedule','direct')->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1],
        ])->count();
        $countRealtime = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where('type_schedule','realtime')->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1],
        ])->count();

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
        })->whereDate('created_at', Carbon::today());

        $schedule = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->whereDate('created_at', Carbon::today())->count();

        $totalPending = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->whereDate('created_at', Carbon::today())->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',0],
            ['start','=',0],
        ])->count();

        $totalActive = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->whereDate('created_at', Carbon::today())->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',1]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $totalSelesai = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->whereDate('created_at', Carbon::today())->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1],
        ])->count();

        $countDaring = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->whereDate('created_at', Carbon::today())->where('type_schedule','daring')->count();
        $countDirect = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->whereDate('created_at', Carbon::today())->where('type_schedule','direct')->count();
        $countRealtime = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->whereDate('created_at', Carbon::today())->where('type_schedule','realtime')->count();

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

    public function generateDiary() {
        // $diary = Diary::withAndWhereHas('user', function($query) {
        //     $query->where('sekolah_id', Auth::user()->sekolah_id);
        // });

        // $diary = Diary::withAndWhereHas('user', function($query) {
        //     $query->where('sekolah_id', Auth::user()->sekolah_id);
        // });
        $diary = Diary::with('user')->get();
        $pdf = PDF::loadView('diari_pdf', ['diari' => $diary])->setPaper('a4','portrait');
        $fileName = 'testing';
        // return Response::download($file);
        return $pdf->stream($fileName. '.pdf'); 
    }

    public function generateSchedule() {
        $schedule = Schedule::where('finish', 1)->with('consultant', 'requester','feedback')->get();

        $pdf = PDF::loadView('konseling', ['konseling' => $schedule])->setPaper('a2','portrait');
        $fileName = 'testing2';
        // return Response::download($file);
        return $pdf->stream($fileName. '.pdf'); 
    }

}
