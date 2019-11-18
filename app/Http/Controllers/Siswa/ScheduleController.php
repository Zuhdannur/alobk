<?php


namespace App\Http\Controllers\Siswa;


use App\Http\Controllers\Controller;
use App\Schedule;
use Berkayk\OneSignal\OneSignalClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ScheduleController extends Controller
{
    private $schedule;

    /**
     * ScheduleControlle constructor.
     * @param $schedule
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }

    public function post(Request $request)
    {
        $insert = $this->schedule;
        $insert->requester_id = Auth::user()->id;
        $insert->title = $request->title;
        $insert->type_schedule = $request->type_schedule;
        $insert->desc = $request->desc;
        $insert->location = $request->location;
        if ($insert->type_schedule != 'daring') {
            if ($this->isLessThanFiveMinutes($request->time)) {
                return Response::json([
                    'message' => 'Jeda waktu dari waktu sekarang disarankan minimal 5 menit.'
                ], 201);
            }
        }

        $insert->time = $request->time;
        $insert->save();

        if ($insert) {
            $client = new OneSignalClient(
                'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
                'Y2QyMTVhMzMtOGVlOC00MjFiLThmNDctMTAzNzYwNDM2YWMy',
                'YzRiYzZlNjAtYmIwNC00MzJiLTk3NTYtNzBhNmU2ZTNjNDQx');

            $client->sendNotificationUsingTags(
                "Mendapatkan pengajuan baru dari siswa.",
                array(
                    ["field" => "tag", "key" => "user_type", "relation" => "=", "value" => "guru"]
                ),
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );
        }

        return Response::json($insert, 200);
    }

    private function isLessThanFiveMinutes($time)
    {
        if (Carbon::parse($time)->lessThanOrEqualTo(Carbon::now())) {
            return true;
        }
        return false;
    }

    public function put(Request $request, $id)
    {
        $schedule = $this->schedule->find($id);

        if ($schedule->expired == 1) {
            return Response::json([
                'message' => 'Pengajuan telah kedaluwarsa.'
            ], 201);
        }

        if ($schedule->canceled == 1) {
            return Response::json([
                'message' => 'Pengajuan ini telah dibatalkan.'
            ], 201);
        }

        if ($schedule->active == 1) {
            return Response::json([
                'message' => 'Pengajuan ini telah selesai.'
            ], 201);
        }

        if ($schedule->active == 1) {
            return Response::json([
                'message' => 'Pengajuan ini telah diterima oleh guru.'
            ], 201);
        }

        if ($schedule->start == 1) {
            return Response::json([
                'message' => 'Pengajuan ini telah dimulai.'
            ], 201);
        }

        if ($this->isLessThanFiveMinutes($request->time)) {
            return Response::json([
                'message' => 'Jeda waktu dari waktu sekarang disarankan minimal 5 menit.'
            ], 201);
        }

        $update = tap($schedule)
            ->update([
                'title' => $request->title,
                'desc' => $request->desc,
                'time' => $request->time
            ]);
//            ->where('requester_id', Auth::user()->id)
//            ->where('pending', 1)
//            ->where('expired', 0)
//            ->where('canceled', 0)
//            ->where('finish', 0)
//            ->where('active', 0)
//            ->where('start', 0)


        return Response::json([
            "data" => $update,
            "message" => 'Pengajuan berhasil disunting.'
        ], 200);
    }

//    public function all(Request $request)
//    {
//        $data = $this->schedule->orderBy('created_at', 'desc')->whereHas('requester', function ($query) {
//            $query->where('role', 'siswa')->where('sekolah_id', Auth::user()->sekolah_id);
//        })->with('consultant');
//
//        if ($request->has('type_schedule')) {
//            if ($request->type_schedule == 'online') {
//                $data = $data
//                    ->where('canceled', 0)
//                    ->where('expired', 0)
//                    ->where('pending', 1)
//                    ->where('finish', 0)
//                    ->where('active', 0)
//                    ->where('start', 0)
//                    ->where('type_schedule', 'daring')->orWhere('type_schedule', 'realtime');
//            } else {
//                if ($request->has('status')) {
//                    if ($request->status == 'pending') {
//                        $data = $data
//                            ->where('canceled', 0)
//                            ->where('expired', 0)
//                            ->where('pending', 1)
//                            ->where('finish', 0)
//                            ->where('active', 0)
//                            ->where('start', 0);
//                    } else if ($request->status == 'aktif') {
//                        $data = $data
//                            ->where('canceled', 0)
//                            ->where('expired', 0)
//                            ->where('pending', 1)
//                            ->where('finish', 0)
//                            ->where('active', 1);
//                    }
//                }
//
//                $data = $data->where('type_schedule', $request->type_schedule);
//            }
//        }
//
//        $data = $data->paginate($request->per_page);
//
//        return Response::json($data, 200);
//    }

    public function jadwalPending(Request $request)
    {
        $data = $this->schedule->orderBy('created_at', 'desc')->whereHas('requester', function ($query) {
            $query->where('role', 'siswa')->where('sekolah_id', Auth::user()->sekolah_id);
        })->with('consultant');

        $data = $data
            ->where('type_schedule', 'direct')
            ->where('canceled', 0)
            ->where('expired', 0)
            ->where('pending', 1)
            ->where('finish', 0)
            ->where('active', 0)
            ->where('start', 0);

        $data = $data->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function jadwalAktif(Request $request)
    {
        $data = $this->schedule->orderBy('created_at', 'desc')->whereHas('requester', function ($query) {
            $query->where('role', 'siswa')->where('sekolah_id', Auth::user()->sekolah_id);
        })->with('consultant');

        $data = $data
            ->where('type_schedule', 'direct')
            ->where('canceled', 0)
            ->where('expired', 0)
            ->where('pending', 1)
            ->where('finish', 0)
            ->where('active', 1);

        $data = $data->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function obrolanPending(Request $request)
    {
        $data = $this->schedule->orderBy('created_at', 'desc')->whereHas('requester', function ($query) {
            $query->where('role', 'siswa')->where('sekolah_id', Auth::user()->sekolah_id);
        })->with('consultant');

        $data = $data
            ->where('type_schedule','!=', 'direct')
            ->where('canceled', 0)
            ->where('expired', 0)
            ->where('pending', 1)
            ->where('finish', 0)
            ->where('active', 0)
            ->where('start', 0);

        $data = $data->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function finish($id)
    {
        $update = $this->schedule->where('id', $id)->update([
            'finish' => 1
        ]);

        if (!$update) {
            return Response::json([
                'message' => 'Pengajuan gagal diselesaikan.'
            ], 201);
        }

        return Response::json([
            'message' => 'Pengajuan berhasil diselesaikan.'
        ], 201);
    }

    public function riwayat()
    {
        $schedule = $this->schedule->where('expired', 1)->get();
        return Response::json($schedule, 200);
    }

    public function cancel($id)
    {
        $schedule = $this->schedule->find($id);

        if ($schedule->expired == 1) {
            return Response::json([
                'message' => 'Pengajuan telah kedaluwarsa.'
            ], 201);
        }

        if ($schedule->canceled == 1) {
            return Response::json([
                'message' => 'Pengajuan ini telah dibatalkan.'
            ], 201);
        }

        if ($schedule->active == 1) {
            return Response::json([
                'message' => 'Pengajuan ini telah selesai.'
            ], 201);
        }

        if ($schedule->active == 1) {
            return Response::json([
                'message' => 'Pengajuan ini telah diterima oleh guru.'
            ], 201);
        }

        if ($schedule->start == 1) {
            return Response::json([
                'message' => 'Pengajuan ini telah dimulai.'
            ], 201);
        }

        $cancel = $schedule
            ->where('pending', 1)
            ->update([
                'canceled' => 1
            ], 201);

        if (!$cancel) {
            return Response::json([
                'message' => 'Gagal membatalkan pengajuan.'
            ], 201);
        }

        return Response::json([
            'message' => 'Berhasil membatalkan pengajuan.'
        ], 200);
    }

}
