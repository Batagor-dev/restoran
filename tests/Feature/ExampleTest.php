<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Halaman utama: guest diarahkan ke login, user login ke dashboard.
     */
    public function test_the_application_redirects_root_correctly(): void
    {
        $this->get('/')->assertRedirect(route('login'));

        $outlet = Outlet::create([
            'name' => 'Root Outlet '.uniqid(),
            'status' => true,
        ]);

        $user = User::create([
            'name' => 'Root User',
            'username' => 'root_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $outlet->id,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->outlets()->attach($outlet->id);

        $this->actingAs($user)->get('/')->assertRedirect(route('dashboard'));
    }
}
