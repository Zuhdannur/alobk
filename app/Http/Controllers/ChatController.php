<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Berkayk\OneSignal\OneSignalClient;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $params = array(
            'app_id' => 'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
            'included_segments' => ['all'],
            'include_external_user_ids' => ["3"],
            'headings' => array("en" => "Test notifikasi obrolan"),
            'contents' => array("en" => "Test notifikasi obrolan"),
            'filters' => array(["field" => "tag", "key" => "obrolan_notif", "relation" => "=", "value" => "on"])
        );

        $client = new OneSignalClient(
            'e90e8fc3-6a1f-47d1-a834-d5579ff2dfee',
            'Y2QyMTVhMzMtOGVlOC00MjFiLThmNDctMTAzNzYwNDM2YWMy',
            'YzRiYzZlNjAtYmIwNC00MzJiLTk3NTYtNzBhNmU2ZTNjNDQx');

        $client->sendNotificationCustom($params);

        return Response::json([
            'message' => 'Berhasil mengirim notifikasi.'
        ], 200);
    }
}
