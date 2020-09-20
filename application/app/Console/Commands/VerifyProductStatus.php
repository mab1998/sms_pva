<?php

namespace App\Console\Commands;

use App\AppConfig;
use Illuminate\Console\Command;

class VerifyProductStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'VerifyProductStatus:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Envato license key';

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
        $purchase_code = app_config('purchase_key');

        $data = array();
		$data['status'] = 'success';

        if (isset($data) && is_array($data) && array_key_exists('status', $data)) {
            if ($data['status'] != 'success') {
                $error_count = app_config('purchase_code_error_count');
                if ($error_count > 5) {
                    AppConfig::where('setting', '=', 'purchase_key')->update(['value' => null]);
                } else {
                    $error_count++;
                    AppConfig::where('setting', '=', 'purchase_code_error_count')->update(['value' => $error_count]);
                }
            }
        } else {
            $error_count = app_config('purchase_code_error_count');
            if ($error_count > 5) {
                AppConfig::where('setting', '=', 'purchase_key')->update(['value' => null]);
            } else {
                $error_count++;
                AppConfig::where('setting', '=', 'purchase_code_error_count')->update(['value' => $error_count]);
            }
        }

    }
}
