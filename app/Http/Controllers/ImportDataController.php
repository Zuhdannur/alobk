<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Psy\Util\Json;

class ImportDataController extends Controller
{
    public function import(Request $request)
    {

        //Move File
        $file = $request->upload->getClientOriginalExtension();

        if ($file == "xlsx" || $file == "csv") {

            $file = uniqid() . '.' . $file;
            $path = 'uploads' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;

            $find = \App\Sekolah::where('nama_sekolah','like','%'. $request->nama_sekolah .'%')->first();
            if(empty($find)) {
                return response()->json([
                    "message" => "Nama Sekolah Tidak Ada",
                    "status" => 201
                ]);
            }

            $destinationPath = public_path($path);

            File::makeDirectory($destinationPath, 0777, true, true);
            $request->upload->move($destinationPath, $file);


            $baseUrl = getenv('URL_IMPORT_DATA');

            $client = new Client();
            $uploadToAdonisJs = $client->request('POST', $baseUrl . 'import', [
                'multipart' => [
                    [
                        'name' => 'upload',
                        'contents' => fopen($destinationPath . $file, 'r')
                    ],
                    [
                        'name' => 'sekolah_id',
                        'contents' => $find->id
                    ]
                ]
            ]);

            unlink($destinationPath . $file);

            $message = json_decode($uploadToAdonisJs->getBody()->getContents(),true);
            $return = [
                "code" => $uploadToAdonisJs->getStatusCode(),
                "message" => $message['message']
            ];

            return response()->json($return,$uploadToAdonisJs->getStatusCode());
        }
        return response()->json([
            "message" => "extension tidak sesuai , format harus xlsx atau csv",
            "code" => 400
        ],201);
    }
}
