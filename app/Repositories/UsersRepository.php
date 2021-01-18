<?php


namespace App\Repositories;


use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Firebase;

class UsersRepository
{

    private $user;

    /**
     * UsersRepository constructor.
     * @param $user
     * @param $detailUser
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    private function isUsernameExists($username , $role , $school)
    {
//        $check = $this->user->where('username', $username)->where('role',$role)
//          ->wherehas('sekolah',function ($q) use ($school , $role) {
//            if($role != "master") {
//                $q->where('nama_sekolah',$school);
//            }
//        })
//->first();
        $check = $this->user->makeVisible('password')->where('username', $username)->where('role',$role);
        if($role != "master") {
            $check->wherehas('sekolah',function ($q) use ($school , $role) {
                    $q->where('nama_sekolah',$school);
            });
        }

//        if (empty($check)) {
//            return null;
//        }
        return $check->first();
    }


    public function register(Request $request)
    {
        if ($this->isUsernameExists($request->username,$request->role,$request->school)) {
            return Response::json([
                'message' => 'Username ini telah digunakan.'
            ], 201);
        }

        $insert = $this->user;
        $insert->name = $request->name;
        $insert->username = $request->username;
        $insert->password = Hash::make($request->password);
        $insert->role = $request->role;
        $insert->avatar = $request->avatar;

        $insert->jenkel = $request->jenkel;
        $insert->alamat = $request->alamat;
        $insert->nomor_hp = $request->nomor_hp;
        $insert->kelas = $request->kelas;
        $insert->sekolah_id = $request->sekolah_id;
        $insert->perguruan_tinggi = $request->perguruan_tinggi;
        $insert->kota = $request->kota;
        $insert->tanggal_lahir = $request->tanggal_lahir;
        $insert->kota_lahir = $request->kota_lahir;
        $insert->save();

        if($request->role == 'guru' || $request->role == 'siswa') {
            // createUserInFirebase($request);
            $this->createUserInFirebase($request,$insert);
        }

        return Response::json([
            'user_id' => $insert->id,
            'message' => 'Berhasil daftar.'
        ], 200);
    }

    private function createUserInFirebase(Request $request , $params) {
        // Jika role nya siswa atau guru, then create firebase account. In order todo chat....
        //if($request->role == 'siswa' || $request->role == 'guru') {
            $data = [
                'name' => $request->name,
                'id' => $params->id,
                'username' => $request->username,
                'role' => $request->role,
                'avatar' => $request->avatar,
                'sekolah_id' => $request->sekolah_id
            ];
            Firebase::set('/users/'.$params->id, $data);
        //}
    }

    private function getLastID()
    {
        return $this->user->orderBy('id', 'desc')->first();
    }

    public function login(Request $request)
    {
        $user = $this->isUsernameExists($request->username,$request->role,$request->school);

        if (!$user) {
            return Response::json([
                'message' => 'Akun tidak ditemukan.'
            ], 201);
        }

        if (!Hash::check($request->password, $user->password)) {
            return Response::json([
                "message" => 'Username atau kata sandi salah.',
            ], 201);
        }

        $apiKey = base64_encode(str_random(40));
        $this->user->where('username', $request->username)->update([
            'api_token' => $apiKey,
            'firebase_token' => $request->firebase_token
        ]);

        if ($user->role == 'siswa') {
            $data = $this->user->where('api_token', $apiKey)->with('sekolah')->first();
        } else {
            $data = $this->user->where('api_token', $apiKey)->with('sekolah')->first();
            $this->addTopic($data);
        }

        if(!empty($user->role)) {
            if(!in_array($user->role,array("admin","master")) && $request->type="web") {
                return Response::json([
                    "message" => 'Akun Tidak Memiliki Otoritas Admin ',
                ], 201);
            }

        }

        return Response::json([
            "message" => 'success',
            "api_token" => $apiKey,
            "role" => $user->role,
            "data" => $data
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $user = $this->user->find(Auth::user()->id);

        if (!Hash::check($request->oldPassword, $user->password)) {
            return Response::json(
                ["message" => "Kata sandi saat ini salah."],
                201);
        }

        if (Hash::check($request->newPassword, $user->password)) {
            return Response::json(
                ["message" => "Kata sandi baru tidak boleh sama dengan kata sandi saat ini."],
                201);
        }

        $user->password = Hash::make($request->newPassword);
        $save = $user->save();

        $updateHasEver = $user->update([
            'ever_change_password' => 1
        ]);

        if (!$save || !$updateHasEver) {
            return Response::json(
                ["message" => "Gagal mengganti kata sandi."],
                201);
        }

        return Response::json(["message" => "Kata sandi berhasil diubah."], 200);
    }

    public function get($id)
    {
        if (Auth::user()->role == 'siswa') {
            $data = $this->user->where('api_token', $id)->with('sekolah')->first();
        } else {
            $data = $this->user->where('api_token', $id)->with('sekolah')->first();
            $this->addTopic($data);
        }

        return Response::json($data, 200);
    }

    private function addTopic($data)
    {
        // $client = new Client();
        // $client->setApiKey(self::$API_ACCESS_KEY);
        // $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

        // $query = \App\User::where('role', 'guru')->withAndWhereHas('detail', function ($query) {
        //     $query->where('sekolah_id', Auth::user()->detail->sekolah_id);
        // })->get();

        // $pattern = "guru";

        // foreach ($query as $value) {
        //     $client->addTopicSubscription($pattern.$value['detail']['sekolah_id']."pengajuan", $value['firebase_token']);
        // }
    }

    public function checkUsername($username)
    {
        $check = $this->user->where('username', $username)->first();
        if ($check) {
            return Response::json(['message' => 'Username telah terdaftar.'], 201);
        }
        return Response::json(['message' => 'Username dapat digunakan.'], 200);
    }


    public function all()
    {
        $data = $this->user->with('sekolah')->get();
        return Response::json($data, 200);
    }

    public function remove($id)
    {
        $data = $this->user->find($id)->delete();
        return Response::json([
            "message" => "success",
        ], 200);
    }

    public function put(Request $request)
    {

        $update = $this->user->where('id', Auth::user()->id)->first();

        $update = $update->fill($request->input())->save();

        if (!$update) {
            return Response::json(['message' => 'Gagal menyunting profils.']);
        }

        return Response::json(["message" => 'Profil berhasil disunting.'], 200);
    }

    public function getStudentInfo($id)
    {
        $data = $this->user->where('id', $id)->with('sekolah')->first();
        return Response::json($data, 200);
    }

    public function updateImageProfile(Request $request)
    {
        $image = $this->user->find(Auth::user()->id);

        if (!$image) {
            return Response::json([
                "message" => "Failed to update"
            ], 201);
        }

        $image->avatar = $request->avatar;
        $image->save();

        return Response::json([
            "message" => "Success to update"
        ], 200);
    }

    public function getAllGuru(Request $request){
        $query = \App\User::where([['role','guru'],['sekolah_id',Auth::user()->sekolah_id]]);

        return $query;
    }

    public function getAllGuruWithoutPaging(Request $request) {
        $query = \App\User::where([['role','guru'],['sekolah_id',Auth::user()->sekolah_id]])->get();
        return response()->json($query);
    }

    public function getDetailGuru($id) {
        $query = \App\User::find($id)->first();

        return \response()->json($query);
    }

}
