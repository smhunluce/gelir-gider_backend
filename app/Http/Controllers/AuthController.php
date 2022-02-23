<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Rules\TurkishPhone;

class AuthController extends Controller
{

    /**
     * Create user
     *
     * @param Request $request
     * @return JsonResponse [string] message
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname'    => 'required',
            'lastname'     => 'required',
            'email'        => 'required|email|unique:users',
            'phone_number' => ['required', 'unique:users', new TurkishPhone],
            'password'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user               = new User();
        $user->firstname    = $request->input('firstname');
        $user->lastname     = $request->input('lastname');
        $user->email        = $request->input('email');
        $user->phone_number = formatPhoneNumber($request->input('phone_number'));
        $user->password     = bcrypt($request->input('password'));
        $user->save();

        return response()->json([
            'message' => 'Successfully created user!',
        ], 201);
    }

    /**
     * User Login
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $login_with_phone = FALSE;
        if (filter_var($request->input('email_phone'), FILTER_VALIDATE_EMAIL)) {
            $login_field = 'email';
            $login_rules = 'required|email';
            $request->request->add(['email' => $request->input('email_phone')]);
        } else {
            $login_with_phone = TRUE;
            $login_field      = 'phone_number';
            $login_rules      = ['required', new TurkishPhone];
            $request->request->add(['phone_number' => $request->input('email_phone')]);
        }

        $credentials = $request->only([$login_field, 'password']);

        $validator = Validator::make($credentials, [
            $login_field => $login_rules,
            'password'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($login_with_phone) {
            $credentials['phone_number'] = formatPhoneNumber($credentials['phone_number']);
        }

        if (!auth()->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $tokenData = auth()->user()->createToken('apiAuthToken');
        $token     = $tokenData->token;

        if ($request->has('remember_me'))
            $token->expires_at = Carbon::now()->addWeeks();

        $token->save();

        return response()->json([
            'access_token' => $tokenData->accessToken,
            'token_type'   => 'Bearer',
            'expires_in'   => Carbon::parse($tokenData->token->expires_at)->toDateTimeString(),
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return JsonResponse [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return JsonResponse [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Expired Sessions
     *
     * @return JsonResponse [json] user object
     */
    public function unauthorized()
    {
        return response()->json(['message' => 'Unauthorized.'], 401);
    }
}
