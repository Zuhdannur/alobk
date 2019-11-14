<?php


namespace App\Http\Controllers\Guru;


use App\Diary;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    private $diary, $user;

    /**
     * DiaryController constructor.
     * @param $diary
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getStudentInfo($id)
    {
        $data = $this->user->where('id', $id)->with('sekolah')->first();
        return Response::json($data, 200);
    }


}
