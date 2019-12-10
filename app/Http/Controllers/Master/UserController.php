<?php


namespace App\Http\Controllers\Master;

use App\Feed;
use App\Http\Controllers\Controller;
use App\Sekolah;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Spatie\Activitylog\Models\Activity;
use Firebase;

class UserController extends Controller {

    const MODEL = "App\User";

    private $user, $feed, $sekolah;

    /**
     * UserController constructor.
     * @param $user
     */
    public function __construct(User $user, Feed $feed, Sekolah $sekolah)
    {
        $this->user = $user;
        $this->feed = $feed;
        $this->sekolah = $sekolah;
    }


    public function adminCount() {
        $total = $this->user->where('role','admin')->count();

        $doesntHaveSchool = $this->user->where('role', 'admin')->where('sekolah_id', null)->count();

        $hasSchool = $this->user->where('role','admin')->whereNotNull('sekolah_id')->count();

        return Response::json([
            'total' => $total,
            'has_school' => $hasSchool,
            'doesnt_have_school' => $doesntHaveSchool
        ], 200);
    }

    public function countAdminInEverySchool() {
        $smaCount = User::where('role','admin')->whereHas('sekolah', function($query) {
            $query->where('type','SMA');
        })->count();

        $smkCount = User::where('role','admin')->whereHas('sekolah', function($query) {
            $query->where('type','SMK');
        })->count();

        $maCount = User::where('role','admin')->whereHas('sekolah', function($query) {
            $query->where('type','MA');
        })->count();

        $makCount = User::where('role','admin')->whereHas('sekolah', function($query) {
            $query->where('type','MAK');
        })->count();

        return Response::json([
            'total_sma' => $smaCount,
            'total_smk' => $smkCount,
            'total_ma' => $maCount,
            'total_mak' => $makCount
        ], 200);
    }

    public function remove($id)
    {
        $data = $this->user->find($id)->delete();
        if(!$data) {
            return Response::json([
                "message" => "Gagal menghapus akun admin.",
            ], 201);
        }
        return Response::json([
            "message" => "Berhasil menghapus akun admin.",
        ], 200);
    }

    public function getAdmin(Request $request) {
        $user = $this->user->where('role', 'admin')->with('sekolahOnlyName')->paginate($request->per_page);

        return Response::json($user, 200);
    }

    public function all(Request $request) {
        $user = $this->user;

        if ($request->has('orderBy')) {
            $user = $user->orderBy($request->orderBy, 'desc');
        }

        if($request->has('doesnt_have_school')) {
            $user = $user->where('role', 'admin')->whereNull('sekolah_id')->get();
            return Response::json($user, 200);
        }

        if($request->has('has_school')) {
            $user = $user->where('role','admin')->whereNotNull('sekolah_id')->get();
            return Response::json($user, 200);
        }

        if($request->has('take')) {
            $data = $user->where('role','admin')->take($request->take)->get();
            return Response::json($data, 200);
        }

        return $user;
    }

    private function isUsernameExists($username)
    {
        $check = $this->user->where('username', $username)->first();
        if (!$check) {
            return null;
        }
        return $check;
    }


    public function register(Request $request){
        if ($this->isUsernameExists($request->username)) {
            return Response::json([
                'message' => 'Username talah digunakan oleh user lain.'
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
        $insert->kota = $request->kota;
        $insert->tanggal_lahir = $request->tanggal_lahir;
        $insert->kota_lahir = $request->kota_lahir;
        $insert->save();

        if($request->role == 'guru' || $request->role == 'siswa') {
            createUserInFirebase($request);
        }

        return Response::json([
            'user_id' => $insert->id,
            'message' => 'Berhasil daftar.',
            'model' => $insert->with('sekolahOnlyName')->first()
        ], 200);
    }

    private function createUserInFirebase(Request $request) {
        // Jika role nya siswa atau guru, then create firebase account. In order todo chat....
        //if($request->role == 'siswa' || $request->role == 'guru') {
            $data = [
                'name' => $request->name,
                'id' => $insert->id,
                'username' => $request->username,
                'role' => $request->role, 
                'avatar' => $request->avatar,
                'sekolah_id' => $request->sekolah_id
            ];
            Firebase::set('/users/'.$insert->id, $data);
        //}
    }

}

