<?php


namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Response;

class UserController extends Controller {

    const MODEL = "App\User";

    private $user;

    /**
     * UserController constructor.
     * @param $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function adminCount() {
        $total = $this->user->count();

        $doesntHaveSchool = $this->user->where('role', 'admin')->where('sekolah_id',null)->count();

        $hasSchool = $this->user->where('role','admin')->whereNotNull('sekolah_id')->count();

        return Response::json([
            'total' => $total,
            'has_school' => $hasSchool,
            'doesnt_have_school' => $doesntHaveSchool
        ], 200);
    }

    public function all(Request $request) {
        $user = $this->user;

        if($request->has('doesnt_have_school')) {
            $user = $user->where('role', 'admin')->whereNull('sekolah_id')->get();
            return Response::json($user, 200);
        }

        if($request->has('has_school')) {
            $user = $user->where('role','admin')->whereNotNull('sekolah_id')->get();
            return Response::json($user, 200);
        }

        return $user;
    }

    public function recentActivity() {
        $data = $this->user->feed;
        return \response()->json($data, 200);
    }

}

