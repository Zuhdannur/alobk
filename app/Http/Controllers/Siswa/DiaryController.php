<?php


namespace App\Http\Controllers\Siswa;


use App\Diary;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class DiaryController extends Controller
{
    private $diary;

    /**
     * DiaryController constructor.
     * @param $diary
     */
    public function __construct(Diary $diary)
    {
        $this->diary = $diary;
    }

    public function all(Request $request) {
        $data = $this->diary->where('user_id', Auth::user()->id);

        if(!empty($request->kategori)) {
            $data = $data->where('kategori',$request->kategori);
        }

        if($request->has('orderBy')) {
            $data = $data->orderBy($request->orderBy, 'desc');
        }
        $data = $data->paginate($request->per_page);

        return Response::json($data, 200);
    }

    public function post(Request $request) {
        $insert = $this->diary;
        $insert->user_id = Auth::user()->id;
        $insert->body = $request->body;
        $insert->title = $request->title;
        $insert->tgl = $request->tgl;
        $insert->kategori = $request->kategori;
        $insert->save();

        return Response::json([
            'message' => 'Berhasil menambah catatan.',
            'data' => $insert
        ], 200);
    }

    public function remove($id)
    {
        $data = $this->diary->where('id', $id)->where('user_id', Auth::user()->id)->delete();
        if (!$data) {
            return Response::json([
                "message" => 'Gagal menghapus data.'
            ], 201);
        }
        return Response::json([
            "message" => "Berhasil menghapus data.",
        ], 200);
    }

    public function put(Request $request)
    {
        $update = tap($this->diary->find($request->id))->update([
            'title' => $request->title,
            'body' => $request->body,
            'tgl' => $request->tgl,
            'kategori' => $request->kategori
        ]);

        if (!$update) {
            return Response::json([
                "message" => 'Gagal menyunting catatan.'
            ], 201);
        }

        return Response::json([
            "data" => $update,
            "message" => 'Berhasil menyunting catatan.'
        ], 200);
    }


}
