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
        })->with('consultant');

        if($request->has('status')) {
            if($request->status == 'pending') {
                $schedule = $schedule
                    ->where('canceled', 0)
                    ->where('expired', 0)
                    ->where('pending', 1)
                    ->where('finish', 0)
                    ->where('active', 0)
                    ->where('start', 0);
            }
            else if($request->status == 'aktif') {
                $schedule = $schedule
                    ->where('canceled', 0)
                    ->where('expired', 0)
                    ->where('pending', 1)
                    ->where('finish', 0)
                    ->where('active', 1);
            }
        }

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

    public function getStudentScheduleCount($id)
    {
        $total = $this->schedule->where('requester_id', $id)->count();

        return Response::json([
            "total" => $total
        ], 200);
    }

}
