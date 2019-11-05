<?php


namespace App\Http\Controllers\Admin;

use App\Feed;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\Activitylog\Models\Activity;

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


    public function getAdminCount() {
        $data = $this->user->where('sekolah_id', Auth::user()->sekolah_id);

        $total = $data->whereIn('role', ['siswa','guru','supervisor'])->count();

        $guruTotal = $data->where('role', 'guru')->count();

        $siswaTotal = $data->where('role', 'siswa')->count();

        $supervisorTotal = $data->where('role', 'supervisor')->count();

        return Response::json([
            'total' => $total,
            'total_siswa' => $siswaTotal,
            'total_guru' => $guruTotal,
            'total_supervisor' => $supervisorTotal
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
//
//    public function remove($id)
//    {
//        $data = $this->user->find($id)->delete();
//        if(!$data) {
//            return Response::json([
//                "message" => "Gagal menghapus akun admin.",
//            ], 201);
//        }
//        return Response::json([
//            "message" => "Berhasil menghapus akun admin.",
//        ], 200);
//    }
//
//    public function getAdmin(Request $request) {
//        $user = $this->user->where('role', 'admin')->with('sekolahOnlyName')->paginate($request->per_page);
//
//        return Response::json($user, 200);
//    }
//
//    public function all(Request $request) {
//        $user = $this->user;
//
//        if($request->has('doesnt_have_school')) {
//            $user = $user->where('role', 'admin')->whereNull('sekolah_id')->get();
//            return Response::json($user, 200);
//        }
//
//        if($request->has('has_school')) {
//            $user = $user->where('role','admin')->whereNotNull('sekolah_id')->get();
//            return Response::json($user, 200);
//        }
//
//        return $user;
//    }
}

