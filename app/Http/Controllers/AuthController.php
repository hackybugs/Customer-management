<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Customer ERP API",
 *      description="This API provides authentication and user management services.",
 *      @OA\Contact(
 *          email="support@example.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url="http://localhost:8000/api",
 *      description="Local development server"
 * )
 */

class AuthController extends Controller
{
    // Show register form
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Show login form
    public function showLoginForm()
    {
        return view('auth.login');
    }
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="securePassword123"),
     *             @OA\Property(property="user_type", type="string", example="customer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OTP sent to email. Please verify.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP sent to email. Please verify."),
     *             @OA\Property(property="temp_token", type="string", example="randomstringfortemptoken")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */

    public function register(Request $request):JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password'=> Hash::make($request->password),
            'user_type'=>$request->user_type??'customer'
        ]);

         // Generate a temporary token (random string)
         $tempToken = Str::random(40);         
         // Generate OTP and store it
         $otp = rand(100000, 999999);
         VerificationCode::create([
            'user_id'=>$user->id,
            'email_otp'=>$otp,
            'temp_token'=>$tempToken,
            'expires_at'=> Carbon::now()->addMinutes(10)
         ]);
        // Send OTP to user's email
        Mail::to($user->email)->send(new OtpMail($otp));
 
         // Return temporary token (user needs to verify OTP)
         return response()->json([
             'message' => 'OTP sent to email. Please verify.',
             'temp_token' => $tempToken
         ], 201);

    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login",
     *     description="Authenticate a user and send OTP for verification",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent to email. Please verify.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP sent to email. Please verify."),
     *             @OA\Property(property="temp_token", type="string", example="abc123xyz456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|exists:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'=>'failded',
                'message'=>'validation error ',
                'data'=>$validator->errors()->all()
            ], 400);
        }

          // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid credentials'
            ], 401);
        }
         // Generate a temporary token (random string)
         $tempToken = Str::random(40);         
         // Generate OTP and store it
         $otp = rand(100000, 999999);
         VerificationCode::create([
            'user_id'=>$user->id,
            'email_otp'=>$otp,
            'temp_token'=>$tempToken,
            'expires_at'=> Carbon::now()->addMinutes(10)
         ]);
         // Send OTP to user's email
         Mail::to($user->email)->send(new OtpMail($otp));
 
         // Return temporary token (user needs to verify OTP)
         return response()->json([
             'message' => 'OTP sent to email. Please verify.',
             'temp_token' => $tempToken
         ], 201);

    }
 
    /**
     * @OA\Post(
     *     path="/api/verify-otp",
     *     summary="Verify OTP",
     *     description="Verify the OTP and issue an access token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"temp_token", "email_otp"},
     *             @OA\Property(property="temp_token", type="string", example="abc123xyz456"),
     *             @OA\Property(property="email_otp", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully, access token generated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP verified successfully"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="user@example.com")
     *             ),
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1Ni..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid or expired OTP",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid or expired OTP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function verifyOtp(Request $request){

        $validator = Validator::make($request->all(), [
            'temp_token' => 'required|string',
            'email_otp' => 'required|digits:6'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $otp_verification = VerificationCode::where('temp_token', $request->temp_token)
                ->where('email_otp', $request->email_otp)
                ->where('expires_at', '>', Carbon::now()) // Ensure OTP is not expired
                ->first();

        if (!$otp_verification) {
            $otp_verification->delete();
             return response()->json(['message' => 'Invalid or expired OTP'], 401);
        }

        $user = User::find($otp_verification->user_id);
        if (!$user) {
            $otp_verification->delete();
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->email_verified_at = Carbon::now(); // Mark email as verified
        $user->save();
        $otp_verification->delete();
  
        // Generate access token via Password Grant
        $tokenResult = $user->createToken('customer_erp Personal Access Client');
        $accessToken = $tokenResult->accessToken;
        $tokenExpiration = $tokenResult->token->expires_at;

      // Return success response with access token
        return response()->json([
            'message' => 'OTP verified successfully',
            'user' => $user,
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => $tokenExpiration->diffInSeconds(Carbon::now())
        ]);
    }
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     description="Logs out the user and revokes their access token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully, token revoked.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not authenticated")
     *         )
     *     )
     * )
     */
 
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Revoke current access token
            $request->user()->token()->revoke();

            return response()->json([
                'message' => 'Logged out successfully, token revoked.'
            ]);
        }

        return response()->json(['message' => 'User not authenticated'], 401);
    }
}