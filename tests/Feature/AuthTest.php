<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
class AuthTest extends TestCase
{
    use RefreshDatabase;
    #[Test]    
    public function test_user_can_login_and_receive_otp()
    {
        // Generate a dynamic email
        $email = 'testuser' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
    
        // Create a user before testing login
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    
        // Fake email sending
        Mail::fake();
    
        // Send login request
        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password,
        ]);
    
        // Assert response status (typically 201)
        $response->assertStatus(201)
                 ->assertJsonStructure(['temp_token']);
    
        // Retrieve the OTP record from the database
        $otpRecord = VerificationCode::where('user_id', $user->id)->first();
    
        // Ensure OTP and temp_token exist
        $this->assertNotNull($otpRecord, 'OTP record not found in database');
        $this->assertNotNull($otpRecord->email_otp, 'Email OTP not generated');
        $this->assertNotNull($otpRecord->temp_token, 'Temp token not generated');
    
        // Assert email was sent with correct recipient
        Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
    


}
