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

        $data = $data->paginate($request->per_page);
        return Response::json($data, 200);
    }
}
