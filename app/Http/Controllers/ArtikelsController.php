<?php namespace App\Http\Controllers;

use App\Favorite;
use http\Env\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Http\Request;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ArtikelsController extends Controller
{
    public $searchQuery = "SELECT 
                        exists(select 1 from `favorite` fav where fav.id_artikel = p.id_artikel and fav.id_user = u.id limit 1) as bookmarked
                        , u.nama
                        , p.id_artikel
                        FROM
                        user u,
                        artikel p
                        WHERE
                        u.id = 2 AND
                        p.nama_artikel LIKE '%GurindaM%'";

    public function getTitle()
    {
        $data = \App\Artikel::select('title', 'id')->get();
        return \Illuminate\Support\Facades\Response::json([
            "message" => 'success',
            "result" => $data
        ], 200);
    }

    public function create(Request $request)
    {
        $insert = new \App\Artikel;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->save();
        if ($insert) {
            return \Illuminate\Support\Facades\Response::json([
                "message" => 'success'
            ], 200);
        } else {
            return \Illuminate\Support\Facades\Response::json([
                "message" => 'failed'
            ], 201);
        }
    }

    public function getRelatedArtikel(Request $request)
    {

//        $data = \App\Artikel::where('LOWER(`title`)','LIKE','%'.strtolower($request->title).'%')->get();
        // $datas = \App\Artikel::where(function ($q) use ($request) {
        //     $q->whereRaw('LOWER(title) LIKE ? ', '%' . strtolower($request->title) . '%');
        // });

        // $categorias = \App\Favorit::with(['artikel' => function($query) use ($bookmark){
        //     $query->select($bookmark, 'tbl_user.name', 'tbl_artikel.title')
        // }])->whereRaw('u.id =:id', ['id' => 1])->get();

        // $data = DB::selectOne(
        //     'SELECT exists(select 1 from tbl_fav_artikel fav where fav.id_artikel = tbl_artikel.id and fav.id_user = tbl_user.id limit 1) as bookmarked FROM tbl_artikel, tbl_user'
        // );


        // $data = \App\Favorite::with(['artikel','user'], function ($q) use ($request) {
        //     $q->select(array(
        //         DB::raw('exists(select 1 from tbl_fav_artikel fav where fav.id_artikel = artikel.id and fav.id_user = user.id limit 1) as hasBookmark')
        //         ,'user.name'
        //         ,'artikel.title'
        //     ))->whereRaw('tbl_user.id:=id', ['id' => 1])
        //     ->whereRaw('tbl_artikel.title LIKE ? ', '%' . strtolower($request->title) . '%');
        // })->get();
        // WHERE u.id =:id AND p.title LIKE :q", ['id' => 1, 'q' => '%'.$request->title.'%']);
        // $data = DB::select(
        //     "exists(select 1 from tbl_fav_artikel fav where fav.id_artikel = p.id and fav.id_user = u.id limit 1) as hasBookmark",
        //     "u.name",
        //     "p.id",
        //     "p.title",
        //     "p.desc"
        // )->from('tbl_user u', 'tbl_artikel p');

        // $data = DB::table('tbl_artikel')
        //     ->select(array(
        //         DB::raw('exists(select 1 from tbl_fav_artikel fav where fav.id_artikel = tbl_artikel.id and fav.id_user = tbl_user.id limit 1) as hasBookmark')
        //         ,'tbl_user.name'
        //         ,'tbl_artikel.title'
        //     ))->from('tbl_user')->get();



        $data = DB::select("
            SELECT
            exists(select 1 from tbl_fav_artikel where tbl_fav_artikel.id_artikel = tbl_artikel.id and tbl_fav_artikel.id_user = tbl_user.id limit 1) as hasBookmark,
            (select tbl_fav_artikel.id_favorit from tbl_fav_artikel where tbl_fav_artikel.id_artikel = tbl_artikel.id and tbl_fav_artikel.id_user = tbl_user.id limit 1) as id_favorit
            ,tbl_user.name
            ,tbl_artikel.id
            ,tbl_artikel.title
            ,tbl_artikel.desc
            ,tbl_artikel.created_at
            ,tbl_user.id as user_id
            FROM
            tbl_artikel,
            tbl_user
            WHERE tbl_user.id =:id AND LOWER(tbl_artikel.title) LIKE :q", ['id' => Auth::user()->id, 'q' => '%'.strtolower($request->title).'%']);

        $datas = collect($data);

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        // set limit
        $perPage = $request->per_page;
        // generate pagination
        $currentResults = $datas->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $results = new LengthAwarePaginator($currentResults, $datas->count(), $perPage);

        return \Illuminate\Support\Facades\Response::json($results, 200);
    }

//     public function getRelatedArtikelCount(Request $request)
//     {
//         $limit = $request->limit;

//         if ($request->page == "") {
//             $skip = 0;
//         } else {
//             $skip = $limit * $request->page;
//         }

    // //        $data = \App\Artikel::where('LOWER(`title`)','LIKE','%'.strtolower($request->title).'%')->get();
//         $datas = \App\Artikel::where(function ($q) use ($request) {
//             $q->whereRaw('LOWER(title) LIKE ? ', '%' . strtolower($request->title) . '%');
//         });

//         $datas = \App\Artikel::selectRaw('')

//         $count = $datas
//         ->paginate($limit)
//         ->lastPage();

//         return \Illuminate\Support\Facades\Response::json([
//             "total_page" => $count
//         ], 200);
//     }

    public function storeFavorite(Request $request, $id)
    {
        $bookmark = User::find(Auth::user()->id)->artikel()->where('artikel_id', '=', $id)->first();

        if(empty($bookmark)) {
            $insert = new Favorite;
            $insert->id_artikel = $id;
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
            $delete = Favorite::where('artikel_id', $id)->where('id', $request->favorite_id)->delete();
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

    public function removeMyFavorit($id, $id_favorit)
    {
        $delete = Favorite::where('id_artikel', $id)->where('id_favorit', $id_favorit)->delete();
        if ($delete) {
            return \response([
                "message" => "succsess"
            ], 200);
        } else {
            return \response([
                "message" => "failed"
            ], 201);
        }
    }

    private function getFavorite(Request $request) {
        $datas = Favorite::where('user_id', Auth::user()->id)->with('artikel');
        $paginate = $datas->paginate($request->per_page);

        return $paginate;
    }

    public function getMyFavorite(Request $request)
    {
        $paginate = $this->getFavorite($request);

        // $result = [];
        // foreach ($data as $key => $value) {
        //     $result[$key] = $value['artikel'];
        //     $result[$key]['id_favorit'] = $value->id_favorit;
        //     $result[$key]['id_user'] = Auth::user()->id;
        // }
        return \response()->json($paginate, 200);
    }

    public function getMyFavoriteCount(Request $request)
    {
        $total = $this->getFavorite($request)->total();

        return \Illuminate\Support\Facades\Response::json([
            "total" => $total
        ], 200);
    }

    public function checkingArtikel($id)
    {
        $check = Favorite::where([['id_user',Auth::user()->id],['id_favorit',$id]])->get();
        if (count($check) > 0) {
            return true;
        } else {
            return false;
        }
    }

}
