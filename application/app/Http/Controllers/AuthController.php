<?php

namespace App\Http\Controllers;

use App\Admin;
use App\AdminRolePermission;
use App\AppConfig;
use App\Campaigns;
use App\CampaignSubscriptionList;
use App\Client;
use App\EmailTemplates;
use App\Language;
use App\LanguageData;
use App\Mail\ForgotPassword;
use App\Mail\PasswordToken;
use App\Mail\UserRegistration;
use App\Mail\VerifyUser;
use App\PaymentGateways;
use App\ScheduleSMS;
use App\SMSGatewayCredential;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSInbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use ReCaptcha\ReCaptcha;
use Exception;

class AuthController extends Controller
{
    //======================================================================
    // clientLogin Function Start Here
    //======================================================================
    public function HomePage()
    {
        if (env('APP_TYPE') == 'new') {
            return redirect('install');
        }

        if (Auth::guard('client')->check()) {
            return redirect('dashboard');
        }
        return view('home.index');
    }
    public function clientLogin()
    {
        if (env('APP_TYPE') == 'new') {
            return redirect('install');
        }

        if (Auth::guard('client')->check()) {
            return redirect('dashboard');
        }

        return view('client.login');
    }

    //======================================================================
    // clientGetLogin Function Start Here
    //======================================================================
    public function clientGetLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required', 'password' => 'required'
        ]);

        $check_input = $request->only('username', 'password');
        $remember    = (Input::has('remember')) ? true : false;


        if (app_config('captcha_in_client') == '1') {
            if (isset($_POST['g-recaptcha-response'])) {
                $getCaptchaResponse = $_POST['g-recaptcha-response'];
                $recaptcha          = new ReCaptcha(app_config('captcha_secret_key'));
                $resp               = $recaptcha->verify($getCaptchaResponse);

                if (!$resp->isSuccess()) {
                    if (array_key_exists('0', $resp->getErrorCodes())) {
                        $error_msg = $resp->getErrorCodes()[0];
                    } else {
                        $error_msg = language_data('Invalid Captcha');
                    }

                    return redirect('/')->with([
                        'message' => $error_msg,
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('/')->with([
                    'message' => language_data('Invalid Captcha'),
                    'message_important' => true
                ]);
            }
        }

        if (Auth::guard('client')->attempt($check_input, $remember)) {

            if (Auth::guard('client')->user()->status == 'Active') {
                return redirect()->intended('dashboard');
            }elseif (Auth::guard('client')->user()->status == 'Inactive') {
                return view('client.user-verification');
            } else {
                Auth::guard('client')->logout();
                return redirect('/')->withInput($request->only('username'))->withErrors([
                    'username' => language_data('Your are inactive or blocked by system. Please contact with administrator')
                ]);
            }

        } else {
            return redirect('/')->withInput($request->only('username'))->withErrors([
                'username' => language_data('Invalid User name or Password')
            ]);
        }
    }




    //======================================================================
    // clientRegistrationVerification Function Start Here
    //======================================================================
    public function clientRegistrationVerification()
    {
        return view('client.user-verification');
    }


    //======================================================================
    // postVerificationToken Function Start Here
    //======================================================================
    public function postVerificationToken()
    {
        $cmd = Input::get('cmd');

        if ($cmd == '') {
            return redirect('/')->with([
                'message' => language_data('Invalid Request'),
                'message_important' => true
            ]);
        }


        $ef = Client::find($cmd);

        if ($ef) {

            $fprand = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');

            $name = $ef->fname . ' ' . $ef->lname;
            $email = $ef->email;
            $fpw_link = url('/verify-user/' . $fprand);

            try {

                \Mail::to($email)->send(new VerifyUser($name, $fpw_link));

                $ef->pwresetkey = $fprand;
                $ef->save();

                return redirect('user/registration-verification')->with([
                    'message' => language_data('Verification code send successfully. Please check your email')
                ]);

            } catch (Exception $ex) {
                return redirect('user/registration-verification')->with([
                    'message' => $ex->getMessage()
                ]);
            }

        } else {
            return redirect('/')->with([
                'message' => language_data('Invalid Request'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // verifyUserAccount Function Start Here
    //======================================================================
    public function verifyUserAccount($token)
    {


        $tfnd = Client::where('pwresetkey', '=', $token)->count();

        if ($tfnd == '1') {
            $d = Client::where('pwresetkey', '=', $token)->first();
            $d->status = 'Active';
            $d->pwresetkey = '';
            $d->save();

            if ($d->emailnotify == 'Yes'){
                $email = $d->email;
                $name = $d->fname.' '.$d->lname;
                $username = $d->username;

                try {

                    \Mail::to($email)->send(new UserRegistration($name, $username, null));

                    return redirect('/')->with([
                        'message' => language_data('Registration Successful')
                    ]);

                } catch (Exception $ex) {
                    return redirect('/')->with([
                        'message' => $ex->getMessage(),
                        'message_important' => true
                    ]);
                }

            }

            return redirect('/')->with([
                'message' => language_data('Registration Successful')
            ]);

        } else {
            return redirect('/')->with([
                'message' => language_data('Verification code not found'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // forgotUserPassword Function Start Here
    //======================================================================
    public function forgotUserPassword()
    {
        return view('client.forgot-password');
    }



    //======================================================================
    // clientSignUp Function Start Here
    //======================================================================
    public function clientSignUp()
    {
        if (app_config('client_registration') != '1') {
            return redirect('/')->with([
                'message' => language_data('Invalid Request'),
                'message_important' => true
            ]);
        }

        return view('client.registration');

    }

    //======================================================================
    // postUserRegistration Function Start Here
    //======================================================================
    public function postUserRegistration(Request $request)
    {

        $v = \Validator::make($request->all(), [
            'first_name' => 'required', 'user_name' => 'required', 'email' => 'required|email', 'password' => 'required', 'cpassword' => 'required', 'phone' => 'required', 'country' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('signup')->withInput($request->all())->withErrors($v->errors());
        }


        if (app_config('captcha_in_client_registration') == '1') {
            if (isset($_POST['g-recaptcha-response'])) {
                $getCaptchaResponse = $_POST['g-recaptcha-response'];
                $recaptcha = new ReCaptcha(app_config('captcha_secret_key'));
                $resp = $recaptcha->verify($getCaptchaResponse);

                if (!$resp->isSuccess()) {
                    if (array_key_exists('0', $resp->getErrorCodes())) {
                        $error_msg = $resp->getErrorCodes()[0];
                    } else {
                        $error_msg = language_data('Invalid Captcha');
                    }

                    return redirect('signup')->withInput($request->all())->with([
                        'message' => $error_msg,
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('signup')->withInput($request->all())->with([
                    'message' => language_data('Invalid Captcha'),
                    'message_important' => true
                ]);
            }
        }

        $exist_user_name = Client::where('username', $request->user_name)->first();
        $exist_user_email = Client::where('email', $request->email)->first();

        if ($exist_user_name) {
            return redirect('signup')->withInput($request->all())->with([
                'message' => language_data('User name already exist'),
                'message_important' => true
            ]);
        }

        if ($exist_user_email) {
            return redirect('signup')->withInput($request->all())->with([
                'message' => language_data('Email already exist'),
                'message_important' => true
            ]);
        }

        $password = $request->password;
        $cpassword = $request->cpassword;

        if ($password !== $cpassword) {
            return redirect('signup')->withInput($request->all())->with([
                'message' => language_data('Both password does not match'),
                'message_important' => true
            ]);
        } else {
            $password = bcrypt($password);
        }

        if (app_config('registration_verification') == '1') {
            $status = 'Inactive';
        } else {
            $status = 'Active';
        }

        $email_notify = $request->email_notify;
        if ($email_notify == 'yes') {
            $email_notify = 'Yes';
        } else {
            $email_notify = 'No';
        }

        $email = $request->email;
        $sms_gateway = SMSGateways::find(app_config('registration_sms_gateway'));
        if (!$sms_gateway) {
            return redirect('signup')->with([
                'message' => 'SMS Gateway not found. Please contact with administrator',
                'message_important' => true
            ]);
        }

        $sms_gateway = array(app_config('registration_sms_gateway'));
        $sms_gateways_id = json_encode($sms_gateway, true);

        if (app_config('sms_api_permission') == 1) {
            $api_permission = 'Yes';
        } else {
            $api_permission = 'No';
        }


        $api_key_generate = $request->user_name . ':' . $cpassword;
        $client = new Client();
        $client->parent = '0';
        $client->fname = $request->first_name;
        $client->lname = $request->last_name;
        $client->email = $email;
        $client->username = $request->user_name;
        $client->password = $password;
        $client->country = $request->country;
        $client->phone = $request->phone;
        $client->image = 'profile.jpg';
        $client->datecreated = date('Y-m-d');
        $client->sms_limit = 0;
        $client->api_access = $api_permission;
        $client->api_key = base64_encode($api_key_generate);
        $client->status = $status;
        $client->reseller = 'No';
        $client->sms_gateway = $sms_gateways_id;
        $client->api_gateway = app_config('registration_sms_gateway');
        $client->emailnotify = $email_notify;
        $client->lan_id = app_config('Language');
        $client->save();
        $client_id = $client->id;

        /*For Email Confirmation*/
        if (is_int($client_id) && $email != '') {
            $name = $request->first_name . ' ' . $request->last_name;

            try {

                if (app_config('registration_verification') == '1') {
                    $fprand = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');
                    $fpw_link = url('/verify-user/' . $fprand);

                    Client::find($client_id)->update([
                        'pwresetkey' => $fprand
                    ]);

                    \Mail::to($email)->send(new VerifyUser($name, $fpw_link));

                    return redirect('/')->with([
                        'message' => language_data('Verification code send successfully. Please check your email')
                    ]);

                } elseif ($email_notify == 'Yes') {
                    \Mail::to($email)->send(new UserRegistration($name, $request->user_name, $cpassword));

                    return redirect('/')->with([
                        'message' => language_data('Registration Successful')
                    ]);
                } else {
                    return redirect('/')->with([
                        'message' => language_data('Registration Successful')
                    ]);
                }

            } catch (Exception $ex) {
                return redirect('/')->with([
                    'message' => $ex->getMessage()
                ]);
            }
        }

        return redirect('/')->with([
            'message' => language_data('Registration Successful')
        ]);

    }



    //======================================================================
    // adminLogin Function Start Here
    //======================================================================
    public function adminLogin()
    {

        if (Auth::check()) {
            return redirect('admin/dashboard');
        }

        return view('admin.login');
    }



    //======================================================================
    // adminGetLogin Function Start Here
    //======================================================================
    public function adminGetLogin(Request $request)
    {

        $this->validate($request, [
            'username' => 'required', 'password' => 'required'
        ]);

        $check_input = $request->only('username', 'password');
        $remember    = (Input::has('remember')) ? true : false;

        if (app_config('captcha_in_admin') == '1') {
            if (isset($_POST['g-recaptcha-response'])) {
                $getCaptchaResponse = $_POST['g-recaptcha-response'];
                $recaptcha          = new ReCaptcha(app_config('captcha_secret_key'));
                $resp               = $recaptcha->verify($getCaptchaResponse);

                if (!$resp->isSuccess()) {
                    if (array_key_exists('0', $resp->getErrorCodes())) {
                        $error_msg = $resp->getErrorCodes()[0];
                    } else {
                        $error_msg = language_data('Invalid Captcha');
                    }

                    return redirect('admin')->with([
                        'message' => $error_msg,
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('admin')->with([
                    'message' => language_data('Invalid Captcha'),
                    'message_important' => true
                ]);
            }
        }

        if (Auth::attempt($check_input, $remember)) {
            if (Auth::user()->status == 'Active') {
                return redirect()->intended('admin/dashboard');
            } else {
                return redirect('admin')->withInput($request->only('username'))->withErrors([
                    'username' => language_data('Your are inactive or blocked by system. Please contact with administrator')
                ]);
            }
        } else {
            return redirect('admin')->withInput($request->only('username'))->withErrors([
                'username' => language_data('Invalid User name or Password')
            ]);
        }
    }

    //======================================================================
    // permissionError Function Start Here
    //======================================================================
    public function permissionError()
    {
        return view('admin.permission-error');
    }

    //======================================================================
    // forgotPassword Function Start Here
    //======================================================================
    public function forgotPassword()
    {
        return view('admin.forgot-password');
    }


    //======================================================================
    // forgotPasswordToken Function Start Here
    //======================================================================
    public function forgotPasswordToken(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('admin')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('forgot-password')->withErrors($v->errors());
        }

        $email = Input::get('email');

        $d = Admin::where('email', '=', $email)->count();
        if ($d == '1') {
            $fprand = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');
            $ef = Admin::where('email', '=', $email)->first();
            $name = $ef->fname . ' ' . $ef->lname;
            $ef->pwresetkey = $fprand;
            $ef->save();

            $fpw_link = url('admin/forgot-password-token-code/' . $fprand);

            try {
                \Mail::to($email)->send(new ForgotPassword($fpw_link, $name));
                return redirect('admin/forgot-password')->with([
                    'message' => language_data('Your Password Already Reset. Please Check your email')
                ]);
            } catch (Exception $ex) {
                return redirect('admin/forgot-password')->with([
                    'message' => $ex->getMessage()
                ]);
            }

        } else {
            return redirect('admin/forgot-password')->with([
                'message' => language_data('Sorry There is no registered user with this email address'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // forgotPasswordTokenCode Function Start Here
    //======================================================================
    public function forgotPasswordTokenCode($token)
    {


        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('admin')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $tfnd = Admin::where('pwresetkey', '=', $token)->count();

        if ($tfnd == '1') {
            $d = Admin::where('pwresetkey', '=', $token)->first();
            $name = $d->fname . ' ' . $d->lname;
            $email = $d->email;
            $username = $d->username;
            $url = url('admin');

            $rawpass = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');
            $password = bcrypt($rawpass);

            $d->password = $password;
            $d->pwresetkey = '';
            $d->save();

            /*For Email Confirmation*/

            try {
                \Mail::to($email)->send(new PasswordToken($name, $username, $rawpass, $url));

                return redirect('admin')->with([
                    'message' => language_data('A New Password Generated. Please Check your email.')
                ]);

            } catch (Exception $ex) {
                return redirect('admin')->with([
                    'message' => $ex->getMessage()
                ]);
            }

        } else {
            return redirect('admin')->with([
                'message' => language_data('Sorry Password reset Token expired or not exist, Please try again.'),
                'message_important' => true
            ]);
        }


    }



    //======================================================================
    // forgotUserPasswordToken Function Start Here
    //======================================================================
    public function forgotUserPasswordToken(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('/')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($v->fails()) {
            return redirect('forgot-password')->withErrors($v->errors());
        }

        $email = Input::get('email');

        $d = Client::where('email', '=', $email)->count();
        if ($d == '1') {
            $fprand = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');
            $ef = Client::where('email', '=', $email)->first();
            $name = $ef->fname . ' ' . $ef->lname;
            $ef->pwresetkey = $fprand;
            $ef->save();

            $fpw_link = url('user/forgot-password-token-code/' . $fprand);

            /*For Email Confirmation*/

            try {
                \Mail::to($email)->send(new ForgotPassword($fpw_link, $name));

                return redirect('forgot-password')->with([
                    'message' => language_data('Your Password Already Reset. Please Check your email')
                ]);
            } catch (Exception $ex) {

                return redirect('forgot-password')->with([
                    'message' => $ex->getMessage()
                ]);
            }


        } else {
            return redirect('forgot-password')->with([
                'message' => language_data('Sorry There is no registered user with this email address'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // forgotUserPasswordTokenCode Function Start Here
    //======================================================================
    public function forgotUserPasswordTokenCode($token)
    {


        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('/')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $tfnd = Client::where('pwresetkey', '=', $token)->count();

        if ($tfnd == '1') {
            $d = Client::where('pwresetkey', '=', $token)->first();
            $name = $d->fname . ' ' . $d->lname;
            $url = url('/');
            $email = $d->email;
            $username = $d->username;

            $rawpass = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');
            $password = bcrypt($rawpass);

            $d->password = $password;
            $d->pwresetkey = '';
            $d->save();

            /*For Email Confirmation*/

            try {
                \Mail::to($email)->send(new PasswordToken($name, $username, $rawpass, $url));

                return redirect('/')->with([
                    'message' => language_data('A New Password Generated. Please Check your email.')
                ]);

            } catch (Exception $ex) {
                return redirect('/')->with([
                    'message' => $ex->getMessage()
                ]);
            }

        } else {
            return redirect('/')->with([
                'message' => language_data('Sorry Password reset Token expired or not exist, Please try again.'),
                'message_important' => true
            ]);
        }
    }
    /* updateApplication  Function Start Here */
    public function updateApplication(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('/')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }


        $v = \Validator::make($request->all(), [
            'purchase_code' => 'required', 'app_url' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('update')->withErrors($v->errors());
        }


        $purchase_code = $request->purchase_code;
        $domain_name   = $request->app_url;

        $input = trim($domain_name, '/');
        if (!preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }

        $urlParts    = parse_url($input);
        $domain_name = preg_replace('/^www\./', '', $urlParts['host']);


        $data = array();
		$data['status'] = 'success';
		$data['license_type'] = 'Extended License';

        if (isset($data) && is_array($data) && array_key_exists('status', $data)) {
            if ($data['status'] != 'success') {
                return redirect('update')->with([
                    'message' => $data['msg'],
                    'message_important' => true
                ]);
            }
        } else {
            return redirect('update')->with([
                'message' => 'Something went wrong. please try again.',
                'message_important' => true
            ]);
        }

        $version = trim(app_config('SoftwareVersion'));

        switch ($version) {
            case '2.8':
                $message = 'You are already using latest version';
                break;

            case '2.7':
                AppConfig::where('setting', '=', 'SoftwareVersion')->update(['value' => '2.8']);
                $message = 'Congratulation!! Application updated to Version 2.8. Now your are using latest version. Refresh your page to back your application login';
                break;

            case '2.6':
                $sql = <<<EOF
DROP TABLE IF EXISTS `sys_sms_inbox`;

CREATE TABLE `sys_sms_inbox` (
  `id` int(10) UNSIGNED NOT NULL,
  `msg_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `status` text COLLATE utf8_unicode_ci,
  `send_by` enum('sender','receiver') COLLATE utf8_unicode_ci NOT NULL,
  `mark_read` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `sys_sms_inbox`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sys_sms_inbox`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

EOF;

                try {
                    DB::connection()->getPdo()->exec($sql);

                    AppConfig::create([
                        'setting' => 'unsubscribe_message',
                        'value' => 'Reply STOP to be removed'
                    ]);

                    $new_sms_gateways = [
                        [
                            'name' => 'CoinSMS',
                            'settings' => 'CoinSMS',
                            'api_link' => 'http://coinsms.net/smsapi.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'MessageWhiz',
                            'settings' => 'MessageWhiz',
                            'api_link' => 'http://smartmessaging.mmdsmart.com/api',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'Futureland',
                            'settings' => 'Futureland',
                            'api_link' => 'https://www.futureland.it/gateway/futuresend.asp',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'GlobalSMS',
                            'settings' => 'GlobalSMS',
                            'api_link' => 'http://78.46.17.110/app/smsapi/index.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'LRTelecom',
                            'settings' => 'LRTelecom',
                            'api_link' => 'https://sms.lrt.com.pk/api/sms-single-or-bulk-api.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'AccessYou',
                            'settings' => 'AccessYou',
                            'api_link' => 'http://api.accessyou.com/sms/sendsms-utf8.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'Montnets',
                            'settings' => 'Montnets',
                            'api_link' => 'http://ip:port/sms/v2/std/send_single',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'ShotBulkSMS',
                            'settings' => 'ShotBulkSMS',
                            'api_link' => 'http://ip:port/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'PlivoPowerpack',
                            'settings' => 'PlivoPowerpack',
                            'api_link' => 'https://api.plivo.com/v1/Account/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'KarixIO',
                            'settings' => 'KarixIO',
                            'api_link' => 'https://api.karix.io/message/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'ElasticEmail',
                            'settings' => 'ElasticEmail',
                            'api_link' => 'https://api.elasticemail.com/v2/sms/send',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'OnnorokomSMS',
                            'settings' => 'OnnorokomSMS',
                            'api_link' => 'https://api2.onnorokomsms.com/HttpSendSms.ashx',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'PowerSMS',
                            'settings' => 'PowerSMS',
                            'api_link' => 'http://powersms.banglaphone.net.bd/httpapi/sendsms',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'Ovh',
                            'settings' => 'Ovh',
                            'api_link' => '',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => '46ELKS',
                            'settings' => '46ELKS',
                            'api_link' => 'https://api.46elks.com/a1',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'Yes',
                            'voice' => 'No'
                        ], [
                            'name' => 'Send99',
                            'settings' => 'Send99',
                            'api_link' => 'http://api.send99.com/api/SendSMS',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'ChikaCampaign',
                            'settings' => 'ChikaCampaign',
                            'api_link' => 'http://api.chikacampaign.com/sms/1/text/single',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                    ];

                    foreach ($new_sms_gateways as $n_gateway) {
                        $exist = SMSGateways::where('settings', $n_gateway)->first();
                        if (!$exist) {
                            SMSGateways::create($n_gateway);
                        }
                    }


                    $language = Language::select('id')->get();

                    foreach ($language as $l) {
                        $lan_id = $l->id;
                        $lan    = [

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
                        foreach ($lan as $d) {
                            LanguageData::create($d);
                        }
                    }

                    $receive_sms = SMSHistory::where('send_by', 'receiver')->get();

                    foreach ($receive_sms as $sms) {
                        if ($sms->status != '' && $sms->amount != '' && $sms->message != '') {
                            SMSInbox::create([
                                'msg_id' => $sms->id,
                                'amount' => $sms->amount,
                                'message' => $sms->message,
                                'status' => $sms->status,
                                'send_by' => 'receiver',
                                'mark_read' => 'yes'
                            ]);
                        }
                    }


                    $server_gateway = app_config('Gateway');
                    $app_name       = app_config('AppName');

                    if ($server_gateway == 'smtp') {
                        $eg = 'smtp';

                        $host_name = app_config('SMTPHostName');
                        $user_name = app_config('SMTPUserName');
                        $password  = app_config('SMTPPassword');
                        $port      = app_config('SMTPPort');
                        $secure    = app_config('SMTPSecure');

                        $smtpSetting = 'MAIL_DRIVER=' . $eg . '
MAIL_HOST=' . $host_name . '
MAIL_PORT=' . $port . '
MAIL_USERNAME=' . $user_name . '
MAIL_PASSWORD=' . $password . '
MAIL_ENCRYPTION=' . $secure . '
APP_NAME="' . $app_name . '"
';
                        // @ignoreCodingStandard
                        $env        = file_get_contents(base_path('.env'));
                        $rows       = explode("\n", $env);
                        $unwanted   = "MAIL_DRIVER|MAIL_HOST|MAIL_PORT|MAIL_USERNAME|MAIL_PASSWORD|MAIL_ENCRYPTION|APP_NAME";
                        $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

                        $cleanString = implode("\n", $cleanArray);
                        $env         = $cleanString . $smtpSetting;
                    } else {
                        $eg          = 'sendmail';
                        $smtpSetting = 'MAIL_DRIVER=' . $eg . '
APP_NAME="' . $app_name . '"
';
                        // @ignoreCodingStandard
                        $env        = file_get_contents(base_path('.env'));
                        $rows       = explode("\n", $env);
                        $unwanted   = "MAIL_DRIVER|APP_NAME";
                        $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

                        $cleanString = implode("\n", $cleanArray);
                        $env         = $cleanString . $smtpSetting;
                    }

                    file_put_contents(base_path('.env'), $env);

                    AppConfig::where('setting', '=', 'SoftwareVersion')->update(['value' => '2.7']);

                    $message = 'Congratulation!! Application updated to Version 2.7. Now your are using latest version. Refresh your page to back your application login';


                } catch (Exception $e) {
                    return $e->getMessage();
                }

                break;

            case '2.5':
                $sql = <<<EOF
ALTER TABLE `sys_int_country_codes` CHANGE `tariff` `plain_tariff` DECIMAL(5,2) NOT NULL DEFAULT '1.00';
ALTER TABLE `sys_int_country_codes` ADD `voice_tariff` DECIMAL(5,2) NOT NULL DEFAULT '1.00' AFTER `plain_tariff`;
ALTER TABLE `sys_int_country_codes` ADD `mms_tariff` DECIMAL(5,2) NOT NULL DEFAULT '1.00' AFTER `voice_tariff`;
ALTER TABLE `sys_operator` CHANGE `price` `plain_price` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '1';
ALTER TABLE `sys_operator` ADD `voice_price` VARCHAR(10) NOT NULL DEFAULT '1' AFTER `plain_price`;
ALTER TABLE `sys_operator` ADD `mms_price` VARCHAR(10) NOT NULL DEFAULT '1' AFTER `voice_price`;


DROP TABLE IF EXISTS `sys_sms_inbox`;

CREATE TABLE `sys_sms_inbox` (
  `id` int(10) UNSIGNED NOT NULL,
  `msg_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `status` text COLLATE utf8_unicode_ci,
  `send_by` enum('sender','receiver') COLLATE utf8_unicode_ci NOT NULL,
  `mark_read` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `sys_sms_inbox`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sys_sms_inbox`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

EOF;

                try {
                    DB::connection()->getPdo()->exec($sql);


                    AppConfig::create([
                        'setting' => 'unsubscribe_message',
                        'value' => 'Reply STOP to be removed'
                    ]);

                    $new_sms_gateways = [
                        [
                            'name' => 'Evyapan',
                            'settings' => 'Evyapan',
                            'api_link' => 'gw.barabut.com',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'BudgetSMS',
                            'settings' => 'BudgetSMS',
                            'api_link' => 'https://api.budgetsms.net/sendsms/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'EasySendSMS',
                            'settings' => 'EasySendSMS',
                            'api_link' => 'https://www.easysendsms.com/sms/bulksms-api/bulksms-api',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Diamondcard',
                            'settings' => 'Diamondcard',
                            'api_link' => 'http://sms.diamondcard.us/doc/sms-api.wsdl',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'ClickSend',
                            'settings' => 'ClickSend',
                            'api_link' => 'https://api-mapper.clicksend.com/http/v2/send.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Gatewayapi',
                            'settings' => 'Gatewayapi',
                            'api_link' => 'https://gatewayapi.com/rest/mtsms',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'CoinSMS',
                            'settings' => 'CoinSMS',
                            'api_link' => 'http://coinsms.net/smsapi.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'MessageWhiz',
                            'settings' => 'MessageWhiz',
                            'api_link' => 'http://smartmessaging.mmdsmart.com/api',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'Futureland',
                            'settings' => 'Futureland',
                            'api_link' => 'https://www.futureland.it/gateway/futuresend.asp',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'GlobalSMS',
                            'settings' => 'GlobalSMS',
                            'api_link' => 'http://78.46.17.110/app/smsapi/index.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'LRTelecom',
                            'settings' => 'LRTelecom',
                            'api_link' => 'https://sms.lrt.com.pk/api/sms-single-or-bulk-api.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'AccessYou',
                            'settings' => 'AccessYou',
                            'api_link' => 'http://api.accessyou.com/sms/sendsms-utf8.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'Montnets',
                            'settings' => 'Montnets',
                            'api_link' => 'http://ip:port/sms/v2/std/send_single',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'ShotBulkSMS',
                            'settings' => 'ShotBulkSMS',
                            'api_link' => 'http://ip:port/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'PlivoPowerpack',
                            'settings' => 'PlivoPowerpack',
                            'api_link' => 'https://api.plivo.com/v1/Account/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'KarixIO',
                            'settings' => 'KarixIO',
                            'api_link' => 'https://api.karix.io/message/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'ElasticEmail',
                            'settings' => 'ElasticEmail',
                            'api_link' => 'https://api.elasticemail.com/v2/sms/send',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'OnnorokomSMS',
                            'settings' => 'OnnorokomSMS',
                            'api_link' => 'https://api2.onnorokomsms.com/HttpSendSms.ashx',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'PowerSMS',
                            'settings' => 'PowerSMS',
                            'api_link' => 'http://powersms.banglaphone.net.bd/httpapi/sendsms',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'Ovh',
                            'settings' => 'Ovh',
                            'api_link' => '',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => '46ELKS',
                            'settings' => '46ELKS',
                            'api_link' => 'https://api.46elks.com/a1',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'Yes',
                            'voice' => 'No'
                        ], [
                            'name' => 'Send99',
                            'settings' => 'Send99',
                            'api_link' => 'http://api.send99.com/api/SendSMS',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'ChikaCampaign',
                            'settings' => 'ChikaCampaign',
                            'api_link' => 'http://api.chikacampaign.com/sms/1/text/single',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                    ];

                    foreach ($new_sms_gateways as $n_gateway) {
                        $exist = SMSGateways::where('settings', $n_gateway)->first();
                        if (!$exist) {
                            SMSGateways::create($n_gateway);
                        }
                    }


                    $language = Language::select('id')->get();

                    foreach ($language as $l) {
                        $lan_id = $l->id;
                        $lan    = [
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
                        foreach ($lan as $d) {
                            LanguageData::create($d);
                        }
                    }


                    $server_gateway = app_config('Gateway');
                    $app_name       = app_config('AppName');

                    if ($server_gateway == 'smtp') {
                        $eg = 'smtp';

                        $host_name = app_config('SMTPHostName');
                        $user_name = app_config('SMTPUserName');
                        $password  = app_config('SMTPPassword');
                        $port      = app_config('SMTPPort');
                        $secure    = app_config('SMTPSecure');

                        $smtpSetting = 'MAIL_DRIVER=' . $eg . '
MAIL_HOST=' . $host_name . '
MAIL_PORT=' . $port . '
MAIL_USERNAME=' . $user_name . '
MAIL_PASSWORD=' . $password . '
MAIL_ENCRYPTION=' . $secure . '
APP_NAME="' . $app_name . '"
';
                        // @ignoreCodingStandard
                        $env        = file_get_contents(base_path('.env'));
                        $rows       = explode("\n", $env);
                        $unwanted   = "MAIL_DRIVER|MAIL_HOST|MAIL_PORT|MAIL_USERNAME|MAIL_PASSWORD|MAIL_ENCRYPTION|APP_NAME";
                        $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

                        $cleanString = implode("\n", $cleanArray);
                        $env         = $cleanString . $smtpSetting;
                    } else {
                        $eg          = 'sendmail';
                        $smtpSetting = 'MAIL_DRIVER=' . $eg . '
APP_NAME="' . $app_name . '"
';
                        // @ignoreCodingStandard
                        $env        = file_get_contents(base_path('.env'));
                        $rows       = explode("\n", $env);
                        $unwanted   = "MAIL_DRIVER|APP_NAME";
                        $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

                        $cleanString = implode("\n", $cleanArray);
                        $env         = $cleanString . $smtpSetting;
                    }

                    file_put_contents(base_path('.env'), $env);

                    AppConfig::where('setting', '=', 'currency_decimal_digits')->update(['value' => '0']);
                    AppConfig::where('setting', '=', 'SoftwareVersion')->update(['value' => '2.7']);

                    $message = 'Congratulation!! Application updated to Version 2.7. Now your are using latest version. Refresh your page to back your application login';


                } catch (Exception $e) {
                    return $e->getMessage();
                }
                break;

            case '2.3':

                $sql = <<<EOF
ALTER TABLE `sys_bulk_sms` CHANGE `type` `type` ENUM('plain','unicode','voice','mms','arabic') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain';

ALTER TABLE `sys_block_message` CHANGE `type` `type` ENUM('plain','unicode','voice','mms','arabic') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain';

ALTER TABLE `sys_recurring_sms` CHANGE `type` `type` ENUM('plain','unicode','voice','mms','arabic') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain';

ALTER TABLE `sys_schedule_sms` CHANGE `type` `type` ENUM('plain','unicode','voice','mms','arabic') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain';

ALTER TABLE `sys_sms_history` CHANGE `sms_type` `sms_type` ENUM('plain','unicode','voice','mms','arabic') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE `sys_block_message` ADD `campaign_id` CHAR(36) NULL DEFAULT NULL AFTER `status`;

ALTER TABLE `sys_sms_bundles` CHANGE `unit_from` `unit_from` INT(20) NULL DEFAULT NULL;

ALTER TABLE `sys_sms_bundles` CHANGE `unit_to` `unit_to` INT(20) NULL DEFAULT NULL;

ALTER TABLE `sys_int_country_codes` CHANGE `tariff` `plain_tariff` DECIMAL(5,2) NOT NULL DEFAULT '1.00';
ALTER TABLE `sys_int_country_codes` ADD `voice_tariff` DECIMAL(5,2) NOT NULL DEFAULT '1.00' AFTER `plain_tariff`;
ALTER TABLE `sys_int_country_codes` ADD `mms_tariff` DECIMAL(5,2) NOT NULL DEFAULT '1.00' AFTER `voice_tariff`;
ALTER TABLE `sys_operator` CHANGE `price` `plain_price` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '1';
ALTER TABLE `sys_operator` ADD `voice_price` VARCHAR(10) NOT NULL DEFAULT '1' AFTER `plain_price`;
ALTER TABLE `sys_operator` ADD `mms_price` VARCHAR(10) NOT NULL DEFAULT '1' AFTER `voice_price`;

CREATE TABLE `sys_campaigns` (
  `id` int(10) UNSIGNED NOT NULL,
  `campaign_id` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sms_type` enum('plain','unicode','voice','mms','arabic') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain',
  `camp_type` enum('regular','scheduled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'regular',
  `status` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `use_gateway` int(11) NOT NULL,
  `total_recipient` int(11) NOT NULL DEFAULT '0',
  `total_delivered` int(11) NOT NULL DEFAULT '0',
  `total_failed` int(11) NOT NULL DEFAULT '0',
  `media_url` text COLLATE utf8_unicode_ci,
  `keyword` text COLLATE utf8_unicode_ci,
  `run_at` timestamp NULL DEFAULT NULL,
  `delivery_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



CREATE TABLE `sys_campaign_subscription_list` (
  `id` int(10) UNSIGNED NOT NULL,
  `campaign_id` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `number` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8_unicode_ci NOT NULL,
  `amount` int(11) NOT NULL DEFAULT '1',
  `status` text COLLATE utf8_unicode_ci NOT NULL,
  `submitted_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `sys_campaigns`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sys_campaign_subscription_list`
  ADD PRIMARY KEY (`id`);
  
  ALTER TABLE `sys_campaigns`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `sys_campaign_subscription_list`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;


CREATE TABLE `sys_keywords` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `keyword_name` text COLLATE utf8_unicode_ci NOT NULL,
  `reply_text` text COLLATE utf8_unicode_ci,
  `reply_voice` text COLLATE utf8_unicode_ci,
  `reply_mms` text COLLATE utf8_unicode_ci,
  `status` enum('available','assigned','expired') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'available',
  `price` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `validity` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `validity_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `sys_keywords`
  ADD PRIMARY KEY (`id`);
  
  ALTER TABLE `sys_keywords`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  CREATE TABLE `sys_two_way_communication` (
  `id` int(10) UNSIGNED NOT NULL,
  `gateway_id` int(11) NOT NULL,
  `source_param` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `destination_param` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `message_param` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `sys_two_way_communication`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sys_two_way_communication`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `sys_int_country_codes` CHANGE `tariff` `plain_tariff` DECIMAL(5,2) NOT NULL DEFAULT '1.00';
ALTER TABLE `sys_int_country_codes` ADD `voice_tariff` DECIMAL(5,2) NOT NULL DEFAULT '1.00' AFTER `plain_tariff`;
ALTER TABLE `sys_int_country_codes` ADD `mms_tariff` DECIMAL(5,2) NOT NULL DEFAULT '1.00' AFTER `voice_tariff`;
ALTER TABLE `sys_operator` CHANGE `price` `plain_price` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '1';
ALTER TABLE `sys_operator` ADD `voice_price` VARCHAR(10) NOT NULL DEFAULT '1' AFTER `plain_price`;
ALTER TABLE `sys_operator` ADD `mms_price` VARCHAR(10) NOT NULL DEFAULT '1' AFTER `voice_price`;


DROP TABLE IF EXISTS `sys_sms_inbox`;

CREATE TABLE `sys_sms_inbox` (
  `id` int(10) UNSIGNED NOT NULL,
  `msg_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `status` text COLLATE utf8_unicode_ci,
  `send_by` enum('sender','receiver') COLLATE utf8_unicode_ci NOT NULL,
  `mark_read` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `sys_sms_inbox`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sys_sms_inbox`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;


EOF;

                try {
                    DB::connection()->getPdo()->exec($sql);
                    $app_config = [
                        [
                            'setting' => 'registration_sms_gateway',
                            'value' => '1'
                        ], [
                            'setting' => 'send_sms_country_code',
                            'value' => '0'
                        ], [
                            'setting' => 'show_keyword_in_client',
                            'value' => '0'
                        ], [
                            'setting' => 'opt_in_sms_keyword',
                            'value' => 'Start,Subscribe,Unstop,Yes'
                        ], [
                            'setting' => 'opt_out_sms_keyword',
                            'value' => 'Stop,Unsubscribe,Stop,No,Quit,Cancel'
                        ], [
                            'setting' => 'custom_gateway_response_status',
                            'value' => 'OK,Success,Deliver,message_pending,accept,1701'
                        ], [
                            'setting' => 'unsubscribe_message',
                            'value' => 'Reply STOP to be removed'
                        ]
                    ];

                    foreach ($app_config as $config) {
                        AppConfig::create($config);
                    }

                    $new_sms_gateways = [
                        [
                            'name' => 'Textme',
                            'settings' => 'Textme',
                            'api_link' => 'https://my.textme.co.il/api',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Mailjet',
                            'settings' => 'Mailjet',
                            'api_link' => 'https://api.mailjet.com/v4/sms-send',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Bulksmsgateway',
                            'settings' => 'Bulksmsgateway',
                            'api_link' => 'http://bulksmsgateway.co.in/SMS_API/sendsms.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Alaris',
                            'settings' => 'Alaris',
                            'api_link' => 'https://api.passport.mgage.com',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Ejoin',
                            'settings' => 'Ejoin',
                            'api_link' => 'Host_IP_Address',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Mobitel',
                            'settings' => 'Mobitel',
                            'api_link' => 'http://smeapps.mobitel.lk:8585/EnterpriseSMS/EnterpriseSMSWS.wsdl',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'OpenVox',
                            'settings' => 'OpenVox',
                            'api_link' => 'IP_ADDRESS:PORT',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Smsgatewayhub',
                            'settings' => 'Smsgatewayhub',
                            'api_link' => 'http://login.smsgatewayhub.com/api/mt/SendSMS',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Ayyildiz',
                            'settings' => 'Ayyildiz',
                            'api_link' => 'http://sms.ayyildiz.net/SendSmsMany.aspx',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'BulkGate',
                            'settings' => 'BulkGate',
                            'api_link' => 'https://portal.bulkgate.com/api/1.0/simple/transactional',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'MaskSMS',
                            'settings' => 'MaskSMS',
                            'api_link' => 'https://mask-sms.com/masksms/sms/api',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'EblogUs',
                            'settings' => 'EblogUs',
                            'api_link' => 'http://www.eblog.us/sms/c23273833/api.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'TwilioCopilot',
                            'settings' => 'TwilioCopilot',
                            'api_link' => '',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Connectmedia',
                            'settings' => 'Connectmedia',
                            'api_link' => 'https://www.connectmedia.co.ke/user-board/?api',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'WhatsApp By Chat API',
                            'settings' => 'WhatsAppChatApi',
                            'api_link' => 'https://eu8.chat-api.com/instance105654',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'Yes',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Evyapan',
                            'settings' => 'Evyapan',
                            'api_link' => 'gw.barabut.com',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'BudgetSMS',
                            'settings' => 'BudgetSMS',
                            'api_link' => 'https://api.budgetsms.net/sendsms/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'EasySendSMS',
                            'settings' => 'EasySendSMS',
                            'api_link' => 'https://www.easysendsms.com/sms/bulksms-api/bulksms-api',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Diamondcard',
                            'settings' => 'Diamondcard',
                            'api_link' => 'http://sms.diamondcard.us/doc/sms-api.wsdl',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'ClickSend',
                            'settings' => 'ClickSend',
                            'api_link' => 'https://api-mapper.clicksend.com/http/v2/send.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Gatewayapi',
                            'settings' => 'Gatewayapi',
                            'api_link' => 'https://gatewayapi.com/rest/mtsms',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'Evyapan',
                            'settings' => 'Evyapan',
                            'api_link' => 'gw.barabut.com',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'BudgetSMS',
                            'settings' => 'BudgetSMS',
                            'api_link' => 'https://api.budgetsms.net/sendsms/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'EasySendSMS',
                            'settings' => 'EasySendSMS',
                            'api_link' => 'https://www.easysendsms.com/sms/bulksms-api/bulksms-api',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Diamondcard',
                            'settings' => 'Diamondcard',
                            'api_link' => 'http://sms.diamondcard.us/doc/sms-api.wsdl',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'ClickSend',
                            'settings' => 'ClickSend',
                            'api_link' => 'https://api-mapper.clicksend.com/http/v2/send.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                        [
                            'name' => 'Gatewayapi',
                            'settings' => 'Gatewayapi',
                            'api_link' => 'https://gatewayapi.com/rest/mtsms',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'CoinSMS',
                            'settings' => 'CoinSMS',
                            'api_link' => 'http://coinsms.net/smsapi.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'MessageWhiz',
                            'settings' => 'MessageWhiz',
                            'api_link' => 'http://smartmessaging.mmdsmart.com/api',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'Futureland',
                            'settings' => 'Futureland',
                            'api_link' => 'https://www.futureland.it/gateway/futuresend.asp',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'GlobalSMS',
                            'settings' => 'GlobalSMS',
                            'api_link' => 'http://78.46.17.110/app/smsapi/index.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'LRTelecom',
                            'settings' => 'LRTelecom',
                            'api_link' => 'https://sms.lrt.com.pk/api/sms-single-or-bulk-api.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'AccessYou',
                            'settings' => 'AccessYou',
                            'api_link' => 'http://api.accessyou.com/sms/sendsms-utf8.php',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'Montnets',
                            'settings' => 'Montnets',
                            'api_link' => 'http://ip:port/sms/v2/std/send_single',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'ShotBulkSMS',
                            'settings' => 'ShotBulkSMS',
                            'api_link' => 'http://ip:port/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'PlivoPowerpack',
                            'settings' => 'PlivoPowerpack',
                            'api_link' => 'https://api.plivo.com/v1/Account/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'KarixIO',
                            'settings' => 'KarixIO',
                            'api_link' => 'https://api.karix.io/message/',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'ElasticEmail',
                            'settings' => 'ElasticEmail',
                            'api_link' => 'https://api.elasticemail.com/v2/sms/send',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'OnnorokomSMS',
                            'settings' => 'OnnorokomSMS',
                            'api_link' => 'https://api2.onnorokomsms.com/HttpSendSms.ashx',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'PowerSMS',
                            'settings' => 'PowerSMS',
                            'api_link' => 'http://powersms.banglaphone.net.bd/httpapi/sendsms',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'Ovh',
                            'settings' => 'Ovh',
                            'api_link' => '',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => '46ELKS',
                            'settings' => '46ELKS',
                            'api_link' => 'https://api.46elks.com/a1',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'Yes',
                            'mms' => 'Yes',
                            'voice' => 'No'
                        ], [
                            'name' => 'Send99',
                            'settings' => 'Send99',
                            'api_link' => 'http://api.send99.com/api/SendSMS',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ], [
                            'name' => 'ChikaCampaign',
                            'settings' => 'ChikaCampaign',
                            'api_link' => 'http://api.chikacampaign.com/sms/1/text/single',
                            'type' => 'http',
                            'status' => 'Inactive',
                            'two_way' => 'No',
                            'mms' => 'No',
                            'voice' => 'No'
                        ],
                    ];

                    foreach ($new_sms_gateways as $n_gateway) {
                        $exist = SMSGateways::where('settings', $n_gateway)->first();
                        if (!$exist) {
                            SMSGateways::create($n_gateway);
                        }
                    }

                    $language = Language::select('id')->get();

                    foreach ($language as $l) {
                        $lan_id = $l->id;
                        $lan    = [
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
                            ], [
                                'lan_id' => $lan_id,
                                'lan_data' => 'Arabic',
                                'lan_value' => 'Arabic'
                            ],
                            [
                                'lan_id' => $lan_id,
                                'lan_data' => 'Original Name',
                                'lan_value' => 'Original Name'
                            ],
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
                        foreach ($lan as $d) {
                            LanguageData::create($d);
                        }
                    }

                    $schedule_sms = ScheduleSMS::all()->toArray();

                    if (isset($schedule_sms) && is_array($schedule_sms) && count($schedule_sms) > 0) {

                        $groupedItems = array();

                        foreach ($schedule_sms as $item) {
                            $groupedItems[$item['userid']][] = $item;
                        }

                        $groupedItems = array_values($groupedItems);

                        $campaign      = [];
                        $campaign_item = [];
                        foreach ($groupedItems as $key => $groupedItem) {
                            $campaign[$key] = [
                                'campaign_id' => uniqid('C'),
                                'user_id' => $groupedItem[0]['userid'],
                                'sender' => $groupedItem[0]['sender'],
                                'sms_type' => $groupedItem[0]['type'],
                                'camp_type' => 'scheduled',
                                'status' => 'Scheduled',
                                'use_gateway' => $groupedItem[0]['use_gateway'],
                                'total_recipient' => count($groupedItem),
                                'run_at' => date('Y-m-d H:i:s'),
                                'media_url' => $groupedItem[0]['media_url'],
                                'keyword' => null,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];

                            foreach ($groupedItem as $item) {
                                array_push($campaign_item, [
                                    'campaign_id' => $campaign[$key]['campaign_id'],
                                    'number' => $item['receiver'],
                                    'message' => $item['message'],
                                    'amount' => $item['amount'],
                                    'status' => 'queued',
                                    'submitted_time' => $item['submit_time'],
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                        }

                        Campaigns::insert($campaign);
                        CampaignSubscriptionList::insert($campaign_item);
                    }

                    AppConfig::where('setting', '=', 'currency_decimal_digits')->update(['value' => '0']);
                    AppConfig::where('setting', '=', 'SoftwareVersion')->update(['value' => '2.7']);

                    $timeZoneSetting = "\n" .
                        'APP_TYPE=installed' .
                        "\n";
                    // @ignoreCodingStandard
                    $env        = file_get_contents(base_path('.env'));
                    $rows       = explode("\n", $env);
                    $unwanted   = "APP_TYPE";
                    $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

                    $cleanString = implode("\n", $cleanArray);
                    $env         = $cleanString . $timeZoneSetting;

                    file_put_contents(base_path('.env'), $env);


                    $server_gateway = app_config('Gateway');
                    $app_name       = app_config('AppName');

                    if ($server_gateway == 'smtp') {
                        $eg = 'smtp';

                        $host_name = app_config('SMTPHostName');
                        $user_name = app_config('SMTPUserName');
                        $password  = app_config('SMTPPassword');
                        $port      = app_config('SMTPPort');
                        $secure    = app_config('SMTPSecure');

                        $smtpSetting = 'MAIL_DRIVER=' . $eg . '
MAIL_HOST=' . $host_name . '
MAIL_PORT=' . $port . '
MAIL_USERNAME=' . $user_name . '
MAIL_PASSWORD=' . $password . '
MAIL_ENCRYPTION=' . $secure . '
APP_NAME="' . $app_name . '"
';
                        // @ignoreCodingStandard
                        $env        = file_get_contents(base_path('.env'));
                        $rows       = explode("\n", $env);
                        $unwanted   = "MAIL_DRIVER|MAIL_HOST|MAIL_PORT|MAIL_USERNAME|MAIL_PASSWORD|MAIL_ENCRYPTION|APP_NAME";
                        $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

                        $cleanString = implode("\n", $cleanArray);
                        $env         = $cleanString . $smtpSetting;
                    } else {
                        $eg          = 'sendmail';
                        $smtpSetting = 'MAIL_DRIVER=' . $eg . '
APP_NAME="' . $app_name . '"
';
                        // @ignoreCodingStandard
                        $env        = file_get_contents(base_path('.env'));
                        $rows       = explode("\n", $env);
                        $unwanted   = "MAIL_DRIVER|APP_NAME";
                        $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

                        $cleanString = implode("\n", $cleanArray);
                        $env         = $cleanString . $smtpSetting;
                    }

                    file_put_contents(base_path('.env'), $env);

                    $message = 'Congratulation!! Application updated to Version 2.7. Now your are using latest version. Refresh your page to back your application login';


                } catch (Exception $e) {
                    return $e->getMessage();
                }

                break;

            case '2.2':
                $sql = <<<EOF
ALTER TABLE `sys_clients` ADD `lan_id` INT(11) NOT NULL DEFAULT '1' AFTER `menu_open`;
ALTER TABLE `sys_clients` CHANGE `sms_gateway` `sms_gateway` TEXT NOT NULL;
ALTER TABLE `sys_clients` ADD `api_gateway` INT(11) NULL DEFAULT NULL AFTER `api_key`;
ALTER TABLE `sys_payment_gateways` ADD `custom_one` TEXT NULL DEFAULT NULL AFTER `password`;
ALTER TABLE `sys_payment_gateways` ADD `custom_two` TEXT NULL DEFAULT NULL AFTER `custom_one`;
ALTER TABLE `sys_payment_gateways` ADD `custom_three` TEXT NULL AFTER `custom_two`;
ALTER TABLE `sys_invoices` CHANGE `subtotal` `subtotal` VARCHAR(100) NOT NULL DEFAULT '0.00';
ALTER TABLE `sys_invoices` CHANGE `total` `total` VARCHAR(100) NOT NULL DEFAULT '0.00';
ALTER TABLE `sys_invoice_items` CHANGE `price` `price` VARCHAR(100) NOT NULL DEFAULT '0.00';
ALTER TABLE `sys_invoice_items` CHANGE `subtotal` `subtotal` VARCHAR(100) NOT NULL DEFAULT '0.00';
ALTER TABLE `sys_invoice_items` CHANGE `tax` `tax` VARCHAR(100) NOT NULL DEFAULT '0.00';
ALTER TABLE `sys_invoice_items` CHANGE `discount` `discount` VARCHAR(100) NOT NULL DEFAULT '0.00';
ALTER TABLE `sys_invoice_items` CHANGE `total` `total` VARCHAR(100) NOT NULL DEFAULT '0.00';

CREATE TABLE `sys_block_message` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender` varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
  `receiver` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `use_gateway` int(11) NOT NULL,
  `scheduled_time` text COLLATE utf8_unicode_ci,
  `type` enum('plain','unicode','voice','mms') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain',
  `status` enum('block','release') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'block',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `sys_block_message` ADD PRIMARY KEY (`id`);
ALTER TABLE `sys_block_message` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `sys_bulk_sms` CHANGE `type` `type` ENUM('plain','unicode','voice','mms') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain';
ALTER TABLE `sys_language` ADD `language_code` VARCHAR(5) NOT NULL DEFAULT 'en' AFTER `status`;


CREATE TABLE `sys_operator` (
  `id` int(10) UNSIGNED NOT NULL,
  `coverage_id` int(11) NOT NULL,
  `operator_name` text COLLATE utf8_unicode_ci NOT NULL,
  `operator_code` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `operator_setting` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `price` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `status` enum('active','inactive') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `sys_operator` ADD PRIMARY KEY (`id`);
ALTER TABLE `sys_operator` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE `sys_recurring_sms` (
  `id` int(10) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `sender` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `total_recipients` int(11) NOT NULL,
  `use_gateway` int(11) NOT NULL,
  `media_url` longtext COLLATE utf8_unicode_ci,
  `recurring` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `recurring_date` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('plain','unicode','voice','mms') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain',
  `status` enum('running','stop') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'running',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



CREATE TABLE `sys_recurring_sms_contacts` (
  `id` int(10) UNSIGNED NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `receiver` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8_unicode_ci,
  `amount` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `sys_recurring_sms` ADD PRIMARY KEY (`id`);
ALTER TABLE `sys_recurring_sms_contacts` ADD PRIMARY KEY (`id`);
ALTER TABLE `sys_recurring_sms` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `sys_recurring_sms_contacts` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `sys_schedule_sms` CHANGE `type` `type` ENUM('plain','unicode','voice','mms') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain';


CREATE TABLE `sys_sms_gateway_credential` (
  `id` int(10) UNSIGNED NOT NULL,
  `gateway_id` int(11) NOT NULL,
  `username` longtext COLLATE utf8_unicode_ci NOT NULL,
  `password` longtext COLLATE utf8_unicode_ci,
  `extra` longtext COLLATE utf8_unicode_ci,
  `status` enum('Active','Inactive') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Inactive',
  `c1` longtext COLLATE utf8_unicode_ci,
  `c2` longtext COLLATE utf8_unicode_ci,
  `c3` longtext COLLATE utf8_unicode_ci,
  `c4` longtext COLLATE utf8_unicode_ci,
  `c5` longtext COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `sys_sms_gateway_credential` ADD PRIMARY KEY (`id`);
ALTER TABLE `sys_sms_gateway_credential` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `sys_sms_history` ADD `sms_type` ENUM('plain','unicode','voice','mms') NOT NULL DEFAULT 'plain' AFTER `status`;
ALTER TABLE `sys_sms_history` ADD `media_url` LONGTEXT NULL DEFAULT NULL AFTER `send_by`;
ALTER TABLE `sys_sms_history` CHANGE `message` `message` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `sys_sms_history` CHANGE `amount` `amount` INT(11) NOT NULL DEFAULT '1';
ALTER TABLE `sys_schedule_sms` ADD `media_url` LONGTEXT NULL DEFAULT NULL AFTER `submit_time`;


CREATE TABLE `sys_spam_word` (
  `id` int(10) UNSIGNED NOT NULL,
  `word` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `sys_spam_word` ADD PRIMARY KEY (`id`);
ALTER TABLE `sys_spam_word` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `sys_sms_gateways` ADD `settings` VARCHAR(50) NOT NULL AFTER `name`;
ALTER TABLE `sys_sms_gateways` ADD `port` VARCHAR(20) NULL DEFAULT NULL AFTER `api_link`;
ALTER TABLE `sys_sms_gateways` ADD `mms` ENUM('Yes','No') NOT NULL DEFAULT 'No' AFTER `two_way`;
ALTER TABLE `sys_sms_gateways` ADD `voice` ENUM('Yes','No') NOT NULL DEFAULT 'No' AFTER `mms`;
EOF;

                DB::connection()->getPdo()->exec($sql);

                $app_config = [
                    [
                        'setting' => 'fraud_detection',
                        'value' => '0'
                    ], [
                        'setting' => 'dec_point',
                        'value' => '.'
                    ], [
                        'setting' => 'thousands_sep',
                        'value' => ','
                    ], [
                        'setting' => 'currency_decimal_digits',
                        'value' => true
                    ], [
                        'setting' => 'currency_symbol_position',
                        'value' => 'left'
                    ]
                ];

                foreach ($app_config as $config) {
                    AppConfig::create($config);
                }

                $sms_gateways = SMSGateways::all();

                foreach ($sms_gateways as $gateway) {
                    $status = SMSGatewayCredential::create([
                        'gateway_id' => $gateway->id,
                        'username' => $gateway->username,
                        'password' => $gateway->password,
                        'extra' => $gateway->api_id,
                        'status' => 'Active'
                    ]);

                    if ($status) {
                        $gateway->settings = $gateway->name;
                        $gateway->save();
                    }
                }

                EmailTemplates::create([
                    'tplname' => 'Spam Word Notification',
                    'subject' => 'Get spam word from {{business_name}}]',
                    'message' => '<div style="margin:0;padding:0">
<table cellspacing="0" cellpadding="0" width="100%" border="0" bgcolor="#439cc8">
  <tbody><tr>
    <td align="center">
            <table cellspacing="0" cellpadding="0" width="672" border="0">
              <tbody><tr>
                <td height="95" bgcolor="#439cc8" style="background:#439cc8;text-align:left">
                <table cellspacing="0" cellpadding="0" width="672" border="0">
                      <tbody><tr>
                        <td width="672" height="40" style="font-size:40px;line-height:40px;height:40px;text-align:left"></td>
                      </tr>
                      <tr>
                        <td style="text-align:left">
                        <table cellspacing="0" cellpadding="0" width="672" border="0">
                          <tbody><tr>
                            <td width="37" height="24" style="font-size:40px;line-height:40px;height:40px;text-align:left">
                            </td>
                            <td width="523" height="24" style="text-align:left">
                            <div width="125" height="23" style="display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto">{{business_name}}</div>
                            </td>
                            <td width="44" style="text-align:left"></td>
                            <td width="30" style="text-align:left"></td>
                            <td width="38" height="24" style="font-size:40px;line-height:40px;height:40px;text-align:left"></td>
                          </tr>
                        </tbody></table>
                        </td>
                      </tr>
                      <tr><td width="672" height="33" style="font-size:33px;line-height:33px;height:33px;text-align:left"></td></tr>
                    </tbody></table>

                </td>
              </tr>
            </tbody></table>
     </td>
    </tr>
 </tbody></table>

 <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#439cc8"><tbody><tr><td height="5" style="background:#439cc8;height:5px;font-size:5px;line-height:5px"></td></tr></tbody></table>

 <table cellspacing="0" cellpadding="0" width="100%" border="0" bgcolor="#e9eff0">
  <tbody><tr>
    <td align="center">
      <table cellspacing="0" cellpadding="0" width="671" border="0" bgcolor="#e9eff0" style="background:#e9eff0">
        <tbody><tr>
          <td width="38" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left"></td>
          <td width="596" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left"></td>
          <td width="37" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left"></td>
        </tr>
        <tr>
          <td width="38" height="40" style="font-size:40px;line-height:40px;height:40px;text-align:left"></td>
          <td style="text-align:left"><table cellspacing="0" cellpadding="0" width="596" border="0" bgcolor="#ffffff">
            <tbody><tr>
              <td width="20" height="26" style="font-size:26px;line-height:26px;height:26px;text-align:left"></td>
              <td width="556" height="26" style="font-size:26px;line-height:26px;height:26px;text-align:left"></td>
              <td width="20" height="26" style="font-size:26px;line-height:26px;height:26px;text-align:left"></td>
            </tr>
            <tr>
              <td width="20" height="26" style="font-size:26px;line-height:26px;height:26px;text-align:left"></td>
              <td width="556" style="text-align:left"><table cellspacing="0" cellpadding="0" width="556" border="0" style="font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px">
                <tbody><tr>
                  <td style="text-align:left"></td>
                </tr>
                <tr>
                  <td style="text-align:left"><table cellspacing="0" cellpadding="0" width="556" border="0">
                    <tbody><tr><td style="font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left"></td></tr>
                    <tr><td width="556" height="20" style="font-size:20px;line-height:20px;height:20px;text-align:left"></td></tr>
                    <tr>
                      <td style="text-align:left">
                 Hi,<br>
                 <br>
                 Spam word detected. Here is the message and client details:
            <br>
                User name: <a href="{{profile_link}}" target="_blank">{{user_name}}</a><br>
                Message: {{message}}<br><br>
                Waiting for your quick response.
            <br><br>
            Thank you.
            <br>
            Regards,<br>
            {{business_name}}
            <br>
          </td>
                    </tr>
                    <tr>
                      <td width="556" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left">&nbsp;</td>
                    </tr>
                  </tbody></table></td>
                </tr>
              </tbody></table></td>
              <td width="20" height="26" style="font-size:26px;line-height:26px;height:26px;text-align:left"></td>
            </tr>
            <tr>
              <td width="20" height="2" bgcolor="#d9dfe1" style="background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left"></td>
              <td width="556" height="2" bgcolor="#d9dfe1" style="background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left"></td>
              <td width="20" height="2" bgcolor="#d9dfe1" style="background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left"></td>
            </tr>
          </tbody></table></td>
          <td width="37" height="40" style="font-size:40px;line-height:40px;height:40px;text-align:left"></td>
        </tr>
        <tr>
          <td width="38" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left"></td>
          <td width="596" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left"></td>
          <td width="37" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left"></td>
        </tr>
      </tbody></table>
  </td></tr>
</tbody>
</table>
<table cellspacing="0" cellpadding="0" width="100%" border="0" bgcolor="#273f47"><tbody><tr><td align="center">&nbsp;</td></tr></tbody></table>
<table cellspacing="0" cellpadding="0" width="100%" border="0" bgcolor="#364a51">
  <tbody><tr>
    <td align="center">
       <table cellspacing="0" cellpadding="0" width="672" border="0" bgcolor="#364a51">
              <tbody><tr>
              <td width="38" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left"></td>
          <td width="569" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left"></td>
          <td width="38" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left"></td>
              </tr>
              <tr>
                <td width="38" height="40" style="font-size:40px;line-height:40px;text-align:left">
                </td>
                <td valign="top" style="font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>
                <td width="38" height="40" style="font-size:40px;line-height:40px;text-align:left"></td>
              </tr>
              <tr>
              <td width="38" height="40" style="font-size:40px;line-height:40px;text-align:left"></td>
              <td width="569" height="40" style="font-size:40px;line-height:40px;text-align:left"></td>
                <td width="38" height="40" style="font-size:40px;line-height:40px;text-align:left"></td>
              </tr>
            </tbody></table>
     </td>
  </tr>
</tbody></table><div class="yj6qo"></div><div class="adL">

</div></div>
',
                    'status' => '1'
                ]);

                $permission_id = [
                    [
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
                    ]
                ];

                foreach ($permission_id as $perm_id) {
                    AdminRolePermission::create($perm_id);
                }

                $all_clients = Client::all();

                foreach ($all_clients as $client) {
                    $exist_gateway       = $client->sms_gateway;
                    $insert_gateway      = "[\"$exist_gateway\"]";
                    $client->sms_gateway = $insert_gateway;
                    $client->save();
                }

                $sms_sql = <<<EOF
ALTER TABLE `sys_sms_gateways` DROP `username`;
ALTER TABLE `sys_sms_gateways` DROP `password`;
ALTER TABLE `sys_sms_gateways` DROP `api_id`;
EOF;
                \DB::connection()->getPdo()->exec($sms_sql);

                $new_sms_gateways = [
                    [
                        'name' => 'Tyntec',
                        'settings' => 'Tyntec',
                        'api_link' => 'https://rest.tyntec.com/sms/v1/outbound/requests',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'TobeprecisesmsSMPP',
                        'settings' => 'TobeprecisesmsSMPP',
                        'api_link' => 'IP_Address/HostName',
                        'type' => 'smpp',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Onehop',
                        'settings' => 'Onehop',
                        'api_link' => 'http://api.onehop.co/v1/sms/send/',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'TigoBeekun',
                        'settings' => 'TigoBeekun',
                        'api_link' => 'https://tigo.beekun.com/pushapi',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'MubasherSMS',
                        'settings' => 'MubasherSMS',
                        'api_link' => 'http://www.mubashersms.com/sendsms/default.aspx',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Advansystelecom',
                        'settings' => 'Advansystelecom',
                        'api_link' => 'http://www.advansystelecom.com/AdvansysBulk/Message_Request.aspx',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Beepsend',
                        'settings' => 'Beepsend',
                        'api_link' => 'https://api.beepsend.com/2/send',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Toplusms',
                        'settings' => 'Toplusms',
                        'api_link' => 'http://www.toplusms.com.tr/api/mesaj_gonder',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'AlertSMS',
                        'settings' => 'AlertSMS',
                        'api_link' => 'http://client.alertsms.ro/api/v2',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Easy',
                        'settings' => 'Easy',
                        'api_link' => 'http://app.easy.com.np/easyApi',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Clxnetworks',
                        'settings' => 'Clxnetworks',
                        'api_link' => 'http://sms1.clxnetworks.net:3800/sendsms',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Textmarketer',
                        'settings' => 'Textmarketer',
                        'api_link' => 'https://api.textmarketer.co.uk/gateway/',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Bhashsms',
                        'settings' => 'Bhashsms',
                        'api_link' => 'http://bhashsms.com/api/sendmsg.php',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'KingTelecom',
                        'settings' => 'KingTelecom',
                        'api_link' => 'http://sms.kingtelecom.com.br/kingsms/api.php',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Diafaan',
                        'settings' => 'Diafaan',
                        'api_link' => 'https://127.0.0.1:8080',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'Yes',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Smsmisr',
                        'settings' => 'Smsmisr',
                        'api_link' => 'https://www.smsmisr.com/api/send',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ],
                    [
                        'name' => 'Broadnet',
                        'settings' => 'Broadnet',
                        'api_link' => 'http://104.156.253.108:8008/websmpp',
                        'type' => 'http',
                        'status' => 'Inactive',
                        'two_way' => 'No',
                        'mms' => 'No',
                        'voice' => 'No'
                    ]
                ];

                foreach ($new_sms_gateways as $n_gateway) {
                    $exist = SMSGateways::where('settings', $n_gateway)->first();
                    if (!$exist) {
                        SMSGateways::create($n_gateway);
                    }
                }

                SMSGateways::where('settings', 'Twilio')->update(['mms' => 'Yes', 'voice' => 'Yes']);
                SMSGateways::where('settings', 'Text Local')->update(['mms' => 'Yes']);
                SMSGateways::where('settings', 'Plivo')->update(['voice' => 'Yes']);
                SMSGateways::where('settings', 'SMSGlobal')->update(['mms' => 'Yes']);
                SMSGateways::where('settings', 'Nexmo')->update(['voice' => 'Yes']);
                SMSGateways::where('settings', 'InfoBip')->update(['voice' => 'Yes']);
                SMSGateways::where('settings', 'MessageBird')->update(['mms' => 'Yes', 'voice' => 'Yes']);

                $payment_gateways = [
                    [
                        'name' => 'WebXPay',
                        'value' => 'secret_key',
                        'settings' => 'webxpay',
                        'extra_value' => 'public_key',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'CoinPayments',
                        'value' => 'merchant_id',
                        'settings' => 'coinpayments',
                        'extra_value' => 'Ipn_secret',
                        'status' => 'Active',
                    ],
                ];

                foreach ($payment_gateways as $p_gateway) {
                    PaymentGateways::create($p_gateway);

                }

                $language = Language::select('id')->get();

                foreach ($language as $l) {
                    $lan_id = $l->id;
                    $lan    = [
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
                            'lan_data' => 'Currency Code',
                            'lan_value' => 'Currency Code'
                        ], [
                            'lan_id' => $lan_id,
                            'lan_data' => 'Currency Symbol',
                            'lan_value' => 'Currency Symbol'
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
                        ]
                    ];
                    foreach ($lan as $d) {
                        LanguageData::create($d);
                    }
                }

                AppConfig::where('setting', '=', 'SoftwareVersion')->update(['value' => '2.3']);

                $message = 'Congratulation!! Application updated to Version 2.3. But current version is 2.7. Please click update button again for latest version';

                break;

            case '2.0':
                $sql = <<<EOF
ALTER TABLE `sys_sms_history` CHANGE `send_by` `send_by` ENUM('receiver','sender','api') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `sys_clients` CHANGE `sms_limit` `sms_limit` VARCHAR(11) NOT NULL DEFAULT '0';
ALTER TABLE `sys_sms_gateways` ADD `type` ENUM('http','smpp') NOT NULL DEFAULT 'http' AFTER `custom`;
ALTER TABLE `sys_schedule_sms` CHANGE `original_msg` `message` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `sys_schedule_sms` DROP `encrypt_msg`;
ALTER TABLE `sys_schedule_sms` DROP `ip`;
ALTER TABLE `sys_schedule_sms` ADD `type` ENUM('plain','unicode') NOT NULL DEFAULT 'plain' AFTER `message`;
ALTER TABLE `sys_bulk_sms` ADD `type` ENUM('plain','unicode') NOT NULL DEFAULT 'plain' AFTER `use_gateway`;
INSERT INTO `sys_app_config` (`id`, `setting`, `value`) VALUES (NULL, 'license_type', '');
EOF;
                \DB::connection()->getPdo()->exec($sql);

                PaymentGateways::create([
                    'name' => 'Pagopar',
                    'value' => 'public_key',
                    'settings' => 'pagopar',
                    'extra_value' => 'private_key',
                    'status' => 'Active',
                ]);

                $gateways = [
                    [
                        'name' => 'Smsconnexion',
                        'api_link' => 'http://smsc.smsconnexion.com/api/gateway.aspx',
                        'username' => 'username',
                        'password' => 'passphrase',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'BrandedSMS',
                        'api_link' => 'http://www.brandedsms.net//api/sms-api.php',
                        'username' => 'username',
                        'password' => 'password',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'Ibrbd',
                        'api_link' => 'http://wdgw.ibrbd.net:8080/bagaduli/apigiso/sender.php',
                        'username' => 'username',
                        'password' => 'password',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'TxtNation',
                        'api_link' => 'http://client.txtnation.com/gateway.php',
                        'username' => 'company',
                        'password' => 'ekey',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'TeleSign',
                        'api_link' => '',
                        'username' => 'Customer ID',
                        'password' => 'API_Key',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'JasminSMS',
                        'api_link' => 'http://127.0.0.1',
                        'username' => 'foo',
                        'password' => 'bar',
                        'api_id' => '1401',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'Ezeee',
                        'api_link' => 'http://my.ezeee.pk/sendsms_url.html',
                        'username' => 'user_name',
                        'password' => 'password',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ], [
                        'name' => 'InfoBipSMPP',
                        'api_link' => 'smpp3.infobip.com',
                        'username' => 'system_id',
                        'password' => 'password',
                        'api_id' => '8888',
                        'type' => 'smpp',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'SMSGlobalSMPP',
                        'api_link' => 'smpp.smsglobal.com',
                        'username' => 'system_id',
                        'password' => 'password',
                        'api_id' => '1775',
                        'type' => 'smpp',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'ClickatellSMPP',
                        'api_link' => 'smpp.clickatell.com',
                        'username' => 'system_id',
                        'password' => 'password',
                        'api_id' => '2775',
                        'type' => 'smpp',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'JasminSmsSMPP',
                        'api_link' => 'host_name',
                        'username' => 'system_id',
                        'password' => 'password',
                        'api_id' => 'port',
                        'type' => 'smpp',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'WavecellSMPP',
                        'api_link' => 'smpp.wavecell.com',
                        'username' => 'system_id',
                        'password' => 'password',
                        'api_id' => '2775',
                        'type' => 'smpp',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'Moreify',
                        'api_link' => 'https://mapi.moreify.com/api/v1/sendSms',
                        'username' => 'project_id',
                        'password' => 'your_token',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'Digitalreachapi',
                        'api_link' => 'https://digitalreachapi.dialog.lk/camp_req.php',
                        'username' => 'user_name',
                        'password' => 'password',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'Tropo',
                        'api_link' => 'https://api.tropo.com/1.0/sessions',
                        'username' => 'api_token',
                        'password' => '',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'CheapSMS',
                        'api_link' => 'http://198.24.149.4/API/pushsms.aspx',
                        'username' => 'loginID',
                        'password' => 'password',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'CCSSMS',
                        'api_link' => 'http://193.58.235.30:8001/api',
                        'username' => 'Username',
                        'password' => 'Password',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'MyCoolSMS',
                        'api_link' => 'http://www.my-cool-sms.com/api-socket.php',
                        'username' => 'Username',
                        'password' => 'Password',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'SmsBump',
                        'api_link' => 'https://api.smsbump.com/send',
                        'username' => 'API_KEY',
                        'password' => '',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'BSG',
                        'api_link' => '',
                        'username' => 'API_KEY',
                        'password' => '',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'SmsBroadcast',
                        'api_link' => 'https://api.smsbroadcast.co.uk/api-adv.php',
                        'username' => 'username',
                        'password' => 'password',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'BullSMS',
                        'api_link' => 'http://portal.bullsms.com/vendorsms/pushsms.aspx',
                        'username' => 'user',
                        'password' => 'password',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ],
                    [
                        'name' => 'Skebby',
                        'api_link' => 'https://api.skebby.it/API/v1.0/REST/sms',
                        'username' => 'User_key',
                        'password' => 'Access_Token',
                        'api_id' => '',
                        'type' => 'http',
                        'two_way' => 'No'
                    ]
                ];

                foreach ($gateways as $g) {
                    $exist = SMSGateways::where('name', $g)->first();
                    if (!$exist) {
                        SMSGateways::create($g);
                    }
                }


                $language = Language::select('id')->get();

                foreach ($language as $l) {
                    $lan_id = $l->id;
                    $lan    = [
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
                        ]
                    ];
                    foreach ($lan as $d) {
                        LanguageData::create($d);
                    }
                }

                AppConfig::where('setting', '=', 'SoftwareVersion')->update(['value' => '2.2']);
                AppConfig::where('setting', '=', 'purchase_key')->update(['value' => $purchase_code]);
                AppConfig::where('setting', '=', 'purchase_code_error_count')->update(['value' => 0]);
                AppConfig::where('setting', '=', 'license_type')->update(['value' => $data['license_type']]);
                AppConfig::where('setting', '=', 'valid_domain')->update(['value' => 'yes']);

                $message = 'Congratulation!! Application updated to Version 2.2. But current version is 2.7. Please click update button again for latest version';

                break;

            case '1.1':
            case '1.2':
            case '1.5':
            case 'default':
                $message = 'Please contact with application author. Author email address: akasham67@gmail.com';
                break;

        }

        return redirect('update')->with([
            'message' => $message
        ]);


    }


    //======================================================================
    // verifyProductUpdate Function Start Here
    //======================================================================
    public function verifyProductUpdate()
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('/')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if (app_config('SoftwareVersion') == '2.8') {
            return redirect('/')->with([
                'message' => 'You are already in latest version'
            ]);
        }

        return view('admin.verify-product-update', compact('file'));
    }

    //======================================================================
    // checkAvailableUpdate Function Start Here
    //======================================================================
    public function checkAvailableUpdate()
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('admin/update-application')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $app_version      = app_config('SoftwareVersion');
        $data = '2.8';

        if ($app_version == $data) {
            return redirect('admin/update-application')->with([
                'message' => 'You are using latest version'
            ]);
        }

        return redirect('admin/update-application')->with([
            'message' => 'Version ' . $data . ' released. Please download with update file from Envato marketplace and follow the update blog.'
        ]);

    }


}
