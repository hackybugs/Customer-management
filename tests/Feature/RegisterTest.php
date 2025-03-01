<?php
namespace Tests\Feature;

use App\Mail\OtpMail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Str;

class RegisterTest extends TestCase
{
    use RefreshDatabase; // Clears the database after each test

    /** @test */
    public function user_can_register_and_receive_otp()
    {
        // Fake email sending
        Mail::fake();

        // Generate unique test email
        $email = 'test_' . Str::random(6) . '@example.com';

        // Make a registration request
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'password123',
        ]);

        // Assert response status
        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'temp_token']);

        // Check if the user was created in the database
        $this->assertDatabaseHas('users', ['email' => $email]);
        // Retrieve temp_token from response
        $tempToken = $response['temp_token'];
        // Get the newly created user
        $user = User::where('email', $email)->first();

        // Verify OTP record exists in verification_codes table
        $this->assertDatabaseHas('verification_codes', [
            'user_id' => $user->id,
            'temp_token' => $tempToken,  // Ensure temp_token matches response
        ]);

       // Retrieve OTP record
       $otpRecord = VerificationCode::where('user_id', $user->id)->first();

       // Ensure OTP & temp_token are valid
       $this->assertNotNull($otpRecord->email_otp);
       $this->assertNotNull($otpRecord->temp_token);
       $this->assertEquals($tempToken, $otpRecord->temp_token);

       // Assert email was sent with correct recipient
       Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}
