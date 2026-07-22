<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationDuplicatePhoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_otp_rejects_existing_phone_after_normalization(): void
    {
        User::factory()->create([
            'phone' => '919876543210',
            'mobile_verified_at' => now(),
        ]);

        $response = $this->from('/register')->post('/register/send-otp', [
            'phone' => '9876543210',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'phone' => 'This mobile number is already registered. Please login instead.',
        ]);
    }

    public function test_registration_details_redirects_to_login_when_verified_phone_was_already_registered(): void
    {
        User::factory()->create([
            'phone' => '919876543210',
            'mobile_verified_at' => now(),
        ]);

        $response = $this
            ->withSession([
                'registration_phone' => '919876543210',
                'registration_mobile_verified' => true,
            ])
            ->post('/register/details', [
                'name' => 'Test Student',
                'email' => 'student@example.com',
            ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'phone' => 'This mobile number is already registered. Please login instead.',
        ]);
        $this->assertDatabaseCount('users', 1);
    }
}
