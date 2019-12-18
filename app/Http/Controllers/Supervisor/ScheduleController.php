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
        $schedule = Schedule::requesterSameSchool()->consultantSameSchool()->justFinish()->count();

        $countDaring = Schedule::requesterSameSchool()->consultantSameSchool()->where(function($query){
            $query->isDaring()->justFinish();
        })->count();

        $countRealtime = Schedule::requesterSameSchool()->consultantSameSchool()->where(function($query){
            $query->isRealtime()->justFinish();
        })->count();

        $countDirect = Schedule::requesterSameSchool()->consultantSameSchool()->where(function($query){
            $query->isDirect()->justFinish();
        })->count();
        
        return Response::json([
            'total_schedule' => $schedule,
            'total_daring' => $countDaring,
            'total_direct' => $countDirect,
            'total_realtime' => $countRealtime,
        ]);
    }

    public function getSchedule() {
        $schedule = Schedule::requesterSameSchool()->createdToday()->get();
        return Response::json($schedule, 200);
    }

    public function getTotalToday() {
        $schedule = Schedule::requesterSameSchool()->updatedToday()->count();

        $totalPending = Schedule::requesterSameSchool()->createdToday()->isPending()->count();

        $totalActive = Schedule::requesterSameSchool()->updatedToday()->isActive()->count();

        $totalSelesai = Schedule::requesterSameSchool()->updatedToday()->isFinish()->count();

        $totalCanceled = Schedule::requesterSameSchool()->updatedToday()->isCanceled()->count();

        $totalOutdated = Schedule::requesterSameSchool()->updatedToday()->isExpired()->count();

        $lastData = Schedule::requesterSameSchool()->createdToday()->latest()->first();

        $getLastData = $lastData == null ? null : $lastData->readable_created_at;

        return Response::json([
            'total_schedule' => $schedule,

            'total_pending' => $totalPending,
            'total_aktif' => $totalActive,
            'total_selesai' => $totalSelesai,
            'total_batalkan' => $totalCanceled,
            'total_kedaluwarsa' => $totalOutdated,

            'last_data' => $getLastData
        ]);
    }

    public function getScheduleByAktif(Request $request) {

        if($request->tipe == 'aktif') {
            $direct = Schedule::requesterSameSchool()->consultantSameSchool()->updatedToday()->isDirect()->isActive()->count();
            $realtime = Schedule::requesterSameSchool()->consultantSameSchool()->updatedToday()->isRealtime()->isActive()->count();
            $daring = Schedule::requesterSameSchool()->consultantSameSchool()->updatedToday()->isDaring()->isActive()->count();
        }

        else if($request->tipe == 'pending') {
            $direct = Schedule::requesterSameSchool()->createdToday()->isDirect()->isPending()->count();
            $realtime = Schedule::requesterSameSchool()->createdToday()->isRealtime()->isPending()->count();
            $daring = Schedule::requesterSameSchool()->createdToday()->isDaring()->isPending()->count();
        }

        else if($request->tipe == 'selesai') {
            $direct = Schedule::requesterSameSchool()->consultantSameSchool()->updatedToday()->isDirect()->isFinish()->count();
            $realtime = Schedule::requesterSameSchool()->consultantSameSchool()->updatedToday()->isRealtime()->isFinish()->count();
            $daring = Schedule::requesterSameSchool()->consultantSameSchool()->updatedToday()->isDaring()->isFinish()->count();
        }

        else if($request->tipe == 'dibatalkan') {
            $direct = Schedule::requesterSameSchool()->orConsultantSameSchool()->updatedToday()->isDirect()->isCanceled()->count();
            $realtime = Schedule::requesterSameSchool()->orConsultantSameSchool()->updatedToday()->isRealtime()->isCanceled()->count();
            $daring = Schedule::requesterSameSchool()->orConsultantSameSchool()->updatedToday()->isDaring()->isCanceled()->count();
        }

        else if($request->tipe == 'kedaluwarsa') {
            $direct = Schedule::requesterSameSchool()->orConsultantSameSchool()->updatedToday()->isDirect()->isExpired()->count();
            $realtime = Schedule::requesterSameSchool()->orConsultantSameSchool()->updatedToday()->isRealtime()->isExpired()->count();
            $daring = Schedule::requesterSameSchool()->orConsultantSameSchool()->updatedToday()->isDaring()->isExpired()->count();
        }

        else {
            throw new \Exception('Parameter tipe harus diisi/benar.');
        }

        return Response::json([
            'total_daring' => $daring,
            'total_realtime' => $realtime,
            'total_direct' => $direct
        ]);
    }

    public function getScheduleByPending() {
        $direct = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isDirect()->isPending()->count();
        $realtime = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isRealtime()->isPending()->count();
        $daring = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isDaring()->isPending()->count();

        return Response::json([
            'total_daring' => $daring,
            'total_realtime' => $realtime,
            'total_direct' => $direct
        ]);

    }

    public function getScheduleByEnded() {
        $direct = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isDirect()->isFinish()->count();
        $realtime = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isRealtime()->isFinish()->count();
        $daring = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isDaring()->isFinish()->count();

        return Response::json([
            'total_daring' => $daring,
            'total_realtime' => $realtime,
            'total_direct' => $direct
        ]);
    }

    public function getScheduleByCanceled() {
        $direct = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isDirect()->isCanceled()->count();
        $realtime = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isRealtime()->isCanceled()->count();
        $daring = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isDaring()->isCanceled()->count();

        return Response::json([
            'total_daring' => $daring,
            'total_realtime' => $realtime,
            'total_direct' => $direct
        ]);
    }

    public function generateDiary() {
        $diary = Diary::userSameSchool()->get();

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
        $schedule = Schedule::justFinish()->requesterSameSchool()->consultantSameSchool()->with('consultant', 'requester','feedback')->get();

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
