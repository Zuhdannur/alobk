<?php namespace App\Http\Controllers;

use Faker\Provider\Image;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller {

    public function login(Request $request){
       $user = $this->checking($request->username);
       if($user){
           if(Hash::check($request->password,$user->password)){
               $apiKey = base64_encode(str_random(40));
               \App\User::where('username',$request->username)->update([
                   'api_token' => $apiKey
               ]);
               return [
                 "message" => 'success',
                 "api_token" => $apiKey
               ];
           }
       } else {
           return [
               'message'=>'Username Not Found'
           ];
       }
    }

    public function register(Request $request){

        if(!$this->checking($request->username)){
            $insert = new \App\User;
            if($request->file('photo')->getClientOriginalName()){
                $image = $request->file('photo');
                $filename = time() .'.'. $image->getClientOriginalExtension();
                $path = base_path().'\\public\\image\\';
//                $path = public_path('images/'.$filename);
                $image->move($path,$filename);
                $insert->avatar =$filename;
            } else {
                $insert->avatar ='default.png';
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

            if($insertDetail){
                return [
                    'message' => 'register successfully'
                ];
            } else {
                return [
                    'message' => 'register failed'
                ];
            }
        } else {
            return [
                'message' => 'Duplicate Username'
            ];
        }
    }

    public function getLastID(){
        return \App\User::orderBy('id','desc')->first();
    }

    public function checking($username){
        $check = \App\User::where('username',$username)->first();
        if($check){ return $check;}
        else return null;
    }

    public function getMyProfile()
    {
        $data = \App\User::where('id',Auth::user()->id)->with('detail')->first();
        return [
            "message" => "success",
            "result" => $data
        ];
    }
}
