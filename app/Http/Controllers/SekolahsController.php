<?php namespace App\Http\Controllers;

use App\Repositories\SekolahRepository;
use App\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SekolahsController extends Controller
{

    private $sekolahRepository;

    /**
     * SekolahsController constructor.
     * @param $sekolahRepository
     */
    public function recentAct() {
        $data = Sekolah::with('actions');

        return Response::json($data, 200);
    }

    public function __construct(SekolahRepository $sekolahRepository)
    {
        $this->sekolahRepository = $sekolahRepository;
    }

    public function getDataThisMonth() {
        return $this->sekolahRepository->getDataThisMonth();
    }

    public function getSekolahCount() {
        return $this->sekolahRepository->getSekolahCount();
    }

    public function checkSekolahName(Request $namaSekolah) {
        return $this->sekolahRepository->checkSekolahName($namaSekolah);
    }

    public function all(Request $request)
    {
        return $this->sekolahRepository->all($request);
    }

    public function getSekolahThenCheckAdmin(Request $request)
    {
        return $this->sekolahRepository->getSekolahThenCheckAdmin($request);
    }

    public function get($id)
    {
        return $this->sekolahRepository->get($id);
    }

    public function add(Request $request)
    {
        return $this->sekolahRepository->add($request);
    }

    public function put($id, Request $request)
    {
        return $this->sekolahRepository->put($id, $request);
    }

    public function remove($id)
    {
        return $this->sekolahRepository->remove($id);
    }
}
