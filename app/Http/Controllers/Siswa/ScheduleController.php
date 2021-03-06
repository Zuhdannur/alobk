<?php


namespace App\Http\Controllers\Siswa;


use App\Http\Controllers\Controller;
use App\Schedule;
use Berkayk\OneSignal\OneSignalClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Firebase;

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
        if(!empty($request->consultant_id)) {
            $insert->consultant_id = $request->consultant_id;
        }
        $insert->title = $request->title;
        $insert->type_schedule = $request->type_schedule;
        $insert->desc = $request->desc;
        $insert->location = $request->location;
        if ($insert->type_schedule != 'realtime') {
            if ($this->isLessThanFiveMinutes($request->time)) {
                return Response::json([
                    'message' => 'Waktu tidak boleh masa lampau.'
                ], 201);
            }
        }

        //Jika pengajuan daring
        //Memakai waktu dari server, diharamkan menggunakan waktu dari client
        //thats why this statement alive
        if(!$request->has('time')) {
            $insert->time = Carbon::now();
        } else {
            $insert->time = $request->time;
        }


        //For daring only

        $insert->save();

        if ($insert) {
            $client = new OneSignalClient(
                'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
                'Y2QyMTVhMzMtOGVlOC00MjFiLThmNDctMTAzNzYwNDM2YWMy',
                'YzRiYzZlNjAtYmIwNC00MzJiLTk3NTYtNzBhNmU2ZTNjNDQx');

            $scheduleDetail = $this->schedule->where('id', $insert->id)->with('requester')->first();
            //["field" => "tag", "key" => "schedule_notif", "relation" => "=", "value" => "on"],

            $client->sendNotificationUsingTags(
                "Mendapatkan pengajuan baru dari ".Auth::user()->name,
                array(
                    ["field" => "tag", "key" => "user_type", "relation" => "=", "value" => "guru"],
                    ["field" => "tag", "key" => "sekolah_id", "relation" => "=", "value" => Auth::user()->sekolah_id]
                ),
                $url = null,
                $data = [
                    "id" => $insert->id,
                    "data" => $scheduleDetail,
                    "type" => "schedule",
                    "detail" => "guru_receive_post"
                ],
                $buttons = null,
                $schedule = null,
                $headings = "Pengajuan baru"
            );
        }

        return Response::json($insert, 200);
    }

    public function get($id) {
        $schedule = $this->schedule->find($id);

        return Response::json($schedule, 200);
    }

    public function accept($id, Request $request) {
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

        if($schedule->type_schedule == 'realtime') {
            $update = tap($schedule)->update([
                'active' => 1,
                'start' => 1,
                'consultant_id' => $schedule->consultant_id
            ]);
        } else {
            $update = tap($schedule)->update([
                'active' => 1,
                'consultant_id' => $schedule->consultant_id
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

        $getObject = $this->schedule->where('id', $update->id)->with('requester')->first();

        $scheduleInfo = $this->schedule->find($id);

        if($schedule->type_schedule != 'direct') {
            $data = [
                'active' => true,
                'chatId' => $id,

                'consultantActive' => $scheduleInfo->consultant_id.'_true',
                'consultantId' => "$scheduleInfo->consultant_id",
                'desc' => $scheduleInfo->desc,
                'requesterActive' => $scheduleInfo->requester_id.'_true',
                'requesterId' => "$scheduleInfo->requester_id",
                'title' => $scheduleInfo->title,
                'time' => (int)$request->time,
                'typeSchedule' => $scheduleInfo->type_schedule
            ];

            Firebase::set('/room/metainfo/'.$id, $data);
        }

        $client->sendNotificationToExternalUser(
            "Perubahan waktu konseling dengan pengajuan id #$update->id disetujui oleh siswa.",
            $schedule->consultant_id,
            $url = null,
            $data = [
                "id" => $update->id,
                "data" => $getObject,
                "type" => "schedule",
                "detail" => "guru_receive_accept"
            ],
            $buttons = null,
            $schedule = null,
            $headings = "Perubahan waktu konseling disetujui"
        );

        return Response::json([
            'data' => $update,
            'message' => 'Pengajuan berhasil disetujui.'
        ], 200);
    }

    public function cancelRequestSchedule($id) {
        $schedule = $this->schedule->find($id);

        if ($schedule->expired == 1) {
            return Response::json([
                'message' => 'Pengajuan telah kedaluwarsa.'
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

        $cancel = tap($schedule)->update(['canceled' => 1]);
        $getObject = $this->schedule->where('id', $cancel->id)->with('requester')->first();

        $client = new OneSignalClient(
            'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
            'Y2QyMTVhMzMtOGVlOC00MjFiLThmNDctMTAzNzYwNDM2YWMy',
            'YzRiYzZlNjAtYmIwNC00MzJiLTk3NTYtNzBhNmU2ZTNjNDQx');

        $client->sendNotificationToExternalUser(
            "Permintaan perubahan waktu konseling dibatalkan oleh siswa.",
            $schedule->consultant_id,
            $url = null,
            $data = [
                "id" => $cancel->id,
                "data" => $getObject,
                "type" => "schedule",
                "detail" => "guru_receive_cancel_request"
            ],
            $buttons = null,
            $schedule = null,
            $headings = "Permintaan perubahan waktu konseling dibatalkan"
        );

        if (!$cancel) {
            return Response::json([
                'message' => 'Gagal membatalkan pengajuan.'
            ], 201);
        }

        return Response::json([
            'id' => $id,
            'message' => 'Berhasil membatalkan pengajuan.'
        ], 200);
    }

    public function deleteHelper($id) {
        $delete = $this->schedule->find($id)->delete();
        return Response::json(['message' => 'Berhasil menghapus'], 200);
    }

    public function updateHelper($id, Request $request) {
        $schedule = $this->schedule->find($id)->update([
            'updated_new_time' => $request->new,
            'updated_old_time' => $request->old
        ]);
        return Response::json(['message' => 'Berhasil update'], 200);
    }

    public function updateTimeHelper($id, Request $request) {
        $schedule = $this->schedule->find($id)->update([
            'time' => $request->time
        ]);
        return Response::json(['message' => 'Berhasil update'], 200);
    }

    public function insertHelper($id, Request $request) {
        $delete = $this->schedule->find($id)->insert(

        );
        return Response::json(['message' => 'Berhasil menghapus'], 200);
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

        if ($schedule->type_schedule != 'realtime') {
            if ($this->isLessThanFiveMinutes($request->time)) {
                return Response::json([
                    'message' => 'Waktu tidak boleh masa lampau.'
                ], 201);
            }
        }

        $update = tap($schedule)
            ->update([
                'title' => $request->title,
                'desc' => $request->desc,
                'time' => $request->time,
                'location' => $request->location
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

   public function all(Request $request)
   {
       $schedule = $this->schedule->all();
       return Response::json($schedule, 200);
   }

    public function update() {
        $schedule1 = Schedule::where('type_schedule', 'daring1')->update(['type_schedule' => 'daring']);
        $schedule2 = Schedule::where('type_schedule', 'realtime1')->update(['type_schedule' => 'realtime']);

        if($schedule1 && $schedule2) {
            return Response::json(['message' => 'Berhasil'], 200);
        }
        return Response::json(['message' => 'Gagal'], 201);
    }

    public function jadwalPending(Request $request)
    {
        $data = $this->schedule->orderDescCreated()->requesterSchedule()->withConsultant();

        $data = $data->isDirect()->isPending();

        $data = $data->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function jadwalAktif(Request $request)
    {
        $data = $this->schedule->orderDescCreated()->requesterSchedule()->withConsultant();

        $data = $data->isDirect()->isActive()->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function obrolanPending(Request $request)
    {
        $data = $this->schedule->orderDescCreated()->requesterSchedule()->withConsultant();

        $data = $data->isOnline()->isPending()->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function obrolanAktif(Request $request)
    {
        $data = $this->schedule->orderDescCreated()->requesterSchedule()->withConsultant();

        $data = $data->isOnline()->isActive()->paginate($request->per_page);

        return Response::json($data, 200);
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

        if (!$update) {
            return Response::json([
                'message' => 'Pengajuan gagal diselesaikan.'
            ], 201);
        }

        $client = new OneSignalClient(
            'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
            'Y2QyMTVhMzMtOGVlOC00MjFiLThmNDctMTAzNzYwNDM2YWMy',
            'YzRiYzZlNjAtYmIwNC00MzJiLTk3NTYtNzBhNmU2ZTNjNDQx');

        $getObject = $this->schedule->where('id', $update->id)->with('requester')->first();

        if($schedule->type_schedule != 'direct') {
            $data = [
                'active' => false,
                'consultantActive' => "$schedule->consultant_id"."_false",
                'requesterActive' => "$schedule->requester_id"."_false"
            ];
            Firebase::update('/room/metainfo/'.$id, $data);
        }

        $client->sendNotificationToExternalUser(
            "Pengajuan dengan id #".$update->id." telah diselesaikan oleh siswa.",
            $update->consultant_id,
            $url = null,
            $data = [
                "id" => $update->id,
                "data" => $getObject,
                "type" => "schedule",
                "detail" => "guru_receive_finish"
            ],
            $buttons = null,
            $schedule = null,
            $headings = "Pengajuan telah diselesaikan"
        );

        return Response::json([
            'id' => $update->id,
            'message' => 'Pengajuan berhasil diselesaikan.'
        ], 200);
    }

    public function riwayat(Request $request)
    {
        $schedule = $this->schedule;
        $q = $schedule->where(function($query) {
            $query->requesterIsMe();
        })->where(function($query) {
            $query->isHistory();
        })->withConsultant()->withFeedback();

        $schedule = $q
            ->orderDescUpdated()
            ->paginate($request->per_page);

        return Response::json($schedule, 200);
    }

    public function getScheduleFinished() {
        $totalObrolan = $this->schedule->justFinish()->requesterIsMe()->isOnline()->count();
        $totalDirect = $this->schedule->justFinish()->requesterIsMe()->isDirect()->count();

        return Response::json(['total_obrolan' => $totalObrolan,'total_direct' => $totalDirect], 200);
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
                'message' => 'Pengajuan ini telah diterima oleh guru.'
            ], 201);
        }

        if ($schedule->start == 1) {
            return Response::json([
                'message' => 'Pengajuan ini telah dimulai.'
            ], 201);
        }

        $cancel = $this->schedule->find($id)->update(['canceled' => 1]);

        if (!$cancel) {
            return Response::json([
                'message' => 'Gagal membatalkan pengajuan.'
            ], 201);
        }

        return Response::json([
            'message' => 'Berhasil membatalkan pengajuan.'
        ], 200);
    }

    public function getKonselingAktif() {
        $data = $this->schedule->requesterIsMe()->isScheduleIsRunning()->withConsultant();
        return Response::json($data->get() , 200);
    }

}
