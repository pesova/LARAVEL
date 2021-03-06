<?php


namespace App\Http\Controllers;
use App\User;
use Validator;
use Carbon\Carbon;
use App\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserRegistration;








class AuthController extends Controller
{
/*
    public function login(Request $request){

        //logic for logging in with username or email, and password.
        if(Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])){
            $user = Auth::user();
            $token = $user->createToken('HNGApp')->accessToken;
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ],
            200
        );
        }else{
            return response()->json(['status' => false,
            'error' => 'Unauthorized'
        ], 401);
        }
    }
*/

    public function register(Request $request) {
        //logic for sign up

        $messages = [];
        $validator = Validator::make($request->all(),[
            'firstname' => 'required',
          //  'lastname' => 'required',
          //  'username' => 'required|unique:users,username|max:30',
          //  'email' => 'required|email|unique:users,email',
          //  'password' => 'required',
          //  'confirm_password' => 'required|same:password',
            'location' => 'nullable',
            'gender' => 'nullable'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 401);
        }
        $input = $request->all();

        //$input['password'] = bcrypt($input['password']);


        DB::beginTransaction();
        $user = User::create($input);
        // $user->notify(new UserRegistration($user));
        DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'Registration successful',
            'user' => $user,
        ], 200);

    }
  }
/*
    public function request_reset(Request $request){
        try{
            $request->validate([ 'email' => 'required|string|email' ]);
            $user = User::findOrFail($request->email);
            if (!$user) return $this->ERROR("We can't find a user with the e-mail address " . $request->email);

            $passwordReset = PasswordReset::updateOrCreate(
                ['email' => $user->email],
                [
                    'email' => $user->email,
                    'token' => $this->GENERATE_TOKEN(6)
                ]
            );
            if ($user && $passwordReset){
                //Send email
            }
            logger("Password reset link sent to " . Auth::user()->email);
            return $this->SUCCESS("Password reset link sent");
        }
        catch(\Throwable $e){
            logger("Password reset failed for " . Auth::user()->email);
            return $this->ERROR('Password rest failed. Please try again');
        }
    }

    public function findResetToken($token){
        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset)
            return $this->ERROR("This password reset token is invalid.");
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return $this->ERROR("This link has expired!");
        }
        return $this->SUCCESS('Enter a new password', $passwordReset->token);
    }

    public function resetPassword(Request $request){
        DB::beginTransaction();
        try{
            $validation = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string|confirmed',
                'token' => 'required|string'
            ]);

            if ($validation->fails())  return $this->ERROR($validation->errors());

            $passwordReset = PasswordReset::where([
                ['token', $request->token],
                ['email', $request->email]
            ])->first();
            if (!$passwordReset) return $this->ERROR("This password reset token is invalid.", $request->token);

            $user = User::findOrFail($request->email);
            if (!$user) return $this->ERROR("We can't find a user with the e-mail address " . $request->email);

            $user->password = bcrypt($request->password);
            $user->save();
            $passwordReset->delete();

            DB::commit();
        }
        catch(\Throwable $e){
            return $this->ERROR('Password reset failed', $e);
        }
        //Send Email
        return $this->SUCCESS("Password reset successful!");
    }

    public function updatePassword(Request $request){
        try{
            $validation = Validator::make($request->all(), [
                'current' => 'required',
                'password' => 'required|confirmed',
                'password_confirmation' => 'required'
            ]);

            if ($validation->fails()) {
                return $this->ERROR('Password update failed', $validation->errors());
            }

            $user = User::find(Auth::user()->id);
            if (!Hash::check($request->current, $user->password)) {
                return $this->ERROR('Current password does not match');
            }

            $user->password = bcrypt($request->password);
            $user->save();

            return $this->SUCCESS('Password changed');
        }
        catch(\Throwable $e){
            return $this->ERROR('Password update failed', $e);
        }
    }


    public function logout(){
        $accessToken = Auth::user()->token();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);

        $accessToken->revoke();
        return $this->SUCCESS('You are successfully logged out');
    }

    public function clear_session(){
        $token_id = Auth::user()->token()->id; // Use this to revoke refresh_token
        $user_id = Auth::user()->id;
        DB::table('oauth_access_tokens AS OAT')
            ->join('oauth_refresh_tokens AS ORT', 'OAT.id', 'ORT.access_token_id')
            ->where('OAT.id', '!=', $token_id)
            ->where('OAT.user_id', $user_id)
            ->update([
                'OAT.revoked' => true,
                'ORT.revoked' => true
            ]);
        return $this->SUCCESS();
    }


    public function verify(Request $request, $token)
    {
        if ($user = User::where('token', $request->token)->first()) {
            $user->token = null;
            if ($user->save() && $user->markEmailAsVerified()) {
                return $this->SUCCESS('You are successfully verified');
            }
        } else {
            return $this->ERROR('Invalid verification code');
        }
    }

    private function generateOTP(int $n)
    {
        $generator = "1234567890";
        $result = "";

        for ($i = 1; $i <= $n; $i++) {
            $result .= \substr($generator, (rand() % (strlen($generator))), 1);
        }
        return $result;
    }

}
