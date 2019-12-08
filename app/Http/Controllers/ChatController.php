<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Berkayk\OneSignal\OneSignalClient;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $senderName = Auth::user()->name;
        $senderMessage = $request->message;

        $params = array(
            'app_id' => 'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
            'included_segments' => ['all'],
            'include_external_user_ids' => ["$request->receiver_id"],
            'headings' => array("en" => "$senderName"),
            'contents' => array("en" => "$senderMessage"),
            'data' => [
                "chat_id" => $request->id,
                "to" => 'guru',
                "type" => "chat",
                "detail" => "guru_receive_finish"
            ],
            'android_group' => $request->id,
            'android_group_message' => array("en" => "Kamu memiliki banyak pesan baru.")
        );

        $client = new OneSignalClient(
            'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
            'Y2QyMTVhMzMtOGVlOC00MjFiLThmNDctMTAzNzYwNDM2YWMy',
            'YzRiYzZlNjAtYmIwNC00MzJiLTk3NTYtNzBhNmU2ZTNjNDQx');

        // if(Auth::user()->role == 'siswa') {
        //     $client->sendNotificationToExternalUser(
        //         "$senderMessage",
        //         $request->receiver_id,
        //         $url = null,
        //         $data = [
        //             "chat_id" => $update->id,
        //             "to" => 'guru',
        //             "type" => "chat",
        //             "detail" => "guru_receive_finish"
        //         ],
        //         $buttons = null,
        //         $schedule = null,
        //         $headings = "$senderName"
        //     );            
        // }
        

        $client->sendNotificationCustom($params);

        return Response::json([
            'message' => 'Berhasil mengirim notifikasi.'
        ], 200);
    }
}
