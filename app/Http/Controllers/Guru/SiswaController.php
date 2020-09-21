<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    private $user;

    /**
     * DiaryController constructor.
     * @param $diary
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getAllSiswa(Request $request) {
        $data = $this->user->where('role','siswa')->where('sekolah_id',Auth::user()->sekolah_id);
        $data = $data->paginate($request->per_page);

        return Response::json($data,200);
    }
}
