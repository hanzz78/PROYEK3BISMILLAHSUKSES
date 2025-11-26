<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint; // Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Macro untuk rawColumn agar migrasi ENUM PostgreSQL berhasil
        Blueprint::macro('rawColumn', function ($name, $type) {
            return $this->addColumn('plain', $name, ['type' => $type]);
        });
    }
}