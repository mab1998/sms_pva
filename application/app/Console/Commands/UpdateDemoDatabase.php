<?php

namespace App\Console\Commands;

use App\Admin;
use App\AdminRole;
use App\AdminRolePermission;
use App\BlackListContact;
use App\BlockMessage;
use App\Campaigns;
use App\CampaignSubscriptionList;
use App\Client;
use App\ClientGroups;
use App\ContactList;
use App\CustomSMSGateways;
use App\ImportPhoneNumber;
use App\InvoiceItems;
use App\Invoices;
use App\Keywords;
use App\Language;
use App\Operator;
use App\RecurringSMS;
use App\RecurringSMSContacts;
use App\SenderIdManage;
use App\SMSBundles;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSInbox;
use App\SMSPlanFeature;
use App\SMSPricePlan;
use App\SMSTemplates;
use App\SpamWord;
use App\SupportDepartments;
use App\SupportTickets;
use App\SupportTicketsReplies;
use Illuminate\Console\Command;

class UpdateDemoDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:updatedatabase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Database in every 1 hour';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        Admin::truncate();

        $admins = [
            [
                'fname' => 'Abul Kashem',
                'lname' => 'Shamim',
                'username' => 'admin',
                'password' => bcrypt('admin.password'),
                'status' => 'Active',
                'email' => 'akasham67@gmail.com',
                'image' => 'profile.jpg',
                'roleid' => '0',
                'emailnotify' => 'No',
                'menu_open' => '1',
            ],
            [
                'fname' => 'Shamim Rahman',
                'lname' => 'Kabbo',
                'username' => 'shamimrahman97',
                'password' => bcrypt('12345678'),
                'status' => 'Active',
                'email' => 'shamimcoc97@gmail.com',
                'image' => 'profile.jpg',
                'roleid' => '1',
                'emailnotify' => 'No',
                'menu_open' => '1',
            ]
        ];


        foreach ($admins as $a) {
            Admin::create($a);
        }


        AdminRole::truncate();
        $admin_role = [
            [
                'role_name' => 'Full Administrator',
                'status' => 'Active'
            ],
            [
                'role_name' => 'Support Engineer',
                'status' => 'Active'
            ]
        ];

        foreach ($admin_role as $role) {
            AdminRole::create($role);
        }

        AdminRolePermission::truncate();

        $role_perams = [
            [
                'role_id' => 1,
                'perm_id' => 1,
            ], [
                'role_id' => 1,
                'perm_id' => 2,
            ], [
                'role_id' => 1,
                'perm_id' => 3,
            ], [
                'role_id' => 1,
                'perm_id' => 4,
            ], [
                'role_id' => 1,
                'perm_id' => 5,
            ], [
                'role_id' => 1,
                'perm_id' => 6,
            ], [
                'role_id' => 1,
                'perm_id' => 7,
            ], [
                'role_id' => 1,
                'perm_id' => 8,
            ], [
                'role_id' => 1,
                'perm_id' => 9,
            ], [
                'role_id' => 1,
                'perm_id' => 10,
            ], [
                'role_id' => 1,
                'perm_id' => 11,
            ], [
                'role_id' => 1,
                'perm_id' => 12,
            ], [
                'role_id' => 1,
                'perm_id' => 13,
            ], [
                'role_id' => 1,
                'perm_id' => 14,
            ], [
                'role_id' => 1,
                'perm_id' => 15,
            ], [
                'role_id' => 1,
                'perm_id' => 16,
            ], [
                'role_id' => 1,
                'perm_id' => 17,
            ], [
                'role_id' => 1,
                'perm_id' => 18,
            ], [
                'role_id' => 1,
                'perm_id' => 19,
            ], [
                'role_id' => 1,
                'perm_id' => 20,
            ], [
                'role_id' => 1,
                'perm_id' => 21,
            ], [
                'role_id' => 1,
                'perm_id' => 22,
            ], [
                'role_id' => 1,
                'perm_id' => 23,
            ], [
                'role_id' => 1,
                'perm_id' => 24,
            ], [
                'role_id' => 1,
                'perm_id' => 25,
            ], [
                'role_id' => 1,
                'perm_id' => 26,
            ], [
                'role_id' => 1,
                'perm_id' => 27,
            ], [
                'role_id' => 1,
                'perm_id' => 28,
            ], [
                'role_id' => 1,
                'perm_id' => 29,
            ], [
                'role_id' => 1,
                'perm_id' => 30,
            ], [
                'role_id' => 1,
                'perm_id' => 31,
            ], [
                'role_id' => 1,
                'perm_id' => 32,
            ], [
                'role_id' => 1,
                'perm_id' => 33,
            ], [
                'role_id' => 1,
                'perm_id' => 34,
            ], [
                'role_id' => 1,
                'perm_id' => 35,
            ], [
                'role_id' => 1,
                'perm_id' => 36,
            ], [
                'role_id' => 1,
                'perm_id' => 37,
            ], [
                'role_id' => 1,
                'perm_id' => 38
            ],
            [
                'role_id' => 1,
                'perm_id' => 39
            ],
            [
                'role_id' => 1,
                'perm_id' => 40
            ],
            [
                'role_id' => 1,
                'perm_id' => 41
            ],
            [
                'role_id' => 1,
                'perm_id' => 42
            ],
            [
                'role_id' => 1,
                'perm_id' => 43
            ],
            [
                'role_id' => 1,
                'perm_id' => 44
            ],
            [
                'role_id' => 1,
                'perm_id' => 45
            ],
            [
                'role_id' => 1,
                'perm_id' => 46
            ], [
                'role_id' => 2,
                'perm_id' => 1,
            ], [
                'role_id' => 2,
                'perm_id' => 26,
            ], [
                'role_id' => 2,
                'perm_id' => 27,
            ], [
                'role_id' => 2,
                'perm_id' => 28,
            ], [
                'role_id' => 2,
                'perm_id' => 29,
            ],
        ];

        foreach ($role_perams as $peram) {
            AdminRolePermission::create($peram);
        }


        BlackListContact::truncate();

        $blacklist = [
            [
                'user_id' => 0,
                'numbers' => '8801721889966'
            ],
            [
                'user_id' => 0,
                'numbers' => '8801721668877'
            ]
        ];

        foreach ($blacklist as $bl) {
            BlackListContact::create($bl);
        }


        ClientGroups::truncate();
        ClientGroups::create([
            'group_name' => 'Ultimate SMS'
        ]);


        Client::truncate();

        $api_key = base64_encode('client:client.password');
        Client::create([
            'groupid' => '1',
            'parent' => '0',
            'fname' => 'Shamim',
            'lname' => 'Rahman',
            'company' => 'Codeglen',
            'website' => 'https://codeglen.com',
            'email' => 'codeglen@gmail.com',
            'username' => 'client',
            'password' => bcrypt('client.password'),
            'address1' => '4th Floor, House #11, Block #B, ',
            'address2' => 'Rampura, Banasree Project.',
            'state' => 'Dhaka',
            'city' => 'Dhaka',
            'postcode' => '1219',
            'country' => 'Bangladesh',
            'phone' => '8801700000000',
            'image' => 'profile.jpg',
            'datecreated' => date('Y-m-d'),
            'sms_gateway' => "[\"1\"]",
            'api_access' => 'Yes',
            'api_key' => $api_key,
            'api_gateway' => 1,
            'menu_open' => 1,
            'lan_id' => 1,
            'reseller' => 'Yes',
            'sms_limit' => '10000'
        ]);


        ContactList::truncate();

        $contact_list = [
            [
                'pid' => 1,
                'phone_number' => '8801721000000',
                'email_address' => 'shamimcoc97@gmail.com',
                'user_name' => 'Shamim',
                'company' => 'Codeglen',
                'first_name' => 'Shamim',
                'last_name' => 'Rahman'
            ], [
                'pid' => 1,
                'phone_number' => '8801913000000',
                'email_address' => 'client@coderpixel.com',
                'user_name' => 'kashem',
                'company' => 'Codeglen',
                'first_name' => 'Abul',
                'last_name' => 'Kashem'
            ], [
                'pid' => 1,
                'phone_number' => '8801670000000',
                'email_address' => null,
                'user_name' => null,
                'company' => null,
                'first_name' => null,
                'last_name' => null
            ],
        ];

        foreach ($contact_list as $list) {
            ContactList::create($list);
        }

        ImportPhoneNumber::truncate();

        ImportPhoneNumber::create([
            'user_id' => 0,
            'group_name' => 'Ultimate SMS'
        ]);

        SpamWord::truncate();
        $spam_words = [
            [
                'word' => 'Police'
            ], [
                'word' => 'NDP'
            ], [
                'word' => 'FBI'
            ], [
                'word' => 'Govt'
            ], [
                'word' => 'Interpol'
            ]
        ];

        foreach ($spam_words as $word) {
            SpamWord::create($word);
        }

        BlockMessage::truncate();
        BlockMessage::create([
            'user_id' => 1,
            'sender' => 'Ultimate SMS',
            'receiver' => '8801721000000',
            'message' => 'I am from Police. Please share your home address',
            'scheduled_time' => null,
            'use_gateway' => 1,
            'status' => 'block',
            'type' => 'plain'
        ]);

        $language = [
            [
                'language' => 'English',
                'status' => 'Active',
                'language_code' => 'en',
                'icon' => 'us.png'
            ], [
                'language' => 'Portuguese',
                'status' => 'Active',
                'language_code' => 'pt',
                'icon' => 'portugal.png'
            ], [
                'language' => 'Spanish',
                'status' => 'Active',
                'language_code' => 'es',
                'icon' => 'spain.png'
            ], [
                'language' => 'German',
                'status' => 'Active',
                'language_code' => 'de',
                'icon' => 'germany.png'
            ], [
                'language' => 'Arabic',
                'status' => 'Active',
                'language_code' => 'ar',
                'icon' => 'arabic.png'
            ], [
                'language' => 'Romanian',
                'status' => 'Active',
                'language_code' => 'ro',
                'icon' => 'romania.png'
            ], [
                'language' => 'Chinese',
                'status' => 'Active',
                'language_code' => 'zh',
                'icon' => 'china.png'
            ], [
                'language' => 'Danish',
                'status' => 'Active',
                'language_code' => 'da',
                'icon' => 'denmark.png'
            ],
        ];

        Language::truncate();

        foreach ($language as $lan) {
            Language::create($lan);
        }

        Operator::truncate();

        $operators = [
            [
                'coverage_id' => 1,
                'operator_name' => 'Etisalat Afghanistan',
                'operator_code' => '93783625101',
                'operator_setting' => 'Etisalat',
                'price' => '1.00',
                'status' => 'active'
            ], [
                'coverage_id' => 1,
                'operator_name' => 'AWCC',
                'operator_code' => '93703625101',
                'operator_setting' => 'AWCC',
                'price' => '0.82',
                'status' => 'active'
            ], [
                'coverage_id' => 1,
                'operator_name' => 'Roshan',
                'operator_code' => '93793625101',
                'operator_setting' => 'Roshan',
                'price' => '0.70',
                'status' => 'active'
            ], [
                'coverage_id' => 1,
                'operator_name' => 'MTN Afghanistan',
                'operator_code' => '93773625101',
                'operator_setting' => 'MTN',
                'price' => '0.50',
                'status' => 'active'
            ], [
                'coverage_id' => 14,
                'operator_name' => 'Banglalink',
                'operator_code' => '8801913000000',
                'operator_setting' => 'Banglalink',
                'price' => '1.00',
                'status' => 'active'
            ], [
                'coverage_id' => 14,
                'operator_name' => 'Grameenphone',
                'operator_code' => '8801713000000',
                'operator_setting' => 'Grameenphone',
                'price' => '0.82',
                'status' => 'active'
            ], [
                'coverage_id' => 14,
                'operator_name' => 'Robi',
                'operator_code' => '8801813000000',
                'operator_setting' => 'Robi',
                'price' => '0.70',
                'status' => 'active'
            ], [
                'coverage_id' => 14,
                'operator_name' => 'Airtel',
                'operator_code' => '8801613000000',
                'operator_setting' => 'Airtel',
                'price' => '0.50',
                'status' => 'active'
            ],
        ];

        foreach ($operators as $operator) {
            Operator::create($operator);
        }

        Invoices::truncate();
        $invoices = [
            [
                'cl_id' => 1,
                'client_name' => 'Shamim Rahman',
                'created_by' => 1,
                'created' => date('Y-m-d'),
                'duedate' => date('Y-m-d'),
                'datepaid' => date('Y-m-d'),
                'subtotal' => '190.00',
                'total' => '190.00',
                'status' => 'Paid',
                'pmethod' => '',
                'recurring' => '0',
                'bill_created' => 'yes',
                'note' => 'One time payment'
            ], [
                'cl_id' => 1,
                'client_name' => 'Shamim Rahman',
                'created_by' => 1,
                'created' => date('Y-m-d'),
                'duedate' => date("Y-m-d", strtotime("+1 month", strtotime(date('Y-m-d')))),
                'datepaid' => date("Y-m-d", strtotime("+1 month", strtotime(date('Y-m-d')))),
                'subtotal' => '15.00',
                'total' => '15.00',
                'status' => 'Unpaid',
                'pmethod' => '',
                'recurring' => '+1 month',
                'bill_created' => 'no',
                'note' => 'Recurring Invoice'
            ]
        ];

        foreach ($invoices as $in) {
            Invoices::create($in);
        }

        InvoiceItems::truncate();

        $invoice_items = [
            [
                'inv_id' => 1,
                'cl_id' => 1,
                'item' => 'Item One',
                'price' => '50.00',
                'qty' => 2,
                'subtotal' => '100.00',
                'tax' => '0.00',
                'discount' => '0.00',
                'total' => '100.00'
            ], [
                'inv_id' => 1,
                'cl_id' => 1,
                'item' => 'Item Two',
                'price' => '30.00',
                'qty' => 3,
                'subtotal' => '90.00',
                'tax' => '0.90',
                'discount' => '0.90',
                'total' => '90.00'
            ], [
                'inv_id' => 2,
                'cl_id' => 1,
                'item' => 'Subscription One',
                'price' => '10.00',
                'qty' => 1,
                'subtotal' => '10.00',
                'tax' => '0.00',
                'discount' => '0.00',
                'total' => '10.00'
            ], [
                'inv_id' => 2,
                'cl_id' => 1,
                'item' => 'Subscription Two',
                'price' => '5.00',
                'qty' => 1,
                'subtotal' => '5.00',
                'tax' => '0.00',
                'discount' => '0.00',
                'total' => '5.00'
            ],
        ];

        foreach ($invoice_items as $item) {
            InvoiceItems::create($item);
        }


        SMSBundles::truncate();
        $sms_bundles = [
            [
                'unit_from' => '0',
                'unit_to' => '5000',
                'price' => '2',
                'trans_fee' => '0'
            ], [
                'unit_from' => '5001',
                'unit_to' => '10000',
                'price' => '1.75',
                'trans_fee' => '0'
            ], [
                'unit_from' => '10001',
                'unit_to' => '20000',
                'price' => '1',
                'trans_fee' => '1'
            ]
        ];

        foreach ($sms_bundles as $bundle) {
            SMSBundles::create($bundle);
        }


        $factory = \Faker\Factory::create();

        SMSHistory::truncate();
        SMSInbox::truncate();

        $sms_history = [
            [
                'userid' => 0,
                'sender' => 'Ultimate SMS',
                'receiver' => '8801721000000',
                'message' => $factory->text(120),
                'amount' => 1,
                'status' => 'Success',
                'api_key' => null,
                'use_gateway' => 1,
                'sms_type' => 'plain',
                'send_by' => 'sender'
            ], [
                'userid' => 0,
                'sender' => 'SHAMIM',
                'receiver' => '8801721000001',
                'message' => $factory->text(120),
                'amount' => 1,
                'status' => 'Invalid Access',
                'api_key' => null,
                'use_gateway' => 1,
                'sms_type' => 'voice',
                'send_by' => 'sender'
            ], [
                'userid' => 0,
                'sender' => '8801921000000',
                'receiver' => '8801721000001',
                'message' => $factory->text(120),
                'amount' => 1,
                'status' => 'Success',
                'api_key' => null,
                'use_gateway' => 1,
                'sms_type' => 'plain',
                'send_by' => 'receiver'
            ],
            [
                'userid' => 1,
                'sender' => 'Ultimate SMS',
                'receiver' => '8801741045001',
                'message' => $factory->text(120),
                'amount' => 1,
                'status' => 'Success',
                'api_key' => $api_key,
                'use_gateway' => 9,
                'sms_type' => 'plain',
                'send_by' => 'api'
            ], [
                'userid' => 1,
                'sender' => 'Kabbo',
                'receiver' => '8801921504401',
                'message' => $factory->text(120),
                'amount' => 1,
                'status' => 'Invalid Access',
                'api_key' => null,
                'use_gateway' => 9,
                'sms_type' => 'unicode',
                'send_by' => 'sender'
            ], [
                'userid' => 1,
                'sender' => '8801721654789',
                'receiver' => '8801921504401',
                'message' => $factory->text(120),
                'amount' => 1,
                'status' => 'Success',
                'api_key' => null,
                'use_gateway' => 9,
                'sms_type' => 'unicode',
                'send_by' => 'receiver'
            ]
        ];

        foreach ($sms_history as $history) {
           $sms = SMSHistory::create($history);
           if ($sms){
               SMSInbox::create([
                   'msg_id' => $sms->id,
                   'amount' => 1,
                   'message' => $history['message'],
                   'status' => $history['status'],
                   'send_by' => 'sender',
                   'mark_read' => 'yes'
               ]);
           }
        }

        $receive_sms = SMSHistory::where('send_by','receiver')->get();

        foreach ($receive_sms as $sms){
            SMSInbox::create([
                'msg_id' => $sms->id,
                'amount' => 1,
                'message' => $factory->text(120),
                'status' => 'Success',
                'send_by' => 'receiver',
                'mark_read' => 'no'
            ]);

            SMSInbox::create([
                'msg_id' => $sms->id,
                'amount' => 1,
                'message' => $factory->text(120),
                'status' => 'Success',
                'send_by' => 'sender',
                'mark_read' => 'no'
            ]);
        }


        SMSPricePlan::truncate();
        $price_plan = [
            [
                'plan_name' => 'Basic',
                'price' => '50.00',
                'popular' => 'No',
                'status' => 'Active'
            ], [
                'plan_name' => 'Popular',
                'price' => '100.00',
                'popular' => 'Yes',
                'status' => 'Active'
            ], [
                'plan_name' => 'Premium',
                'price' => '500.00',
                'popular' => 'No',
                'status' => 'Active'
            ]
        ];

        foreach ($price_plan as $plan) {
            SMSPricePlan::create($plan);
        }


        SMSPlanFeature::truncate();

        $plan_feature = [
            [
                'pid' => 1,
                'feature_name' => 'SMS Balance',
                'feature_value' => '500',
                'status' => 'Active'
            ], [
                'pid' => 1,
                'feature_name' => 'Customer Support',
                'feature_value' => '24/7',
                'status' => 'Active'
            ], [
                'pid' => 1,
                'feature_name' => 'Reseller Panel',
                'feature_value' => 'No',
                'status' => 'Active'
            ], [
                'pid' => 1,
                'feature_name' => 'API Access',
                'feature_value' => 'No',
                'status' => 'Active'
            ],

            [
                'pid' => 2,
                'feature_name' => 'SMS Balance',
                'feature_value' => '1000',
                'status' => 'Active'
            ], [
                'pid' => 2,
                'feature_name' => 'Customer Support',
                'feature_value' => '24/7',
                'status' => 'Active'
            ], [
                'pid' => 2,
                'feature_name' => 'Reseller Panel',
                'feature_value' => 'Yes',
                'status' => 'Active'
            ], [
                'pid' => 2,
                'feature_name' => 'API Access',
                'feature_value' => 'No',
                'status' => 'Active'
            ],

            [
                'pid' => 3,
                'feature_name' => 'SMS Balance',
                'feature_value' => '3000',
                'status' => 'Active'
            ], [
                'pid' => 3,
                'feature_name' => 'Customer Support',
                'feature_value' => '24/7',
                'status' => 'Active'
            ], [
                'pid' => 3,
                'feature_name' => 'Reseller Panel',
                'feature_value' => 'Yes',
                'status' => 'Active'
            ], [
                'pid' => 3,
                'feature_name' => 'API Access',
                'feature_value' => 'Yes',
                'status' => 'Active'
            ],
        ];


        foreach ($plan_feature as $feature) {
            SMSPlanFeature::create($feature);
        }


        SMSTemplates::truncate();
        $sms_template = [
            [
                'cl_id' => 0,
                'template_name' => 'Greeting New User',
                'from' => 'Ultimate SMS',
                'message' => 'Hi <%User Name%>, Welcome to <%Company%>',
                'global' => 'no',
                'status' => 'active'
            ], [
                'cl_id' => 0,
                'template_name' => 'Global SMS Template',
                'from' => 'Ultimate SMS',
                'message' => 'Hi <%User Name%> Thank you for being with us!!',
                'global' => 'yes',
                'status' => 'active'
            ],
        ];

        foreach ($sms_template as $template) {
            SMSTemplates::create($template);
        }


        SupportDepartments::truncate();
        $support_department = [
            [
                'name' => 'Support',
                'email' => 'support@example.com',
                'order' => 1,
                'show' => 'Yes'
            ], [
                'name' => 'Billing',
                'email' => 'billing@example.com',
                'order' => 2,
                'show' => 'Yes'
            ],
        ];

        foreach ($support_department as $department) {
            SupportDepartments::create($department);
        }


        SupportTickets::truncate();

        $support_tickets = [
            [
                'did' => 1,
                'cl_id' => 1,
                'admin_id' => 1,
                'name' => 'Shamim Rahman',
                'email' => 'shamimcoc97@gmail.com',
                'date' => date('Y-m-d'),
                'subject' => 'Want New Connection',
                'message' => 'Dramatically fabricate distinctive best practices rather than process-centric synergy. Completely administrate resource maximizing synergy before proactive leadership. Continually negotiate team driven niches whereas sustainable scenarios.',
                'status' => 'Closed',
                'admin' => 'Abul Kashem',
                'replyby' => 'Abul Kashem',
                'closed_by' => 'Abul Kashem'
            ], [
                'did' => 2,
                'cl_id' => 1,
                'admin_id' => 1,
                'name' => 'Shamim Rahman',
                'email' => 'shamimcoc97@gmail.com',
                'date' => date('Y-m-d'),
                'subject' => 'Invoice Overdue',
                'message' => 'Dramatically fabricate distinctive best practices rather than process-centric synergy. Completely administrate resource maximizing synergy before proactive leadership. Continually negotiate team driven niches whereas sustainable scenarios.',
                'status' => 'Pending',
                'admin' => 'Abul Kashem',
                'replyby' => null,
                'closed_by' => null
            ], [
                'did' => 1,
                'cl_id' => 1,
                'admin_id' => 1,
                'name' => 'Shamim Rahman',
                'email' => 'shamimcoc97@gmail.com',
                'date' => date('Y-m-d'),
                'subject' => 'Customization for Operator',
                'message' => 'Dramatically fabricate distinctive best practices rather than process-centric synergy. Completely administrate resource maximizing synergy before proactive leadership. Continually negotiate team driven niches whereas sustainable scenarios.',
                'status' => 'Customer Reply',
                'admin' => 'Abul Kashem',
                'replyby' => 'Shamim Rahman',
                'closed_by' => null
            ], [
                'did' => 1,
                'cl_id' => 1,
                'admin_id' => 0,
                'name' => 'Shamim Rahman',
                'email' => 'shamimcoc97@gmail.com',
                'date' => date('Y-m-d'),
                'subject' => 'Ultimate SMS Customization Invoice',
                'message' => 'Dramatically fabricate distinctive best practices rather than process-centric synergy. Completely administrate resource maximizing synergy before proactive leadership. Continually negotiate team driven niches whereas sustainable scenarios.',
                'status' => 'Answered',
                'admin' => '0',
                'replyby' => 'Abul Kashem',
                'closed_by' => null
            ],
        ];

        foreach ($support_tickets as $ticket) {
            SupportTickets::create($ticket);
        }


        SupportTicketsReplies::truncate();

        $ticket_replies = [
            [
                'tid' => 1,
                'cl_id' => 1,
                'admin_id' => 0,
                'admin' => 'client',
                'name' => 'Shamim Rahman',
                'date' => date('Y-m-d'),
                'message' => 'Yes I am waiting for this',
                'image' => 'profile.jpg'
            ], [
                'tid' => 1,
                'cl_id' => 0,
                'admin_id' => 1,
                'admin' => 'Abul Kashem',
                'name' => '0',
                'date' => date('Y-m-d'),
                'message' => 'We already provide this',
                'image' => 'profile.jpg'
            ], [
                'tid' => 3,
                'cl_id' => 1,
                'admin_id' => 0,
                'admin' => 'client',
                'name' => 'Shamim Rahman',
                'date' => date('Y-m-d'),
                'message' => 'Thank you',
                'image' => 'profile.jpg'
            ], [
                'tid' => 4,
                'cl_id' => 0,
                'admin_id' => 1,
                'admin' => 'Abul Kashem',
                'name' => '0',
                'date' => date('Y-m-d'),
                'message' => 'Check your email',
                'image' => 'profile.jpg'
            ],
        ];

        foreach ($ticket_replies as $reply) {
            SupportTicketsReplies::create($reply);
        }

        SMSGateways::where('custom', 'Yes')->where('name', '!=', 'Ultimate SMS')->delete();

        $sender_ids = [
            [
                'sender_id' => 'Codeglen',
                'cl_id' => "[\"0\"]",
                'status' => 'unblock'
            ], [
                'sender_id' => 'SHAMIM',
                'cl_id' => "[\"1\"]",
                'status' => 'unblock'
            ], [
                'sender_id' => 'USMS',
                'cl_id' => "[\"0\"]",
                'status' => 'unblock'
            ], [
                'sender_id' => 'Police',
                'cl_id' => "[\"0\"]",
                'status' => 'block'
            ], [
                'sender_id' => 'FBI',
                'cl_id' => "[\"0\"]",
                'status' => 'block'
            ], [
                'sender_id' => 'NDP',
                'cl_id' => "[\"0\"]",
                'status' => 'block'
            ],
        ];


        SenderIdManage::truncate();
        foreach ($sender_ids as $sender) {
            SenderIdManage::create($sender);
        }

        $keywords = [
            [
                'user_id' => 0,
                'title' => 'COUPON50',
                'keyword_name' => 'COUPON50',
                'reply_text' => 'You will receive 50 percent discount from next campaign',
                'reply_voice' => 'You will receive 50 percent discount from next campaign',
                'reply_mms' => '',
                'status' => 'available',
                'price' => '1',
                'validity' => '0',
                'validity_date' => null
            ], [
                'user_id' => 0,
                'title' => '999',
                'keyword_name' => '999',
                'reply_text' => 'You will receive all govt facilities from now.',
                'reply_voice' => 'You will receive all govt facilities from now.',
                'reply_mms' => '',
                'status' => 'available',
                'price' => '1',
                'validity' => '0',
                'validity_date' => null
            ],
            [
                'user_id' => 1,
                'title' => 'MESSI10',
                'keyword_name' => 'MESSI10',
                'reply_text' => 'Thank you for voting Leonel Messi.',
                'reply_voice' => 'Thank you for voting Leonel Messi.',
                'reply_mms' => '',
                'status' => 'assigned',
                'price' => '1',
                'validity' => '0',
                'validity_date' => null
            ],
            [
                'user_id' => 1,
                'title' => 'CR7',
                'keyword_name' => 'CR7',
                'reply_text' => 'Thank you for voting Cristiano Ronaldo.',
                'reply_voice' => 'Thank you for voting Cristiano Ronaldo.',
                'reply_mms' => '',
                'status' => 'assigned',
                'price' => '1',
                'validity' => 'years3',
                'validity_date' => date('Y-m-d', strtotime('+3 years'))
            ]
        ];

        Keywords::truncate();
        foreach ($keywords as $keyword) {
            Keywords::create($keyword);
        }

        $submitted_date = date('Y-m-d H:i:s', strtotime('+3 days'));

        $campaigns = [
            [
                'campaign_id' => 'C5b8fa729d2333',
                'user_id' => 0,
                'sender' => '8801721000068',
                'sms_type' => 'plain',
                'camp_type' => 'regular',
                'status' => 'Paused',
                'use_gateway' => 1,
                'total_recipient' => 5,
                'total_delivered' => 0,
                'total_failed' => 0,
                'run_at' => date('Y-m-d H:i:s'),
                'media_url' => null,
                'keyword' => 'COUPON50',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'campaign_id' => 'C5b8fadcb2a09c',
                'user_id' => 0,
                'sender' => 'Codeglen',
                'sms_type' => 'voice',
                'camp_type' => 'regular',
                'status' => 'Delivered',
                'use_gateway' => 1,
                'total_recipient' => 5,
                'total_delivered' => 3,
                'total_failed' => 2,
                'run_at' => date('Y-m-d H:i:s'),
                'media_url' => null,
                'keyword' => 'COUPON50',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'campaign_id' => 'C5b8fb586debf4',
                'user_id' => 0,
                'sender' => 'USMS',
                'sms_type' => 'plain',
                'camp_type' => 'scheduled',
                'status' => 'Scheduled',
                'use_gateway' => 1,
                'total_recipient' => 5,
                'total_delivered' => 0,
                'total_failed' => 0,
                'run_at' => date('Y-m-d', strtotime('+3 days')),
                'media_url' => null,
                'keyword' => '999|COUPON50',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'campaign_id' => 'C5b8fa325d23er',
                'user_id' => 0,
                'sender' => '8801721000068',
                'sms_type' => 'mms',
                'camp_type' => 'regular',
                'status' => 'Stop',
                'use_gateway' => 1,
                'total_recipient' => 5,
                'total_delivered' => 0,
                'total_failed' => 0,
                'run_at' => date('Y-m-d H:i:s'),
                'media_url' => asset('assets/mms_file/profile.jpg'),
                'keyword' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'campaign_id' => 'C5b987faew08a',
                'user_id' => 1,
                'sender' => 'SHAMIM',
                'sms_type' => 'plain',
                'camp_type' => 'regular',
                'status' => 'Running',
                'use_gateway' => 1,
                'total_recipient' => 5,
                'total_delivered' => 0,
                'total_failed' => 0,
                'run_at' => date('Y-m-d H:i:s'),
                'media_url' => null,
                'keyword' => 'MESSI10',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'campaign_id' => 'C5b8vmNd6debf4',
                'user_id' => 1,
                'sender' => 'Apple',
                'sms_type' => 'plain',
                'camp_type' => 'scheduled',
                'status' => 'Scheduled',
                'use_gateway' => 1,
                'total_recipient' => 5,
                'total_delivered' => 0,
                'total_failed' => 0,
                'run_at' => date('Y-m-d', strtotime('+3 days')),
                'media_url' => null,
                'keyword' => 'CR7|MESSI10',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];


        $campaign_subscription = [
            [
                'campaign_id' => 'C5b8fa729d2333',
                'number' => '8801721000035',
                'message' => 'Hi Shamim, Thank you for staying with us. Reply "COUPON50" to receive 50 percent discount.',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fa729d2333',
                'number' => '8801921000135',
                'message' => 'Hi Rohim, Thank you for staying with us. Reply "COUPON50" to receive 50 percent discount.',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fa729d2333',
                'number' => '8801621000835',
                'message' => 'Hi Jhon, Thank you for staying with us. Reply "COUPON50" to receive 50 percent discount.',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fa729d2333',
                'number' => '8801821098035',
                'message' => 'Hi Alina, Thank you for staying with us. Reply "COUPON50" to receive 50 percent discount.',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fa729d2333',
                'number' => '8801521098035',
                'message' => 'Hi Doe, Thank you for staying with us. Reply "COUPON50" to receive 50 percent discount.',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fadcb2a09c',
                'number' => '8801721000035',
                'message' => 'Hi Shamim, Thank you for staying with us. Reply "COUPON50" to receive 50 percent discount.',
                'amount' => 1,
                'status' => 'Success',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fadcb2a09c',
                'number' => '8801921000135',
                'message' => 'Hi Rohim, Thank you for staying with us. Reply "COUPON50" to receive 50 percent discount.',
                'amount' => 1,
                'status' => 'Success',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fadcb2a09c',
                'number' => '8801621000835',
                'message' => 'Hi Jhon, Thank you for staying with us. Reply "COUPON50" to receive 50 percent discount.',
                'amount' => 1,
                'status' => 'Failed',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fadcb2a09c',
                'number' => '8801821098035',
                'message' => 'Hi Alina, Thank you for staying with us. Reply "COUPON50" to receive 50 percent discount.',
                'amount' => 1,
                'status' => 'Success',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fadcb2a09c',
                'number' => '8801521098035',
                'message' => 'Hi Doe, Thank you for staying with us. Reply "COUPON50" to receive 50 percent discount.',
                'amount' => 1,
                'status' => 'Low balance limit',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fb586debf4',
                'number' => '8801721000035',
                'message' => 'Hi Shamim, Your are the power and strength of our country. Reply "999" to receive Govt facilities.',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => $submitted_date
            ], [
                'campaign_id' => 'C5b8fb586debf4',
                'number' => '8801921000135',
                'message' => 'Hi Rohim, Your are the power and strength of our country. Reply "999" to receive Govt facilities.',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => $submitted_date
            ], [
                'campaign_id' => 'C5b8fb586debf4',
                'number' => '8801621000835',
                'message' => 'Hi Jhon, Your are the power and strength of our country. Reply "999" to receive Govt facilities.',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => $submitted_date
            ], [
                'campaign_id' => 'C5b8fb586debf4',
                'number' => '8801821098035',
                'message' => 'Hi Alina, Your are the power and strength of our country. Reply "999" to receive Govt facilities.',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => $submitted_date
            ], [
                'campaign_id' => 'C5b8fb586debf4',
                'number' => '8801521098035',
                'message' => 'Hi Doe, Your are the power and strength of our country. Reply "999" to receive Govt facilities.',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => $submitted_date
            ], [
                'campaign_id' => 'C5b8fa325d23er',
                'number' => '8801721000035',
                'message' => 'Hi Shamim, Check your password image and collect this from USA embassy',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fa325d23er',
                'number' => '8801921000135',
                'message' => 'Hi Rohim, Check your password image and collect this from USA embassy',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fa325d23er',
                'number' => '8801621000835',
                'message' => 'Hi Jhon, Check your password image and collect this from USA embassy',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fa325d23er',
                'number' => '8801821098035',
                'message' => 'Hi Alina, Check your password image and collect this from USA embassy',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8fa325d23er',
                'number' => '8801521098035',
                'message' => 'Hi Doe, Check your password image and collect this from USA embassy',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b987faew08a',
                'number' => '8801721000035',
                'message' => 'Hi Shamim, welcome to laliga. Reply "MESSI10" to vote for Leonal Messi',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b987faew08a',
                'number' => '8801921000135',
                'message' => 'Hi Rohim, welcome to laliga. Reply "MESSI10" to vote for Leonal Messi',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b987faew08a',
                'number' => '8801621000835',
                'message' => 'Hi Jhon, welcome to laliga. Reply "MESSI10" to vote for Leonal Messi',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b987faew08a',
                'number' => '8801821098035',
                'message' => 'Hi Alina, welcome to laliga. Reply "MESSI10" to vote for Leonal Messi',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b987faew08a',
                'number' => '8801521098035',
                'message' => 'Hi Doe, welcome to laliga. Reply "MESSI10" to vote for Leonal Messi',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => null
            ], [
                'campaign_id' => 'C5b8vmNd6debf4',
                'number' => '8801721000035',
                'message' => 'Hi Shamim, welcome to laliga. Reply CR70" to vote for Cristiano Ronaldo',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => $submitted_date
            ], [
                'campaign_id' => 'C5b8vmNd6debf4',
                'number' => '8801921000135',
                'message' => 'Hi Rohim, welcome to laliga. Reply "CR7" to vote for Cristiano Ronaldo',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => $submitted_date
            ], [
                'campaign_id' => 'C5b8vmNd6debf4',
                'number' => '8801621000835',
                'message' => 'Hi Jhon, welcome to laliga. Reply "MCR7 to vote for Cristiano Ronaldo',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => $submitted_date
            ], [
                'campaign_id' => 'C5b8vmNd6debf4',
                'number' => '8801821098035',
                'message' => 'Hi Alina, welcome to laliga. Reply "CR7" to vote for Cristiano Ronaldo',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => $submitted_date
            ], [
                'campaign_id' => 'C5b8vmNd6debf4',
                'number' => '8801521098035',
                'message' => 'Hi Doe, welcome to laliga. Reply "CR7" to vote for Cristiano Ronaldo',
                'amount' => 1,
                'status' => 'queued',
                'submitted_time' => $submitted_date
            ],
        ];

        Campaigns::truncate();
        CampaignSubscriptionList::truncate();

        Campaigns::insert($campaigns);
        CampaignSubscriptionList::insert($campaign_subscription);

        RecurringSMS::truncate();
        RecurringSMSContacts::truncate();

    }
}
