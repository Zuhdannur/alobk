<?php


namespace App\Http\Controllers\Siswa;


use App\Artikel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ArticleController extends Controller
{

    private $article;

    /**
     * ArticleController constructor.
     * @param $article
     */
    public function __construct(Artikel $article)
    {
        $this->article = $article;
    }


    public function all(Request $request) {
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

        return Response::json($results, 200);
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
