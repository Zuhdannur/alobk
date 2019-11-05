<?php


namespace App\Http\Controllers\Master;
use App\Feed;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;


class FeedController
{
    private $feed;
    private $user;

    /**
     * FeedController constructor.
     * @param $feed
     * @param $user
     */
    public function __construct(Feed $feed, User $user)
    {
        $this->feed = $feed;
        $this->user = $user;
    }

    /**
     * FeedController constructor.
     * @param $feed
     */


    public function all(Request $request) {
        $data = $this->feed->where('user_id', Auth::user()->id);
        if($request->has('paginate')) {
            $data = $data->paginate($request->per_page);
            return Response::json($data, 200);
        }
        $data = $data->get();
        return \response()->json($data, 200);
    }


}
