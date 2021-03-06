<?php namespace App\Http\Controllers\Master;

use App\Artikel;
use App\Events\MyEvent;
use App\Feed;
use App\Http\Controllers\Controller;
use App\Sekolah;
use App\User;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class SekolahController extends Controller
{

    const MODEL = "App\Sekolah";

    private $sekolah, $user, $artikel, $recent;

    /**
     * SekolahController constructor.
     * @param $sekolah
     */
    public function __construct(Sekolah $sekolah, User $user, Artikel $artikel, Feed $recent)
    {
        $this->sekolah = $sekolah;
        $this->user = $user;
        $this->artikel = $artikel;
        $this->recent = $recent;
    }

    public function all(Request $request)
    {
        $per_page = $request->per_page;

        $data = $this->sekolah;

        if ($request->has('get_with_admin')) {
            $data = $this->sekolah->with('firstAdmin');
        }

        if($request->has('take')) {
            $data = $data->orderBy('created_at', 'desc')->take($request->take)->get();
            return Response::json($data, 200);
        }

        if ($request->has('not_manage_by_admin')) {
            $data = $this->sekolah->doesntHave('user')->orWhereHas('user', function ($query) {
                $query->whereNotIn('role', ['admin']);
            })->get();
            return Response::json($data, 200);
        }

        $data = $data->orderBy('created_at', 'desc')->paginate($per_page);

        return Response::json($data, 200);
    }

    public function post(Request $request)
    {
        if ($this->isSekolahExists($request->nama_sekolah)) {
            return Response::json([
                'message' => 'Gagal, sekolah telah terdaftar di server.'
            ], 201);
        }
        $insert = $this->sekolah;
        $insert->nama_sekolah = $request->nama_sekolah;
        $insert->alamat = $request->alamat;
        $insert->type = $request->type;
        $insert->save();

        return Response::json([
            'message' => 'Berhasil mendaftarkan sekolah.',
            'id' => $this->sekolah->id,
            'model' => $insert
        ], 200);
    }

    private function isSekolahExists($namaSekolah)
    {
        $check = $this->sekolah->where('nama_sekolah', $namaSekolah)->first();
        if (!$check) {
            return null;
        }
        return $check;
    }

    public function countSchool() {
        $sma = $this->sekolah->where('type','SMA')->count();
        $smk = $this->sekolah->where('type','SMK')->count();
        $ma = $this->sekolah->where('type','MA')->count();
        $mak = $this->sekolah->where('type','MAK')->count();

        return Response::json([
            'total_sma' => $sma,
            'total_smk' => $smk,
            'total_ma' => $ma,
            'total_mak' => $mak
        ], 200);
    }

    public function count()
    {
        $total = $this->sekolah->count();
        $totalAdmin = $this->user->where('role', 'admin')->count();
        $totalArtikel = $this->artikel->count();

        $doesntHaveAdmin = $this->sekolah->doesntHave('user')->orWhereHas('user', function ($query) {
            $query->whereNotIn('role', ['admin']);
        })->count();

        $hasAdmin = $this->sekolah->whereHas('user', function ($query) {
            $query->where('role', 'admin');
        })->count();

        $countRecent = $this->recent->where('user_id', Auth::user()->id)->count();

        return Response::json([
            'total' => $total,
            'total_admin' => $totalAdmin,
            'total_artikel' => $totalArtikel,
            'total_activity' => $countRecent,
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

    public function put(Request $request, $id)
    {
        $sekolah = $this->sekolah->find($id);

        $update = $sekolah->update([
            'nama_sekolah' => $request->nama_sekolah,
            'alamat' => $request->alamat,
            'type' => $request->type
        ]);

        if (!$update) {
            return Response::json([
                'message' => 'Gagal menyunting sekolah.'
            ], 201);
        }

        return Response::json([
            'message' => 'Berhasil menyunting sekolah.'
        ], 200);

    }

    public function recentActivity()
    {
        $data = $this->user->feeds;
        return \response()->json($data, 200);
    }

}
