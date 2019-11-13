<?php


namespace App\Http\Controllers\Siswa;


use App\Http\Controllers\Controller;
use App\Schedule;
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
        $data = $this->schedule;
        $data->requester_id = Auth::user()->id;
        $data->title = $request->title;
        $data->type_schedule = $request->type_schedule;
        $data->desc = $request->desc;
        $data->location = $request->location;
        $data->time = $request->time;
        $data->save();

        return Response::json($data, 200);
    }

    public function put(Request $request, $id)
    {
        if ($request->type_schedule == 'daring') {
            $update = $this->schedule->where('id', $id)
                ->where('requester_id', Auth::user()->id)
                ->where('active', 0)
                ->where('pending', 1)
                ->update([
                    'title' => $request->title,
                    'desc' => $request->desc
                ]);

            if (!$update) {
                return Response::json([
                    "message" => 'Gagal menyunting jadwal.'
                ], 201);
            }
            return Response::json([
                "message" => 'Jadwal berhasil disunting.'
            ], 200);
        } else {
            //Direct dan Realtime
            if ($this->schedule->time->isPast()) {
                return Response::json(["message" => 'Pengajuan telah kedaluwarsa.'], 201);
            }

            $update = $this->schedule->where('id', $id)
                ->where('requester_id', Auth::user()->id)
                ->where('expired', 0)
                ->where('active', 0)
                ->where('pending', 1)->update([
                    'title' => $request->title,
                    'desc' => $request->desc,
                    'time' => $request->time
                ]);

            if (!$update) {
                return Response::json(["message" => 'pengajuan telah diterima oleh guru.'], 201);
            }
            return Response::json(["message" => 'schedule updated'], 200);
        }

        return $request;
    }

    public function all(Request $request)
    {
        $data = $this->schedule->orderBy('created_at','desc')->withAndWhereHas('requester', function ($query) {
            $query->where('role', 'siswa')->where('sekolah_id', Auth::user()->sekolah_id);
        });

        $update = $data->update(['expired' => 1]);

        if($request->has('status')) {
            if($request->status == 'pending') {
                $data = $data
                    ->where('canceled', 0)
                    ->where('expired', 0)
                    ->where('pending', 1)
                    ->where('finish', 0)
                    ->where('active', 0)
                    ->where('start', 0);
            }
            else if($request->status == 'aktif') {
                $data = $data
                    ->where('canceled', 0)
                    ->where('expired', 0)
                    ->where('pending', 1)
                    ->where('finish', 0)
                    ->where('active', 1)
                    ->where('start', 1);
            }
        }

        if($request->has('type_schedule')) {
            if ($request->type_schedule == 'online') {
                $data = $data->where('type_schedule', 'daring')->orWhere('type_schedule', 'realtime');
            } else {
                $data = $data->where('type_schedule', $request->type_schedule);
            }
        }

        $data = $data->paginate($request->per_page);

        return Response::json([
            'data' => $data,
            'update' => $update
        ], 200);
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
