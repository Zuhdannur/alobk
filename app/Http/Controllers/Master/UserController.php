<?php


namespace App\Http\Controllers\Master;

use App\Feed;
use App\Http\Controllers\Controller;
use App\Sekolah;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\Activitylog\Models\Activity;

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
        $school = $this->user->where('role','admin')->whereNotNull('sekolah_id');

        $smaCount = $school->whereHas('sekolah', function($query) {
            $query->where('type','SMA');
        })->count();

        $smkCount = $school->whereHas('sekolah', function($query) {
            $query->where('type','SMK');
        })->count();

        $maCount = $school->whereHas('sekolah', function($query) {
            $query->where('type','MA');
        })->count();

        $makCount = $school->whereHas('sekolah', function($query) {
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

}

