<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {

        $applications = Application::select('id', 'full_name','application_no','email', 'mobile', 'age' , 'gender', 'dob' , 'status','payment_status','community')
                          ->where('payment_status','paid')
                          ->get();
        return response()->json([
            'success' => true,
            'message' => 'Paid Application data fetched successfully!',
            'data' => $applications,
        ], 200);
    }
}
