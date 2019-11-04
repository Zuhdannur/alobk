<?php


namespace App\Http\Controllers\Master;

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

    public function getAdmin(Request $request) {
        $user = $this->user->where('role', 'admin')->with('sekolahOnlyName')->paginate($request->per_page);

        return Response::json($user, 200);
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

    public function recentActivity(Request $request) {
        $data = $this->feed->where('user_id', Auth::user()->id)->orderBy('created_at', 'desc');
        if($request->has('take')) {
            $data = $data->take($request->take);
            return Response::json($data->get(), 200);
        }
        return \response()->json($data->paginate($request->per_page), 200);
    }

}

