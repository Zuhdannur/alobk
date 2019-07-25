<?php namespace App\Http\Controllers;

use App\Helpers\Helper;
use Carbon\Carbon;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Pusher\Pusher;
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Notification;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;

class SchedulesController extends Controller
{

    protected static $API_ACCESS_KEY = 'AAAA_vRurwA:APA91bFvUdoT1ruL0WZC3rkvQWoK76WFOgUSAFuc3aUpN0_kjiP22y3Pf_o1TthpfN6_o_0HnHJeMGZMp8MqHzm1zTCk8zuTY4UzAByzknPDlcBlNFvz60oN6fx9Kq3gkfR373aboRy0';

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

    public function get($id, Request $request) {
        $data = \App\Schedule::where('requester_id', $id)
        ->orderBy('created_at', 'desc')
        ->take($request->limit)
        ->get();

        return Response::json($data);
    }

    public function receive(Request $filters) {
        $user = \App\User::where('id', Auth::user()->id)->with('detail')->first();

        $schedule = \App\Schedule::where(function ($query) use ($user, $filters) {
            $query->whereHas('request', function($q) use ($user) {
                $q->whereHas('detail', function($sql) use ($user) {
                    $sql->where('id_sekolah', $user->detail->id_sekolah);
                });
            });

            foreach ($query->get() as $key => $row) {
                if ($row->type_schedule != "daring") {
                    if (Carbon::parse($row->time)->lessThan(Carbon::now())) {
                        $row->update([
                            'exp'=> 1,
                            'ended' => 1
                        ]);
                    }
                }
            }
            if($filters->has('type_schedule')) $query->where('type_schedule', $filters->type_schedule);
            

            if($filters->has('status')) $query->where('status', $filters->status);
            

            if($filters->has('upcoming')) if ($filters->upcoming == "true") {
                $query->where('time', '>', Carbon::now());
            }
        })->with('request')->with('consultant')->orderBy('id', 'desc');

        $limit = $filters->limit;

        if (empty($filters->page)) $skip = 0;
        else $skip = $limit * $filters->page;

        $datas = $schedule
            ->skip($skip)
            ->take($limit)
            ->get();
            
        return Response::json($datas, 200);
    }

    public function receiveCount(Request $filters) {
        $user = \App\User::where('id', Auth::user()->id)->with('detail')->first();

        $schedule = \App\Schedule::where(function ($query) use ($user, $filters) {
            $query->whereHas('request', function($q) use ($user) {
                $q->whereHas('detail', function($sql) use ($user) {
                    $sql->where('id_sekolah', $user->detail->id_sekolah);
                });
            });
            if($filters->has('type_schedule')) {
                $query->where('type_schedule', $filters->type_schedule);
            }

            if($filters->has('status')) {
                $query->where('status', $filters->status);
            }

            if($filters->has('upcoming')) {
                if ($filters->upcoming == "true") $query->where('time', '>', Carbon::now());
            }
        })->with('request')->with('consultant')->orderBy('id', 'desc');

        $limit = $filters->limit;

        if (empty($filters->page)) $skip = 0;
        else $skip = $limit * $filters->page;


        $count = $schedule
        ->paginate($skip)
        ->lastPage($limit);
            
        return Response::json([
            'total_page' => $count
        ], 200);
    }

    public function accept(Request $request) {
        
        if(\App\Schedule::where('id', $request->schedule_id)->exists()) {
            if (\App\Schedule::where('id', $request->schedule_id)->first()->status == 0) {
                if (\App\Schedule::where('id', $request->schedule_id)->first()->exp == 0) {
                    $update = \App\Schedule::where('id', $request->schedule_id)->update([
                        'status' => 1,
                        'tgl_pengajuan' => $request->date,
                        'consultant_id' => Auth::user()->id
                    ]);

                    if ($update) {
                        $schedule = \App\Schedule::where('id', $request->schedule_id)->with('consultant')->first();

                        // if($schedule->type_schedule == 'direct') {
                        //     $this->sendNotificationToDirect();
                        // }

                        // if($schedule->type_schedule == 'realtime') {
                        //     $this->sendNotificationToRealtime();
                        // }

                        // if($schedule->type_schedule == 'daring') {
                        //     $this->sendNotificationToDaring();
                        // }
                        $senderName = \App\User::where('id', $schedule['consultant_id'])->first()->name;

                        $result['requester_id'] = $schedule['requester_id'];
                        $result['consultant_id'] = $schedule['consultant_id'];
                        $result['title'] = 'Pengajuanmu telah diterima';
                        $result['body'] = "Pengajuanmu telah diterima oleh ".$senderName;
                        
                        Helper::sendNotificationToSingle($result);

                        // $data['requester_id'] = $schedule['requester_id'];
                        // $data['title'] = 'Pengajuanmu telah diterima.';
                        // $data['body'] = 'Pengajuan '.$schedule['title']. ' telah diterima oleh '. $schedule['consultant']['name'];
                        // $data['id_user'] = $schedule['requester_id'];
                        // $data['type'] = 'accept';
                        // Helper::storeDataNotification($data);

                        return Response::json($result, 200);
                    } else {
                        return Response::json([
                            "message" => "Gagal menerima."
                        ], 201);
                    }
                } else {
                    return Response::json([
                        "message" => "Pengajuan telah kadaluarsa."
                    ], 201);
                }
            } else {
                return Response::json([
                    "message" => "Pengajuan telah diterima oleh guru lain."
                ], 201);
            }
        } else {
            return Response::json(["message" => "Pengajuan tidak ditemukan"], 201);
        }
    }

    private function sendNotificationToDirect() {
        $client = new Client();
        $client->setApiKey(self::$API_ACCESS_KEY);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

        $message = new Message();
        $message->setPriority('normal');
        $pattern = "guru".Auth::user()->detail->id_sekolah."pengajuan";

//        $message->addRecipient(new Device("cQlOvwQ3lu4:APA91bHZiKXMaRYNmsSEx6LojxNrAUzJPKp1LsRJMUaIfxsZ3hu59P8CWhoZWaSz-fnCmETuP34o87whE9NnhFkPGZBnyLt4s8MDT4pk_mrMhdzli95gsjJ3v-_jIyR04Zw2S6KFu4Tm"));
        $message->addRecipient(new Topic($pattern));
        $message->setData([
            'title' => $senderName." menerima pengajuanmu."
        ]);
        
        $response = $client->send($message);
        return \response()->json($response);
    }

    public function all(Request $filters) {
        $schedule = new \App\Schedule;
        $schedule = $schedule->where('requester_id', Auth::user()->id);

        foreach ($schedule->get() as $key => $row) {
            if ($row->type_schedule != "daring") {
                if (Carbon::parse($row->time)->lessThan(Carbon::now())) {
                    $row->update([
                        'exp'=> 1,
                        'ended'=> 1
                    ]);
                }
            }
        }
        
        $schedule = $schedule->with('request', 'consultant');
        
        if ($filters->has('status')) {
            $schedule = $schedule->where('status', $filters->status);
        }

        if ($filters->has('type_schedule')) {
            if(!empty($filters->type_schedule)) {
                $schedule = $schedule->where('type_schedule', $filters->type_schedule);
            }
        }

        if ($filters->has('exp')) {
            $schedule = $schedule->where('exp', $filters->exp);
        }

        if ($filters->has('upcoming')) {
            if($filters->upcoming == 'true') {
                $schedule = $schedule->where('time', ">" ,Carbon::now());
            }
        }

        if($filters->has('limit') && $filters->has('page')) {
            $limit = $filters->limit;

            if (empty($filters->page)) $skip = 0;
            else $skip = $limit * $filters->page;

            $schedule = $schedule
                ->skip($skip)
                ->take($limit);
        }

        $schedule = $schedule->orderBy('created_at', 'desc');

        return Response::json($schedule->get(), 200);
    }

    public function count(Request $filters) {
        $schedule = new \App\Schedule;
        $schedule = $schedule->where('requester_id', Auth::user()->id);
        $schedule = $schedule->with('request', 'consultant');

        if ($filters->has('status')) {
            $schedule = $schedule->where('status', $filters->status);
        }

        if ($filters->has('type_schedule')) {
            $schedule = $schedule->where('type_schedule', $filters->type_schedule);
        }

        if ($filters->has('exp')) {
            $schedule = $schedule->where('exp', $filters->exp);
        }

        if ($filters->has('upcoming')) {
            if($filters->upcoming == 'true') {
                $schedule = $schedule->where('time', Carbon::now());
            }
        }

        if($filters->has('limit') && $filters->has('page')) {
            $limit = $filters->limit;

            if (empty($filters->page)) $skip = 0;
            else $skip = $limit * $filters->page;

            $schedule = $schedule
                ->paginate($skip)
                ->lastPage($limit);
        }

        return Response::json(["total_page" => $schedule], 200);
    }

    public function add(Request $request) {
        if ($request->type_schedule == 'realtime') {
            $insert = $this->storeRealtime($request);
        } elseif ($request->type_schedule == 'direct') {
            $insert = $this->storeDirect($request);
        } else {
            $insert = $this->storeDaring($request);
        }

        if ($insert) {
            Helper::sendNotificationTopic($request->desc);
            return \Illuminate\Support\Facades\Response::json([
                "message" => 'success create schedule'
            ], 200);
        } else {
            return Response::json([
                "message" => 'failed create schedule'
            ], 201);
        }
    }

    private function storeRealtime($request)
    {
        $insert = new \App\Schedule;
        $insert->requester_id = Auth::user()->id;
        $insert->time = $request->time;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->exp  = 0;
        $insert->status  = 0;
        $insert->type_schedule = 'realtime';
        $insert->save();
        return $insert;
    }
    

    private function storeDaring($request)
    {
        $insert = new \App\Schedule;
        $insert->requester_id = Auth::user()->id;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->type_schedule = 'daring';
        $insert->save();
        return $insert;
    }

    private function storeDirect($request)
    {
        $insert = new \App\Schedule;
        $insert->requester_id = Auth::user()->id;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->type_schedule = 'direct';
        $insert->exp  = 0;
        $insert->status  = 0;
        $insert->time = $request->time;
        $insert->location = $request->location;
        $insert->save();
        return $insert;
    }

    public function put(Request $request)
    {
        if($request->type_schedule == 'daring') {
            $update = \App\Schedule::where('id', $request->schedule_id)
            ->where('requester_id', Auth::user()->id)
            ->where('exp', 0)
            ->where('status', 0)->update([
                'title' => $request->title,
                'desc' => $request->desc
            ]);
    
            if($update) {
                return \Illuminate\Support\Facades\Response::json([
                    "message" => 'schedule updated'
                ],200);
            } else {
                return \Illuminate\Support\Facades\Response::json([
                    "message" => 'failed to update'
                ],201);
            }
        } else { 
            //Direct dan Realtime
            if (!$this->isExpired($request)) {
                $update = \App\Schedule::where('id', $request->schedule_id)
                ->where('requester_id', Auth::user()->id)
                ->where('exp', 0)
                ->where('status', 0)->update([
                    'title' => $request->title,
                    'desc' => $request->desc,
                    'time' => $request->time
                ]);
    
                if ($update) {
                    return \Illuminate\Support\Facades\Response::json([
                    "message" => 'schedule updated'], 200);
                } else {
                    return \Illuminate\Support\Facades\Response::json([
                    "message" => 'pengajuan telah diterima oleh guru.'], 201);
                }
            } else {
                return \Illuminate\Support\Facades\Response::json(["message" => 'pengajuan telah kadaluarsa.'], 201);
            }
        } 

        return $request;
    }

    private function isExpired($request) {
        if (Carbon::parse($request->original_time)->lte(Carbon::now())) {
            \App\Schedule::where('id', $request->schedule_id)->update([
                'exp'=> 1
            ]);
            return true;
        } 
        return false;
    }

    public function remove($id)
    {
        if (Auth::user()->role == 'siswa') {
            $delete = \App\Schedule::where('id', $id)->where('requester_id', Auth::user()->id)->where('status', 0)->delete();
            if ($delete) {
                return \Illuminate\Support\Facades\Response::json(["message" => "success"], 200);
            } else {
                return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan telah diterima oleh guru."], 201);
            }
        }
    }

    public function removeByGuru($id, $requester_id, $status) {
        $schedule = \App\Schedule::where('id', $id)->where('requester_id', $requester_id)->where('status', 1)->first();
        if($schedule) {

            $senderName = \App\User::where('id', $schedule['consultant_id'])->first()->name;

            $result['requester_id'] = $schedule['requester_id'];
            $result['consultant_id'] = $schedule['consultant_id'];
            $result['title'] = 'Pengajuanmu telah dibatalkan.';
            $result['body'] = "Pengajuanmu telah dibatalkan oleh ".$senderName;
            
            Helper::sendNotificationToSingle($result);

            // $data['requester_id'] = $schedule['requester_id'];
            // $data['title'] = 'Pengajuanmu telah dibatalkan.';
            // $data['body'] = 'Pengajuan '.$schedule['title']. ' telah dibatalkan oleh '. $schedule['consultant']['name'];
            // $data['id_user'] = $schedule['requester_id'];
            // $data['type'] = 'cancel';
            // Helper::storeDataNotification($data);

            if($schedule->delete()) {
                 return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan berhasil dibatalkan."], 200);
            } else {
                return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan gagal dibatalkan."], 201);
            }
        } else {
            return \Illuminate\Support\Facades\Response::json(["message" => "Pengajuan telah dibatalkan oleh siswa."], 201);
        }
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
                // Helper::sendNotificationTopic($title, $desc);
                return \Illuminate\Support\Facades\Response::json([
                    "message" => 'success create schedule'
                ], 200);
            } else {
                return Response::json([
                    "message" => 'failed create schedule'
                ], 201);
            }
        } else {
            if(\App\Schedule::where('id', $request->schedule_id)->exists()) {
                if(\App\Schedule::where('id',$request->schedule_id)->first()->exp) {
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
                    ],200);
                }
            } else {
                return Response::json(["message" => "id not found"], 201);
            }
        }
    }

    public function deleteDirectSchedule($id) {
        $delete = \App\Schedule::where('id', $id)->where('status', 1)->delete();
        if ($delete) return \response()->json(["message" => "success"], 200);
        else return \response()->json(["message" => "failed"], 201);
    }

    public function getConsultan()
    {
        $data = \App\User::where('role', 'guru')->get();
        return $data[rand(0, count($data) - 1)];
    }

    public function postScheduleDirect(Request $request, $id)
    {
        $limit = $request->limit;

        if (empty($request->page)) $skip = 0;
        else $skip = $limit * $request->page;

        $stats = $request->status;
        $upcoming = $request->upcoming;

        $user = \App\User::where('id', Auth::user()->id)->with('detail')->first();
        $schedule = \App\Schedule::where(function ($query) use ($user, $id, $stats, $upcoming) {

            if (Auth::user()->role == "siswa") $query->where('requester_id', Auth::user()->id);
            else $query->where('consultant_id', Auth::user()->id);

            $query->whereHas('request', function ($q) use ($user) {
                $q->whereHas('detail', function ($sql) use ($user) {
                    $sql->where('Sekolah', $user->detail->Sekolah);
                });
            });
            $query->where('type_schedule', $id);
            $query->where('status', $stats);
            if ($upcoming == "true") $query->where('time', '>', Carbon::now());
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

        if (empty($request->page)) $skip = 0;
        else $skip = $limit * $request->page;

        $stat = $request->status;
        $upcoming = $request->upcoming;

        $user = \App\User::where('id', Auth::user()->id)->with('detail')->first();
        $schedule = \App\Schedule::where(function ($query) use ($user, $id, $stat, $upcoming) {

            if (Auth::user()->role == "siswa") $query->where('requester_id', Auth::user()->id);
            else $query->where('consultant_id', Auth::user()->id);

            $query->whereHas('request', function ($q) use ($user) {
                $q->whereHas('detail', function ($sql) use ($user) {
                    $sql->where('Sekolah', $user->detail->Sekolah);
                });
            });
            $query->where('type_schedule', $id);
            $query->where('status', $stat);
            if ($upcoming == "true") $query->where('time', '>', Carbon::now());
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

        if (empty($request->page)) $skip = 0;
        else $skip = $limit * $request->page;

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
                if ($upcoming == "true") $query->where('time', '>', Carbon::now());
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

        if (empty($request->page)) $skip = 0;
        else $skip = $limit * $request->page;

        if (Auth::user()->role == "siswa" && $id == '') {
            $data = "";
            if ($request->only == "online") {
                $data = \App\Schedule::where('requester_id', Auth::user()->id)->where('type_schedule', 'online');
            } else if ($request->only == "daring") {
                $data = \App\Schedule::where('requester_id', Auth::user()->id)->where('type_schedule', 'daring');
            } else if ($request->only == "direct") {
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

        if (empty($request->page)) $skip = 0;
        else $skip = $limit * $request->page;

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

        if (empty($request->page)) $skip = 0;
        else $skip = $limit * $request->page;

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
