<?php namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SekolahController extends Controller {

    const MODEL = "App\Sekolah";

    private $sekolah;

    /**
     * SekolahController constructor.
     * @param $sekolah
     */
    public function __construct(Sekolah $sekolah)
    {
        $this->sekolah = $sekolah;
    }

    public function all(Request $request)
    {
        $per_page = $request->per_page;

        $data = $this->sekolah;

        if ($request->has('orderBy')) {
            $data = $data->orderBy($request->orderBy, 'desc');
        }

        if ($request->has('get_with_admin')) {
            $data = $this->sekolah->with('firstAdmin');
        }

        if($request->has('not_manage_by_admin')) {
            $data = $this->sekolah->doesntHave('user')->orWhereHas('user', function ($query) {
                $query->whereNotIn('role', ['admin']);
            })->get();
            return Response::json($data, 200);
        }


        $data = $data->paginate($per_page);

        return Response::json($data, 200);
    }

    public function count() {
        $total = $this->sekolah->count();

        $doesntHaveAdmin = $this->sekolah->doesntHave('user')->orWhereHas('user', function ($query) {
            $query->whereNotIn('role', ['admin']);
        })->count();

        $hasAdmin = $this->sekolah->whereHas('user', function ($query) {
            $query->where('role', 'admin');
        })->count();

        return Response::json([
            'total' => $total,
            'has_admin' => $hasAdmin,
            'doesnt_have_admin' => $doesntHaveAdmin
        ], 200);
    }

    public function put(Request $request, $id) {
        $sekolah = $this->sekolah->find($id);

        $update = $sekolah->update([
            'nama_sekolah' => $request->nama_sekolah,
            'alamat' => $request->alamat
        ]);

        if(!$update) {
            return Response::json([
                'message' => 'Gagal menyunting sekolah.'
            ], 201);
        }

        return Response::json([
            'message' => 'Berhasil menyunting sekolah.'
        ], 200);

    }

}
