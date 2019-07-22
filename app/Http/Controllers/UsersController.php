<?php namespace App\Http\Controllers;

use App\Classes\Kraken;
use Faker\Provider\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller {

    const MODEL = "App\User";

    public function login(Request $request)
    {
        $user = $this->checking($request->username);
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $apiKey = base64_encode(str_random(40));
                \App\User::where('username', $request->username)->update([
                    'api_token' => $apiKey,
                    'firebase_token' => $request->firebase_token
                ]);
                return Response::json([
                    "message"   => 'success',
                    "api_token" => $apiKey,
                    "role"      => $user->role,
                ], 200);
            } else {
                return Response::json([
                    "message" => 'wrong password',
                ], 201);
            }
        } else {
            return Response::json([
                'message' => 'Username Not Found'
            ], 201);
        }
    }

    public function register(Request $request)
    {
        if (!$this->checking($request->username)) {
            $insert = new \App\User;
//             if ($request->file('photo') != null) {
//                 $image = $request->file('photo');
//                 $realpath = $request->file('photo')->getRealPath();
//                 $filename = time() . '.' . $image->getClientOriginalExtension();
//                 $path = base_path() . '\\public\\image\\';
            // //                $path = public_path('images/'.$filename);
//                 $image->move($path, $filename);
//                 $insert->avatar = $filename;
//             } else {
//                 $filename = 'default.png';
//                 $insert->avatar = $filename;
//             }

//            $kraken = new Kraken("612e57b58501cfdfcaa2493248e99f6d","1c58fdd9be2d5f87f0896197749989883d3ed324");
//
//            $params = array(
//                "file" => "C:\Users\Zuhdan Nur\Pictures\download.png",
//                "wait" => true
//            );
//            $data = $kraken->upload($params);

            $insert->name = $request->name;
            $insert->username = $request->username;
            $insert->password = Hash::make($request->password);
            $insert->role = $request->role;
            $insert->avatar = $request->avatar;
            $insert->save();

            $insertDetail = new \App\DetailUser;
            $insertDetail->id_user = $this->getLastID()->id;
            $insertDetail->gender = $request->gender;
            $insertDetail->address = $request->address;
            $insertDetail->phone_number = $request->phone;

            $isSekolahIdExist = \App\School::find($request->id_sekolah);
            if (!$isSekolahIdExist) {
                return Response::json([
                    'message' => 'sekolah id is not found'
                ]);
            }

            $isKelasExist = \App\Kelas::find($request->id_kelas);
            if (!$isKelasExist) {
                return Response::json([
                    'message' => 'kelas id is not found'
                ]);
            }

            $insertDetail->id_kelas = $request->id_kelas;
            $insertDetail->id_sekolah = $request->id_sekolah;
            
            $insertDetail->save();

            if ($insertDetail) {
                return Response::json([
                    'message' => 'register successfully',
                    'user_id' => $insertDetail->id_user
                ], 200);
            } else {
                return Response::json([
                    'message' => 'register failed'
                ], 400);
            }
        } else {
            return Response::json([
                'message' => 'Duplicate Username'
            ], 201);
        }
    }

    public function getLastID()
    {
        return \App\User::orderBy('id', 'desc')->first();
    }

    public function checking($username)
    {
        $check = \App\User::where('username', $username)->first();
        if ($check) {
            return $check;
        } else {
            return null;
        }
    }

    public function get($id)
    {
        $data = \App\User::where('id', Auth::user()->id)->with('detail', 'detail.sekolah')->first();
        // $data['avatar'] = $data->avatar;
        return Response::json($data, 200);
    }
    
    public function all()
    {
        // $data = \App\User::with('detail', 'kelas', 'sekolah')->get();
        $data = \App\User::with('detail', 'detail.sekolah', 'detail.kelas')->get();
        return Response::json($data, 200);
    }
    
    public function remove($id)
    {
        $data = \App\User::find($id)->delete();
        $detail = \App\DetailUser::find($id)->delete();
        return Response::json([
            "message" => "success",
        ], 200);
    }

    public function put(Request $request)
    {
        $update = \App\User::find(Auth::user()->id)->update([
            'name' => $request->name
        ]);
        
        $kelasId = \App\Kelas::where('nama_kelas', $request->nama_kelas)->first()->id;
        
        if ($update && $kelasId) {
            $update_detail = \App\DetailUser::where('id_user', Auth::user()->id)->update([
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'id_kelas' => $kelasId,
                'gender' => $request->gender
            ]);

            if ($update_detail) {
                return Response::json([
                    "message" => 'profile Updated'
                ], 200);
            } else {
                return Response::json([
                    "message" => 'failed to Updated'
                ], 201);
            }
        } else {
            return Response::json([
                "message" => 'nama siswa atau nama kelas tidak ditemukan'
            ], 201);
        }
        return $request;
    }

    public function updateImageProfile(Request $request)
    {
        $image = \App\User::find(Auth::user()->id);

        // Make sure you've got the Page model
        if ($image) {
            $image->avatar = $request->avatar;
            $image->save();

            return Response::json([
                "message" => "Success to update"
            ], 200);
        } else {
            return Response::json([
                "message" => "Failed to update"
            ], 201);
        }
    }


}
