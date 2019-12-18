<?php


namespace App\Http\Controllers\Supervisor;

use App\Diary;
use App\Schedule;
use App\User;
use App\Sekolah;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use PDF;
use Firebase;

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
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->whereHas('consultant', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->where('finish', 1)->count();

        // $schedule = Schedule::whereHas('requester', function($query) {
        //     $query->where('sekolah_id',Auth::user()->sekolah_id);
        // })->where([
        //     ['pending','=',1],
        //     ['expired','=',0],
        //     ['canceled','=',0],
        //     ['finish','=',1],
        //     ['active','=',1],
        //     ['start','=',1],
        // ])->where([
        //     ['pending','=',1],
        //     ['expired','=',0],
        //     ['canceled','=',0],
        //     ['finish','=',1],
        //     ['active','=',1],
        //     ['start','=',1],
        // ])->count();
        $countDaring = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->whereHas('consultant', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->where(function($query){
            $query->where('type_schedule', 'daring')->where('finish', 1);
        })->count();

        $countRealtime = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->whereHas('consultant', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->where(function($query){
            $query->where('type_schedule', 'realtime')->where('finish', 1);
        })->count();

        $countDirect = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->whereHas('consultant', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->where(function($query){
            $query->where('type_schedule', 'direct')->where('finish', 1);
        })->count();
        
        // $countDaring = Schedule::whereHas('requester', function($query) {
        //     $query->where('sekolah_id',Auth::user()->sekolah_id);
        // })->where('type_schedule','daring')->where([
        //     ['pending','=',1],
        //     ['expired','=',0],
        //     ['canceled','=',0],
        //     ['finish','=',1],
        //     ['active','=',1],
        //     ['start','=',1],
        // ])->count();
        // $countDirect = Schedule::whereHas('requester', function($query) {
        //     $query->where('sekolah_id',Auth::user()->sekolah_id);
        // })->where('type_schedule','direct')->where([
        //     ['pending','=',1],
        //     ['expired','=',0],
        //     ['canceled','=',0],
        //     ['finish','=',1],
        //     ['active','=',1],
        //     ['start','=',1],
        // ])->count();
        // $countRealtime = Schedule::whereHas('requester', function($query) {
        //     $query->where('sekolah_id',Auth::user()->sekolah_id);
        // })->where('type_schedule','realtime')->where([
        //     ['pending','=',1],
        //     ['expired','=',0],
        //     ['canceled','=',0],
        //     ['finish','=',1],
        //     ['active','=',1],
        //     ['start','=',1],
        // ])->count();

        return Response::json([
            'total_schedule' => $schedule,
            'total_daring' => $countDaring,
            'total_direct' => $countDirect,
            'total_realtime' => $countRealtime,
        ]);
    }

    public function getTotalToday() {
        $schedule = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->createdToday()->count();

        $totalPending = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->createdToday()->isPending()->count();

        $totalActive = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->createdToday()->isActive()->count();

        $totalSelesai = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->createdToday()->isFinish()->count();

        $totalCanceled = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->createdToday()->isCanceled()->count();

        $lastData = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->createdToday()->latest()->first();

        $getLastData = $lastData == null ? null : $lastData->readable_created_at;

        return Response::json([
            'total_schedule' => $schedule,

            'total_pending' => $totalPending,
            'total_aktif' => $totalActive,
            'total_selesai' => $totalSelesai,
            'total_batalkan' => $totalCanceled,

            'last_data' => $getLastData
        ]);
    }

    public function getScheduleByAktif() {
        $direct = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',1],
            ['type_schedule', 'direct']
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $realtime = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',1],
            ['type_schedule', 'realtime']
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $daring = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',1],
            ['type_schedule', 'daring']
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        return Response::json([
            'total_daring' => $daring,
            'total_realtime' => $realtime,
            'total_direct' => $direct
        ]);

    }

    public function getScheduleByPending() {
        $direct = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',0],
            ['start','=',0]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $realtime = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',0],
            ['start','=',0]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $daring = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',0],
            ['active','=',0],
            ['start','=',0]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        return Response::json([
            'total_daring' => $daring,
            'total_realtime' => $realtime,
            'total_direct' => $direct
        ]);

    }

    public function getScheduleByEnded() {
        $direct = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $realtime = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $daring = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',0],
            ['finish','=',1],
            ['active','=',1],
            ['start','=',1]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        return Response::json([
            'total_daring' => $daring,
            'total_realtime' => $realtime,
            'total_direct' => $direct
        ]);

    }

    public function getScheduleByCanceled() {
        $direct = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',1],
            ['finish','=',0],
            ['active','=',0],
            ['start','=',0]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $realtime = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',1],
            ['finish','=',0],
            ['active','=',0],
            ['start','=',0]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        $daring = Schedule::whereHas('requester', function($query) {
            $query->where('sekolah_id',Auth::user()->sekolah_id);
        })->where([
            ['pending','=',1],
            ['expired','=',0],
            ['canceled','=',1],
            ['finish','=',0],
            ['active','=',0],
            ['start','=',0]
            // ['start','=',0], START CAN BE 0 OR 1
        ])->count();

        return Response::json([
            'total_daring' => $daring,
            'total_realtime' => $realtime,
            'total_direct' => $direct
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
        $diary = Diary::whereHas('user', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->get();

        $timeGenerated = Carbon::now()->format('d/m/Y H:i:s');
        $timeForFileGenerate = Carbon::now()->format('dmYHs');
        $namaSekolah = Sekolah::where('id', Auth::user()->sekolah_id)->first()->nama_sekolah;

        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
        ->loadView('diari_pdf', 
            [
                'diari' => $diary,
                'time' => $timeGenerated,
                'nama_sekolah' => $namaSekolah
            ]
        )->setPaper('a4','portrait');

        $fileName = 'rekap_diari_'.$namaSekolah."";
        // return Response::download($file);
        return $pdf->download("$fileName.pdf");
    }

    public function generateScheduleTest() {
        $schedule = Schedule::where('finish', 1)
        ->whereHas('requester', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->whereHas('consultant', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->with('consultant', 'requester','feedback')->get();

        $timeGenerated = Carbon::now()->format('d/m/Y H:i:s');
        $timeForFileGenerate = Carbon::now()->format('dmYHs');
        $namaSekolah = Sekolah::where('id', Auth::user()->sekolah_id)->first()->nama_sekolah;

        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
        ->loadView('konselingtest', 
            [
                'konseling' => $schedule,
                'time' => $timeGenerated,
                'nama_sekolah' => $namaSekolah
            ]
        )->setPaper('a2','portrait');
        $fileName = 'rekap_konseling_'.strtolower(str_replace(' ','_',$namaSekolah))."_".$timeForFileGenerate;
        return $pdf->download("$fileName.pdf");
    }

}
