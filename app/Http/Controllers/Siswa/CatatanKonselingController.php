<?php


namespace App\Http\Controllers\Siswa;


use App\CatatanKonseling;
use App\Http\Controllers\Controller;
use App\Schedule;
use Berkayk\OneSignal\OneSignalClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class CatatanKonselingController extends Controller
{
    private $catatan;

    /**
     * ScheduleControlle constructor.
     * @param $schedule
     */
    public function __construct(CatatanKonseling $catatan)
    {
        $this->$catatan = $catatan;
    }

    public function post(Request $request)
    {
        $data = $this->catatan;
        $data->schedule_id = $request->schedule_id;
        $data->save();

        return Response::json([
            'message' => 'Berhasil mengirim penilaian.'
        ], 200);
    }

}
