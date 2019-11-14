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
                    ->where('active', 1)
                    ->where('start', 1);
            }
        }

        $data = $schedule->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function accept(Request $request, $id) {
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

        $update = $schedule->update([
            'active' => 1,
            'consultant_id' => Auth::user()->id
        ]);

        if (!$update) {
            return Response::json([
                "message" => "Gagal menerima."
            ], 201);
        }

        $schedule = $this->schedule->find($id)->with('consultant')->first();

        // if($schedule->type_schedule == 'direct') {
        //     $this->sendNotificationToDirect();
        // }

        // if($schedule->type_schedule == 'realtime') {
        //     $this->sendNotificationToRealtime();
        // }

        // if($schedule->type_schedule == 'daring') {
        //     $this->sendNotificationToDaring();
        // }
//        $senderName = $this->user->where('id', $schedule['consultant_id'])->first()->name;
//
//        $result['type'] = "accept";
//        $result['schedule_id'] = $schedule['id'];
//        $result['requester_id'] = $schedule['requester_id'];
//        $result['consultant_id'] = $schedule['consultant_id'];
//        $result['title'] = 'Pengajuanmu telah diterima';
//        $result['body'] = "Pengajuan " . $schedule['title'] . " telah diterima oleh " . $senderName;
//        $result['read'] = 0;

//            Helper::sendNotificationToSingle($result);

        // $data['requester_id'] = $schedule['requester_id'];
        // $data['title'] = 'Pengajuanmu telah diterima.';
        // $data['body'] = 'Pengajuan '.$schedule['title']. ' telah diterima oleh '. $schedule['consultant']['name'];
        // $data['id_user'] = $schedule['requester_id'];
        // $data['type'] = 'accept';
        // Helper::storeDataNotification($data);
        $client = new OneSignalClient(
            'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
            'Y2QyMTVhMzMtOGVlOC00MjFiLThmNDctMTAzNzYwNDM2YWMy',
            'YzRiYzZlNjAtYmIwNC00MzJiLTk3NTYtNzBhNmU2ZTNjNDQx');

        $client->sendNotificationToExternalUser(
            "Pengajuanmu diterima",
            $schedule->consultant_id,
            $url = null,
            $data = null,
            $buttons = null,
            $schedule = null
        );

        return Response::json($schedule, 200);
    }

}
