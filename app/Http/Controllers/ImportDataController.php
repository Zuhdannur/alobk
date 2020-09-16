<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class ImportDataController extends Controller
{
    public function import(Request $request)
    {

        //Move File
        $file = $request->upload->getClientOriginalExtension();


        if ($file == "xlsx" || $file == "csv") {

            $file = uniqid() . '.' . $file;
            $path = 'uploads' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;

            $destinationPath = public_path($path);

            File::makeDirectory($destinationPath, 0777, true, true);
            $request->upload->move($destinationPath, $file);


            $baseUrl = getenv('URL_IMPORT_DATA');

            $client = new Client();
            $request = $client->request('POST', $baseUrl . 'import', [
                'multipart' => [
                    [
                        'name' => 'upload',
                        'contents' => fopen($destinationPath . $file, 'r')
                    ]
                ]
            ]);

            unlink($destinationPath . $file);

            $return = [
                "code" => $request->getStatusCode(),
                "message" => "Imported"
            ];

            return response()->json($return);
        }
        return response()->json([
            "message" => "extension tidak sesuai , format harus xlsx atau csv",
            "code" => 400
        ]);
    }
}
