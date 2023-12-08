<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use Exception;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * validates the input, and returns a JSON response with a token and 
     * expiration time if successful, or an error message if unsuccessful.
     * 
     * @param LoginRequest custom request class that validates the incoming login request data. It contains the
     * username and password fields that are required for authentication.
     * 
     * @return JsonResponse This function returns a JSON response. 
     */
    public function login(LoginRequest $request): JsonResponse
    {

        try {
            $data = $request->validated();

            if (!$token = auth()->attempt($data, true)) {
                return response()->error("credential incorrect for user: $request->username", 401);
            }

            return response()->success([
                'token' => $token,
                'minutes_to_expire' => auth()->factory()->getTTL(),
            ], 200);
        } catch (Exception $ex) {
            return response()->error([
                'line' => $ex->getLine(),
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * logs out the user and returns a JSON response with a success message.
     * 
     * @return JsonResponse This function returns a JSON response. 
     */
    public function logout(): JsonResponse
    {
        try {
            auth()->logout();
            return response()->success('logged out', 200);
        } catch (Exception $ex) {
            return response()->error([
                'line' => $ex->getLine(),
                'message' => $ex->getMessage()
            ], 500);
        }
    }
}
