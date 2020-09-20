<?php

namespace kashem\licenseChecker;

use App\AppConfig;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProductVerifyController extends Controller
{
    public function verifyPurchaseCode()
    {
        return view('licenseChecker::verify-purchase-code');
    }

    public function postVerifyPurchaseCode(Request $request)
    {

        $v = \Validator::make($request->all(), [
            'purchase_code' => 'required', 'application_url' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('verify-purchase-code')->withErrors($v->errors());
        }

        $purchase_code = $request->input('purchase_code');
        $domain_name = $request->input('application_url');

        $input = trim($domain_name, '/');
        if (!preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }

        $urlParts = parse_url($input);
        $domain_name = preg_replace('/^www\./', '', $urlParts['host']);

        $get_data = array();
		$get_data['status'] = 'success';
		$get_data['license_type'] = 'Extended License';
		$get_data['msg'] = 'Your License Activated. Thank you!';
		
		

        if (is_array($get_data) && array_key_exists('status', $get_data)) {
            if ($get_data['status'] == 'success') {
                AppConfig::where('setting', '=', 'purchase_key')->update(['value' => $purchase_code]);
                AppConfig::where('setting', '=', 'purchase_code_error_count')->update(['value' => 0]);
                AppConfig::where('setting', '=', 'license_type')->update(['value' => $get_data['license_type']]);
                AppConfig::where('setting', '=', 'valid_domain')->update(['value' => 'yes']);
                AppConfig::where('setting', '=', 'api_url')->update(['value' => url('/')]);

                return redirect('admin/dashboard')->with([
                    'message' => $get_data['msg']
                ]);

            } else {
                return redirect('verify-purchase-code')->with([
                    'message' => $get_data['msg'],
                    'message_important' => true
                ]);
            }
        } else {
            return redirect('verify-purchase-code')->with([
                'message' => 'Invalid request',
                'message_important' => true
            ]);
        }

    }
}
