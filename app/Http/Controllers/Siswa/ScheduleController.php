<?php


namespace App\Http\Controllers\Siswa;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use App\Schedule;

class ScheduleController extends Controller
{
    private $schedule;

    /**
     * ScheduleControlle constructor.
     * @param $schedule
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }

    public function post(Request $request) {
        $data = $this->schedule;
        $data->requester_id = Auth::user()->id;
        $data->title = $request->title;
        $data->type_schedule = $request->type_schedule;
        $data->desc = $request->desc;
        $data->location = $request->location;
        $data->time = $request->time;
        $data->save();

        return Response::json($data, 200);
    }

    public function all(Request $request)
    {
        $data = $this->schedule->withAndWhereHas('role', function($query) {
            $query->where('role', 'siswa');
        })->withAndWhereHas('requester', function ($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        });

        if($request->has('type_schedule')) {
            $data = $data->where('type_schedule', $request->type_schedule);
        }

        $data = $data->paginate($request->per_page);

        return Response::json($data, 200);
    }

//        $query = \App\User::where('role', 'guru')->withAndWhereHas('detail', function ($query) {
//            //     $query->where('sekolah_id', Auth::user()->detail->sekolah_id);
//            // })->get();
//    }


}
