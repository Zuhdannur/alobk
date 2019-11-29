<?php


namespace App\Http\Controllers\Guru;


use App\Http\Controllers\Controller;
use Berkayk\OneSignal\OneSignalClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use App\Schedule;

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

    public function all(Request $request)
    {
        $schedule = $this->schedule->withAndWhereHas('requester', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        })->with('requester');

        if($request->has('orderBy')) {
            $schedule = $schedule->orderBy('id', 'desc');
        }

        if($request->has('type_schedule')) {
            if($request->type_schedule == 'online') {
                $schedule = $schedule->where('type_schedule', 'daring')->orWhere('type_schedule', 'realtime');
            } else {
                $schedule = $schedule->where('type_schedule', $request->type_schedule);
            }
        }



        $data = $schedule->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function accept($id) {
        $schedule = $this->schedule->find($id);

        if ($schedule->canceled != 0) {
            return Response::json(["message" => "Pengajuan ini telah dibatalkan."], 201);
        }

        if ($schedule->active != 0) {
            return Response::json([
                "message" => "Pengajuan telah diterima oleh guru lain."
            ], 201);
        }

        if ($schedule->expired != 0) {
            return Response::json([
                "message" => "Pengajuan telah kedaluwarsa."
            ], 201);
        }

        if($schedule->type_schedule == 'daring') {
            $update = tap($schedule)->update([
                'active' => 1,
                'start' => 1,
                'consultant_id' => Auth::user()->id
            ]);
        } else {
            $update = tap($schedule)->update([
                'active' => 1,
                'consultant_id' => Auth::user()->id
            ]);
        }

        if (!$update) {
            return Response::json([
                "message" => "Gagal menerima."
            ], 201);
        }

        $client = new OneSignalClient(
            'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
            'Y2QyMTVhMzMtOGVlOC00MjFiLThmNDctMTAzNzYwNDM2YWMy',
            'YzRiYzZlNjAtYmIwNC00MzJiLTk3NTYtNzBhNmU2ZTNjNDQx');

        $client->sendNotificationToExternalUser(
            "Pengajuanmu diterima",
            $schedule->requester_id,
            $url = null,
            $data = null,
            $buttons = null,
            $schedule = null
        );

        return Response::json([
            'data' => $update,
            'message' => 'Pengajuan berhasil diterima.'
        ], 200);
    }

    public function updateThenAccept(Request $request, $id) {
        $schedule = $this->schedule->find($id);

        if ($schedule->canceled != 0) {
            return Response::json(["message" => "Pengajuan ini telah dibatalkan."], 201);
        }

        if ($schedule->active != 0) {
            return Response::json([
                "message" => "Pengajuan telah diterima oleh guru lain."
            ], 201);
        }

        if ($schedule->expired != 0) {
            return Response::json([
                "message" => "Pengajuan telah kedaluwarsa."
            ], 201);
        }

        $update = tap($schedule)->update([
            'time' => $request->time,
            'active' => 1,
            'consultant_id' => Auth::user()->id
        ]);

        if (!$update) {
            return Response::json([
                "message" => "Gagal menerima."
            ], 201);
        }

        $client = new OneSignalClient(
            'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
            'Y2QyMTVhMzMtOGVlOC00MjFiLThmNDctMTAzNzYwNDM2YWMy',
            'YzRiYzZlNjAtYmIwNC00MzJiLTk3NTYtNzBhNmU2ZTNjNDQx');

        $client->sendNotificationToExternalUser(
            "Pengajuanmu diterima",
            $schedule->requester_id,
            $url = null,
            $data = array(
                ["field" => "tag", "key" => "message", "relation" => "=", "value" => "pengajuan berhasil diterima dengan tanggal " + $update->time]
            ),
            $buttons = null,
            $schedule = null
        );

        return Response::json([
            'data' => $update,
            'message' => 'Pengajuan berhasil diterima.'
        ], 200);
    }

    public function finish($id)
    {
        $schedule = $this->schedule->find($id);

        if($schedule->finish == 1) {
            return Response::json([
                'message' => 'Pengajuan ini telah diselesaikan.'
            ], 201);
        }

        $update = tap($schedule)->update([
            'finish' => 1
        ]);

        $client = new OneSignalClient(
            'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
            'Y2QyMTVhMzMtOGVlOC00MjFiLThmNDctMTAzNzYwNDM2YWMy',
            'YzRiYzZlNjAtYmIwNC00MzJiLTk3NTYtNzBhNmU2ZTNjNDQx');

        $client->sendNotificationToExternalUser(
            "Pengajuan telah diselesaikan",
            $update->requester_id,
            $url = null,
            $data = null,
            $buttons = null,
            $schedule = null
        );


        if (!$update) {
            return Response::json([
                'message' => 'Pengajuan gagal diselesaikan.'
            ], 201);
        }

        return Response::json([
            'id' => $update->id,
            'message' => 'Pengajuan berhasil diselesaikan.'
        ], 200);
    }

    public function riwayat(Request $request)
    {
        $schedule = $this->schedule
            ->where('expired', 1)
            ->orWhere('canceled', 1)
            ->orWhere('finish', 1)
            ->orderBy('updated_at', 'desc')
            ->where('consultant_id', Auth::user()->id)
            ->with('requester')
            ->with('feedback')
            ->paginate($request->per_page);

        return Response::json($schedule, 200);
    }

    public function jadwalPending(Request $request) {
        $data = $this->schedule->orderBy('created_at', 'desc')->whereHas('requester', function ($query) {
            $query->where('role', 'siswa')->where('sekolah_id', Auth::user()->sekolah_id);
        })->with('requester');

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

    public function jadwalAktif(Request $request) {
        $data = $this->schedule->orderBy('created_at', 'desc')->whereHas('requester', function ($query) {
            $query->where('role', 'siswa')
            ->where('consultant_id', Auth::user()->id)
            ->where('sekolah_id', Auth::user()->sekolah_id);
        })->with('requester');

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

    public function obrolanPending(Request $request) {
        $data = $this->schedule->orderBy('created_at', 'desc')->whereHas('requester', function ($query) {
            $query->where('role', 'siswa')->where('sekolah_id', Auth::user()->sekolah_id);
        })->with('requester');

        $data = $data
            ->where('type_schedule','!=', 'direct')
            ->where('canceled', 0)
            ->where('expired', 0)
            ->where('finish', 0)
            ->where('active', 0)
            ->where('start', 0);

        $data = $data->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function getScheduleFinished() {
        $totalObrolan = $this->schedule->where('finish', 1)->where('consultant_id', Auth::user()->id)->where('type_schedule', '!=' , 'direct')->count();
        $totalDirect = $this->schedule->where('finish', 1)->where('consultant_id', Auth::user()->id)->where('type_schedule', 'direct')->count();

        $total_five = $this->schedule->whereHas('feedback', function($query) {
            $query->where('rating', 5);
        })->count();
        $total_four = $this->schedule->whereHas('feedback', function($query) {
            $query->where('rating', 4);
        })->count();
        $total_three = $this->schedule->whereHas('feedback', function($query) {
            $query->where('rating', 3);
        })->count();
        $total_two = $this->schedule->whereHas('feedback', function($query) {
            $query->where('rating', 2);
        })->count();
        $total_one = $this->schedule->whereHas('feedback', function($query) {
            $query->where('rating', 1);
        })->count();

        $calculate = (5*$total_five + 4*$total_four + 3*$total_three + 2*$total_two + 1*$total_one) / ($total_five+$total_four+$total_three+$total_two+$total_one);

        $total_schedule = $total_five+$total_four+$total_three+$total_two+$total_one;

        return Response::json([
            'total_obrolan' => $totalObrolan,
            'total_direct' => $totalDirect,
            'calculate' => number_format($calculate, 2, '.', ''),
            'total' => $total_schedule
        ], 200);
    }

    public function obrolanAktif(Request $request)
    {
        $data = $this->schedule->orderBy('created_at', 'desc')->whereHas('requester', function ($query) {
            $query->where('role', 'siswa')
                ->where('consultant_id', Auth::user()->id)
                ->where('sekolah_id', Auth::user()->sekolah_id);
        })->with('requester');

        $data = $data
            ->where('type_schedule', '!=', 'direct')
            ->where('canceled', 0)
            ->where('expired', 0)
            ->where('pending', 1)
            ->where('finish', 0)
            ->where('active', 1);

        $data = $data->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function getStudentScheduleCount($id)
    {
        $total = $this->schedule->where('requester_id', $id)->count();

        return Response::json([
            "total" => $total
        ], 200);
    }

}
