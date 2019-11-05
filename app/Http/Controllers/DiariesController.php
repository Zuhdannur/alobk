<?php namespace App\Http\Controllers;

use App\Repositories\DiaryRepository;
use http\Env\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers;

class DiariesController extends Controller
{

    private $diaryRepository;

    /**
     * DiariesController constructor.
     * @param $diaryRepository
     */
    public function __construct(DiaryRepository $diaryRepository)
    {
        $this->diaryRepository = $diaryRepository;
    }


    public function add(Request $request)
    {
        $add = $this->diaryRepository->add($request);
        return $add;
    }

    public function remove($id)
    {
        $remove = $this->diaryRepository->remove($id);
        return $remove;
    }

    public function all(Request $request)
    {
        $all = $this->diaryRepository->all($request);
        return $all;
    }

    public function diaryCount($id)
    {
        $total = $this->diaryRepository->diaryCount($id);
        return $total;
    }

    public function readDiary(Request $request)
    {
        $read = $this->diaryRepository->readDiary($request);
        return $read;
    }

    public function put(Request $request)
    {
        $update = $this->diaryRepository->update($request);
        return $update;
    }
}
