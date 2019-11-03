<?php namespace App\Http\Controllers\Master;

use App\User;
use Event;
use App\Events\MyEvent;
use App\Http\Controllers\Controller;
use App\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SekolahController extends Controller {

    const MODEL = "App\Sekolah";

    private $sekolah, $user;

    /**
     * SekolahController constructor.
     * @param $sekolah
     */
    public function __construct(Sekolah $sekolah, User $user)
    {
        $this->sekolah = $sekolah;
        $this->user = $user;
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

    public function post(Request $request)
    {
        if ($this->isSekolahExists($request->nama_sekolah)) {
            return Response::json([
                'message' => 'Gagal, sekolah telah terdaftar di server.'
            ], 201);
        }
        $this->sekolah->nama_sekolah = $request->nama_sekolah;
        $this->sekolah->alamat = $request->alamat;
        $this->sekolah->save();

        return Response::json([
            'message' => 'Berhasil mendaftarkan sekolah.',
            'id' => $this->sekolah->id
        ], 200);
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

    public function remove($id)
    {
        $delete = $this->sekolah->find($id)->delete();
        if ($delete) {
            return Response::json(["message" => 'Sekolah berhasil dihapus.'], 200);
        } else {
            return Response::json(["message" => 'Sekolah gagal dihapus'], 201);
        }
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

        event(new MyEvent('hello world'));

        return Response::json([
            'message' => 'Berhasil menyunting sekolah.'
        ], 200);

    }

    public function recentActivity() {
        $data = $this->user->feeds;
        return \response()->json($data, 200);
    }

}
