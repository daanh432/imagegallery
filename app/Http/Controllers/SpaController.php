<?php

namespace App\Http\Controllers;

class SpaController extends Controller
{
    public function index()
    {
        return view('spa');
    }

    public function NotFound()
    {
        return response()->json(['status' => 'error', 'message' => 'This route does not exist.'], 404);
    }
}
