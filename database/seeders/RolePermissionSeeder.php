<?php

namespace Database\Seeders;

use App\Constants\RolePermissions;
use App\Enums\PermissionNames;
use App\Enums\Roles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Class RolePermissionSeeder
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            foreach (PermissionNames::cases() as $permission) {
                Permission::firstOrCreate(['name' => $permission->value, 'guard_name' => 'sanctum']);
            }

            $adminRole  = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'sanctum']);
            $authorRole = Role::firstOrCreate(['name' => Roles::AUTHOR, 'guard_name' => 'sanctum']);

            $adminRole->syncPermissions(array_map(static fn($p) => $p->value, RolePermissions::ADMIN));
            $authorRole->syncPermissions(array_map(static fn($p) => $p->value, RolePermissions::AUTHOR));
        } catch (\Throwable $th) {
            logger()->error($th);
        }
    }
}
