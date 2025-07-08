<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Timebox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PasswordTimeboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_uses_timebox(): void
    {
        $user = User::factory()->create();

        $timebox = new class extends Timebox {
            public int $calls = 0;

            public function call(callable $callback, int $microseconds)
            {
                $this->calls++;

                return $callback($this);
            }
        };

        $this->app->instance(Timebox::class, $timebox);

        Volt::test('auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        $this->assertSame(1, $timebox->calls);
    }

    public function test_reset_password_uses_timebox(): void
    {
        $user = User::factory()->create();

        $timebox = new class extends Timebox {
            public int $calls = 0;

            public function call(callable $callback, int $microseconds)
            {
                $this->calls++;

                return $callback($this);
            }
        };

        $this->app->instance(Timebox::class, $timebox);

        Volt::test('auth.reset-password', ['token' => 'invalid'])
            ->set('email', $user->email)
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('resetPassword');

        $this->assertSame(1, $timebox->calls);
    }
}

