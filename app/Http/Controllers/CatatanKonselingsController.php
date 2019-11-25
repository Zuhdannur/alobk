<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CatatanKonselingsController extends Controller
{
    public function all(Request $request)
    {
        $user = \App\User::with('detail')->where('id', Auth::user()->id)->first()->detail;
        // $riwayat = \App\Riwayat::all()->groupBy('schedule_id');
        $riwayat = \App\Feedback::whereHas('schedule', function ($q) use ($user) {
            $q->whereHas('request', function ($query) use ($user) {
                $query->whereHas('detail', function ($q) use ($user) {
                    $q->where('sekolah_id', $user->sekolah_id);
                });
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
}
