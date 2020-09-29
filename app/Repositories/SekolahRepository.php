<?php


namespace App\Repositories;


use App\Sekolah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SekolahRepository
{
    private $sekolah;

    /**
     * SekolahRepository constructor.
     * @param $sekolah
     */
    public function __construct(Sekolah $sekolah)
    {
        $this->sekolah = $sekolah;
    }

    public function getDataThisMonth()
    {
        $data = $this->sekolah
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        return Response::json([
            'total' => $data
        ], 200);
    }

    public function getSekolahCount()
    {
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

    public function all(Request $request)
    {
        $per_page = $request->per_page;

        $data = $this->sekolah;

//        $data = $this->sekolah->withAndWhereHas('firstAdmin', function ($query) {
//            $query->where('role', 'admin');
//        })->get();
        return \response()->json($data->get(),200);
    }

    public
    function get($id)
    {
        $data = $this->sekolah->find($id);
        return Response::json($data, 200);
    }

    private
    function isSekolahExists($namaSekolah)
    {
        $check = $this->sekolah->where('nama_sekolah','like','%'.$namaSekolah.'%')->first();
        if (empty($check)) {
            return false;
        }
        return true;
    }

    public
    function checkSekolahName($namaSekolah)
    {
        $check = $this->sekolah->where('nama_sekolah', $namaSekolah)->first();
        if ($check) {
            return Response::json(['message' => 'Sekolah telah terdaftar.'], 201);
        }
        return Response::json(['message' => 'Sekolah dapat digunakan.'], 200);
    }

    public
    function add(Request $request)
    {
        if ($this->isSekolahExists($request->nama_sekolah)) {
            return Response::json([
                'message' => 'Gagal, sekolah telah terdaftar di server.'
            ], 201);
        }
        $create = \App\Sekolah::create($request->all());


        if($create) {
            return Response::json([
                'message' => 'Berhasil mendaftarkan sekolah.',
                'id' => $create->id
            ], 200);
        } else {
            return Response::json([
                'message' => 'Gagal Mendaftarkan'
            ], 201);
        }


    }

    public
    function put($id, Request $request)
    {
        $update = $this->sekolah->find($id)->update([
            "nama_sekolah" => $request->nama_sekolah,
            "alamat" => $request->alamat,
            "type" => $request->type
        ]);
        if ($update) {
            return Response::json(["message" => "berhasil menyunting."], 200);
        } else {
            return Response::json(["message" => "gagal menyunting."], 201);
        }
    }

    public
    function remove($id)
    {
        $delete = $this->sekolah->find($id)->delete();
        if ($delete) {
            return Response::json(["message" => 'Sekolah berhasil dihapus.'], 200);
        } else {
            return Response::json(["message" => 'Sekolah gagal dihapus'], 201);
        }
    }


}
