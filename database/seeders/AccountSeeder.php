<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Default accounts for new users.
     */
    protected array $defaultAccounts = [
        [
            'name' => 'Efectivo',
            'type' => 'cash',
            'balance' => 0,
            'currency' => 'MXN',
            'color' => '#22c55e',
            'icon' => 'banknote',
            'is_active' => true,
        ],
        [
            'name' => 'Cuenta Principal',
            'type' => 'checking',
            'balance' => 0,
            'currency' => 'MXN',
            'color' => '#3b82f6',
            'icon' => 'landmark',
            'is_active' => true,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::all()->each(function (User $user) {
            $this->createAccountsForUser($user);
        });
    }

    /**
     * Create default accounts for a specific user.
     */
    public function createAccountsForUser(User $user): void
    {
        foreach ($this->defaultAccounts as $accountData) {
            Account::create([
                'user_id' => $user->id,
                ...$accountData,
            ]);
        }
    }
}
