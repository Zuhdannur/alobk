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
            $query->sameSchool();
        })->whereHas('consultant', function($query) {
            $query->sameSchool();
        })->justFinish()->count();

        $countDaring = Schedule::whereHas('requester', function($query) {
            $query->sameSchool();
        })->whereHas('consultant', function($query) {
            $query->sameSchool();
        })->where(function($query){
            $query->isDaring()->justFinish();
        })->count();

        $countRealtime = Schedule::whereHas('requester', function($query) {
            $query->sameSchool();
        })->whereHas('consultant', function($query) {
            $query->sameSchool();
        })->where(function($query){
            $query->isRealtime()->justFinish();
        })->count();

        $countDirect = Schedule::whereHas('requester', function($query) {
            $query->sameSchool();
        })->whereHas('consultant', function($query) {
            $query->sameSchool();
        })->where(function($query){
            $query->isDirect()->justFinish();
        })->count();
        
        return Response::json([
            'total_schedule' => $schedule,
            'total_daring' => $countDaring,
            'total_direct' => $countDirect,
            'total_realtime' => $countRealtime,
        ]);
    }

    public function getTotalToday() {
        $schedule = Schedule::requesterSameSchool()->createdToday()->count();

        $totalPending = Schedule::requesterSameSchool()->createdToday()->isPending()->count();

        $totalActive = Schedule::requesterSameSchool()->createdToday()->isActive()->count();

        $totalSelesai = Schedule::requesterSameSchool()->createdToday()->isFinish()->count();

        $totalCanceled = Schedule::requesterSameSchool()->createdToday()->isCanceled()->count();

        $lastData = Schedule::requesterSameSchool()->createdToday()->latest()->first();

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

    public function getScheduleByAktif(Request $request) {

        if($request->tipe == 'active') {
            $direct = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isDirect()->isActive()->count();
            $realtime = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isRealtime()->isActive()->count();
            $daring = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isDaring()->isActive()->count();
        }

        else if($request->tipe == 'pending') {
            $direct = Schedule::requesterSameSchool()->createdToday()->isDirect()->isPending()->count();
            $realtime = Schedule::requesterSameSchool()->createdToday()->isRealtime()->isPending()->count();
            $daring = Schedule::requesterSameSchool()->createdToday()->isDaring()->isPending()->count();
        }

        else if($request->tipe == 'finish') {
            $direct = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isDirect()->isFinish()->count();
            $realtime = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isRealtime()->isFinish()->count();
            $daring = Schedule::requesterSameSchool()->consultantSameSchool()->createdToday()->isDaring()->isFinish()->count();
        }

        else if($request->tipe == 'canceled') {
            $direct = Schedule::requesterSameSchool()->orConsultantSameSchool()->createdToday()->isDirect()->isCanceled()->count();
            $realtime = Schedule::requesterSameSchool()->orConsultantSameSchool()->createdToday()->isRealtime()->isCanceled()->count();
            $daring = Schedule::requesterSameSchool()->orConsultantSameSchool()->createdToday()->isDaring()->isCanceled()->count();
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
