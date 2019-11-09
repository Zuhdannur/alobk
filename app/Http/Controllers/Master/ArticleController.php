<?php


namespace App\Http\Controllers\Master;


use App\Artikel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $data = $this->article;
        if($request->has('take')) {
            $data = $data->take($request->take)->get();
            return Response::json($data, 200);
        }

        if($request->has('orderBy')) {
            $data = $data->orderBy($request->orderBy, 'desc');
        }

        $data = $data->paginate($request->per_page);
        return Response::json($data, 200);
    }

    public function post(Request $request)
    {
        $insert = $this->article;
        $insert->title = $request->title;
        $insert->desc = $request->desc;
        $insert->save();
        if ($insert) {
            return \Illuminate\Support\Facades\Response::json([
                "message" => 'Berhasil membuat artikel.'
            ], 200);
        } else {
            return \Illuminate\Support\Facades\Response::json([
                "message" => 'Gagal membuat artikel.'
            ], 201);
        }
    }

    public function delete($id)
    {
        $delete = $this->article->find($id)->delete();

        if(!$delete) {
            return \Illuminate\Support\Facades\Response::json([
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
