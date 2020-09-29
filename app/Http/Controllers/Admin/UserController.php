<?php


namespace App\Http\Controllers\Admin;

use App\Feed;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\Activitylog\Models\Activity;
use Firebase;

class UserController extends Controller {

    const MODEL = "App\User";

    private $user, $feed;

    /**
     * UserController constructor.
     * @param $user
     */
    public function __construct(User $user, Feed $feed)
    {
        $this->user = $user;
        $this->feed = $feed;
    }

    public function getUsers(Request $request) {
        $data = $this->user->where('sekolah_id', Auth::user()->sekolah_id)->where('role','!=','admin');

        if($request->has('role')) {
            $data = $data->where('role', $request->role);
        }

        $data = $data->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function getUsersWeb(Request $request) {
        $data = $this->user->where('sekolah_id', Auth::user()->sekolah_id)->where('role','!=','admin');
        if($request->has('role')) {
                $data = $data->where('role', $request->role);
        }

        return Response::json($data, 200);
    }


    public function getAdminCount() {

        $total = $this->user->where('sekolah_id', Auth::user()->sekolah_id)->whereIn('role', ['siswa','guru','supervisor'])->count();

        $guruTotal = $this->user->where('sekolah_id', Auth::user()->sekolah_id)->where('role', 'guru')->count();

        $siswaTotal = $this->user->where('sekolah_id', Auth::user()->sekolah_id)->where('role', 'siswa')->count();

        $supervisorTotal = $this->user->where('sekolah_id', Auth::user()->sekolah_id)->where('role', 'supervisor')->count();

        $aktivitasTotal = $this->feed->where('user_id', Auth::user()->id)->count();

        return Response::json([
            'total' => $total,
            'total_siswa' => $siswaTotal,
            'total_guru' => $guruTotal,
            'total_supervisor' => $supervisorTotal,
            'total_feed' => $aktivitasTotal
        ], 200);
    }

    public function recentActivity(Request $request) {
        $data = $this->feed->where('user_id', Auth::user()->id)->orderBy('created_at', 'desc');
        if($request->has('take')) {
            $data = $data->take($request->take);
            return Response::json($data->get(), 200);
        }
        return \response()->json($data->paginate($request->per_page), 200);
    }

    public function remove($id)
    {
        $data = $this->user->find($id);
        $delete = $data->delete();
        if($data->role == 'siswa' || $data->role == 'guru') {
            $this->removeFirebaseUser($id);
        }
        if(!$delete) {
            return Response::json([
                "message" => "Gagal menghapus akun.",
            ], 201);
        }
        return Response::json([
            "message" => "Berhasil menghapus akun.",
        ], 200);
    }

    private function removeFirebaseUser($id) {
        Firebase::delete('/users/'.$id);
    }

}

