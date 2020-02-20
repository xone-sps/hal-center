<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CornJobLog;
use Carbon\Carbon;

class CornJobLogController extends Controller
{
    public function store($branchId)
    {
        $date = date("Y-m-d h:i:sa");
        $arr = [
            'branch_id' => $branchId,
            // 'created_at' => $date,
            // 'updated_at' => $date
        ];
        $id = CornJobLog::getInsertedId($arr);
        return [
            'message' => 'stored'
        ];
    }

    public function getLastElement()
    {
        return CornJobLog::getLastObject();
    }
}
