<?php

namespace App\Http\Controllers;

use App\Repositories\UsersRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psy\Util\Json;

class ImportDataController extends Controller
{
    private $userRepository;
    public function __construct(UsersRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

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

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($destinationPath.$file);
            $sheet = $spreadsheet->getActiveSheet()->toArray();

            $errors = "";
            foreach ($sheet as $index => $item) {
                if($index != 0) {
                    $res = $this->insertOrUpdate($item,$find);
                    if(!$res) {
                        $errors .= "$item[0],";
                    }
                }
            }

//            $baseUrl = getenv('URL_IMPORT_DATA');
//
//            $client = new Client();
//            $uploadToAdonisJs = $client->request('POST', $baseUrl . 'import', [
//                'multipart' => [
//                    [
//                        'name' => 'upload',
//                        'contents' => fopen($destinationPath . $file, 'r')
//                    ],
//                    [
//                        'name' => 'sekolah_id',
//                        'contents' => $find->id
//                    ]
//                ]
//            ]);
//
//
//            $message = json_decode($uploadToAdonisJs->getBody()->getContents(),true);
//            $return = [
//                "code" => $uploadToAdonisJs->getStatusCode(),
//                "message" => $message['message']
//            ];
            unlink($destinationPath . $file);
            $status = 200;
            if(!empty($errors) > 0) {
                $message = [
                    "status" => false,
                    "message" => "ada kesalahan pada nim: $errors"
                ];
                $status = 201;
            } else {
                $message = [
                    "status" => true,
                    "message" => "sukses"
                ];
            }

            return Response::json($message,$status);
        }
        return response()->json([
            "message" => "extension tidak sesuai , format harus xlsx atau csv",
            "code" => 400
        ],201);
    }

    public function insertOrUpdate($data , $sekolah) {
        $find = \App\User::where('username',$data[0])->where('sekolah_id',$sekolah->id)->first();
        if(!empty($find)) {
            return false;
        } else {
            $request = new \Illuminate\Http\Request();
            $request->replace([
                'username' => $data[0],
                'password' => $data[2],
                'role' => "siswa",
                'name' => $data[3],
                'jenkel' => $data[4],
                'nomor_hp' => $data[5],
                'kelas' => $data[6],
                'alamat' => $data[7],
                'kota' => $data['8'],
                'tanggal_lahir' => $this->birthDateFormatted($data[9]),
                'school' => $sekolah->nama_sekolah,
                'sekolah_id' => $sekolah->id
            ]);

            return $this->userRepository->registerNoReturnJson($request);
        }
    }

    public function birthDateFormatted($data) {
        if(str_contains($data,'/')) {
            $date = explode("/",$data);
            $year = $date[2];
            $month = $date[1];
            $day = $date[0];
            return "$year-$month-$day";
        } else {
            return "";
        }
    }

    public function downloadExampleFile() {
        $filePath = public_path()."/example.xlsx";

        return response()->download(
            $filePath,"example.xlsx",[
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'inline; filename="example.xlsx"'
            ]
        );
    }
}
