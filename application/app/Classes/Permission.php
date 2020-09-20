<?php
namespace App\Classes;

use App\AdminRolePermission;

Class Permission{

    public static $perms = array(
        1 => 'dashboard',
        2 => 'all-clients',
        3 => 'add-new-client',
        4 => 'manage-client',
        5 => 'export-n-import-client',
        6 => 'client-groups',
        7 => 'edit-client-group',
        8 => 'all-invoices',
        9 => 'recurring-invoices',
        10 => 'manage-invoices',
        11 => 'add-new-invoice',
        12 => 'send-bulk-sms',
        13 => 'send-sms-from-file',
        14 => 'send-schedule-sms',
        15 => 'schedule-sms-from-file',
        16 => 'sms-history',
        17 => 'sms-gateways',
        18 => 'add-sms-gateway',
        19 => 'manage-sms-gateway',
        20 => 'sms-price-plan',
        21 => 'add-price-plan',
        22 => 'coverage',
        23 => 'sender-id-management',
        24 => 'sms-templates',
        25 => 'sms-api',
        26 => 'all-support-tickets',
        27 => 'create-new-ticket',
        28 => 'manage-support-tickets',
        29 => 'support-departments',
        30 => 'administrators',
        31 => 'administrator-role',
        32 => 'system-settings',
        33 => 'localization',
        34 => 'email-templates',
        35 => 'language-settings',
        36 => 'payment-gateway',
        37 => 'send-quick-sms',
        38 => 'price-bundles',
        39 => 'phone-book',
        40 => 'import-contacts',
        41 => 'spam-words',
        42 => 'blacklist-contacts',
        43 => 'block-message',
        44 => 'recurring-sms',
        45 => 'send-recurring-sms',
        46 => 'recurring-sms-file',
        47 => 'all-keywords',
        48 => 'add-new-keyword',
        49 => 'keyword-settings',
        50 => 'campaign-reports',
        51 => 'chat-sms',
        52 => 'sms-api-sdk',
        53 => 'background-job',
        54 => 'purchase-code',
        55 => 'update-application',
    );


    public static function permitted ($page) {

        $perms=self::$perms;
        $permid = array_search($page, $perms);

        $role = \Auth::user()->roleid;


        $permcheck = AdminRolePermission::where('role_id', $role)->where('perm_id', $permid)->first();

        if ($permcheck==NULL){
            return 'access denied';
        }else{
            if ($permcheck->perm_id<0){
                return 'access denied';
            }else{
                return 'access granted';
            }
        }

    }
}
