<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required|max:8096'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please enter all the fields',
                'errors' => $validator->errors()
            ], 422);
        } else {
            Mail::to(config('mail.admin'))->send(new ContactFormMail($validator->valid()));

            return redirect(route('index'));
        }
    }
}
