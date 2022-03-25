<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Validator;

class RegistrationController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Validation errors', 'errors' =>  $validator->errors(), 'status' => false], 422);
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        try {
            $user = User::create($input);
        } catch (\Exception $e) {
            Log::error("Error occur while creating this account " . $request->input('email') . json_encode($e));
            return response()->json(['data' => null, 'message' => 'Error occured while creating account', 'status' => false], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        try {
            /**Generate user accessToken **/
            $data['token'] =  $user->createToken('MyApp')->accessToken;
            $data['name'] =  $user->name;
            return response()->json(['data' => $data, 'message' => 'Account created successfully!', 'status' => true], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error("Error occur while generating token for created account " . $request->input('email') . json_encode($e));
            return response()->json(['data' => null, 'message' => 'Error occured while while generating token for created account', 'status' => false], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}