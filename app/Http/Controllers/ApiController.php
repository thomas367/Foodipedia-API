<?php
 
namespace App\Http\Controllers;
 
use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Validator, DB, Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
 
class ApiController extends Controller{
 
	/*
	 * Register function
	 */
    public function register(Request $request){
        /* 
		 * 1. Checks if both fields are submitted.
		 * 2. Checks if the username is unique.
		 * 3. Checks if the username and password have at least 6 characters.
		 */
		$validator = Validator::make($request->all(), [
            'username' => 'required|min:6|unique:users',
            'password'=> 'required|min:6'
        ]);
		
		// Returns a message of the error.
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'error' => $validator->errors()
            ], 401);
        }
		// User creation
		$user = new User();
		$user->username = $request->get('username');
		$user->password = bcrypt($request->get('password'));
		$user->save();
		 
        $data = array(
			'user_id' => $user->id,
			'username' => $user->username,
		);
		
        $token = JWTAuth::fromUser($user);
		
		return response()->json([
				'data' => $data,
				'token' => $token
			], 200);
    }
 
	/*
	 * Login function
	 */
    public function login(Request $request){
        
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);
		
        if($validator->fails()) {
            return response()->json([
				'success'=> false, 
				'error'=> $validator->messages()
			], 401);
        }
        
	    $input = $request->only('username', 'password');
        
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($input)) {
                return response()->json([
					'success' => false, 
					'error' => 'We cant find an account with this credentials. Please make sure you entered the right information.'
				], 404);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
				'success' => false, 
				'error' => 'Failed to login, please try again.'
			], 500);
        }

		$user = JWTAuth::user($token);
		
        return response()->json([
			'success' => true, 
			'data'=> [ 
                'user' => $user,
				'token' => $token 
			]
		], 200);
    }
	/*
    public function logout(Request $request){
        $this->validate($request, [
            'token' => 'required'
        ]);
 
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ], 500);
        }
    }
 
    public function getAuthUser(Request $request){
        $this->validate($request, [
            'token' => 'required'
        ]);
 
        $user = JWTAuth::authenticate($request->token);
 
        return response()->json(['user' => $user]);
    }
	*/
}