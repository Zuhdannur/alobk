<?php

namespace App\Http\Controllers\Admin;

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
}
