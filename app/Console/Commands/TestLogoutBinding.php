<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse;

class TestLogoutBinding extends Command
{
    protected $signature = 'test:logout-binding';
    protected $description = 'Test if logout response binding works';

    public function handle()
    {
        try {
            $logoutResponse = app(LogoutResponse::class);
            $this->info('Success! Logout response class: ' . get_class($logoutResponse));
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
