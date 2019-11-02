<?php namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RiwayatsController extends Controller
{
    public function view(Request $request)
    {
        $user = \App\User::with('detail')->where('id', Auth::user()->id)->first()->detail;
        // $riwayat = \App\Riwayat::all()->groupBy('schedule_id');
        $riwayat = \App\Riwayat::whereHas('user', function ($q) use ($user) {
            $q->whereHas('detail', function ($query) use ($user) {
                $query->where('sekolah_id', $user->sekolah_id);
            });
        })->with('schedule.consultant')->with('schedule.request')->orderBy('created_at', 'desc');

        $riwayat = $riwayat->whereHas('schedule', function ($q) {
            $q->where('ended', 1);
        });

        if ($request->has('isToday')) {
            if ($request->isToday == 'true') {
                $riwayat = $riwayat->where('created_at', '>=', Carbon::today());
            } else {
                $riwayat = $riwayat->where('created_at', '<', Carbon::today());
            }
        }

        $datas = $riwayat->paginate($request->limit);
        return Response::json($datas, 200);
    }

    public function all(Request $request)
    {
        $datas = \App\Riwayat::where('user_id', Auth::user()->id);
        $datas->with('schedule')->with('user')->with('schedule.consultant')->with('schedule.request')->orderBy('id', 'desc');

        if ($request->has('status')) {
            $datas = $datas->whereHas('schedule', function ($query) use ($request, $datas) {
                if ($request->status == 'selesai') {
                    $query->where('ended', 1);
                }

                if ($request->status == 'dibatalkan') {
                    $query->where('canceled', 1);
                }

                if ($request->status == 'kedaluwarsa') {
                    $query->where('exp', 1);
                }
            });
        }

        if ($request->has('type') && $request->status != '') {
            $datas = $datas->whereHas('schedule', function ($query) use ($request, $datas) {
                $query->where('type_schedule', $request->type);
            });
        }

        $paginate = $datas->paginate($request->per_page);

        return Response::json($paginate, 200);
    }


    public function remove($id)
    {
        $data = \App\Riwayat::where('id', $id)->where('user_id', Auth::user()->id);

        if ($data) {
            return Response::json(['message' => 'Berhasil menghapus riwayat.'], 200);
        } else {
            return Response::json(['message' => 'Gagal menghapus riwayat.'], 201);
        }
    }
}
