<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // ================= USERS =================
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // ================= ROLES =================
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // ================= PERMISSIONS =================
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            // ================= CUSTOMER =================
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            'customers.expired',
            'customers.online',
            'customers.bind_mac',
            'customers.unbind_mac',
            'customers.disconnect',

            // ================= INTERNET PLAN =================
            'plans.view',
            'plans.create',
            'plans.edit',
            'plans.delete',

            // ================= BILLING / RECHARGE =================
            'billing.view',
            'billing.create',
            'billing.edit',
            'billing.delete',
            'recharge.create',
            'recharge.view',

            // ================= TICKETS =================
            'tickets.view',
            'tickets.create',
            'tickets.edit',
            'tickets.delete',
            'tickets.assign',
            'tickets.reply',
            'tickets.close',

            // ================= BRANCH =================
            'branch.view',
            'branch.create',
            'branch.edit',
            'branch.delete',
            'branch.balance_add',

            // ================= NAS / RADIUS =================
            'nas.view',
            'nas.create',
            'nas.edit',
            'nas.delete',

            // ================= MIKROTIK =================
            'mikrotik.view',
            'mikrotik.create',
            'mikrotik.edit',
            'mikrotik.delete',

            // ================= CRON JOB =================
            'cron.view',
            'cron.create',
            'cron.edit',
            'cron.delete',
            'cron.toggle',

            // ================= SMS =================
            'sms.view',
            'sms.send',
            'sms.queue',

            // ================= MENU =================
            'menu.view',
            'menu.create',
            'menu.edit',
            'menu.delete',

            // ================= DASHBOARD =================
            'dashboard.view',

            // ================= TR069 =================
            'tr069.view',
            'tr069.create',
            'tr069.edit',
            'tr069.delete',

            // ================= SYSTEM =================
            'system.logs_view',
            'system.activity_view',
            'change_password',
        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([
                'name' => $permission
            ]);
        }
    }
}
