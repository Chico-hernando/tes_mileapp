<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function responseSuccess($message, $data)
    {
        return response()->json([
            "status" => true,
            "message" => $message,
            "data" => $data
        ], 200);
    }

    protected function responseError($error, $message)
    {
        return response()->json([
            "status" => false,
            "error" => $error,
            "message" => $message
        ], 200);
    }

    public function datetimeNow()
    {
        date_default_timezone_set("Asia/Jakarta");
        return date('Y-m-d H:i:s');
    }

    public function dateNow()
    {
        date_default_timezone_set("Asia/Jakarta");
        return date('Y-m-d');
    }

    public function dateTransaction()
    {
        date_default_timezone_set("Asia/Jakarta");
        return date('Ymd');
    }

    public function datetimeAWB() {
        date_default_timezone_set("Asia/Jakarta");
        
        return Carbon::now()->addDays(25)->format('dmY');
    }
}
