<?php namespace App\Http\Controllers;

use App\Repositories\UsersRepository;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class UsersController extends Controller
{
    private $userRepository;
    private $user;

    /**
     * UsersController constructor.
     * @param $userRepository
     */
    public function __construct(UsersRepository $userRepository, User $user)
    {
        $this->userRepository = $userRepository;
        $this->user = $user;
    }

    public function getTotalAccountBySchool(Request $request) {
        return $this->userRepository->getTotalAccountBySchool($request);
    }

    public function login(Request $request)
    {
        return $this->userRepository->login($request);
    }

    public function checkUsername(Request $request) {
        return $this->userRepository->checkUsername($request->username);
    }

    public function register(Request $request)
    {
        return $this->userRepository->register($request);
    }

    public function getTotalAccount(Request $request) {
        return $this->userRepository->getTotalAccount($request);
    }

    public function get($id)
    {
        return $this->userRepository->get($id);
    }

    public function all()
    {
        return $this->userRepository->all();
    }

    public function remove($id)
    {
        return $this->userRepository->remove($id);
    }

    public function put(Request $request)
    {
        return $this->userRepository->put($request);
    }

    public function getStudentInfo($id)
    {
        return $this->userRepository->getStudentInfo($id);
    }

    public function updateImageProfile(Request $request)
    {
        return $this->userRepository->updateImageProfile($request);
    }

    public function getAllKonselor(Request $request){
        return $this->userRepository->getAllGuruWithoutPaging($request);
    }

    public function getAllPagingKonselor(Request $request) {
        $data = $this->userRepository->getAllGuru($request);
        $data = $data->paginate($request->per_page);

        return response()->json($data);
    }

    public function getDetailKonselor($id) {
        return $this->userRepository->getDetailGuru($id);
    }

    public function changePassword(Request $request) {
        $user = $this->user->find(Auth::user()->id);

        if (!Hash::check($request->oldPassword, $user->password)) {
            return Response::json(
                ["message" => "Kata sandi saat ini salah."],
                201);
        }

        if (Hash::check($request->newPassword, $user->password)) {
            return Response::json(
                ["message" => "Kata sandi baru tidak boleh sama dengan kata sandi saat ini."],
                201);
        }

        $user->password = Hash::make($request->newPassword);
        $save = $user->save();

        $updateHasEver = $user->update([
            'ever_change_password' => 1
        ]);

        if (!$save || !$updateHasEver) {
            return Response::json(
                ["message" => "Gagal mengganti kata sandi."],
                201);
        }

        return Response::json(["message" => "Kata sandi berhasil diubah."], 200);
    }

    public function getSchool(Request $request) {
        $per_page = $request->per_page;

        $data = new \App\Sekolah;

        if($request->has('nama') && !empty($request->input('nama'))) {
            $data = $data->where('nama_sekolah','like','%'.$request->nama.'%');
        }

        $data = $data->get();

        return Response::json($data, 200);
    }

}
