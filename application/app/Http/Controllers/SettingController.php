<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Classes\Permission;
use App\EmailTemplates;
use App\IntCountryCodes;
use App\Language;
use App\LanguageData;
use App\PaymentGateways;
use App\SMSGateways;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /* general  Function Start Here */
    public function general()
    {
        $self = 'system-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $sms_gateways = SMSGateways::where('status', 'Active')->get();

        return view('admin.system-setting', compact('sms_gateways'));
    }

    /* postGeneralSetting  Function Start Here */
    public function postGeneralSetting(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('settings/general')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'system-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'app_name' => 'required', 'app_title' => 'required', 'email' => 'required', 'footer' => 'required', 'app_logo' => 'image|mimes:jpeg,jpg,png,gif', 'app_fav' => 'image|mimes:jpeg,jpg,png,gif,ico'
        ]);
        if ($v->fails()) {
            return redirect('settings/general')->withInput($request->all())->withErrors($v->errors());
        }
        $destinationPath = public_path() . '/assets/img/';
        $save_path       = 'assets/img/';
        $app_name        = Input::get('app_name');
        $app_title       = Input::get('app_title');
        $email           = Input::get('email');
        $footer          = Input::get('footer');
        $app_logo        = Input::file('app_logo');
        $app_fav         = Input::file('app_fav');
        $address         = Input::get('address');

        if ($app_name != '') {
            AppConfig::where('setting', '=', 'AppName')->update(['value' => $app_name]);

            $appNameSetting = "\n" .
                'APP_NAME="' . $app_name . '"' .
                "\n";
            // @ignoreCodingStandard
            $env        = file_get_contents(base_path('.env'));
            $rows       = explode("\n", $env);
            $unwanted   = "APP_NAME";
            $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

            $cleanString = implode("\n", $cleanArray);
            $env         = $cleanString . $appNameSetting;

            try {
                file_put_contents(base_path('.env'), $env);
            } catch (\Exception $e) {
                return redirect('settings/general')->with([
                    'message' => $e->getMessage(),
                    'message_important' => true
                ]);
            }

        }
        if ($app_title != '') {
            AppConfig::where('setting', '=', 'AppTitle')->update(['value' => $app_title]);
        }
        if ($email != '') {
            AppConfig::where('setting', '=', 'Email')->update(['value' => $email]);
        }
        if ($footer != '') {
            AppConfig::where('setting', '=', 'FooterTxt')->update(['value' => $footer]);
        }
        if ($address != '') {
            AppConfig::where('setting', '=', 'Address')->update(['value' => $address]);
        }
        if ($app_logo != '') {
            if (isset($app_logo) && in_array(strtolower($app_logo->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg'))) {
                $app_logo_name = $app_logo->getClientOriginalName();
                Input::file('app_logo')->move($destinationPath, $app_logo_name);
                $or_path = $save_path . $app_logo_name;
                AppConfig::where('setting', '=', 'AppLogo')->update(['value' => $or_path]);
            } else {
                return redirect('settings/general')->withInput($request->all())->with([
                    'message' => language_data('Upload .png or .jpeg or .jpg or .gif file'),
                    'message_important' => true
                ]);
            }
        }
        if ($app_fav != '') {
            if (isset($app_fav) && in_array(strtolower($app_fav->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'ico'))) {

                $app_fav_name = $app_fav->getClientOriginalName();
                Input::file('app_fav')->move($destinationPath, $app_fav_name);
                $or_path = $save_path . $app_fav_name;
                AppConfig::where('setting', '=', 'AppFav')->update(['value' => $or_path]);
            } else {
                return redirect('settings/general')->withInput($request->all())->with([
                    'message' => language_data('Upload .png or .jpeg or .jpg or .gif file'),
                    'message_important' => true
                ]);
            }
        }

        return redirect('settings/general')->with([
            'message' => language_data('Setting Update Successfully')
        ]);
    }


    /* postSystemEmailSetting  Function Start Here */
    public function postSystemEmailSetting(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('settings/general')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'system-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $eg = $request->email_gateway;

        if ($eg == '') {
            return redirect('settings/general')->with([
                'message' => 'System Email Setting Required',
                'message_important' => true
            ]);
        }

        AppConfig::where('setting', '=', 'Gateway')->update(['value' => $eg]);

        if ($eg == 'smtp') {
            $v = \Validator::make($request->all(), [
                'smtp_host_name' => 'required', 'smtp_user_name' => 'required', 'smtp_password' => 'required', 'smtp_port' => 'required', 'smtp_secure' => 'required'
            ]);
            if ($v->fails()) {
                return redirect('settings/general')->withErrors($v->errors());
            }
            $host_name = Input::get('smtp_host_name');
            $user_name = Input::get('smtp_user_name');
            $password  = Input::get('smtp_password');
            $port      = Input::get('smtp_port');
            $secure    = Input::get('smtp_secure');


            $smtpSetting = 'MAIL_DRIVER=' . $eg . '
MAIL_HOST=' . $host_name . '
MAIL_PORT=' . $port . '
MAIL_USERNAME=' . $user_name . '
MAIL_PASSWORD=' . $password . '
MAIL_ENCRYPTION=' . $secure . '
';
            // @ignoreCodingStandard
            $env        = file_get_contents(base_path('.env'));
            $rows       = explode("\n", $env);
            $unwanted   = "MAIL_DRIVER|MAIL_HOST|MAIL_PORT|MAIL_USERNAME|MAIL_PASSWORD|MAIL_ENCRYPTION";
            $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

            $cleanString = implode("\n", $cleanArray);
            $env         = $cleanString . $smtpSetting;

            try {
                file_put_contents(base_path('.env'), $env);

                return redirect('settings/general')->with([
                    'message' => language_data('Setting Update Successfully')
                ]);

            } catch (\Exception $e) {
                return redirect('settings/general')->with([
                    'message' => $e->getMessage(),
                    'message_important' => true
                ]);
            }


        } elseif ($eg == 'mailgun') {

            $v = \Validator::make($request->all(), [
                'domain_name' => 'required', 'api_key' => 'required', 'host_name' => 'required', 'user_name' => 'required', 'password' => 'required', 'port' => 'required', 'secure' => 'required'
            ]);
            if ($v->fails()) {
                return redirect('settings/general')->withErrors($v->errors());
            }
            $domain_name = Input::get('domain_name');
            $api_key     = Input::get('api_key');
            $host_name   = Input::get('host_name');
            $user_name   = Input::get('user_name');
            $password    = Input::get('password');
            $port        = Input::get('port');
            $secure      = Input::get('secure');


            $smtpSetting = 'MAIL_DRIVER=' . $eg . '
MAIL_HOST=' . $host_name . '
MAIL_PORT=' . $port . '
MAIL_USERNAME=' . $user_name . '
MAIL_PASSWORD=' . $password . '
MAIL_ENCRYPTION=' . $secure . '
MAILGUN_DOMAIN=' . $domain_name . '
MAILGUN_SECRET=' . $api_key . '
';
            // @ignoreCodingStandard
            $env        = file_get_contents(base_path('.env'));
            $rows       = explode("\n", $env);
            $unwanted   = "MAIL_DRIVER|MAIL_HOST|MAIL_PORT|MAIL_USERNAME|MAIL_PASSWORD|MAIL_ENCRYPTION|MAILGUN_DOMAIN|MAILGUN_SECRET";
            $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

            $cleanString = implode("\n", $cleanArray);
            $env         = $cleanString . $smtpSetting;

            try {
                file_put_contents(base_path('.env'), $env);

                return redirect('settings/general')->with([
                    'message' => language_data('Setting Update Successfully')
                ]);

            } catch (\Exception $e) {
                return redirect('settings/general')->with([
                    'message' => $e->getMessage(),
                    'message_important' => true
                ]);
            }

        } else {

            $timeZoneSetting = "\n" .
                'MAIL_DRIVER=' . $eg .
                "\n";
            // @ignoreCodingStandard
            $env        = file_get_contents(base_path('.env'));
            $rows       = explode("\n", $env);
            $unwanted   = "MAIL_DRIVER";
            $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

            $cleanString = implode("\n", $cleanArray);
            $env         = $cleanString . $timeZoneSetting;

            try {
                file_put_contents(base_path('.env'), $env);

                return redirect('settings/general')->with([
                    'message' => language_data('Setting Update Successfully')
                ]);

            } catch (\Exception $e) {
                return redirect('settings/general')->with([
                    'message' => $e->getMessage(),
                    'message_important' => true
                ]);
            }
        }
    }

    /* postSystemSMSSetting  Function Start Here */
    public function postSystemSMSSetting(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('settings/general')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'system-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'registration_sms_gateway' => 'required', 'api_permission' => 'required', 'sender_id_verification' => 'required', 'fraud_detection' => 'required'
        ]);
        if ($v->fails()) {
            return redirect('settings/general')->withInput($request->all())->withErrors($v->errors());
        }

        $registration_sms_gateway = Input::get('registration_sms_gateway');
        $api_permission           = Input::get('api_permission');
        $sender_id_verification   = Input::get('sender_id_verification');
        $fraud_detection          = Input::get('fraud_detection');
        $unsubscribe_message      = Input::get('unsubscribe_message');


        if ($registration_sms_gateway != '') {
            AppConfig::where('setting', '=', 'registration_sms_gateway')->update(['value' => $registration_sms_gateway]);
        }

        if ($api_permission != '') {
            AppConfig::where('setting', '=', 'sms_api_permission')->update(['value' => $api_permission]);
        }

        if ($sender_id_verification != '') {
            AppConfig::where('setting', '=', 'sender_id_verification')->update(['value' => $sender_id_verification]);
        }

        if ($fraud_detection != '') {
            AppConfig::where('setting', '=', 'fraud_detection')->update(['value' => $fraud_detection]);
        }

        if ($unsubscribe_message != '') {
            AppConfig::where('setting', '=', 'unsubscribe_message')->update(['value' => $unsubscribe_message]);
        }

        return redirect('settings/general')->with([
            'message' => language_data('Setting Update Successfully')
        ]);
    }


    /* postSystemAuthSetting  Function Start Here */
    public function postSystemAuthSetting(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('settings/general')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'system-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'client_registration' => 'required', 'registration_verification' => 'required', 'captcha_in_admin' => 'required', 'captcha_in_client' => 'required', 'captcha_in_client_registration' => 'required'
        ]);
        if ($v->fails()) {
            return redirect('settings/general')->withInput($request->all())->withErrors($v->errors());
        }


        $client_registration            = Input::get('client_registration');
        $registration_verification      = Input::get('registration_verification');
        $captcha_in_admin               = Input::get('captcha_in_admin');
        $captcha_in_client              = Input::get('captcha_in_client');
        $captcha_in_client_registration = Input::get('captcha_in_client_registration');
        $captcha_site_key               = Input::get('captcha_site_key');
        $captcha_secret_key             = Input::get('captcha_secret_key');


        if ($captcha_in_admin == '1' || $captcha_in_admin == '1' || $captcha_in_client == '1' || $captcha_in_client_registration == '1') {

            $v = \Validator::make($request->all(), [
                'captcha_site_key' => 'required', 'captcha_secret_key' => 'required'
            ]);
            if ($v->fails()) {
                return redirect('settings/general')->withInput($request->all())->withErrors($v->errors());
            }
        }

        if ($client_registration != '') {
            AppConfig::where('setting', '=', 'client_registration')->update(['value' => $client_registration]);
        }

        if ($registration_verification != '') {
            AppConfig::where('setting', '=', 'registration_verification')->update(['value' => $registration_verification]);
        }

        if ($captcha_in_admin != '') {
            AppConfig::where('setting', '=', 'captcha_in_admin')->update(['value' => $captcha_in_admin]);
        }


        if ($captcha_in_client != '') {
            AppConfig::where('setting', '=', 'captcha_in_client')->update(['value' => $captcha_in_client]);
        }

        if ($captcha_in_client_registration != '') {
            AppConfig::where('setting', '=', 'captcha_in_client_registration')->update(['value' => $captcha_in_client_registration]);
        }

        if ($captcha_site_key != '') {
            AppConfig::where('setting', '=', 'captcha_site_key')->update(['value' => $captcha_site_key]);
        }

        if ($captcha_secret_key != '') {
            AppConfig::where('setting', '=', 'captcha_secret_key')->update(['value' => $captcha_secret_key]);
        }

        return redirect('settings/general')->with([
            'message' => language_data('Setting Update Successfully')
        ]);
    }


    /* localization  Function Start Here */
    public function localization()
    {
        $self = 'localization';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $language_data = Language::where('status', 'Active')->get();
        $country_code  = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();

        return view('admin.localization', compact('language_data', 'country_code'));
    }


    /* localizationPost  Function Start Here */
    public function localizationPost(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('settings/localization')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'localization';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'country' => 'required', 'date_format' => 'required', 'language' => 'required', 'currency_code' => 'required', 'currency_symbol' => 'required', 'timezone' => 'required', 'cformat' => 'required', 'currency_decimal_digits' => 'required', 'currency_symbol_position' => 'required', 'country_code' => 'required'
        ]);
        if ($v->fails()) {
            return redirect('settings/localization')->withInput($request->all())->withErrors($v->errors());
        }
        $country                  = Input::get('country');
        $date_format              = Input::get('date_format');
        $language                 = Input::get('language');
        $currency_code            = Input::get('currency_code');
        $currency_symbol          = Input::get('currency_symbol');
        $get_timezone             = Input::get('timezone');
        $cformat                  = Input::get('cformat');
        $currency_decimal_digits  = Input::get('currency_decimal_digits');
        $currency_symbol_position = Input::get('currency_symbol_position');
        $country_code             = Input::get('country_code');

        if ($country != '' AND $date_format != '' AND $language != '' AND $currency_code != '' AND $currency_symbol != '' AND $cformat != '' AND $currency_decimal_digits != '' AND $currency_symbol_position != '') {
            AppConfig::where('setting', '=', 'Country')->update(['value' => $country]);
            AppConfig::where('setting', '=', 'DateFormat')->update(['value' => $date_format]);
            AppConfig::where('setting', '=', 'Language')->update(['value' => $language]);
            AppConfig::where('setting', '=', 'Currency')->update(['value' => $currency_code]);
            AppConfig::where('setting', '=', 'CurrencyCode')->update(['value' => $currency_symbol]);
            AppConfig::where('setting', '=', 'send_sms_country_code')->update(['value' => $country_code]);

            if ($cformat == '1') {
                AppConfig::where('setting', '=', 'dec_point')->update(['value' => '.']);
                AppConfig::where('setting', '=', 'thousands_sep')->update(['value' => '']);
            } elseif ($cformat == '2') {
                AppConfig::where('setting', '=', 'dec_point')->update(['value' => '.']);
                AppConfig::where('setting', '=', 'thousands_sep')->update(['value' => ',']);
            } elseif ($cformat == '3') {
                AppConfig::where('setting', '=', 'dec_point')->update(['value' => ',']);
                AppConfig::where('setting', '=', 'thousands_sep')->update(['value' => '']);
            } elseif ($cformat == '4') {
                AppConfig::where('setting', '=', 'dec_point')->update(['value' => ',']);
                AppConfig::where('setting', '=', 'thousands_sep')->update(['value' => '.']);
            } else {
                AppConfig::where('setting', '=', 'dec_point')->update(['value' => '.']);
                AppConfig::where('setting', '=', 'thousands_sep')->update(['value' => ',']);
            }

            AppConfig::where('setting', '=', 'currency_decimal_digits')->update(['value' => $currency_decimal_digits]);
            AppConfig::where('setting', '=', 'currency_symbol_position')->update(['value' => $currency_symbol_position]);

            if (config('app.timezone') != $get_timezone) {

                AppConfig::where('setting', '=', 'Timezone')->update(['value' => $get_timezone]);

                $timeZoneSetting = "\n" .
                    'TIME_ZONE=' . $get_timezone .
                    "\n";
                // @ignoreCodingStandard
                $env        = file_get_contents(base_path('.env'));
                $rows       = explode("\n", $env);
                $unwanted   = "TIME_ZONE";
                $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

                $cleanString = implode("\n", $cleanArray);
                $env         = $cleanString . $timeZoneSetting;

                try {
                    file_put_contents(base_path('.env'), $env);
                } catch (\Exception $e) {
                    return redirect('settings/localization')->with([
                        'message' => $e->getMessage(),
                        'message_important' => true
                    ]);
                }
            }

            return redirect('settings/localization')->with([
                'message' => language_data('Setting Update Successfully')
            ]);
        } else {
            return redirect('settings/localization')->with([
                'message' => language_data('Please try again'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // paymentGateways Function Start Here
    //======================================================================
    public function paymentGateways()
    {
        $self = 'payment-gateway';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $paymentGateways = PaymentGateways::all();
        return view('admin.payment-gateways', compact('paymentGateways'));
    }

    //======================================================================
    // paymentGatewayManage Function Start Here
    //======================================================================
    public function paymentGatewayManage($id)
    {
        $self = 'payment-gateway';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $pg = PaymentGateways::find($id);
        if ($pg) {
            return view('admin.payment-gateway-manage', compact('pg'));
        } else {
            return redirect('settings/payment-gateways')->with([
                'message' => language_data('Payment Gateway not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postPaymentGatewayManage Function Start Here
    //======================================================================
    public function postPaymentGatewayManage(Request $request)
    {
        $appStage = app_config('AppStage');
        $cmd      = Input::get('cmd');

        if ($appStage == 'Demo') {
            return redirect('settings/payment-gateway-manage/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'payment-gateway';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'pg_value' => 'required', 'gateway_name' => 'required', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('settings/payment-gateway-manage/' . $cmd)->withErrors($v->errors());
        }
        $pg = PaymentGateways::find($cmd);


        if ($pg->settings == 'paystack') {
            $payStackSetting = 'PAYSTACK_PUBLIC_KEY=' . $request->pg_value . '
PAYSTACK_SECRET_KEY=' . $request->pg_extra_value . '
PAYSTACK_PAYMENT_URL= https://api.paystack.co' . '
MERCHANT_EMAIL=' . $request->pg_password . '
';
            // @ignoreCodingStandard
            $env        = file_get_contents(base_path('.env'));
            $rows       = explode("\n", $env);
            $unwanted   = "PAYSTACK_PUBLIC_KEY|PAYSTACK_SECRET_KEY|MERCHANT_EMAIL|PAYSTACK_PAYMENT_URL";
            $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

            $cleanString = implode("\n", $cleanArray);
            $env         = $cleanString . $payStackSetting;

            try {
                file_put_contents(base_path('.env'), $env);
            } catch (\Exception $e) {
                return redirect('settings/payment-gateways')->with([
                    'message' => $e->getMessage(),
                    'message_important' => true
                ]);
            }
        }


        if ($pg->settings == 'paypal') {
            $payStackSetting = 'PAYPAL_CLIENT_ID=' . $request->pg_value . '
PAYPAL_SECRET=' . $request->pg_extra_value . '
PAYPAL_MODE=' . $request->pg_mode . '
';
            // @ignoreCodingStandard
            $env        = file_get_contents(base_path('.env'));
            $rows       = explode("\n", $env);
            $unwanted   = "PAYPAL_CLIENT_ID|PAYPAL_SECRET|PAYPAL_MODE";
            $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

            $cleanString = implode("\n", $cleanArray);
            $env         = $cleanString . $payStackSetting;

            try {
                file_put_contents(base_path('.env'), $env);
            } catch (\Exception $e) {
                return redirect('settings/payment-gateways')->with([
                    'message' => $e->getMessage(),
                    'message_important' => true
                ]);
            }
        }


        if ($pg->settings == 'gopay') {
            $payStackSetting = 'GOPAY_CLIENT_ID=' . $request->pg_value . '
GOPAY_SECRET=' . $request->pg_extra_value . '
GOPAY_ID=' . $request->pg_password . '
GOPAY_MODE=' . $request->pg_mode . '
';
            // @ignoreCodingStandard
            $env        = file_get_contents(base_path('.env'));
            $rows       = explode("\n", $env);
            $unwanted   = "GOPAY_CLIENT_ID|GOPAY_SECRET|GOPAY_ID|GOPAY_MODE";
            $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

            $cleanString = implode("\n", $cleanArray);
            $env         = $cleanString . $payStackSetting;

            try {
                file_put_contents(base_path('.env'), $env);
            } catch (\Exception $e) {
                return redirect('settings/payment-gateways')->with([
                    'message' => $e->getMessage(),
                    'message_important' => true
                ]);
            }
        }

        if ($pg) {
            $pg->name        = $request->gateway_name;
            $pg->value       = $request->pg_value;
            $pg->password    = $request->pg_password;
            $pg->extra_value = $request->pg_extra_value;
            $pg->custom_one  = $request->pg_custom_one;
            $pg->status      = $request->status;
            $pg->save();
            return redirect('settings/payment-gateways')->with([
                'message' => language_data('Payment Gateway update successfully'),
            ]);
        } else {
            return redirect('settings/payment-gateways')->with([
                'message' => language_data('Payment Gateway not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // languageSettings Function Start Here
    //======================================================================
    public function languageSettings()
    {
        $self = 'language-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $language = Language::all();
        return view('admin.language', compact('language'));
    }

    //======================================================================
    // addLanguage Function Start Here
    //======================================================================
    public function addLanguage(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('settings/language-settings')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'language-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $v = \Validator::make($request->all(), [
            'language_name' => 'required', 'status' => 'required', 'flag' => 'required|image|mimes:jpeg,jpg,png,gif'
        ]);

        if ($v->fails()) {
            return redirect('settings/language-settings')->withErrors($v->errors());
        }

        $language = explode('_', $request->language_name);

        if (is_array($language)) {
            $language_name = $language['1'];
            $language_code = $language['0'];
        } else {
            return redirect('settings/language-settings')->with([
                'message' => language_data('Please try again'),
                'message_important' => true
            ]);
        }

        $status = Input::get('status');
        $flag   = Input::file('flag');

        $exist = Language::where('language', $language_name)->first();
        if ($exist) {
            return redirect('settings/language-settings')->with([
                'message' => language_data('Language Already Exist'),
                'message_important' => true
            ]);
        }


        if ($flag != '') {
            $destinationPath = public_path() . '/assets/country_flag/';
            $flag_name       = $flag->getClientOriginalName();
            Input::file('flag')->move($destinationPath, $flag_name);
        } else {
            $flag_name = '';
        }

        $language = Language::firstOrCreate(['language' => $language_name, 'language_code' => $language_code, 'status' => $status, 'icon' => $flag_name]);

        $lan_id = $language->id;


        if ($language->wasRecentlyCreated) {

            $data = [
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Admin',
                    'lan_value' => 'Admin'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Login',
                    'lan_value' => 'Login'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Forget Password',
                    'lan_value' => 'Forget Password'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sign to your account',
                    'lan_value' => 'Sign to your account'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'User Name',
                    'lan_value' => 'User Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Password',
                    'lan_value' => 'Password'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Remember Me',
                    'lan_value' => 'Remember Me'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Reset your password',
                    'lan_value' => 'Reset your password'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Email',
                    'lan_value' => 'Email'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add New Client',
                    'lan_value' => 'Add New Client'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'First Name',
                    'lan_value' => 'First Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Last Name',
                    'lan_value' => 'Last Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Company',
                    'lan_value' => 'Company'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Website',
                    'lan_value' => 'Website'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'If you leave this, then you can not reset password or can not maintain email related function',
                    'lan_value' => 'If you leave this, then you can not reset password or can not maintain email related function'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Confirm Password',
                    'lan_value' => 'Confirm Password'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Phone',
                    'lan_value' => 'Phone'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Address',
                    'lan_value' => 'Address'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'More Address',
                    'lan_value' => 'More Address'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'State',
                    'lan_value' => 'State'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'City',
                    'lan_value' => 'City'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Postcode',
                    'lan_value' => 'Postcode'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Country',
                    'lan_value' => 'Country'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Api Access',
                    'lan_value' => 'Api Access'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Yes',
                    'lan_value' => 'Yes'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'No',
                    'lan_value' => 'No'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client Group',
                    'lan_value' => 'Client Group'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'None',
                    'lan_value' => 'None'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Gateway',
                    'lan_value' => 'SMS Gateway'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Limit',
                    'lan_value' => 'SMS Limit'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Avatar',
                    'lan_value' => 'Avatar'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Browse',
                    'lan_value' => 'Browse'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Notify Client with email',
                    'lan_value' => 'Notify Client with email'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add',
                    'lan_value' => 'Add'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add New Invoice',
                    'lan_value' => 'Add New Invoice'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client',
                    'lan_value' => 'Client'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Type',
                    'lan_value' => 'Invoice Type'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'One Time',
                    'lan_value' => 'One Time'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring',
                    'lan_value' => 'Recurring'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Date',
                    'lan_value' => 'Invoice Date'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Due Date',
                    'lan_value' => 'Due Date'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Paid Date',
                    'lan_value' => 'Paid Date'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Repeat Every',
                    'lan_value' => 'Repeat Every'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Week',
                    'lan_value' => 'Week'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => '2 Weeks',
                    'lan_value' => '2 Weeks'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Month',
                    'lan_value' => 'Month'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => '2 Months',
                    'lan_value' => '2 Months'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => '3 Months',
                    'lan_value' => '3 Months'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => '6 Months',
                    'lan_value' => '6 Months'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Year',
                    'lan_value' => 'Year'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => '2 Years',
                    'lan_value' => '2 Years'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => '3 Years',
                    'lan_value' => '3 Years'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Item Name',
                    'lan_value' => 'Item Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Price',
                    'lan_value' => 'Price'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Qty',
                    'lan_value' => 'Qty'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Quantity',
                    'lan_value' => 'Quantity'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Tax',
                    'lan_value' => 'Tax'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Discount',
                    'lan_value' => 'Discount'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Per Item Total',
                    'lan_value' => 'Per Item Total'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Item',
                    'lan_value' => 'Add Item'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Item',
                    'lan_value' => 'Item'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Delete',
                    'lan_value' => 'Delete'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Total',
                    'lan_value' => 'Total'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Note',
                    'lan_value' => 'Invoice Note'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Create Invoice',
                    'lan_value' => 'Create Invoice'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Plan Feature',
                    'lan_value' => 'Add Plan Feature'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Show In Client',
                    'lan_value' => 'Show In Client'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Feature Name',
                    'lan_value' => 'Feature Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Feature Value',
                    'lan_value' => 'Feature Value'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Action',
                    'lan_value' => 'Action'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add More',
                    'lan_value' => 'Add More'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Save',
                    'lan_value' => 'Save'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add SMS Price Plan',
                    'lan_value' => 'Add SMS Price Plan'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Plan Name',
                    'lan_value' => 'Plan Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Mark Popular',
                    'lan_value' => 'Mark Popular'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Popular',
                    'lan_value' => 'Popular'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Show',
                    'lan_value' => 'Show'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Hide',
                    'lan_value' => 'Hide'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Plan',
                    'lan_value' => 'Add Plan'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Sender ID',
                    'lan_value' => 'Add Sender ID'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'All',
                    'lan_value' => 'All'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Status',
                    'lan_value' => 'Status'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Block',
                    'lan_value' => 'Block'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unblock',
                    'lan_value' => 'Unblock'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sender ID',
                    'lan_value' => 'Sender ID'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add SMS Gateway',
                    'lan_value' => 'Add SMS Gateway'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Gateway Name',
                    'lan_value' => 'Gateway Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Gateway API Link',
                    'lan_value' => 'Gateway API Link'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Api link execute like',
                    'lan_value' => 'Api link execute like'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Active',
                    'lan_value' => 'Active'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Inactive',
                    'lan_value' => 'Inactive'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Parameter',
                    'lan_value' => 'Parameter'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Value',
                    'lan_value' => 'Value'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add On URL',
                    'lan_value' => 'Add On URL'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Username_Key',
                    'lan_value' => 'Username/Key'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Set Blank',
                    'lan_value' => 'Set Blank'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add on parameter',
                    'lan_value' => 'Add on parameter'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Source',
                    'lan_value' => 'Source'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Destination',
                    'lan_value' => 'Destination'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Message',
                    'lan_value' => 'Message'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unicode',
                    'lan_value' => 'Unicode'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Type_Route',
                    'lan_value' => 'Type/Route'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Language',
                    'lan_value' => 'Language'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Custom Value 1',
                    'lan_value' => 'Custom Value 1'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Custom Value 2',
                    'lan_value' => 'Custom Value 2'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Custom Value 3',
                    'lan_value' => 'Custom Value 3'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator Roles',
                    'lan_value' => 'Administrator Roles'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Administrator Role',
                    'lan_value' => 'Add Administrator Role'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Role Name',
                    'lan_value' => 'Role Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SL',
                    'lan_value' => 'SL'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Set Roles',
                    'lan_value' => 'Set Roles'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrators',
                    'lan_value' => 'Administrators'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add New Administrator',
                    'lan_value' => 'Add New Administrator'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Role',
                    'lan_value' => 'Role'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Notify Administrator with email',
                    'lan_value' => 'Notify Administrator with email'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Name',
                    'lan_value' => 'Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'All Clients',
                    'lan_value' => 'All Clients'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Clients',
                    'lan_value' => 'Clients'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Created',
                    'lan_value' => 'Created'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Created By',
                    'lan_value' => 'Created By'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Manage',
                    'lan_value' => 'Manage'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Closed',
                    'lan_value' => 'Closed'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'All Invoices',
                    'lan_value' => 'All Invoices'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client Name',
                    'lan_value' => 'Client Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Amount',
                    'lan_value' => 'Amount'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Type',
                    'lan_value' => 'Type'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unpaid',
                    'lan_value' => 'Unpaid'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Paid',
                    'lan_value' => 'Paid'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Cancelled',
                    'lan_value' => 'Cancelled'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Partially Paid',
                    'lan_value' => 'Partially Paid'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Onetime',
                    'lan_value' => 'Onetime'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring',
                    'lan_value' => 'Recurring'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Stop Recurring',
                    'lan_value' => 'Stop Recurring'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'View',
                    'lan_value' => 'View'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Change Password',
                    'lan_value' => 'Change Password'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Current Password',
                    'lan_value' => 'Current Password'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'New Password',
                    'lan_value' => 'New Password'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update',
                    'lan_value' => 'Update'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Edit',
                    'lan_value' => 'Edit'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Clients Groups',
                    'lan_value' => 'Clients Groups'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add New Group',
                    'lan_value' => 'Add New Group'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Group Name',
                    'lan_value' => 'Group Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Export Clients',
                    'lan_value' => 'Export Clients'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'View Profile',
                    'lan_value' => 'View Profile'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Location',
                    'lan_value' => 'Location'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Balance',
                    'lan_value' => 'SMS Balance'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Send SMS',
                    'lan_value' => 'Send SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update Limit',
                    'lan_value' => 'Update Limit'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Change Image',
                    'lan_value' => 'Change Image'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Edit Profile',
                    'lan_value' => 'Edit Profile'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Support Tickets',
                    'lan_value' => 'Support Tickets'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Change',
                    'lan_value' => 'Change'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Basic Info',
                    'lan_value' => 'Basic Info'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoices',
                    'lan_value' => 'Invoices'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Transaction',
                    'lan_value' => 'SMS Transaction'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Leave blank if you do not change',
                    'lan_value' => 'Leave blank if you do not change'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Subject',
                    'lan_value' => 'Subject'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Date',
                    'lan_value' => 'Date'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Pending',
                    'lan_value' => 'Pending'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Answered',
                    'lan_value' => 'Answered'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Customer Reply',
                    'lan_value' => 'Customer Reply'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'characters remaining',
                    'lan_value' => 'characters remaining'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Close',
                    'lan_value' => 'Close'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Send',
                    'lan_value' => 'Send'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update with previous balance. Enter (-) amount for decrease limit',
                    'lan_value' => 'Update with previous balance. Enter (-) amount for decrease limit'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update Image',
                    'lan_value' => 'Update Image'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Coverage',
                    'lan_value' => 'Coverage'
                ] ,
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Account',
                    'lan_value' => 'Account'
                ] , [
                    'lan_id' => $lan_id,
                    'lan_data' => 'ISO Code',
                    'lan_value' => 'ISO Code'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Country Code',
                    'lan_value' => 'Country Code'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Tariff',
                    'lan_value' => 'Tariff'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Live',
                    'lan_value' => 'Live'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Offline',
                    'lan_value' => 'Offline'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Create New Ticket',
                    'lan_value' => 'Create New Ticket'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Ticket For Client',
                    'lan_value' => 'Ticket For Client'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Department',
                    'lan_value' => 'Department'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Create Ticket',
                    'lan_value' => 'Create Ticket'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Create SMS Template',
                    'lan_value' => 'Create SMS Template'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Templates',
                    'lan_value' => 'SMS Templates'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Select Template',
                    'lan_value' => 'Select Template'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Template Name',
                    'lan_value' => 'Template Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'From',
                    'lan_value' => 'From'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Insert Merge Filed',
                    'lan_value' => 'Insert Merge Filed'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Select Merge Field',
                    'lan_value' => 'Select Merge Field'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Phone Number',
                    'lan_value' => 'Phone Number'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add New',
                    'lan_value' => 'Add New'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Tickets',
                    'lan_value' => 'Tickets'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoices History',
                    'lan_value' => 'Invoices History'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Tickets History',
                    'lan_value' => 'Tickets History'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Success History',
                    'lan_value' => 'SMS Success History'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS History By Date',
                    'lan_value' => 'SMS History By Date'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recent 5 Invoices',
                    'lan_value' => 'Recent 5 Invoices'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recent 5 Support Tickets',
                    'lan_value' => 'Recent 5 Support Tickets'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Edit Invoice',
                    'lan_value' => 'Edit Invoice'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'View Invoice',
                    'lan_value' => 'View Invoice'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Send Invoice',
                    'lan_value' => 'Send Invoice'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Access Role',
                    'lan_value' => 'Access Role'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Super Admin',
                    'lan_value' => 'Super Admin'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Personal Details',
                    'lan_value' => 'Personal Details'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unique For every User',
                    'lan_value' => 'Unique For every User'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Email Templates',
                    'lan_value' => 'Email Templates'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Manage Email Template',
                    'lan_value' => 'Manage Email Template'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Export and Import Clients',
                    'lan_value' => 'Export and Import Clients'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Export Clients',
                    'lan_value' => 'Export Clients'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Export Clients as CSV',
                    'lan_value' => 'Export Clients as CSV'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sample File',
                    'lan_value' => 'Sample File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Download Sample File',
                    'lan_value' => 'Download Sample File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Import Clients',
                    'lan_value' => 'Import Clients'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'It will take few minutes. Please do not reload the page',
                    'lan_value' => 'It will take few minutes. Please do not reload the page'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Import',
                    'lan_value' => 'Import'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Reset My Password',
                    'lan_value' => 'Reset My Password'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Back To Sign in',
                    'lan_value' => 'Back To Sign in'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice No',
                    'lan_value' => 'Invoice No'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice',
                    'lan_value' => 'Invoice'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice To',
                    'lan_value' => 'Invoice To'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Printable Version',
                    'lan_value' => 'Printable Version'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Status',
                    'lan_value' => 'Invoice Status'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Subtotal',
                    'lan_value' => 'Subtotal'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Grand Total',
                    'lan_value' => 'Grand Total'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Amount Due',
                    'lan_value' => 'Amount Due'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Language',
                    'lan_value' => 'Add Language'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Flag',
                    'lan_value' => 'Flag'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'All Languages',
                    'lan_value' => 'All Languages'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Translate',
                    'lan_value' => 'Translate'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Language Manage',
                    'lan_value' => 'Language Manage'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Language Name',
                    'lan_value' => 'Language Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'English To',
                    'lan_value' => 'English To'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'English',
                    'lan_value' => 'English'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Localization',
                    'lan_value' => 'Localization'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Date Format',
                    'lan_value' => 'Date Format'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Timezone',
                    'lan_value' => 'Timezone'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Default Language',
                    'lan_value' => 'Default Language'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Currency Code',
                    'lan_value' => 'Currency Code'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Currency Symbol',
                    'lan_value' => 'Currency Symbol'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Default Country',
                    'lan_value' => 'Default Country'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Manage Administrator',
                    'lan_value' => 'Manage Administrator'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Manage Coverage',
                    'lan_value' => 'Manage Coverage'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Cost for per SMS',
                    'lan_value' => 'Cost for per SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Gateway Manage',
                    'lan_value' => 'SMS Gateway Manage'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Manage Plan Feature',
                    'lan_value' => 'Manage Plan Feature'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Plan Features',
                    'lan_value' => 'SMS Plan Features'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update Feature',
                    'lan_value' => 'Update Feature'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Manage SMS Price Plan',
                    'lan_value' => 'Manage SMS Price Plan'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Price Plan',
                    'lan_value' => 'SMS Price Plan'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update Plan',
                    'lan_value' => 'Update Plan'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Msisdn',
                    'lan_value' => 'Msisdn'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Account Sid',
                    'lan_value' => 'Account Sid'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Api',
                    'lan_value' => 'SMS Api'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Api User name',
                    'lan_value' => 'SMS Api User name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Auth Token',
                    'lan_value' => 'Auth Token'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Auth ID',
                    'lan_value' => 'Auth ID'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Api key',
                    'lan_value' => 'SMS Api key'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Api Password',
                    'lan_value' => 'SMS Api Password'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Extra Value',
                    'lan_value' => 'Extra Value'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Schedule SMS',
                    'lan_value' => 'Schedule SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Manage SMS Template',
                    'lan_value' => 'Manage SMS Template'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Edit Administrator Role',
                    'lan_value' => 'Edit Administrator Role'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Manage Payment Gateway',
                    'lan_value' => 'Manage Payment Gateway'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Publishable Key',
                    'lan_value' => 'Publishable Key'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Bank Details',
                    'lan_value' => 'Bank Details'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Api Login ID',
                    'lan_value' => 'Api Login ID'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Secret_Key_Signature',
                    'lan_value' => 'Secret Key/Signature'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Transaction Key',
                    'lan_value' => 'Transaction Key'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Payment Gateways',
                    'lan_value' => 'Payment Gateways'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Send Bulk SMS',
                    'lan_value' => 'Send Bulk SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Bulk SMS',
                    'lan_value' => 'Bulk SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'After click on Send button, do not refresh your browser',
                    'lan_value' => 'After click on Send button, do not refresh your browser'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Schedule Time',
                    'lan_value' => 'Schedule Time'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Import Numbers',
                    'lan_value' => 'Import Numbers'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Set Rules',
                    'lan_value' => 'Set Rules'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Check All',
                    'lan_value' => 'Check All'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Send SMS From File',
                    'lan_value' => 'Send SMS From File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Schedule SMS From File',
                    'lan_value' => 'Schedule SMS From File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS History',
                    'lan_value' => 'SMS History'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Price Plan',
                    'lan_value' => 'Add Price Plan'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sender ID Management',
                    'lan_value' => 'Sender ID Management'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Support Department',
                    'lan_value' => 'Support Department'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Department Name',
                    'lan_value' => 'Department Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Department Email',
                    'lan_value' => 'Department Email'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'System Settings',
                    'lan_value' => 'System Settings'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Language Settings',
                    'lan_value' => 'Language Settings'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS API Info',
                    'lan_value' => 'SMS API Info'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS API URL',
                    'lan_value' => 'SMS API URL'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Generate New',
                    'lan_value' => 'Generate New'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS API Details',
                    'lan_value' => 'SMS API Details'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Gateway',
                    'lan_value' => 'Add Gateway'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Two Way',
                    'lan_value' => 'Two Way'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Send By',
                    'lan_value' => 'Send By'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sender',
                    'lan_value' => 'Sender'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Receiver',
                    'lan_value' => 'Receiver'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Inbox',
                    'lan_value' => 'Inbox'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Feature',
                    'lan_value' => 'Add Feature'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'View Features',
                    'lan_value' => 'View Features'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Create Template',
                    'lan_value' => 'Create Template'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Application Name',
                    'lan_value' => 'Application Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Application Title',
                    'lan_value' => 'Application Title'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'System Email',
                    'lan_value' => 'System Email'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Remember: All Email Going to the Receiver from this Email',
                    'lan_value' => 'Remember: All Email Going to the Receiver from this Email'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Footer Text',
                    'lan_value' => 'Footer Text'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Application Logo',
                    'lan_value' => 'Application Logo'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Application Favicon',
                    'lan_value' => 'Application Favicon'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'API Permission',
                    'lan_value' => 'API Permission'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Allow Client Registration',
                    'lan_value' => 'Allow Client Registration'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client Registration Verification',
                    'lan_value' => 'Client Registration Verification'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Email Gateway',
                    'lan_value' => 'Email Gateway'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Server Default',
                    'lan_value' => 'Server Default'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMTP',
                    'lan_value' => 'SMTP'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Host Name',
                    'lan_value' => 'Host Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Port',
                    'lan_value' => 'Port'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Secure',
                    'lan_value' => 'Secure'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'TLS',
                    'lan_value' => 'TLS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SSL',
                    'lan_value' => 'SSL'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Mark As',
                    'lan_value' => 'Mark As'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Preview',
                    'lan_value' => 'Preview'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'PDF',
                    'lan_value' => 'PDF'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Print',
                    'lan_value' => 'Print'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Ticket Management',
                    'lan_value' => 'Ticket Management'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Ticket Details',
                    'lan_value' => 'Ticket Details'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Ticket Discussion',
                    'lan_value' => 'Ticket Discussion'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Ticket Files',
                    'lan_value' => 'Ticket Files'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Created Date',
                    'lan_value' => 'Created Date'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Created By',
                    'lan_value' => 'Created By'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Department',
                    'lan_value' => 'Department'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Closed By',
                    'lan_value' => 'Closed By'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'File Title',
                    'lan_value' => 'File Title'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Select File',
                    'lan_value' => 'Select File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Files',
                    'lan_value' => 'Files'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Size',
                    'lan_value' => 'Size'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Upload By',
                    'lan_value' => 'Upload By'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Upload',
                    'lan_value' => 'Upload'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Dashboard',
                    'lan_value' => 'Dashboard'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Settings',
                    'lan_value' => 'Settings'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Logout',
                    'lan_value' => 'Logout'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recent 5 Unpaid Invoices',
                    'lan_value' => 'Recent 5 Unpaid Invoices'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'See All Invoices',
                    'lan_value' => 'See All Invoices'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recent 5 Pending Tickets',
                    'lan_value' => 'Recent 5 Pending Tickets'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'See All Tickets',
                    'lan_value' => 'See All Tickets'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update Profile',
                    'lan_value' => 'Update Profile'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'You do not have permission to view this page',
                    'lan_value' => 'You do not have permission to view this page'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'This Option is Disable In Demo Mode',
                    'lan_value' => 'This Option is Disable In Demo Mode'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'User name already exist',
                    'lan_value' => 'User name already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Email already exist',
                    'lan_value' => 'Email already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Both password does not match',
                    'lan_value' => 'Both password does not match'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator added successfully',
                    'lan_value' => 'Administrator added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator not found',
                    'lan_value' => 'Administrator not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator updated successfully',
                    'lan_value' => 'Administrator updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator have support tickets. First delete support ticket',
                    'lan_value' => 'Administrator have support tickets. First delete support ticket'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator have SMS Log. First delete all sms',
                    'lan_value' => 'Administrator have SMS Log. First delete all sms'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator created invoice. First delete all invoice',
                    'lan_value' => 'Administrator created invoice. First delete all invoice'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator delete successfully',
                    'lan_value' => 'Administrator delete successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator Role added successfully',
                    'lan_value' => 'Administrator Role added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator Role already exist',
                    'lan_value' => 'Administrator Role already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator Role updated successfully',
                    'lan_value' => 'Administrator Role updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator Role info not found',
                    'lan_value' => 'Administrator Role info not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Permission not assigned',
                    'lan_value' => 'Permission not assigned'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Permission Updated',
                    'lan_value' => 'Permission Updated'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'An Administrator contain this role',
                    'lan_value' => 'An Administrator contain this role'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Administrator role deleted successfully',
                    'lan_value' => 'Administrator role deleted successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invalid User name or Password',
                    'lan_value' => 'Invalid User name or Password'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Please Check your Email Settings',
                    'lan_value' => 'Please Check your Email Settings'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Password Reset Successfully. Please check your email',
                    'lan_value' => 'Password Reset Successfully. Please check your email'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Your Password Already Reset. Please Check your email',
                    'lan_value' => 'Your Password Already Reset. Please Check your email'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sorry There is no registered user with this email address',
                    'lan_value' => 'Sorry There is no registered user with this email address'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'A New Password Generated. Please Check your email.',
                    'lan_value' => 'A New Password Generated. Please Check your email.'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sorry Password reset Token expired or not exist, Please try again.',
                    'lan_value' => 'Sorry Password reset Token expired or not exist, Please try again.'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client Added Successfully But Email Not Send',
                    'lan_value' => 'Client Added Successfully But Email Not Send'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client Added Successfully',
                    'lan_value' => 'Client Added Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client info not found',
                    'lan_value' => 'Client info not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Limit updated successfully',
                    'lan_value' => 'Limit updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Image updated successfully',
                    'lan_value' => 'Image updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Please try again',
                    'lan_value' => 'Please try again'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client updated successfully',
                    'lan_value' => 'Client updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS gateway not active',
                    'lan_value' => 'SMS gateway not active'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Please check sms history',
                    'lan_value' => 'Please check sms history'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Insert Valid Excel or CSV file',
                    'lan_value' => 'Insert Valid Excel or CSV file'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client imported successfully',
                    'lan_value' => 'Client imported successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client Group added successfully',
                    'lan_value' => 'Client Group added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client Group updated successfully',
                    'lan_value' => 'Client Group updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client Group not found',
                    'lan_value' => 'Client Group not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'This Group exist in a client',
                    'lan_value' => 'This Group exist in a client'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client group deleted successfully',
                    'lan_value' => 'Client group deleted successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice not found',
                    'lan_value' => 'Invoice not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Logout Successfully',
                    'lan_value' => 'Logout Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Profile Updated Successfully',
                    'lan_value' => 'Profile Updated Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Upload an Image',
                    'lan_value' => 'Upload an Image'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Password Change Successfully',
                    'lan_value' => 'Password Change Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Current Password Does Not Match',
                    'lan_value' => 'Current Password Does Not Match'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Select a Customer',
                    'lan_value' => 'Select a Customer'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Created date is required',
                    'lan_value' => 'Invoice Created date is required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Paid date is required',
                    'lan_value' => 'Invoice Paid date is required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Date Parsing Error',
                    'lan_value' => 'Date Parsing Error'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Due date is required',
                    'lan_value' => 'Invoice Due date is required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'At least one item is required',
                    'lan_value' => 'At least one item is required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Updated Successfully',
                    'lan_value' => 'Invoice Updated Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Marked as Paid',
                    'lan_value' => 'Invoice Marked as Paid'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Marked as Unpaid',
                    'lan_value' => 'Invoice Marked as Unpaid'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Marked as Partially Paid',
                    'lan_value' => 'Invoice Marked as Partially Paid'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Marked as Cancelled',
                    'lan_value' => 'Invoice Marked as Cancelled'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Send Successfully',
                    'lan_value' => 'Invoice Send Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice deleted successfully',
                    'lan_value' => 'Invoice deleted successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Stop Recurring Invoice Successfully',
                    'lan_value' => 'Stop Recurring Invoice Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice Created Successfully',
                    'lan_value' => 'Invoice Created Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Reseller Panel',
                    'lan_value' => 'Reseller Panel'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Captcha In Admin Login',
                    'lan_value' => 'Captcha In Admin Login'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Captcha In Client Login',
                    'lan_value' => 'Captcha In Client Login'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Captcha In Client Registration',
                    'lan_value' => 'Captcha In Client Registration'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'reCAPTCHA Site Key',
                    'lan_value' => 'reCAPTCHA Site Key'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'reCAPTCHA Secret Key',
                    'lan_value' => 'reCAPTCHA Secret Key'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Registration Successful',
                    'lan_value' => 'Registration Successful'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Payment gateway required',
                    'lan_value' => 'Payment gateway required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Cancelled the Payment',
                    'lan_value' => 'Cancelled the Payment'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice paid successfully',
                    'lan_value' => 'Invoice paid successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Purchase successfully.Wait for administrator response',
                    'lan_value' => 'Purchase successfully.Wait for administrator response'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Not Found',
                    'lan_value' => 'SMS Not Found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS info deleted successfully',
                    'lan_value' => 'SMS info deleted successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Setting Update Successfully',
                    'lan_value' => 'Setting Update Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Email Template Not Found',
                    'lan_value' => 'Email Template Not Found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Email Template Update Successfully',
                    'lan_value' => 'Email Template Update Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Payment Gateway not found',
                    'lan_value' => 'Payment Gateway not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Payment Gateway update successfully',
                    'lan_value' => 'Payment Gateway update successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Language Already Exist',
                    'lan_value' => 'Language Already Exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Language Added Successfully',
                    'lan_value' => 'Language Added Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Language Translate Successfully',
                    'lan_value' => 'Language Translate Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Language not found',
                    'lan_value' => 'Language not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Language updated Successfully',
                    'lan_value' => 'Language updated Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Can not delete active language',
                    'lan_value' => 'Can not delete active language'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Language deleted successfully',
                    'lan_value' => 'Language deleted successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Information not found',
                    'lan_value' => 'Information not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Coverage updated successfully',
                    'lan_value' => 'Coverage updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sender Id added successfully',
                    'lan_value' => 'Sender Id added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sender Id not found',
                    'lan_value' => 'Sender Id not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sender id updated successfully',
                    'lan_value' => 'Sender id updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sender id deleted successfully',
                    'lan_value' => 'Sender id deleted successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Plan already exist',
                    'lan_value' => 'Plan already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Plan added successfully',
                    'lan_value' => 'Plan added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Plan not found',
                    'lan_value' => 'Plan not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Plan updated successfully',
                    'lan_value' => 'Plan updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Plan features added successfully',
                    'lan_value' => 'Plan features added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Plan feature not found',
                    'lan_value' => 'Plan feature not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Feature already exist',
                    'lan_value' => 'Feature already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Feature updated successfully',
                    'lan_value' => 'Feature updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Plan feature deleted successfully',
                    'lan_value' => 'Plan feature deleted successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Price Plan deleted successfully',
                    'lan_value' => 'Price Plan deleted successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Gateway already exist',
                    'lan_value' => 'Gateway already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Custom gateway added successfully',
                    'lan_value' => 'Custom gateway added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Parameter or Value is empty',
                    'lan_value' => 'Parameter or Value is empty'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Gateway information not found',
                    'lan_value' => 'Gateway information not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Gateway name required',
                    'lan_value' => 'Gateway name required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Custom gateway updated successfully',
                    'lan_value' => 'Custom gateway updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client are registered with this gateway',
                    'lan_value' => 'Client are registered with this gateway'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Gateway deleted successfully',
                    'lan_value' => 'Gateway deleted successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Delete option disable for this gateway',
                    'lan_value' => 'Delete option disable for this gateway'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS added in queue and will deliver one by one',
                    'lan_value' => 'SMS added in queue and will deliver one by one'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Insert Valid Excel or CSV file',
                    'lan_value' => 'Insert Valid Excel or CSV file'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS are scheduled. Deliver in correct time',
                    'lan_value' => 'SMS are scheduled. Deliver in correct time'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Template already exist',
                    'lan_value' => 'Template already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sms template created successfully',
                    'lan_value' => 'Sms template created successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sms template not found',
                    'lan_value' => 'Sms template not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sms template updated successfully',
                    'lan_value' => 'Sms template updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sms template delete successfully',
                    'lan_value' => 'Sms template delete successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'API information updated successfully',
                    'lan_value' => 'API information updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invalid Access',
                    'lan_value' => 'Invalid Access'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invalid Captcha',
                    'lan_value' => 'Invalid Captcha'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invalid Request',
                    'lan_value' => 'Invalid Request'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Verification code send successfully. Please check your email',
                    'lan_value' => 'Verification code send successfully. Please check your email'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Something wrong, Please contact with your provider',
                    'lan_value' => 'Something wrong, Please contact with your provider'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Verification code not found',
                    'lan_value' => 'Verification code not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Department Already exist',
                    'lan_value' => 'Department Already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Department Added Successfully',
                    'lan_value' => 'Department Added Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Department Updated Successfully',
                    'lan_value' => 'Department Updated Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Support Ticket Created Successfully But Email Not Send',
                    'lan_value' => 'Support Ticket Created Successfully But Email Not Send'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Support Ticket Created Successfully',
                    'lan_value' => 'Support Ticket Created Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Basic Info Update Successfully',
                    'lan_value' => 'Basic Info Update Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Ticket Reply Successfully But Email Not Send',
                    'lan_value' => 'Ticket Reply Successfully But Email Not Send'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Ticket Reply Successfully',
                    'lan_value' => 'Ticket Reply Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'File Uploaded Successfully',
                    'lan_value' => 'File Uploaded Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Please Upload a File',
                    'lan_value' => 'Please Upload a File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'File Deleted Successfully',
                    'lan_value' => 'File Deleted Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Ticket File not found',
                    'lan_value' => 'Ticket File not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Ticket Deleted Successfully',
                    'lan_value' => 'Ticket Deleted Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Ticket info not found',
                    'lan_value' => 'Ticket info not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Department Deleted Successfully',
                    'lan_value' => 'Department Deleted Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'There Have no Department For Delete',
                    'lan_value' => 'There Have no Department For Delete'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'You do not have enough sms balance',
                    'lan_value' => 'You do not have enough sms balance'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS gateway not active.Contact with Provider',
                    'lan_value' => 'SMS gateway not active.Contact with Provider'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sender ID required',
                    'lan_value' => 'Sender ID required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Request send successfully',
                    'lan_value' => 'Request send successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'This Sender ID have Blocked By Administrator',
                    'lan_value' => 'This Sender ID have Blocked By Administrator'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Phone Number Coverage are not active',
                    'lan_value' => 'Phone Number Coverage are not active'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS plan not found',
                    'lan_value' => 'SMS plan not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Schedule feature not supported',
                    'lan_value' => 'Schedule feature not supported'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Need Account',
                    'lan_value' => 'Need Account'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sign up',
                    'lan_value' => 'Sign up'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'here',
                    'lan_value' => 'here'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'User Registration',
                    'lan_value' => 'User Registration'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Already have an Account',
                    'lan_value' => 'Already have an Account'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Request New Sender ID',
                    'lan_value' => 'Request New Sender ID'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Purchase Now',
                    'lan_value' => 'Purchase Now'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Purchase VPS Plan',
                    'lan_value' => 'Purchase VPS Plan'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Select Payment Method',
                    'lan_value' => 'Select Payment Method'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Pay with Credit Card',
                    'lan_value' => 'Pay with Credit Card'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'User Registration Verification',
                    'lan_value' => 'User Registration Verification'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Verify Your Account',
                    'lan_value' => 'Verify Your Account'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Send Verification Email',
                    'lan_value' => 'Send Verification Email'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Pay',
                    'lan_value' => 'Pay'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Pay Invoice',
                    'lan_value' => 'Pay Invoice'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Reply Ticket',
                    'lan_value' => 'Reply Ticket'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Whoops! Page Not Found, Go To',
                    'lan_value' => 'Whoops! Page Not Found, Go To'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Home Page',
                    'lan_value' => 'Home Page'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Error',
                    'lan_value' => 'Error'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client contain in',
                    'lan_value' => 'Client contain in'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client sms limit not empty',
                    'lan_value' => 'Client sms limit not empty'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'This client have some customer',
                    'lan_value' => 'This client have some customer'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client delete successfully',
                    'lan_value' => 'Client delete successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client Group is empty',
                    'lan_value' => 'Client Group is empty'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Country flag required',
                    'lan_value' => 'Country flag required'
                ],

                /*Version 1.1*/
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Single',
                    'lan_value' => 'Single'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS',
                    'lan_value' => 'SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client ID',
                    'lan_value' => 'Client ID'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Client Secret',
                    'lan_value' => 'Client Secret'
                ],

                /*Version 1.2*/
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Import Phone Number',
                    'lan_value' => 'Import Phone Number'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sender ID Verification',
                    'lan_value' => 'Sender ID Verification'
                ],

                /*Version 1.3*/
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Price Bundles',
                    'lan_value' => 'Price Bundles'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unit From',
                    'lan_value' => 'Unit From'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unit To',
                    'lan_value' => 'Unit To'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Transaction Fee',
                    'lan_value' => 'Transaction Fee'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Price Bundles Update Successfully',
                    'lan_value' => 'Price Bundles Update Successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Buy Unit',
                    'lan_value' => 'Buy Unit'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recharge your account Online',
                    'lan_value' => 'Recharge your account Online'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Number of Units',
                    'lan_value' => 'Number of Units'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unit Price',
                    'lan_value' => 'Unit Price'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Amount to Pay',
                    'lan_value' => 'Amount to Pay'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Price Per Unit',
                    'lan_value' => 'Price Per Unit'
                ],


                /*Version 2.0*/

                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Contacts',
                    'lan_value' => 'Contacts'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Phone Book',
                    'lan_value' => 'Phone Book'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Import Contacts',
                    'lan_value' => 'Import Contacts'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Blacklist Contacts',
                    'lan_value' => 'Blacklist Contacts'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recharge',
                    'lan_value' => 'Recharge'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Reports',
                    'lan_value' => 'Reports'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add New List',
                    'lan_value' => 'Add New List'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'List name',
                    'lan_value' => 'List name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'View Contacts',
                    'lan_value' => 'View Contacts'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Contact',
                    'lan_value' => 'Add Contact'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add New Contact',
                    'lan_value' => 'Add New Contact'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Edit List',
                    'lan_value' => 'Edit List'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Import Contact By File',
                    'lan_value' => 'Import Contact By File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'First Row As Header',
                    'lan_value' => 'First Row As Header'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Column',
                    'lan_value' => 'Column'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Import List into',
                    'lan_value' => 'Import List into'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Import By Numbers',
                    'lan_value' => 'Import By Numbers'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Paste Numbers',
                    'lan_value' => 'Paste Numbers'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Insert number with comma',
                    'lan_value' => 'Insert number with comma'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Numbers',
                    'lan_value' => 'Numbers'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Select Contact Type',
                    'lan_value' => 'Select Contact Type'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Contact List',
                    'lan_value' => 'Contact List'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recipients',
                    'lan_value' => 'Recipients'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Send Later',
                    'lan_value' => 'Send Later'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Total Number Of Recipients',
                    'lan_value' => 'Total Number Of Recipients'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Direction',
                    'lan_value' => 'Direction'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'To',
                    'lan_value' => 'To'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Segments',
                    'lan_value' => 'Segments'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Incoming',
                    'lan_value' => 'Incoming'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Outgoing',
                    'lan_value' => 'Outgoing'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Message Details',
                    'lan_value' => 'Message Details'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sending User',
                    'lan_value' => 'Sending User'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Created At',
                    'lan_value' => 'Created At'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Background Jobs',
                    'lan_value' => 'Background Jobs'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Please specify the PHP executable path on your system',
                    'lan_value' => 'Please specify the PHP executable path on your system'
                ],

                /*Version 2.2*/

                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Edit Contact',
                    'lan_value' => 'Edit Contact'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Bulk Delete',
                    'lan_value' => 'Bulk Delete'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'File Uploading.. Please wait',
                    'lan_value' => 'File Uploading.. Please wait'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Contact importing.. Please wait',
                    'lan_value' => 'Contact importing.. Please wait'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Send Quick SMS',
                    'lan_value' => 'Send Quick SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Remove Duplicate',
                    'lan_value' => 'Remove Duplicate'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Message Type',
                    'lan_value' => 'Message Type'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Plain',
                    'lan_value' => 'Plain'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unicode',
                    'lan_value' => 'Unicode'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Message adding in Queue.. Please wait',
                    'lan_value' => 'Message adding in Queue.. Please wait'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Purchase Code',
                    'lan_value' => 'Purchase Code'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Search Condition',
                    'lan_value' => 'Search Condition'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Receive SMS',
                    'lan_value' => 'Receive SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'API SMS',
                    'lan_value' => 'API SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Search',
                    'lan_value' => 'Search'
                ],

                //======================================================================
                // Version 2.3
                //======================================================================

                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Spam Words',
                    'lan_value' => 'Spam Words'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Block Message',
                    'lan_value' => 'Block Message'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Block',
                    'lan_value' => 'Block'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Release',
                    'lan_value' => 'Release'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS release successfully',
                    'lan_value' => 'SMS release successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add New Word',
                    'lan_value' => 'Add New Word'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Words',
                    'lan_value' => 'Words'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Word already exist',
                    'lan_value' => 'Word already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Word added on Spam word list',
                    'lan_value' => 'Word added on Spam word list'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Word deleted from list',
                    'lan_value' => 'Word deleted from list'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Word not found on list',
                    'lan_value' => 'Word not found on list'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Fraud Detection',
                    'lan_value' => 'SMS Fraud Detection'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Reply',
                    'lan_value' => 'Reply'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Successfully sent reply',
                    'lan_value' => 'Successfully sent reply'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Routing',
                    'lan_value' => 'Routing'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add Operator',
                    'lan_value' => 'Add Operator'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'View Operator',
                    'lan_value' => 'View Operator'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Operator Name',
                    'lan_value' => 'Operator Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Operator Code',
                    'lan_value' => 'Operator Code'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Area Code',
                    'lan_value' => 'Area Code'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Area Name',
                    'lan_value' => 'Area Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Sample Phone Number',
                    'lan_value' => 'Sample Phone Number'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Enter a real phone number like',
                    'lan_value' => 'Enter a real phone number like'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Exist on phone number',
                    'lan_value' => 'Exist on phone number'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Voice',
                    'lan_value' => 'Voice'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'MMS',
                    'lan_value' => 'MMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Are you sure',
                    'lan_value' => 'Are you sure'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Default Price',
                    'lan_value' => 'Default Price'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Set as Global',
                    'lan_value' => 'Set as Global'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Remain country code at the beginning of the number',
                    'lan_value' => 'Remain country code at the beginning of the number'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Currency Symbol Position',
                    'lan_value' => 'Currency Symbol Position'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Left',
                    'lan_value' => 'Left'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Right',
                    'lan_value' => 'Right'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Currency Format',
                    'lan_value' => 'Currency Format'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Currency Decimal Digits',
                    'lan_value' => 'Currency Decimal Digits'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Basic Information',
                    'lan_value' => 'Basic Information'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Credential Setup',
                    'lan_value' => 'Credential Setup'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Credential Base Status',
                    'lan_value' => 'Credential Base Status'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'You can only active one credential information',
                    'lan_value' => 'You can only active one credential information'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Current Media',
                    'lan_value' => 'Current Media'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring SMS',
                    'lan_value' => 'Recurring SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Period',
                    'lan_value' => 'Period'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Excel',
                    'lan_value' => 'Excel'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'CSV',
                    'lan_value' => 'CSV'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Work only for Recipients number',
                    'lan_value' => 'Work only for Recipients number'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring Period',
                    'lan_value' => 'Recurring Period'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Daily',
                    'lan_value' => 'Daily'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Weekly',
                    'lan_value' => 'Weekly'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Custom Date',
                    'lan_value' => 'Custom Date'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring Time',
                    'lan_value' => 'Recurring Time'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Schedule Time Type',
                    'lan_value' => 'Schedule Time Type'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Schedule Time Using Date',
                    'lan_value' => 'Schedule Time Using Date'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Schedule Time Using File',
                    'lan_value' => 'Schedule Time Using File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Schedule Time must contain this format',
                    'lan_value' => 'Schedule Time must contain this format'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'For Text/Plain SMS',
                    'lan_value' => 'For Text/Plain SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'For Unicode SMS',
                    'lan_value' => 'For Unicode SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'For Voice SMS',
                    'lan_value' => 'For Voice SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'For MMS SMS',
                    'lan_value' => 'For MMS SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'For Schedule SMS',
                    'lan_value' => 'For Schedule SMS'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Balance Check',
                    'lan_value' => 'Balance Check'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Reply Message',
                    'lan_value' => 'Reply Message'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Global',
                    'lan_value' => 'Global'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update Period',
                    'lan_value' => 'Update Period'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update Contact',
                    'lan_value' => 'Update Contact'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update SMS data',
                    'lan_value' => 'Update SMS data'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring Note',
                    'lan_value' => 'Recurring Note'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'The sms unit will be deducted when the recurring sms task starts. If you do not have enough sms unit then
                            its automatically stop the recurring process and sms not send to users',
                    'lan_value' => 'The sms unit will be deducted when the recurring sms task starts. If you do not have enough sms unit then
                            its automatically stop the recurring process and sms not send to users'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Send Recurring SMS File',
                    'lan_value' => 'Send Recurring SMS File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Upload .png or .jpeg or .jpg or .gif file',
                    'lan_value' => 'Upload .png or .jpeg or .jpg or .gif file'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Your are inactive or blocked by system. Please contact with administrator',
                    'lan_value' => 'Your are inactive or blocked by system. Please contact with administrator'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'At least select one sms gateway',
                    'lan_value' => 'At least select one sms gateway'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Gateway credential not found',
                    'lan_value' => 'SMS Gateway credential not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invalid message type',
                    'lan_value' => 'Invalid message type'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'List name already exist',
                    'lan_value' => 'List name already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'List added successfully',
                    'lan_value' => 'List added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Contact list not found',
                    'lan_value' => 'Contact list not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'List updated successfully',
                    'lan_value' => 'List updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invalid Phone book',
                    'lan_value' => 'Invalid Phone book'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Contact number already exist',
                    'lan_value' => 'Contact number already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Contact added successfully',
                    'lan_value' => 'Contact added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Contact updated successfully',
                    'lan_value' => 'Contact updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Contact info not found',
                    'lan_value' => 'Contact info not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Contact deleted successfully',
                    'lan_value' => 'Contact deleted successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invalid phone numbers',
                    'lan_value' => 'Invalid phone numbers'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Phone number imported successfully',
                    'lan_value' => 'Phone number imported successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Empty field',
                    'lan_value' => 'Empty field'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Amount required',
                    'lan_value' => 'Amount required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Item quantity required',
                    'lan_value' => 'Item quantity required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Insert valid tax amount',
                    'lan_value' => 'Insert valid tax amount'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Insert valid discount amount',
                    'lan_value' => 'Insert valid discount amount'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invalid transaction URL, cannot continue',
                    'lan_value' => 'Invalid transaction URL, cannot continue'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Data not found',
                    'lan_value' => 'Data not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invoice not paid. Please try again',
                    'lan_value' => 'Invoice not paid. Please try again'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unauthorized payment',
                    'lan_value' => 'Unauthorized payment'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Payment gateway not active',
                    'lan_value' => 'Payment gateway not active'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'MMS not supported in block message',
                    'lan_value' => 'MMS not supported in block message'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Insert your message',
                    'lan_value' => 'Insert your message'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'MMS not supported in two way communication',
                    'lan_value' => 'MMS not supported in two way communication'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Purchase code information updated',
                    'lan_value' => 'Purchase code information updated'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Select Client',
                    'lan_value' => 'Select Client'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Insert Sender id',
                    'lan_value' => 'Insert Sender id'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Select one credential status as Active',
                    'lan_value' => 'Select one credential status as Active'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Gateway updated successfully',
                    'lan_value' => 'Gateway updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Gateway not supported Voice feature',
                    'lan_value' => 'SMS Gateway not supported Voice feature'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Gateway not supported MMS feature',
                    'lan_value' => 'SMS Gateway not supported MMS feature'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file',
                    'lan_value' => 'Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'MMS file required',
                    'lan_value' => 'MMS file required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'MMS is disable in demo mode',
                    'lan_value' => 'MMS is disable in demo mode'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Message required',
                    'lan_value' => 'Message required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recipient empty',
                    'lan_value' => 'Recipient empty'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Schedule time required',
                    'lan_value' => 'Schedule time required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invalid Recipients',
                    'lan_value' => 'Invalid Recipients'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Invalid time format',
                    'lan_value' => 'Invalid time format'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Phone number contain in blacklist',
                    'lan_value' => 'Phone number contain in blacklist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Number added on blacklist',
                    'lan_value' => 'Number added on blacklist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Number deleted from blacklist',
                    'lan_value' => 'Number deleted from blacklist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Number not found on blacklist',
                    'lan_value' => 'Number not found on blacklist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Please check sms history for status',
                    'lan_value' => 'Please check sms history for status'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'You can not send more than 100 sms using quick sms option',
                    'lan_value' => 'You can not send more than 100 sms using quick sms option'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Operator already exist',
                    'lan_value' => 'Operator already exist'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Operator added successfully',
                    'lan_value' => 'Operator added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Something went wrong please try again',
                    'lan_value' => 'Something went wrong please try again'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Operator updated successfully',
                    'lan_value' => 'Operator updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Operator delete successfully',
                    'lan_value' => 'Operator delete successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Start Recurring',
                    'lan_value' => 'Start Recurring'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Running',
                    'lan_value' => 'Running'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recipients required',
                    'lan_value' => 'Recipients required'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring SMS info not found',
                    'lan_value' => 'Recurring SMS info not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Message recurred successfully. Delivered in correct time',
                    'lan_value' => 'Message recurred successfully. Delivered in correct time'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring SMS stop successfully',
                    'lan_value' => 'Recurring SMS stop successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring information not found',
                    'lan_value' => 'Recurring information not found'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring SMS running successfully',
                    'lan_value' => 'Recurring SMS running successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring contact added successfully',
                    'lan_value' => 'Recurring contact added successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring contact updated successfully',
                    'lan_value' => 'Recurring contact updated successfully'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Recurring SMS period changed',
                    'lan_value' => 'Recurring SMS period changed'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Your are sending fraud message',
                    'lan_value' => 'Your are sending fraud message'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Message contain spam word',
                    'lan_value' => 'Message contain spam word'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update Application',
                    'lan_value' => 'Update Application'
                ],

                /*version 2.5*/

                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Keywords',
                    'lan_value' => 'Keywords'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'All Keywords',
                    'lan_value' => 'All Keywords'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add New Keyword',
                    'lan_value' => 'Add New Keyword'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Keyword Settings',
                    'lan_value' => 'Keyword Settings'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Campaign Reports',
                    'lan_value' => 'Campaign Reports'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Keyword features only work with two way sms gateway provider',
                    'lan_value' => 'Keyword features only work with two way sms gateway provider'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Title',
                    'lan_value' => 'Title'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Keyword',
                    'lan_value' => 'Keyword'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Manage Keyword',
                    'lan_value' => 'Manage Keyword'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Keyword Name',
                    'lan_value' => 'Keyword Name'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Reply Text For Recipient',
                    'lan_value' => 'Reply Text For Recipient'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Reply Voice For Recipient',
                    'lan_value' => 'Reply Voice For Recipient'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'MMS File',
                    'lan_value' => 'MMS File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Reply MMS For Recipient',
                    'lan_value' => 'Reply MMS For Recipient'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unlimited',
                    'lan_value' => 'Unlimited'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Available',
                    'lan_value' => 'Available'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Assigned',
                    'lan_value' => 'Assigned'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Opt in SMS Keyword',
                    'lan_value' => 'Opt in SMS Keyword'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Insert keyword using comma',
                    'lan_value' => 'Insert keyword using comma'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Opt Out SMS Keyword',
                    'lan_value' => 'Opt Out SMS Keyword'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Custom Gateway Success Response Status',
                    'lan_value' => 'Custom Gateway Success Response Status'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Campaign Type',
                    'lan_value' => 'Campaign Type'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Regular',
                    'lan_value' => 'Regular'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Scheduled',
                    'lan_value' => 'Scheduled'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Campaign ID',
                    'lan_value' => 'Campaign ID'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Campaign Details',
                    'lan_value' => 'Campaign Details'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update Campaign',
                    'lan_value' => 'Update Campaign'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Delivered',
                    'lan_value' => 'Delivered'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Failed',
                    'lan_value' => 'Failed'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Queued',
                    'lan_value' => 'Queued'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Type',
                    'lan_value' => 'SMS Type'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Run At',
                    'lan_value' => 'Run At'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Delivered At',
                    'lan_value' => 'Delivered At'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Campaign Status',
                    'lan_value' => 'Campaign Status'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Campaign Keyword',
                    'lan_value' => 'Campaign Keyword'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Only work with two way sms gateway provider',
                    'lan_value' => 'Only work with two way sms gateway provider'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Existing MMS File',
                    'lan_value' => 'Existing MMS File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Update MMS File',
                    'lan_value' => 'Update MMS File'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Stop',
                    'lan_value' => 'Stop'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Paused',
                    'lan_value' => 'Paused'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Number',
                    'lan_value' => 'Number'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Overview',
                    'lan_value' => 'Overview'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Choose delimiter',
                    'lan_value' => 'Choose delimiter'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Automatic',
                    'lan_value' => 'Automatic'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Tab',
                    'lan_value' => 'Tab'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'New Line',
                    'lan_value' => 'New Line'
                ], [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Purchase keyword',
                    'lan_value' => 'Purchase keyword'
                ],
                /*version 2.6*/

                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Arabic',
                    'lan_value' => 'Arabic'
                ],
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Original Name',
                    'lan_value' => 'Original Name'
                ],

                /*Version 2.7*/

                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Chat SMS',
                    'lan_value' => 'Chat SMS'
                ],
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Load More',
                    'lan_value' => 'Load More'
                ],
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Add To Blacklist',
                    'lan_value' => 'Add To Blacklist'
                ],
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Remove History',
                    'lan_value' => 'Remove History'
                ],
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Type your message',
                    'lan_value' => 'Type your message'
                ],
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'System Email',
                    'lan_value' => 'System Email'
                ],
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'SMS Settings',
                    'lan_value' => 'SMS Settings'
                ],
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Authentication Settings',
                    'lan_value' => 'Authentication Settings'
                ],
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Unsubscribe Message',
                    'lan_value' => 'Unsubscribe Message'
                ],
                [
                    'lan_id' => $lan_id,
                    'lan_data' => 'Generate unsubscribe message',
                    'lan_value' => 'Generate unsubscribe message'
                ]
            ];

            foreach ($data as $d) {
                LanguageData::create($d);
            }

            return redirect('settings/language-settings')->with([
                'message' => language_data('Language Added Successfully')
            ]);

        } else {
            return redirect('settings/language-settings')->with([
                'message' => language_data('Language Already Exist'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // translateLanguage Function Start Here
    //======================================================================
    public function translateLanguage($lid)
    {
        $self = 'language-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $lan_name = Language::find($lid)->language;
        $lan_data = LanguageData::where('lan_id', $lid)->get();

        if (ini_get('max_input_vars') < 2500 && app_config('AppStage') != 'Demo') {
            $notify = 'Please update your max_input_vars from ' . ini_get('max_input_vars') . ' to 2500 from your server php.ini';
        } else {
            $notify = false;
        }

        return view('admin.language-translation', compact('lan_name', 'lan_data', 'lid', 'notify'));

    }


    //======================================================================
    // translateLanguagePost Function Start Here
    //======================================================================
    public function translateLanguagePost(Request $request)
    {

        $lan_id = Input::get('lan_id');

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('settings/language-settings-translate/' . $lan_id)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'language-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $english_data   = Input::get('english_data');
        $translate_data = Input::get('translate_data');

        if (count($english_data) !== count($translate_data)) {
            return redirect('settings/language-settings-translate/' . $lan_id)->with([
                'message' => 'Please Set max_input_vars in php.ini to 2500',
                'message_important' => true
            ]);
        }

        $lan_data = array_combine($english_data, $translate_data);
        LanguageData::where('lan_id', $lan_id)->delete();

        foreach ($lan_data as $english => $translate) {
            LanguageData::create([
                'lan_id' => $lan_id,
                'lan_data' => $english,
                'lan_value' => $translate
            ]);
        }

        return redirect('settings/language-settings-translate/' . $lan_id)->with([
            'message' => language_data('Language Translate Successfully')
        ]);
    }


    /* languageChange  Function Start Here */
    public function languageChange($id)
    {

        $appStage = app_config('AppStage');

        if ($appStage == 'Demo') {
            return redirect('admin')->with([
                'message' => 'This Option Is Disable In Demo Mode',
                'message_important' => true
            ]);
        }


        $lang = Language::find($id);

        if ($lang) {
            AppConfig::where('setting', '=', 'Language')->update(['value' => $id]);
            return redirect('admin/dashboard')->with([
                'message' => language_data('Language updated Successfully')
            ]);
        } else {
            return redirect('admin/dashboard')->with([
                'message' => language_data('Language not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // languageSettingsManage Function Start Here
    //======================================================================
    public function languageSettingsManage($id)
    {

        $self = 'language-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $lan = Language::find($id);

        if ($lan) {
            return view('admin.language-setting-manage', compact('lan'));
        } else {
            return redirect('settings/language-settings')->with([
                'message' => language_data('Language not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // languageSettingManagePost Function Start Here
    //======================================================================
    public function languageSettingManagePost(Request $request)
    {
        $cmd = Input::get('cmd');


        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('settings/language-settings-manage/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'language-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'language_name' => 'required', 'status' => 'required', 'flag' => 'image|mimes:jpeg,jpg,png,gif'
        ]);

        if ($v->fails()) {
            return redirect('settings/language-settings-manage/' . $cmd)->withErrors($v->errors());
        }

        $language = explode('_', $request->language_name);

        if (is_array($language)) {
            $language_name = $language['1'];
            $language_code = $language['0'];
        } else {
            return redirect('settings/language-settings-manage/' . $cmd)->with([
                'message' => language_data('Please try again'),
                'message_important' => true
            ]);
        }


        $status = Input::get('status');
        $flag   = Input::file('flag');

        $exist = Language::where('language', $language_name)->first();

        if ($exist) {
            if ($exist->language != $language_name) {
                return redirect('settings/language-settings-manage/' . $cmd)->with([
                    'message' => language_data('Language Already Exist'),
                    'message_important' => true
                ]);
            }
        }

        if ($flag != '') {
            $destinationPath = public_path() . '/assets/country_flag/';
            $flag_name       = $flag->getClientOriginalName();
            Input::file('flag')->move($destinationPath, $flag_name);
        } else {
            if ($exist) {
                $flag_name = $exist->icon;
            } else {
                return redirect('settings/language-settings-manage/' . $cmd)->with([
                    'message' => language_data('Country flag required'),
                    'message_important' => true
                ]);
            }
        }


        $lan = Language::find($cmd);

        if ($lan) {
            $lan->language      = $language_name;
            $lan->status        = $status;
            $lan->language_code = $language_code;
            $lan->icon          = $flag_name;
            $lan->save();

            return redirect('settings/language-settings')->with([
                'message' => language_data('Language updated Successfully')
            ]);
        } else {
            return redirect('settings/language-settings')->with([
                'message' => language_data('Language not found'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // deleteLanguage Function Start Here
    //======================================================================
    public function deleteLanguage($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('settings/language-settings')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'language-settings';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $language = Language::find($id);

        if ($language) {

            if (app_config('Language') == $id) {

                return redirect('settings/language-settings')->with([
                    'message' => language_data('Can not delete active language'),
                    'message_important' => true
                ]);
            }

            LanguageData::where('lan_id', $id)->delete();
            $language->delete();
            return redirect('settings/language-settings')->with([
                'message' => language_data('Language deleted successfully')
            ]);
        } else {
            return redirect('settings/language-settings')->with([
                'message' => language_data('Language not found'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // backgroundJobs Function Start Here
    //======================================================================
    public function backgroundJobs()
    {
        // Suggestion paths
        $paths = [
            '/usr/bin/php',
            '/usr/local/bin/php',
            '/bin/php',
            '/usr/bin/php7',
            '/usr/bin/php7.0',
            '/usr/bin/php70',
            '/usr/bin/php7.1',
            '/usr/bin/php71',
            '/usr/bin/php56',
            '/usr/bin/php5.6',
            '/opt/plesk/php/5.6/bin/php',
            '/opt/plesk/php/7.0/bin/php',
            '/opt/plesk/php/7.1/bin/php',
        ];

        // try to detect system's PHP CLI
        if (exec_enabled()) {
            try {
                $paths           = array_unique(array_merge($paths, explode(" ", exec("whereis php"))));
                $server_php_path = exec('which php');
                $get_message     = '';
            } catch (\Exception $e) {
                $get_message = $e->getMessage();
            }
        } else {
            $server_php_path = 'PHP_Executable_Path';
            $get_message     = 'WARNING: Please enable PHP `exec` function to validate the cron job setting';
        }

        $paths = array_values(array_filter($paths, function ($path) {
            try {
                return is_executable($path) && preg_match("/php[0-9\.a-z]{0,3}$/i", $path);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }));

        return view('admin.background-jobs', compact('paths', 'get_message', 'server_php_path'));

    }


    //======================================================================
    // purchaseCode Function Start Here
    //======================================================================
    public function purchaseCode()
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('admin')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if (app_config('purchase_key') == '') {
            Auth::logout();
            return redirect('admin')->with([
                'message' => 'Invalid purchase key',
                'message_important' => true
            ]);
        }

        if (app_config('license_type') == '') {

            $purchase_code = app_config('purchase_key');
            $domain_name   = app_config('AppUrl');

            $input = trim($domain_name, '/');
            if (!preg_match('#^http(s)?://#', $input)) {
                $input = 'http://' . $input;
            }

            $urlParts    = parse_url($input);
            $domain_name = preg_replace('/^www\./', '', $urlParts['host']);

        $get_data = array();
		$get_data['status'] = 'success';
		$get_data['license_type'] = 'Extended License';

            if (is_array($get_data) && array_key_exists('status', $get_data)) {
                if ($get_data['status'] == 'success') {
                    AppConfig::where('setting', '=', 'license_type')->update(['value' => $get_data['license_type']]);
                }
            }

        }

        return view('admin.purchase-code');
    }

    //======================================================================
    // updatePurchaseCode Function Start Here
    //======================================================================
    public function updatePurchaseCode(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('admin')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if ($request->purchase_code == '') {
            return redirect('settings/purchase-code')->with([
                'message' => 'Purchase code required',
                'message_important' => true
            ]);
        }

        $get_data = array();
		$get_data['status'] = 'success';
		$get_data['license_type'] = 'Extended License';

        if (is_array($get_data) && array_key_exists('status', $get_data)) {
            if ($get_data['status'] == 'success') {
                AppConfig::where('setting', '=', 'purchase_key')->update(['value' => $purchase_code]);
                AppConfig::where('setting', '=', 'purchase_code_error_count')->update(['value' => 0]);
                AppConfig::where('setting', '=', 'license_type')->update(['value' => $get_data['license_type']]);
                AppConfig::where('setting', '=', 'valid_domain')->update(['value' => 'yes']);

                return redirect('settings/purchase-code')->with([
                    'message' => language_data('Purchase code information updated')
                ]);

            } else {
                return redirect('settings/purchase-code')->with([
                    'message' => 'Invalid license key',
                    'message_important' => true
                ]);
            }
        } else {
            return redirect('settings/purchase-code')->with([
                'message' => language_data('Invalid request'),
                'message_important' => true
            ]);
        }

    }


}
