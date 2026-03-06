<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use App\Models\SystemRoute;

class SystemRoutesSeeder extends Seeder
{
    public function run()
    {
        // Get all routes
        $routes = collect(Route::getRoutes())->map(function ($route) {
            $name = $route->getName();
            if ($name) {
                return [
                    'name' => $name,
                    'label' => ucfirst(str_replace(['.', '-'], ' ', $name)), // friendly label
                ];
            }
        })->filter(); // remove nulls

        // Save to database
        foreach ($routes as $route) {
            \App\Models\SystemRoute::updateOrCreate(
                ['name' => $route['name']], // if exists, update
                ['label' => $route['label']] // otherwise insert
            );
        }

        $this->command->info('All routes saved to system_routes table!');
    }
}