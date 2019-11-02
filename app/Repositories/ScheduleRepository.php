<?php


namespace App\Repositories;


use App\Helpers\Helper;
use App\Schedule;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ScheduleRepository
{

    private $schedule;
    private $user;

    /**
     * ScheduleRepository constructor.
     * @param $schedule
     */
    public function __construct(Schedule $schedule, User $user)
    {
        $this->schedule = $schedule;
        $this->user = $user;
    }


    public function getStudentScheduleCount($id)
    {
        $total = $this->schedule->where('requester_id', $id)->count();

        return Response::json([
            "total" => $total
        ], 200);
    }

    public function add(Request $request)
    {
        if ($request->type_schedule == 'realtime') {
            $insert = $this->storeRealtime($request);
        } elseif ($request->type_schedule == 'direct') {
            $insert = $this->storeDirect($request);
        } else {
            $insert = $this->storeDaring($request);
        }

        if (!$insert) {
            // Helper::sendNotificationTopic($request->type_schedule);
            return Response::json([
                "message" => 'failed create schedule'
            ], 201);
        }

        return Response::json([
            "message" => 'success create schedule',
            'type_schedule' => $request->type_schedule
        ], 200);
    }

    private function storeRealtime($request)
    {
        $insert = $this->schedule;
        $insert->requester_id = Auth::user()->id;
        $insert->time = $request->time;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->type_schedule = 'realtime';
        $insert->pending = 1;
        $insert->save();
        return $insert;
    }


    private function storeDaring($request)
    {
        $insert = $this->schedule;
        $insert->requester_id = Auth::user()->id;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->type_schedule = 'daring';
        $insert->pending = 1;
        $insert->save();
        return $insert;
    }

    private function storeDirect($request)
    {
        $insert = $this->schedule;
        $insert->requester_id = Auth::user()->id;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->type_schedule = 'direct';
        $insert->time = $request->time;
        $insert->location = $request->location;
        $insert->pending = 1;
        $insert->save();
        return $insert;
    }

    public function put(Request $request)
    {
        if ($request->type_schedule == 'daring') {
            $update = Schedule::where('id', $request->schedule_id)
                ->where('requester_id', Auth::user()->id)
                ->where('exp', 0)
                ->where('status', 0)->update([
                    'title' => $request->title,
                    'desc' => $request->desc
                ]);

            if (!$update) {
                return Response::json([
                    "message" => 'failed to update'
                ], 201);
            }
            return Response::json([
                "message" => 'schedule updated'
            ], 200);
        } else {
            //Direct dan Realtime
            if ($request->original_time->isPast()) {
                return Response::json(["message" => 'Pengajuan telah kedaluwarsa.'], 201);
            }

            $update = $this->schedule->where('id', $request->schedule_id)
                ->where('requester_id', Auth::user()->id)
                ->where('expired', 0)
                ->where('active', 0)
                ->where('pending', 1)->update([
                    'title' => $request->title,
                    'desc' => $request->desc,
                    'time' => $request->time
                ]);

            if (!$update) {
                return Response::json(["message" => 'Pengajuan telah diterima oleh guru lain.'], 201);
            }
            return Response::json(["message" => 'Pengajuan berhasil diubah.'], 200);
        }

        return $request;
    }

    private function isExpired($request)
    {
        if (Carbon::parse($request->original_time)->lte(Carbon::now())) {
            Schedule::where('id', $request->schedule_id)->update([
                'exp' => 1
            ]);
            return true;
        }
        return false;
    }

    public function send(Request $request)
    {
        if (Auth::user()->role == 'siswa') {
            if ($request->type_schedule == 'realtime') {
                $insert = $this->storeRealtime($request);
            } elseif ($request->type_schedule == 'direct') {
                $insert = $this->storeDirect($request);
            } else {
                $insert = $this->storeDaring($request);
            }

            if ($insert) {
                //Mengirim Dari siswa ke Semua Guru berdasarkan Sekolah
                Helper::sendNotificationTopic($request->type_schedule);
                return \Illuminate\Support\Facades\Response::json([
                    "message" => 'success create schedule'
                ], 200);
            } else {
                return Response::json([
                    "message" => 'failed create schedule'
                ], 201);
            }
        } else {
            if (\App\Schedule::where('id', $request->schedule_id)->exists()) {
                if (\App\Schedule::where('id', $request->schedule_id)->first()->exp) {
                    $update = \App\Schedule::where('id', $request->schedule_id)->update([
                        'status' => 1,
                        'tgl_pengajuan' => $request->date,
                        'consultant_id' => Auth::user()->id
                    ]);

                    $checkType = \App\Schedule::where('id', $request->schedule_id)->first();

                    if ($checkType->type_schedule == "online") {
                        $createRoom = \App\Schedule::where('id', $request->schedule_id)->update([
                            'room_id' => base64_encode(str_random(5))
                        ]);
                    }
                    if ($update) {
                        $schedule = \App\Schedule::where('id', $request->schedule_id)->first();

                        $id = $schedule['requester_id'];
                        // Helper::sendNotificationToSingel($id);

                        $result['requester_id'] = $schedule['requester_id'];
                        $result["title"] = $schedule['title'];
                        $result['desc'] = $schedule['desc'];

                        return Response::json($result, 200);
                    } else {
                        return [
                            "message" => "failed accept"
                        ];
                    }
                } else {
                    return Response::json([
                        "message" => "expired tho"
                    ], 200);
                }
            } else {
                return Response::json(["message" => "id not found"], 201);
            }
        }
    }

    public function all(Request $filters)
    {
        $schedule = $this->schedule;
        $schedule = $schedule->where('requester_id', Auth::user()->id);
        $schedule = $schedule->with('request', 'consultant');

//        if ($filters->has('pengajuan')) {
//            if ($filters->pengajuan == 'pending') {
//                //Saat fetch data jika pengajuannya pending & time < sekarang, update expired ke 1
//                // $schedule = $schedule
//                //     ->where('status', 0)
//                //     ->where('ended', 0)
//                //     ->where('canceled', 0)
//                //     ->where('exp', 0);
//
//                // where([
//                //     ['status', '=', 0],
//                //     ['ended', '=', 0],
//                //     ['canceled', '=', 0],
//                //     ['exp', '=', 0]
//                // ]);
//
//                foreach ($schedule->get() as $key => $row) {
//                    if ($row->type_schedule != "daring") {
//                        if (Carbon::parse($row->time)->lessThan(Carbon::now())) {
//                            $row->update([
//                                'exp'=> 1
//                            ]);
//                        }
//                    }
//                }
//            }
//
//            if ($filters->pengajuan == 'riwayat') {
//                //Saat fetch data jika pengajuannya riwayat
//                $schedule = $schedule->where(function ($query) {
//                    //Selesai Pengajuannya
//                    $query->where('status', 1)
//                            ->where('ended', 1);
//                })->orWhere(function ($query) {
//                    //Di accept tapi di cancel sama guru
//                    $query->where('status', 1)
//                            ->where('canceled', 1);
//                })->orWhere(function ($query) {
//                    //Di cancel sama siswa
//                    $query->where('status', 0)
//                            ->where('canceled', 1);
//                })->orWhere(function ($query) {
//                    //kedaluwarsa
//                    $query->where('status', 0)
//                            ->where('canceled', 0)
//                            ->where('exp', 1)
//                            ->where('ended', 0);
//                });
//            }
//
//            if ($filters->pengajuan == 'acceptedDirect') {
//                if ($filters->has('outdated')) {
//                    if ($filters->outdated == 0 || $filters->outdated == 1) {
//                        $schedule = $schedule->where('outdated', $filters->outdated);
//                    }
//                }
//
//                $schedule = $schedule->where([
//                    ['status', '=', 0],
//                    ['ended', '=', 0],
//                    ['canceled', '=', 0],
//                    ['exp', '=', 0]
//                ]);
//
//                foreach ($schedule->get() as $key => $row) {
//                    if ($row->type_schedule == "direct" && $row->consultant_id == 0) {
//                        if (Carbon::parse($row->time)->lessThan(Carbon::now())) {
//                            $row->update([
//                                'exp'=> 1
//                            ]);
//                        }
//                    }
//                }
//
//                foreach ($schedule->get() as $key => $row) {
//                    if ($row->type_schedule == "direct" && $row->consultant_id != 0) {
//                        if (Carbon::parse($row->time)->lessThan(Carbon::now())) {
//                            if ($row->outdated == 0) {
//                                $row->update([
//                                    'outdated' => 1
//                                ]);
//                            }
//                        }
//                    }
//                }
//            }
//        }

        if ($filters->has('expired')) {
            $schedule = $schedule->where('expired', $filters->canceled);
        }
        if ($filters->has('canceled')) {
            $schedule = $schedule->where('canceled', $filters->canceled);
        }
        if ($filters->has('pending')) {
            $schedule = $schedule->where('pending', $filters->pending);
        }
        if ($filters->has('finish')) {
            $schedule = $schedule->where('finish', $filters->finish);
        }
        if ($filters->has('active')) {
            $schedule = $schedule->where('active', $filters->active);
        }
        if ($filters->has('start')) {
            $schedule = $schedule->where('start', $filters->start);
        }

        if ($filters->has('type_schedule')) {
            $schedule = $schedule->where('type_schedule', $filters->type_schedule);
        }

        if($filters->has('type_schedule')) {
            if($filters->type_schedule == 'online') {
                $schedule = $schedule->where('type_schedule', 'daring')->orWhere('type_schedule', 'realtime');
            }
        }

        if ($filters->has('type_schedule') || $filters->has('type_schedule2')) {
            if (!empty($filters->type_schedule)) {
                $schedule = $schedule->where('type_schedule', $filters->type_schedule)->orWhere('type_schedule', $filters->type_schedule2);
            }
        }


        if ($filters->has('orderBy')) {
            $schedule = $schedule->orderBy($filters->orderBy, 'desc');
        }

        $paginate = $schedule->paginate($filters->per_page);

        return response()->json($paginate, 200);
    }

    public function receive(Request $filters)
    {
        $user = $this->user->where('id', Auth::user()->id)->with('detail')->first();

        $schedule = $this->schedule->where(function ($query) use ($user, $filters) {
            $query->whereHas('request', function ($q) use ($user) {
                $q->whereHas('detail', function ($sql) use ($user) {
                    $sql->where('sekolah_id', $user->detail->sekolah_id);
                });
            });
        })->with('request')->with('consultant')->orderBy('id', 'desc');
//
//            if ($filters->has('pengajuan')) {
//                if ($filters->pengajuan == 'online') {
//                    $query->where('type_schedule', 'daring')->orWhere('type_schedule', 'realtime');
//                }
//                if ($filters->pengajuan == 'realtime') {
//                    foreach ($query->get() as $key => $row) {
//                        if ($row->type_schedule != "daring") {
//                            if (Carbon::parse($row->time)->lessThan(Carbon::now())) {
//                                if ($row->exp == 0) {
//                                    $row->update([
//                                        'exp' => 1
//                                    ]);
//                                }
//                            }
//                        }
//                    }
//                }
//                if ($filters->pengajuan == 'direct') {
//                    foreach ($query->get() as $key => $row) {
//                        if ($row->type_schedule != "daring") {
//                            if (Carbon::parse($row->time)->lessThan(Carbon::now())) {
//                                if ($row->exp == 0) {
//                                    $row->update([
//                                        'exp' => 1
//                                    ]);
//                                }
//                            }
//                        }
//                    }
//                }
//
//                if ($filters->pengajuan == 'acceptedDirect') {
//                    $query->where('consultant_id', Auth::user()->id);
//                    foreach ($query->get() as $key => $row) {
//                        if ($row->type_schedule != "daring" && $row->type_schedule != "realtime") {
//                            if (Carbon::parse($row->time)->lessThan(Carbon::now())) {
//                                if ($row->outdated == 0) {
//                                    $row->update([
//                                        'outdated' => 1
//                                    ]);
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        })->with('request')->with('consultant')->orderBy('id', 'desc');

        if ($filters->has('type_schedule')) {
            $schedule = $schedule->where('type_schedule', $filters->type_schedule);
        }

        if ($filters->has('expired')) {
            $schedule = $schedule->where('expired', $filters->canceled);
        }
        if ($filters->has('canceled')) {
            $schedule = $schedule->where('canceled', $filters->canceled);
        }
        if ($filters->has('pending')) {
            $schedule = $schedule->where('pending', $filters->pending);
        }
        if ($filters->has('finish')) {
            $schedule = $schedule->where('finish', $filters->finish);
        }
        if ($filters->has('active')) {
            $schedule = $schedule->where('active', $filters->active);
        }
        if ($filters->has('start')) {
            $schedule = $schedule->where('start', $filters->start);
        }

        if ($filters->has('upcoming')) {
            if ($filters->upcoming == "true") {
                $schedule = $schedule->where('time', '>', Carbon::now());
            }
        }

        $datas = $schedule->paginate($filters->per_page);

        return Response::json($datas, 200);
    }

    public function accept($id, Request $request)
    {
        $schedule = $this->schedule->where('id', $id)->first();

        if ($schedule->canceled != 0) {
            return Response::json(["message" => "Pengajuan ini telah dibatalkan."], 201);
        }

        if ($schedule->active != 0) {
            return Response::json([
                "message" => "Pengajuan telah diterima."
            ], 201);
        }

        if ($schedule->expired != 0) {
            return Response::json([
                "message" => "Pengajuan telah kedaluwarsa."
            ], 201);
        }

        $update = $this->schedule->where('id', $id)->update([
            'status' => 1,
            'consultant_id' => Auth::user()->id
        ]);

        if (!$update) {
            return Response::json([
                "message" => "Gagal menerima."
            ], 201);
        }


        $schedule = $this->schedule->where('id', $id)->with('consultant')->first();

        // if($schedule->type_schedule == 'direct') {
        //     $this->sendNotificationToDirect();
        // }

        // if($schedule->type_schedule == 'realtime') {
        //     $this->sendNotificationToRealtime();
        // }

        // if($schedule->type_schedule == 'daring') {
        //     $this->sendNotificationToDaring();
        // }
        $senderName = $this->user->where('id', $schedule['consultant_id'])->first()->name;

        $result['type'] = "accept";
        $result['schedule_id'] = $schedule['id'];
        $result['requester_id'] = $schedule['requester_id'];
        $result['consultant_id'] = $schedule['consultant_id'];
        $result['title'] = 'Pengajuanmu telah diterima';
        $result['body'] = "Pengajuan " . $schedule['title'] . " telah diterima oleh " . $senderName;
        $result['read'] = 0;

//            Helper::sendNotificationToSingle($result);

        // $data['requester_id'] = $schedule['requester_id'];
        // $data['title'] = 'Pengajuanmu telah diterima.';
        // $data['body'] = 'Pengajuan '.$schedule['title']. ' telah diterima oleh '. $schedule['consultant']['name'];
        // $data['id_user'] = $schedule['requester_id'];
        // $data['type'] = 'accept';
        // Helper::storeDataNotification($data);

        return Response::json($schedule, 200);
    }

}
