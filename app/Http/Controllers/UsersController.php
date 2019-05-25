<?php namespace App\Http\Controllers;

use Faker\Provider\Image;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{

    public function login(Request $request)
    {
        $user = $this->checking($request->username);
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $apiKey = base64_encode(str_random(40));
                \App\User::where('username', $request->username)->update([
                    'api_token' => $apiKey
                ]);
                return Response::json([
                    "message" => 'success',
                    "api_token" => $apiKey
                ],200);
            }
        } else {
            return Response::json([
                'message' => 'Username Not Found'
            ],201);
        }
    }

    public function register(Request $request)
    {

        if (!$this->checking($request->username)) {
            $insert = new \App\User;
            if ($request->file('photo') != null) {
                $image = $request->file('photo');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $path = base_path() . '\\public\\image\\';
//                $path = public_path('images/'.$filename);
                $image->move($path, $filename);
                $insert->avatar = $filename;
            } else {
                $insert->avatar = 'default.png';
            }

            $insert->name = $request->name;
            $insert->username = $request->username;
            $insert->password = Hash::make($request->password);
            $insert->role = $request->role;
            $insert->save();

            $insertDetail = new \App\DetailUser;
            $insertDetail->id_user = $this->getLastID()->id;
            $insertDetail->gender = $request->gender;
            $insertDetail->address = $request->address;
            $insertDetail->phone_number = $request->phone;
            $insertDetail->class = $request->class;
            $insertDetail->school = $request->school;
            $insertDetail->save();

            if ($insertDetail) {
                return Response::json([
                    'message' => 'register successfully'
                ],200);
            } else {
                return Response::json([
                    'message' => 'register failed'
                ],400);
            }
        } else {
            return Response::json([
                'message' => 'Duplicate Username'
            ],201);
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
        } else return null;
    }

    public function getMyProfile()
    {
        $data = \App\User::where('id', Auth::user()->id)->with('detail')->first();
        $data['avatar'] = base_path() . '\\public\\image\\' . $data->avatar;
        return Response::json([
            "message" => "success",
            "result" => $data
        ],200);
    }

    public function updateProfile(Request $request)
    {
        $update = \App\User::find(Auth::user()->id)->update([
            'name' => $request->name
        ]);
        if ($update) {
            if ($request->class == null || $request->school == null) {
                $class = '';
                $school = '';
            } else {
                $class = $request->class;
                $school = $request->school;
            }
            $update_detail = \App\DetailUser::where('id_user', Auth::user()->id)->update([
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'class' => $class,
                'school' => $school
            ]);

            if($update_detail){
                return Response::json([
                    "message" => 'profile Updated'
                ],200);
            } else {
                return Response::json([
                    "message" => 'failed to Updated'
                ],201);
            }
        }
        return $request;
    }

}
