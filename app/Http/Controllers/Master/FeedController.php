<?php


namespace App\Http\Controllers\Master;
use App\Feed;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class FeedController extends Controller
{
    private $feed;

    /**
     * FeedController constructor.
     * @param $feed
     * @param $user
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    /**
     * FeedController constructor.
     * @param $feed
     */
    public function all(Request $request) {
        $data = $this->feed->where('user_id', Auth::user()->id);
        if($request->has('orderBy')) {
            $data = $data->orderBy($request->orderBy, 'desc');
        }
        if($request->has('take')) {
            $data = $data->take($request->take);
            return Response::json($data->get(), 200);
        }
        if($request->has('paginate')) {
            $data = $data->paginate($request->per_page);
            return Response::json($data, 200);
        }
        $data = $data->get();
        return \response()->json($data, 200);
    }

    public function deleteAll() {
        $deleteAll = $this->feed->where('user_id', Auth::user()->id)->truncate();
        if(!$deleteAll) {
            return Response::json([
                'message' => 'Gagal mengosongkan aktivitas.'
            ], 201);
        }
        return Response::json([
            'message' => 'Berhasil menghapus seluruh aktivitas.'
        ], 200);
    }

    public function count() {
        $count = $this->feed->where('user_id', Auth::user()->id)->count();
        return Response::json($count, 200);
    }

}
