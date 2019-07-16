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

    public function notification(Request $request)
    {
        $data = array();
        Helper::sendNotification($data);
//        $data  = [
//            "title" => "test",
//            "message"=> "HALO"
//        ];
////        $message = [
////            "message" => $data['message'],
////        ];
//        $fields = array(
//            'to' => '/topics/global',
//            'data' => $data
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
//        return \response()->json(["responses"=>$result]);
    }

    public function send(Request $request)
    {
        if (Auth::user()->role == "siswa") {
            if ($request->type_schedule == "online") {
                //Store Online With Schedule
                $insert = $this->storeOnlineRequest($request);
            } elseif ($request->type_schedule == "direct") {
                // Go to Direct
                $insert = $this->storeDirect($request);
            } else {
                //Store To Daring
                $insert = $this->storeDaring($request);
            }
            if ($insert) {
//                $pusher = new Pusher(
//                    'e06a6bacb2b9f8503317',
//                    '865963b7338a3b21359a',
//                    '786060',
//                    [
//                        'cluster' => 'ap1',
//                        'useTLS' => true
//                    ]
//                );
//
//                $data['message'] = "Success create schedule";
////                $data['consultant_id'] = $consultant;
//
//                $pusher->trigger('notif-schedule', 'my-event', $data);
                return \Illuminate\Support\Facades\Response::json([
                    "message" => 'success create schedule'
                ], 200);
            } else {
                return Response::json([
                    "message" => 'failed create schedule'
                ], 201);
            }

        } else {

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
//                $pusher = new Pusher(
//                    'e06a6bacb2b9f8503317',
//                    '865963b7338a3b21359a',
//                    '786060',
//                    [
//                        'cluster' => 'ap1',
//                        'useTLS' => true
//                    ]
//                );
//
//                $data['message'] = "confirmation schedule";
//                $data['requester_id'] = \App\Schedule::where('id', $request->schedule_id)->first()->requester_id;
//
//                $pusher->trigger('notif-schedule', 'my-event', $data);
                $schedule = \App\Schedule::where('id', $request->schedule_id)->first();
                $result['requester_id'] = $schedule['requester_id'];
                $result["title"] = $schedule['title'];
                $result['desc'] = $schedule['desc'];
                return Response::json($result, 200);
            } else {
                return [
                    "message" => "failed accept"
                ];
            }
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

        if (empty($request->pPage)) $skip = 0;
        else $skip = $limit * $request->pPage;

        $stats = $request->status;
        $upcoming = $request->upcoming;

        $user = \App\User::where('id', Auth::user()->id)->with('detail')->first();
        $schedule = \App\Schedule::where(function ($query) use ($user, $id, $stats, $upcoming) {

            if (Auth::user()->role == "siswa") $query->where('requester_id', Auth::user()->id);
            else $query->where('consultant_id', Auth::user()->id);

            $query->whereHas('request', function ($q) use ($user) {
                $q->whereHas('detail', function ($sql) use ($user) {
                    $sql->where('school', $user->detail->school);
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

        if (empty($request->pPage)) $skip = 0;
        else $skip = $limit * $request->pPage;

        $stat = $request->status;
        $upcoming = $request->upcoming;

        $user = \App\User::where('id', Auth::user()->id)->with('detail')->first();
        $schedule = \App\Schedule::where(function ($query) use ($user, $id, $stat, $upcoming) {

            if (Auth::user()->role == "siswa") $query->where('requester_id', Auth::user()->id);
            else $query->where('consultant_id', Auth::user()->id);

            $query->whereHas('request', function ($q) use ($user) {
                $q->whereHas('detail', function ($sql) use ($user) {
                    $sql->where('school', $user->detail->school);
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

        if (empty($request->pPage)) $skip = 0;
        else $skip = $limit * $request->pPage;

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
                        $sql->where('school', $user->detail->school);
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

    public function deleteSchedule($id)
    {
        $delete = \App\Schedule::where('id', $id)->delete();
        if ($delete) return \response()->json(["message" => "success"]);
        else return \response()->json(["message" => "failed"]);
    }

    public function mySchedulePageCount(Request $request, $id = '')
    {
        $limit = $request->limit;

        if (empty($request->pPage)) $skip = 0;
        else $skip = $limit * $request->pPage;

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
                        $sql->where('school', $user->detail->school);
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

    private function getSchoolName($id)
    {
        $data = \App\User::where('id', $id)->with('detail')->first();
        return $data;
    }


    public function getPengajuanByStatus(Request $request)
    {
        $limit = $request->limit;

        if (empty($request->pPage)) $skip = 0;
        else $skip = $limit * $request->pPage;

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

        if (empty($request->pPage)) $skip = 0;
        else $skip = $limit * $request->pPage;

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

    private function storeOnlineRequest($request)
    {
        $insert = new \App\Schedule;
        $insert->requester_id = Auth::user()->id;
        $insert->time = $request->time;
//            $consultant = $this->getConsultan()->id;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->type_schedule = $request->type_schedule;
//            $insert->consultant_id = $consultant;
        $insert->save();
        return $insert;
    }

    private function storeDaring($request)
    {
        $insert = new \App\Schedule;
        $insert->requester_id = Auth::user()->id;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->type_schedule = $request->type_schedule;
        $insert->save();
        return $insert;
    }

    private function storeDirect($request)
    {
        $insert = new \App\Schedule;
        $insert->requester_id = Auth::user()->id;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->type_schedule = $request->type_schedule;
        $insert->time = $request->time;
        $insert->location = $request->location;
        $insert->save();
        return $insert;
    }

    public function studentSchedule(Request $request)
    {
        $data = \App\Schedule::where('requester_id', $request->id_user)->orderBy('created_at', 'desc')->take($request->limit)->get();
        // $data['result']['user'] = \App\User::where('id', $data['result']->consultant_id)->get();

        return Response::json($data);
    }

}
