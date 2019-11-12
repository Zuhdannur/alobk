<?php


namespace App\Http\Controllers\Siswa;


use App\Artikel;
use App\Favorite;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Response;

class ArticleController extends Controller
{

    private $article, $favorite;

    /**
     * ArticleController constructor.
     * @param $article
     */
    public function __construct(Artikel $article, Favorite $favorite)
    {
        $this->article = $article;
        $this->favorite = $favorite;
    }

    public function getFavorite(Request $request) {
        $favorite = $this->favorite->where('user_id', Auth::user()->id)->paginate($request->per_page);

        return Response::json($favorite, 200);
    }


    public function all(Request $request) {
        $isBookmarked = Favorite::with('artikel')->with('user')->exists();
        $data = DB::table('artikel')->leftJoin('fav_artikel', function($join) {
            $join->on('artikel.id', '=', 'fav_artikel.artikel_id');
            $join->on('fav_artikel.user_id', '=', DB::raw(Auth::user()->id));
        })->select('artikel.*', 'fav_artikel.id as bookmarked')->where('artikel.title',$request->title)
            ->paginate($request->per_page);

//        $data = DB::select("
//            SELECT
//            exists(select 1 from fav_artikel where fav_artikel.artikel_id = artikel.id and fav_artikel.user_id = user.id limit 1) as hasBookmark,
//            (select fav_artikel.id from fav_artikel where fav_artikel.artikel_id = artikel.id and fav_artikel.user_id = user.id limit 1) as id_favorit
//            ,user.name
//            ,artikel.id
//            ,artikel.title
//            ,artikel.desc
//            ,artikel.created_at
//            ,user.id as user_id
//            FROM
//            artikel,
//            user
//            WHERE user.id =:id AND LOWER(artikel.title) LIKE :q", ['id' => Auth::user()->id, 'q' => '%'.strtolower($request->title).'%']);
//
//        $datas = collect($data);
//
//        $currentPage = LengthAwarePaginator::resolveCurrentPage();
//        // set limit
//        $perPage = $request->per_page;
//        // generate pagination
//        $currentResults = $datas->slice(($currentPage - 1) * $perPage, $perPage)->all();
//        $results = new LengthAwarePaginator($currentResults, $datas->count(), $perPage);

        return \Illuminate\Support\Facades\Response::json($data, 200);
    }

    public function storeFavorite(Request $request)
    {
        $bookmark = User::find(Auth::user()->id)->withAndWhereHas('favorite', function($query) use ($request) {
            $query->where('artikel_id', '=', $request->id);
        })->first();

        if(empty($bookmark)) {
            $insert = new Favorite;
            $insert->artikel_id = $request->id;
            $insert->user_id = Auth::user()->id;
            $insert->save();

            if ($insert) {
                return \response([
                    "message" => "Berhasil menambahkan ke favorit."
                ], 200);
            } else {
                return \response([
                    "message" => "Gagal menambahkan ke favorit."
                ], 201);
            }
        } else {
            $delete = Favorite::where('artikel_id', $request->id)->delete();
            if ($delete) {
                return \response([
                    "message" => "Berhasil menghapus favorit."
                ], 200);
            } else {
                return \response([
                    "message" => "Gagal menghapus favorit."
                ], 201);
            }
        }
    }

    public function post(Request $request)
    {
        $insert = $this->article;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->save();
        if ($insert) {
            return Response::json([
                "message" => 'Berhasil membuat artikel.',
                'model' => $insert
            ], 200);
        } else {
            return Response::json([
                "message" => 'Gagal membuat artikel.'
            ], 201);
        }
    }

    public function delete($id)
    {
        $delete = $this->article->find($id)->delete();

        if(!$delete) {
            return Response::json([
                "message" => 'Gagal menghapus artikel.'
            ], 201);
        }

        return Response::json([
            'message' => 'Berhasil menghapus artikel.'
        ], 200);
    }

    public function put(Request $request, $id)
    {
        $update = $this->article->find($id);

        $update = $update->update([
            'title' => $request->title,
            'desc' => $request->desc
        ]);

        if (!$update) {
            return Response::json([
                'message' => 'Gagal menyunting artikel.'
            ], 201);
        }

        return Response::json([
            'message' => 'Berhasil menyunting artikel.'
        ], 200);
    }

}
