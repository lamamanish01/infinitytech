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
            'view users',
            'create users',
            'edit users',
            'delete users',

            // ================= ROLES =================
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // ================= PERMISSIONS =================
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',

            // ================= CUSTOMER =================
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'expired customers',
            'online customers',
            'bind mac customers',
            'unbind mac customers',
            'disconnect customers',
            'recharge customers',
            'change expiry customers',
            'grace customers',

            // ================= INTERNET PLAN =================
            'view internet plans',
            'create internet plans',
            'edit internet plans',
            'delete internet plans',

            // ================= BILLING =================
            'view billing',
            'create billing',
            'edit billing',
            'delete billing',
            'create recharge',
            'view recharge',

            // ================= TICKETS =================
            'view tickets',
            'create tickets',
            'edit tickets',
            'delete tickets',
            'assign tickets',
            'reply tickets',
            'note tickets',
            'close tickets',
            'status tickets',

            // ================= BRANCH =================
            'view branch',
            'create branch',
            'edit branch',
            'delete branch',
            'add branch balance',
            'reverse branchTransaction',

            // ================= NAS =================
            'view nas',
            'create nas',
            'edit nas',
            'delete nas',

            // ================= MIKROTIK =================
            'view mikrotik',
            'create mikrotik',
            'edit mikrotik',
            'delete mikrotik',

            // ================= CRON =================
            'view cron',
            'create cron',
            'edit cron',
            'delete cron',
            'toggle cron',
            'view cron logs',
            'delete cron logs',
            'view cron jobs',
            'create cron jobs',
            'edit cron jobs',
            'delete cron jobs',

            // ================= SMS =================
            'view smsgateway',
            'create smsgateway',
            'edit smsgateway',
            'delete smsgateway',
            'send smsgateway',
            'delete sms queue',

            // ================= MENU =================
            'view menus',
            'create menus',
            'edit menus',
            'delete menus',

            // ================= DASHBOARD =================
            'view dashboard',

            // ================= TR069 =================
            'view acsserver',
            'edit acsserver',
            'delete acsserver',

            // ================= SYSTEM =================
            'view system logs',
            'view system activity',
            'change password',

            // ================= CUSTOMER IMPORT =================
            'customer_import showForm',
            'customer_import import',
            'customer_import downloadTemplate',

            // ================= SMS =================
            'view sms',
            'create sms',
            'edit sms',
            'delete sms',

            // ================= RADPOST AUTH =================
            'view radpostauth',
            'create radpostauth',
            'edit radpostauth',
            'delete radpostauth',

            // ================= Server AUTH =================
            'view serverstats',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
    }
}
