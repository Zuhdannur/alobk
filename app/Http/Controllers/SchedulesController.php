<?php namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Repositories\ScheduleRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;

class SchedulesController extends Controller
{
    private $scheduleRepository;

    protected static $API_ACCESS_KEY = 'AAAA_vRurwA:APA91bFvUdoT1ruL0WZC3rkvQWoK76WFOgUSAFuc3aUpN0_kjiP22y3Pf_o1TthpfN6_o_0HnHJeMGZMp8MqHzm1zTCk8zuTY4UzAByzknPDlcBlNFvz60oN6fx9Kq3gkfR373aboRy0';

    /**
     * SchedulesController constructor.
     * @param $scheduleRepository
     */
    public function __construct(ScheduleRepository $scheduleRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
    }

    public function getStudentScheduleCount($id)
    {
        return $this->scheduleRepository->getStudentScheduleCount($id);
    }

    public function notification()
    {
//        $API_ACCSESS_KEY = 'AAAA_vRurwA:APA91bGd7ayeeU2Nlb5D0T1DwRc48CzU-G_ez4SM_qIgdGv-wpQvuUhbJ3xbUFmJZOPtr_EVe_vB2z38O4CUjJPY-WcapZb-Xy_Y1rC3B-v-AFIIQsRxMPJi6pZY8jX1k1eytQSdiXiW';
//        $msg = array
//        (
//            'message' 	=> 'here is a message. message',
//            'title'		=> 'This is a title. title',
//            'subtitle'	=> 'This is a subtitle. subtitle',
//            'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
//            'vibrate'	=> 1,
//            'sound'		=> 1,
//            'largeIcon'	=> 'large_icon',
//            'smallIcon'	=> 'small_icon'
//        );
//        $fields = array(
//            'message' => 'fKdFlF1I53E:APA91bEYdySShRTsMt3xYF0Oz1QMuYXJaZBApgeRL005HkqNH-0n_teE-fdn4y7Y2ukwzQkp7UJa8yyLqM7_nsK8Q-fGk8blgmauxh0EswaWMumX9NiiH9j6H9qpn-2iP5kcoTgqNKhP',
//            'notification' => $msg
//        );
//        $header = [
//            'Authorization: key='. $API_ACCSESS_KEY,
//            'Content-Type: application/json'
//        ];
//
//        $crul = curl_init();
//        curl_setopt($crul,CURLOPT_URL,'https://fcm.googleapis.com/fcm/send');
//        curl_setopt($crul,CURLOPT_POST,true);
//        curl_setopt( $crul,CURLOPT_HTTPHEADER, $header );
//        curl_setopt( $crul,CURLOPT_RETURNTRANSFER, true );
//        curl_setopt( $crul,CURLOPT_SSL_VERIFYPEER, false );
//        curl_setopt( $crul,CURLOPT_POSTFIELDS, json_encode( $fields ) );
//        $result = curl_exec($crul );
//        if($result == FALSE){
//            return response()->json(["Curl Failed "=>curl_error($crul)]);
//        }
//        curl_close( $crul );
//        return response($result,200);
    }

    public function get($id, Request $request)
    {
        $data = \App\Schedule::where('requester_id', $id)
            ->orderBy('created_at', 'desc')
            ->take($request->limit)
            ->get();

        return Response::json($data);
    }

    public function receive(Request $filters)
    {
        return $this->scheduleRepository->receive($filters);
    }

    public function finish(Request $request)
    {
        if (Auth::user()->role == 'guru') {
            $schedule = \App\Schedule::where('id', $request->id)->where('ended', 0)->first();
            if ($schedule) {
                $update = $schedule->update([
                    'ended' => 1
                ]);
                if ($update) {
                    //Simpan riwayat untuk guru
                    $data['user_id'] = Auth::user()->id;
                    $data['schedule_id'] = $schedule->id;
                    $this->saveToRiwayat($data);

                    //Simpan riwayat untuk siswa
                    $data['user_id'] = $schedule->requester_id;
                    $data['schedule_id'] = $schedule->id;
                    $this->saveToRiwayat($data);

                    return Response::json(['message' => 'Pengajuan telah selesai.'], 200);
                } else {
                    return Response::json(['message' => 'Gagal menyelesaikan pengajuan.'], 201);
                }
            } else {
                return Response::json(['message' => 'Telah diselesaikan oleh siswa.'], 201);
            }
        } else {
            $schedule = \App\Schedule::where('id', $request->id)->where('ended', 0)->first();
            if ($schedule) {
                $update = $schedule->update([
                    'ended' => 1
                ]);
                if ($update) {
                    //Simpan riwayat untuk guru
                    $data['user_id'] = $schedule->consultant_id;
                    $data['schedule_id'] = $schedule->id;
                    $this->saveToRiwayat($data);

                    //Simpan riwayat untuk siswa
                    $data['user_id'] = Auth::user()->id;
                    $data['schedule_id'] = $schedule->id;
                    $this->saveToRiwayat($data);

                    return Response::json(['message' => 'Pengajuan telah selesai.'], 200);
                } else {
                    return Response::json(['message' => 'Gagal menyelesaikan pengajuan.'], 201);
                }
            } else {
                return Response::json(['message' => 'Telah diselesaikan oleh guru.'], 201);
            }
        }
    }

    public function updateChannelUrl(Request $request)
    {
        $schedule = \App\Schedule::where('id', $request->id)->first();
        $update = $schedule->update([
            'channel_url' => $request->channel_url
        ]);
        if ($update) {
            return Response::json(['message' => 'Berhasil update channel url.'], 200);
        } else {
            return Response::json(['message' => 'Gagal mengupdate.'], 201);
        }
    }

    //Helpers

    public function accept($id, Request $request)
    {
        return $this->scheduleRepository->accept($id, $request);
    }

    public function remove($id)
    {
        if (Auth::user()->role == 'siswa') {
            $delete = \App\Schedule::where('id', $id)->where('requester_id', Auth::user()->id)->delete();
            if ($delete) {
                return \Illuminate\Support\Facades\Response::json(["message" => "success"], 200);
            } else {
                return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan telah diterima oleh guru."], 201);
            }
        }
    }

    public function cancel($id, $status)
    {
        if (Auth::user()->role == 'guru') {
            $schedule = \App\Schedule::where('id', $id)->where('status', $status)->first();
            if ($schedule) {
                $senderName = \App\User::where('id', $schedule['consultant_id'])->first()->name;

                $result['type'] = 'cancel';
                $result['requester_id'] = $schedule['requester_id'];
                $result['consultant_id'] = $schedule['consultant_id'];
                $result['title'] = 'Pengajuanmu telah dibatalkan.';
                $result['body'] = "Pengajuan " . $schedule['title'] . " telah dibatalkan oleh " . $senderName;
                $result['read'] = 0;

                Helper::sendNotificationToSingle($result);

                // $data['requester_id'] = $schedule['requester_id'];
                // $data['title'] = 'Pengajuanmu telah dibatalkan.';
                // $data['body'] = 'Pengajuan '.$schedule['title']. ' telah dibatalkan oleh '. $schedule['consultant']['name'];
                // $data['id_user'] = $schedule['requester_id'];
                // $data['type'] = 'cancel';
                // Helper::Notification($data);


                $update = $schedule->update([
                    'canceled' => 1
                ]);

                if ($update) {
                    $data['user_id'] = Auth::user()->id;
                    $data['schedule_id'] = $schedule->id;
                    $this->saveToRiwayat($data);

                    $data['user_id'] = $schedule->requester_id;
                    $data['schedule_id'] = $schedule->id;
                    $this->saveToRiwayat($data);

                    return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan berhasil dibatalkan."], 200);
                } else {
                    return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan gagal dibatalkan."], 201);
                }
            } else {
                return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan telah dibatalkan oleh siswa."], 201);
            }
        } else {
            //Role Siswa
            $schedule = \App\Schedule::where('id', $id)->where('requester_id', Auth::user()->id)->where('status', $status)->first();
            if ($schedule) {

                // $senderName = \App\User::where('id', $schedule['requester_id'])->first()->name;

                // $result['type'] = 'cancel';
                // $result['requester_id'] = $schedule['requester_id'];
                // $result['consultant_id'] = $schedule['consultant_id'];
                // $result['title'] = 'Pengajuanmu telah dibatalkan.';
                // $result['body'] = "Pengajuan ". $schedule['title'] ." telah dibatalkan oleh ".$senderName;
                // $result['read'] = 0;

                // Helper::sendNotificationToSingle($result);

                // $data['requester_id'] = $schedule['requester_id'];
                // $data['title'] = 'Pengajuanmu telah dibatalkan.';
                // $data['body'] = 'Pengajuan '.$schedule['title']. ' telah dibatalkan oleh '. $schedule['consultant']['name'];
                // $data['id_user'] = $schedule['requester_id'];
                // $data['type'] = 'cancel';
                // Helper::storeDataNotification($data);


                //Tandai bahwa pengajuan telah dicancel.
                $update = $schedule->update([
                    'canceled' => 1
                ]);

                if ($update) {
                    $data['user_id'] = Auth::user()->id;
                    $data['schedule_id'] = $schedule->id;
                    $this->saveToRiwayat($data);

                    $data['user_id'] = $schedule->consultant_id;
                    $data['schedule_id'] = $schedule->id;
                    $this->saveToRiwayat($data);

                    return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan berhasil dibatalkan."], 200);
                } else {
                    return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan gagal dibatalkan."], 201);
                }
            } else {
                return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan telah diterima oleh guru."], 201);
            }
        }
    }

    private function sendNotificationToDirect()
    {
        $client = new Client();
        $client->setApiKey(self::$API_ACCESS_KEY);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

        $message = new Message();
        $message->setPriority('normal');
        $pattern = "guru" . Auth::user()->sekolah_id . "pengajuan";

        $message->addRecipient(new Topic($pattern));
        $message->setData([
            'title' => $senderName . " menerima pengajuanmu."
        ]);

        $response = $client->send($message);
        return \response()->json($response);
    }

    public function all(Request $filters)
    {
        return $this->scheduleRepository->all($filters);
    }

    public function add(Request $request)
    {
        return $this->scheduleRepository->add($request);
    }

    public function put(Request $request)
    {
        return $this->scheduleRepository->put($request);
    }

    private function saveToRiwayat($data)
    {
        $catatan = new \App\Feedback;
        $catatan->updateOrCreate([
            'schedule_id' => $data['schedule_id']
        ]);

        $riwayat = new \App\Riwayat;
        $riwayat->schedule_id = $data['schedule_id'];
        $riwayat->user_id = $data['user_id'];
        $riwayat->save();

        return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan telah disimpan di riwayat."], 200);
    }

    public function send(Request $request)
    {
        $title = $request->title;
        $desc = $request->desc;
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

    public function deleteDirectSchedule($id)
    {
        $delete = \App\Schedule::where('id', $id)->where('status', 1)->delete();
        if ($delete) {
            return \response()->json(["message" => "success"], 200);
        } else {
            return \response()->json(["message" => "failed"], 201);
        }
    }

    public function getConsultan()
    {
        $data = \App\User::where('role', 'guru')->get();
        return $data[rand(0, count($data) - 1)];
    }

    public function postScheduleDirect(Request $request, $id)
    {
        $limit = $request->limit;

        if (empty($request->page)) {
            $skip = 0;
        } else {
            $skip = $limit * $request->page;
        }

        $stats = $request->status;
        $upcoming = $request->upcoming;

        $user = \App\User::where('id', Auth::user()->id)->with('detail')->first();
        $schedule = \App\Schedule::where(function ($query) use ($user, $id, $stats, $upcoming) {
            if (Auth::user()->role == "siswa") {
                $query->where('requester_id', Auth::user()->id);
            } else {
                $query->where('consultant_id', Auth::user()->id);
            }

            $query->whereHas('request', function ($q) use ($user) {
                $q->whereHas('detail', function ($sql) use ($user) {
                    $sql->where('Sekolah', $user->detail->Sekolah);
                });
            });
            $query->where('type_schedule', $id);
            $query->where('status', $stats);
            if ($upcoming == "true") {
                $query->where('time', '>', Carbon::now());
            }
        })->with('request')->with('consultant')->orderBy('id', 'desc');


        $datas = $schedule
            ->skip($skip)
            ->take($limit)
            ->get();
        foreach ($datas as $key => $row) {
            if ($row->type_schedule != "daring") {
                if (Carbon::parse($row->time)->greaterThan(Carbon::now())) {
                    $datas[$key]['expired_tgl'] = 'expired at ' . $row->time;
                } else {
                    $datas[$key]['expired_tgl'] = 'expired';
                }
            }
        }
        return Response::json($datas, 200);
    }

    public function postScheduleDirectCount(Request $request, $id)
    {
        $limit = $request->limit;

        if (empty($request->page)) {
            $skip = 0;
        } else {
            $skip = $limit * $request->page;
        }

        $stat = $request->status;
        $upcoming = $request->upcoming;

        $user = \App\User::where('id', Auth::user()->id)->with('detail')->first();
        $schedule = \App\Schedule::where(function ($query) use ($user, $id, $stat, $upcoming) {
            if (Auth::user()->role == "siswa") {
                $query->where('requester_id', Auth::user()->id);
            } else {
                $query->where('consultant_id', Auth::user()->id);
            }

            $query->whereHas('request', function ($q) use ($user) {
                $q->whereHas('detail', function ($sql) use ($user) {
                    $sql->where('Sekolah', $user->detail->Sekolah);
                });
            });
            $query->where('type_schedule', $id);
            $query->where('status', $stat);
            if ($upcoming == "true") {
                $query->where('time', '>', Carbon::now());
            }
        })->with('request')->with('consultant')->orderBy('id', 'desc');

        $datas = $schedule
            ->paginate($skip)
            ->lastPage($limit);

        return Response::json([
            "total_page" => $datas
        ], 200);
    }

    public function viewMySchedule($id = '', Request $request)
    {
        $limit = $request->limit;

        if (empty($request->page)) {
            $skip = 0;
        } else {
            $skip = $limit * $request->page;
        }

        if (Auth::user()->role == "siswa" && $id == '') {
            $data = \App\Schedule::where('requester_id', Auth::user()->id);

            // $data['result']['user'] = \App\User::where('id', $data['result']->consultant_id)->get();
            $datas = $data
                ->skip($skip)
                ->take($limit)
                ->get();

            return Response::json($datas, 200);
        } else {
//            dd($request->status);
            $stat = $request->status;
            $upcoming = $request->upcoming;

            $user = \App\User::where('id', Auth::user()->id)->with('detail')->first();
            $schedule = \App\Schedule::where(function ($query) use ($user, $id, $stat, $upcoming) {

                // if(Auth::user()->role == "siswa")$query->where('requester_id',Auth::user()->id);
                // else $query->where('consultant_id',Auth::user()->id);

                $query->whereHas('request', function ($q) use ($user) {
                    $q->whereHas('detail', function ($sql) use ($user) {
                        $sql->where('Sekolah', $user->detail->Sekolah);
                    });
                });
                $query->where('type_schedule', $id);
                $query->where('status', $stat);
                if ($upcoming == "true") {
                    $query->where('time', '>', Carbon::now());
                }
            })->with('request')->with('consultant')->orderBy('id', 'desc');


            $datas = $schedule
                ->skip($skip)
                ->take($limit)
                ->get();
            foreach ($datas as $key => $row) {
                if ($row->type_schedule != "daring") {
                    if (Carbon::parse($row->time)->greaterThan(Carbon::now())) {
                        $datas[$key]['expired_tgl'] = 'expired at ' . $row->time;
                    } else {
                        $datas[$key]['expired_tgl'] = 'expired';
                    }
                }
            }
            return Response::json($datas, 200);
        }
    }

    public function mySchedulePageCount(Request $request, $id = '')
    {
        $limit = $request->limit;

        if (empty($request->page)) {
            $skip = 0;
        } else {
            $skip = $limit * $request->page;
        }

        if (Auth::user()->role == "siswa" && $id == '') {
            $data = "";
            if ($request->only == "online") {
                $data = \App\Schedule::where('requester_id', Auth::user()->id)->where('type_schedule', 'online');
            } elseif ($request->only == "daring") {
                $data = \App\Schedule::where('requester_id', Auth::user()->id)->where('type_schedule', 'daring');
            } elseif ($request->only == "direct") {
                $data = \App\Schedule::where('requester_id', Auth::user()->id)->where('type_schedule', 'direct');
            } else {
                $data = \App\Schedule::where('requester_id', Auth::user()->id);
            }
            // $data['result']['user'] = \App\User::where('id', $data['result']->consultant_id)->get();
            $count = $data
                ->paginate($skip)
                ->lastPage($limit);

            return Response::json([
                "total_page" => $count
            ], 200);
        } else {
            $user = \App\User::where('id', Auth::user()->id)->with('detail')->first();
            $schedule = \App\Schedule::where(function ($query) use ($user, $id) {
                $query->whereHas('request', function ($q) use ($user) {
                    $q->whereHas('detail', function ($sql) use ($user) {
                        $sql->where('Sekolah', $user->detail->Sekolah);
                    });
                });
                $query->where('type_schedule', $id);
                $query->where('status', 0);
            })->with('request')->with('consultant');

            $count = $schedule
                ->paginate($skip)
                ->lastPage($limit);
            return Response::json(["total_page" => $count], 200);
        }
    }

    private function getSekolahName($id)
    {
        $data = \App\User::where('id', $id)->with('detail')->first();
        return $data;
    }


    public function getPengajuanByStatus(Request $request)
    {
        $limit = $request->limit;

        if (empty($request->page)) {
            $skip = 0;
        } else {
            $skip = $limit * $request->page;
        }

        $data = "";
        if (!empty($request->only)) {
            $data = \App\Schedule::where('requester_id', Auth::user()->id)->where('status', $request->status)->where('type_schedule', $request->only);
        } else {
            $data = \App\Schedule::where('requester_id', Auth::user()->id)->where('status', $request->status);
        }

        $result = $data->skip($skip)->take($limit)->orderBy('id', 'desc')->get();
        foreach ($result as $key => $row) {
            if ($row->type_schedule != "daring") {
                if (Carbon::parse($row->time)->greaterThan(Carbon::now())) {
                    $result[$key]['expired_tgl'] = 'expired at ' . $row->time;
                } else {
                    $result[$key]['expired_tgl'] = 'expired';
                }
            }
        }

        return Response::json($result, 200);
    }

    public function getPengajuanByStatusPageCount(Request $request)
    {
        $limit = $request->limit;

        if (empty($request->page)) {
            $skip = 0;
        } else {
            $skip = $limit * $request->page;
        }

        $data = "";
        if (!empty($request->only)) {
            $data = \App\Schedule::where('requester_id', Auth::user()->id)->where('status', $request->status)->where('type_schedule', $request->only);
        } else {
            $data = \App\Schedule::where('requester_id', Auth::user()->id)->where('status', $request->status);
        }

        $result = $data
            ->paginate($skip)
            ->lastPage($limit);

        return Response::json(["total_page" => $result], 200);
    }

    public function studentSchedule(Request $request)
    {
        $data = \App\Schedule::where('requester_id', $request->id_user)->orderBy('created_at', 'desc')->take($request->limit)->get();
        // $data['result']['user'] = \App\User::where('id', $data['result']->consultant_id)->get();

        return Response::json($data);
    }
}
