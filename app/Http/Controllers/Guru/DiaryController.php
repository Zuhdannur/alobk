<?php


namespace App\Http\Controllers\Guru;


use App\Diary;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class DiaryController extends Controller
{
    private $diary, $user;

    /**
     * DiaryController constructor.
     * @param $diary
     */
    public function __construct(Diary $diary, User $user)
    {
        $this->diary = $diary;
        $this->user = $user;
    }

    public function all(Request $request) {
        $per_page = $request->per_page;

        $diary = $this->diary->withAndWhereHas('user', function($query) {
            $query->where('sekolah_id', Auth::user()->sekolah_id);
        });

        if($request->has('orderBy')) {
            $diary = $diary->orderBy('id', 'desc');
        }
        $data = $diary->paginate($per_page);

        return Response::json($data, 200);
    }

    public function getStudentDiaryCount($id)
    {
        $total = $this->diary->where('user_id', $id)->count();

        return Response::json([
            "total" => $total
        ], 200);
    }

}
