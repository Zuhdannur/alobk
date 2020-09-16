<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TermAndConditionController extends Controller
{

    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function getCondition() {
        $query = $this->user->select(['condition_wali_kelas','condition_pembimbing','condition_guru','condition_orang_tua','agree_with_rules'])->where('id',Auth::user()->id)->first();
        return response()->json([
            "message" => "Success",
            "result" => $query
        ]);
    }

    public function update(Request $request) {
        $query = $this->user->find(Auth::user()->id);
        $query->condition_wali_kelas = $request->condition_wali_kelas;
        $query->condition_pembimbing = $request->condition_pembimbing;
        $query->condition_guru = $request->condition_guru;
        $query->condition_orang_tua = $request->condition_orang_tua;
        $query->agree_with_rules = $request->agree_with_rules;
        $query->save();

        if($query) {
            return response()->json([
                "message" => "success",
                "status" => 200
            ]);
        } else {
            return response()->json([
                "message" => "failed",
                "status" => 400
            ]);
        }
    }
}
