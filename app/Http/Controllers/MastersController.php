<?php namespace App\Http\Controllers;

use App\Cron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MastersController extends Controller
{
    public function cronJob()
    {
        $data = Cron::all();
        return Response::json($data, 200);
    }

}
