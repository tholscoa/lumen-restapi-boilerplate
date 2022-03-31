<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getUser(Request $request)
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
            /**Generate user accessToken **/
            $data['token'] =  $user->createToken('MyApp')->accessToken;
            $data['name'] =  $user->name;
            return response()->json(['data' => $data, 'message' => 'Account created successfully!', 'status' => true], Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log($e);
            return response()->json(['data' => null, 'message' => 'Error occured while creating account', 'status' => false], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getMyProfile()
    {
        $me = Auth::user();
        if (!empty($me)) {
            return response()->json(['data' => $me, 'message' => 'profile details fetched', 'status' => true], Response::HTTP_OK);
        } else {
            return response()->json(['data' => null, 'message' => 'User profile cannot be fetched', 'status' => false], Response::HTTP_FORBIDDEN);
        }
    }
}
