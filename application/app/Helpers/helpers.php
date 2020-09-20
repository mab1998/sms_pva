<?php

use App\AppConfig;
use App\Client;
use App\Admin;
use App\AdminRolePermission;
use App\SupportTickets;
use App\Invoices;
use App\LanguageData;
use App\Language;
use App\SMSGateways;

function app_config($value = '')
{
    $conf = AppConfig::where('setting', '=', $value)->first();
    return $conf->value;
}

function language_data($value = '', $lan_id = null)
{
    if ($lan_id === null) {
        $lan_id = app_config('Language');
    }
    $lan_info = LanguageData::where('lan_id', $lan_id)->where('lan_data', $value)->first();

    if ($lan_info){
        return $lan_info->lan_value;
    }else{
        return null;
    }

}

function get_language()
{
    return Language::where('status', 'Active')->get();
}

function get_language_code($lan_id = null)
{
    if ($lan_id === null) {
        $lan_id = app_config('Language');
    }
    return Language::where('status', 'Active')->find($lan_id);
}


function get_mysql_date($date = '')
{
    return date('d', strtotime($date));
}

function countries($default = '')
{
    $countries = '
                                    <option value="Afganistan">Afghanistan</option>
                                    <option value="Albania">Albania</option>
                                    <option value="Algeria">Algeria</option>
                                    <option value="American Samoa">American Samoa</option>
                                    <option value="Andorra">Andorra</option>
                                    <option value="Angola">Angola</option>
                                    <option value="Anguilla">Anguilla</option>
                                    <option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Armenia">Armenia</option>
                                    <option value="Aruba">Aruba</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Austria">Austria</option>
                                    <option value="Azerbaijan">Azerbaijan</option>
                                    <option value="Bahamas">Bahamas</option>
                                    <option value="Bahrain">Bahrain</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Barbados">Barbados</option>
                                    <option value="Belarus">Belarus</option>
                                    <option value="Belgium">Belgium</option>
                                    <option value="Belize">Belize</option>
                                    <option value="Benin">Benin</option>
                                    <option value="Bermuda">Bermuda</option>
                                    <option value="Bhutan">Bhutan</option>
                                    <option value="Bolivia">Bolivia</option>
                                    <option value="Bonaire">Bonaire</option>
                                    <option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option>
                                    <option value="Botswana">Botswana</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="British Indian Ocean Ter">British Indian Ocean Ter</option>
                                    <option value="Brunei">Brunei</option>
                                    <option value="Bulgaria">Bulgaria</option>
                                    <option value="Burkina Faso">Burkina Faso</option>
                                    <option value="Burundi">Burundi</option>
                                    <option value="Cambodia">Cambodia</option>
                                    <option value="Cameroon">Cameroon</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Canary Islands">Canary Islands</option>
                                    <option value="Cape Verde">Cape Verde</option>
                                    <option value="Cayman Islands">Cayman Islands</option>
                                    <option value="Central African Republic">Central African Republic</option>
                                    <option value="Chad">Chad</option>
                                    <option value="Channel Islands">Channel Islands</option>
                                    <option value="Chile">Chile</option>
                                    <option value="China">China</option>
                                    <option value="Christmas Island">Christmas Island</option>
                                    <option value="Cocos Island">Cocos Island</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Comoros">Comoros</option>
                                    <option value="Congo">Congo</option>
                                    <option value="Cook Islands">Cook Islands</option>
                                    <option value="Costa Rica">Costa Rica</option>
                                    <option value="Cote DIvoire">Cote DIvoire</option>
                                    <option value="Croatia">Croatia</option>
                                    <option value="Cuba">Cuba</option>
                                    <option value="Curaco">Curacao</option>
                                    <option value="Cyprus">Cyprus</option>
                                    <option value="Czech Republic">Czech Republic</option>
                                    <option value="Denmark">Denmark</option>
                                    <option value="Djibouti">Djibouti</option>
                                    <option value="Dominica">Dominica</option>
                                    <option value="Dominican Republic">Dominican Republic</option>
                                    <option value="East Timor">East Timor</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="Egypt">Egypt</option>
                                    <option value="El Salvador">El Salvador</option>
                                    <option value="Equatorial Guinea">Equatorial Guinea</option>
                                    <option value="Eritrea">Eritrea</option>
                                    <option value="Estonia">Estonia</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Falkland Islands">Falkland Islands</option>
                                    <option value="Faroe Islands">Faroe Islands</option>
                                    <option value="Fiji">Fiji</option>
                                    <option value="Finland">Finland</option>
                                    <option value="France">France</option>
                                    <option value="French Guiana">French Guiana</option>
                                    <option value="French Polynesia">French Polynesia</option>
                                    <option value="French Southern Ter">French Southern Ter</option>
                                    <option value="Gabon">Gabon</option>
                                    <option value="Gambia">Gambia</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Gibraltar">Gibraltar</option>
                                    <option value="Great Britain">Great Britain</option>
                                    <option value="Greece">Greece</option>
                                    <option value="Greenland">Greenland</option>
                                    <option value="Grenada">Grenada</option>
                                    <option value="Guadeloupe">Guadeloupe</option>
                                    <option value="Guam">Guam</option>
                                    <option value="Guatemala">Guatemala</option>
                                    <option value="Guinea">Guinea</option>
                                    <option value="Guyana">Guyana</option>
                                    <option value="Haiti">Haiti</option>
                                    <option value="Hawaii">Hawaii</option>
                                    <option value="Honduras">Honduras</option>
                                    <option value="Hong Kong">Hong Kong</option>
                                    <option value="Hungary">Hungary</option>
                                    <option value="Iceland">Iceland</option>
                                    <option value="India">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Iran">Iran</option>
                                    <option value="Iraq">Iraq</option>
                                    <option value="Ireland">Ireland</option>
                                    <option value="Isle of Man">Isle of Man</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Jamaica">Jamaica</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Jordan">Jordan</option>
                                    <option value="Kazakhstan">Kazakhstan</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Kiribati">Kiribati</option>
                                    <option value="Korea North">Korea North</option>
                                    <option value="Korea Sout">Korea South</option>
                                    <option value="Kuwait">Kuwait</option>
                                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                                    <option value="Laos">Laos</option>
                                    <option value="Latvia">Latvia</option>
                                    <option value="Lebanon">Lebanon</option>
                                    <option value="Lesotho">Lesotho</option>
                                    <option value="Liberia">Liberia</option>
                                    <option value="Libya">Libya</option>
                                    <option value="Liechtenstein">Liechtenstein</option>
                                    <option value="Lithuania">Lithuania</option>
                                    <option value="Luxembourg">Luxembourg</option>
                                    <option value="Macau">Macau</option>
                                    <option value="Macedonia">Macedonia</option>
                                    <option value="Madagascar">Madagascar</option>
                                    <option value="Malaysia">Malaysia</option>
                                    <option value="Malawi">Malawi</option>
                                    <option value="Maldives">Maldives</option>
                                    <option value="Mali">Mali</option>
                                    <option value="Malta">Malta</option>
                                    <option value="Marshall Islands">Marshall Islands</option>
                                    <option value="Martinique">Martinique</option>
                                    <option value="Mauritania">Mauritania</option>
                                    <option value="Mauritius">Mauritius</option>
                                    <option value="Mayotte">Mayotte</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Midway Islands">Midway Islands</option>
                                    <option value="Moldova">Moldova</option>
                                    <option value="Monaco">Monaco</option>
                                    <option value="Mongolia">Mongolia</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Morocco">Morocco</option>
                                    <option value="Mozambique">Mozambique</option>
                                    <option value="Myanmar">Myanmar</option>
                                    <option value="Nambia">Nambia</option>
                                    <option value="Nauru">Nauru</option>
                                    <option value="Nepal">Nepal</option>
                                    <option value="Netherland Antilles">Netherland Antilles</option>
                                    <option value="Netherlands">Netherlands (Holland, Europe)</option>
                                    <option value="Nevis">Nevis</option>
                                    <option value="New Caledonia">New Caledonia</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="Nicaragua">Nicaragua</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="Niue">Niue</option>
                                    <option value="Norfolk Island">Norfolk Island</option>
                                    <option value="Norway">Norway</option>
                                    <option value="Oman">Oman</option>
                                    <option value="Pakistan">Pakistan</option>
                                    <option value="Palau Island">Palau Island</option>
                                    <option value="Palestine">Palestine</option>
                                    <option value="Panama">Panama</option>
                                    <option value="Papua New Guinea">Papua New Guinea</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Peru">Peru</option>
                                    <option value="Phillipines">Philippines</option>
                                    <option value="Pitcairn Island">Pitcairn Island</option>
                                    <option value="Poland">Poland</option>
                                    <option value="Portugal">Portugal</option>
                                    <option value="Puerto Rico">Puerto Rico</option>
                                    <option value="Qatar">Qatar</option>
                                    <option value="Republic of Montenegro">Republic of Montenegro</option>
                                    <option value="Republic of Serbia">Republic of Serbia</option>
                                    <option value="Reunion">Reunion</option>
                                    <option value="Romania">Romania</option>
                                    <option value="Russia">Russia</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="St Barthelemy">St Barthelemy</option>
                                    <option value="St Eustatius">St Eustatius</option>
                                    <option value="St Helena">St Helena</option>
                                    <option value="St Kitts-Nevis">St Kitts-Nevis</option>
                                    <option value="St Lucia">St Lucia</option>
                                    <option value="St Maarten">St Maarten</option>
                                    <option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option>
                                    <option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option>
                                    <option value="Saipan">Saipan</option>
                                    <option value="Samoa">Samoa</option>
                                    <option value="Samoa American">Samoa American</option>
                                    <option value="San Marino">San Marino</option>
                                    <option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option>
                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                    <option value="Senegal">Senegal</option>
                                    <option value="Serbia">Serbia</option>
                                    <option value="Seychelles">Seychelles</option>
                                    <option value="Sierra Leone">Sierra Leone</option>
                                    <option value="Singapore">Singapore</option>
                                    <option value="Slovakia">Slovakia</option>
                                    <option value="Slovenia">Slovenia</option>
                                    <option value="Solomon Islands">Solomon Islands</option>
                                    <option value="Somalia">Somalia</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="Spain">Spain</option>
                                    <option value="Sri Lanka">Sri Lanka</option>
                                    <option value="Sudan">Sudan</option>
                                    <option value="Suriname">Suriname</option>
                                    <option value="Swaziland">Swaziland</option>
                                    <option value="Sweden">Sweden</option>
                                    <option value="Switzerland">Switzerland</option>
                                    <option value="Syria">Syria</option>
                                    <option value="Tahiti">Tahiti</option>
                                    <option value="Taiwan">Taiwan</option>
                                    <option value="Tajikistan">Tajikistan</option>
                                    <option value="Tanzania">Tanzania</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Togo">Togo</option>
                                    <option value="Tokelau">Tokelau</option>
                                    <option value="Tonga">Tonga</option>
                                    <option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option>
                                    <option value="Tunisia">Tunisia</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="Turkmenistan">Turkmenistan</option>
                                    <option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option>
                                    <option value="Tuvalu">Tuvalu</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Ukraine">Ukraine</option>
                                    <option value="United Arab Erimates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States of America">United States of America</option>
                                    <option value="Uraguay">Uruguay</option>
                                    <option value="Uzbekistan">Uzbekistan</option>
                                    <option value="Vanuatu">Vanuatu</option>
                                    <option value="Vatican City State">Vatican City State</option>
                                    <option value="Venezuela">Venezuela</option>
                                    <option value="Vietnam">Vietnam</option>
                                    <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option>
                                    <option value="Virgin Islands (USA)">Virgin Islands (USA)</option>
                                    <option value="Wake Island">Wake Island</option>
                                    <option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option>
                                    <option value="Yemen">Yemen</option>
                                    <option value="Zaire">Zaire</option>
                                    <option value="Zambia">Zambia</option>
                                    <option value="Zimbabwe">Zimbabwe</option>
                                    ';
    $default   = '"' . $default . '"';


    $countries = str_replace($default, $default . '  selected="selected"', $countries);

    return $countries;
}


function view_id()
{
    $url      = Request::url();
    $url_path = parse_url($url, PHP_URL_PATH);
    $basename = pathinfo($url_path, PATHINFO_BASENAME);

    return $basename;
}


function _render($template, $data)
{

    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }

    return $template;
}


function _raid($l)
{
    return substr(str_shuffle(str_repeat('0123456789', $l)), 0, $l);
}

function timezoneList()
{
    $timezoneIdentifiers = DateTimeZone::listIdentifiers();
    $utcTime             = new DateTime('now', new DateTimeZone('UTC'));

    $tempTimezones = array();
    foreach ($timezoneIdentifiers as $timezoneIdentifier) {
        $currentTimezone = new DateTimeZone($timezoneIdentifier);

        $tempTimezones[] = array(
            'offset' => (int)$currentTimezone->getOffset($utcTime),
            'identifier' => $timezoneIdentifier
        );
    }
    usort($tempTimezones, function ($a, $b) {
        return ($a['offset'] == $b['offset'])
            ? strcmp($a['identifier'], $b['identifier'])
            : $a['offset'] - $b['offset'];
    });

    $timezoneList = array();
    foreach ($tempTimezones as $tz) {
        $sign                            = ($tz['offset'] > 0) ? '+' : '-';
        $offset                          = gmdate('H:i', abs($tz['offset']));
        $timezoneList[$tz['identifier']] = '(UTC ' . $sign . $offset . ') ' .
            $tz['identifier'];
    }

    return $timezoneList;
}

function yearDropdown($startYear, $endYear, $id = "year", $selected = "")
{
    //start the select tag
    echo "<select class='form-control selectpicker' data-live-search='true'  name=" . $id . ">";

    //echo each year as an option
    for ($i = $endYear; $i >= $startYear; $i--) {
        if ($i == $selected) {
            $selected_year = 'selected';
        } else {
            $selected_year = '';
        }
        echo "<option value=" . $i . " " . $selected_year . " >" . $i . "</option>";
    }

    //close the select tag
    echo "</select>";
}


function get_date_format($date = '')
{
    return date(app_config('DateFormat'), strtotime($date));
}

function in_array_r($needle, $haystack, $strict = false)
{
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

function array_empty($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $value) {
            if (!array_empty($value)) {
                return false;
            }
        }
    } elseif (!empty($mixed)) {
        return false;
    }
    return true;
}

function client_info($id = '')
{
    $client = Client::find($id);

    return $client;
}

function admin_info($id = '')
{
    $admin = Admin::find($id);

    return $admin;
}

/*For Permission*/

function permission($role_id, $perm_id)
{

    $permcheck = AdminRolePermission::where('role_id', $role_id)->where('perm_id', $perm_id)->first();

    if ($permcheck == NULL) {
        return false;
    } else {
        if (!$permcheck->perm_id > 0) {
            return false;
        }
        return true;
    }

}

function latest_five_tickets($user_id = 0)
{

    if ($user_id == 0) {
        return SupportTickets::where('status', 'Pending')->orderBy('created_at', 'desc')->take(5)->get();
    } else {
        return SupportTickets::where('status', 'Pending')->orderBy('created_at', 'desc')->where('cl_id', $user_id)->take(5)->get();
    }

}

function latest_five_invoices($user_id = 0)
{

    if ($user_id == 0) {
        return Invoices::where('status', 'Unpaid')->orderBy('created_at', 'desc')->take(5)->get();
    } else {
        return Invoices::where('status', 'Unpaid')->orderBy('created_at', 'desc')->where('cl_id', $user_id)->take(5)->get();
    }

}

function get_sms_gateway_info($gateway_id = '')
{
    return SMSGateways::find($gateway_id);
}

/**
 * Check if exec() function is available
 *
 * @return boolean
 */
function exec_enabled()
{
    try {
        // make a small test
        exec("ls");
        return function_exists('exec') && !in_array('exec', array_map('trim', explode(', ', ini_get('disable_functions'))));
    } catch (\Exception $ex) {
        return false;
    }
}


function unique_multidim_array($array, $key)
{
    $temp_array = array();
    $i          = 0;
    $key_array  = array();
    foreach (array_chunk($array, 50) as $chunk_result) {
        foreach ($chunk_result as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i]  = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
    }
    return $temp_array;
}

function active_sms_gateway()
{
    return SMSGateways::where('status', 'Active')->count();
}

function multi_explode($delimiters, $string)
{

    $ready  = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return $launch;
}


function us_money_format($amount, $symbol = '')
{

    if ($symbol == '') {
        $currency_code = app_config('CurrencyCode');
    } else {
        $currency_code = $symbol;
    }

    $currency_decimal_digits  = app_config('currency_decimal_digits');
    $currency_symbol_position = app_config('currency_symbol_position');
    $dec_point                = app_config('dec_point');
    $thousands_sep            = app_config('thousands_sep');


    if ($currency_symbol_position == 'right') {
        $retval = number_format($amount, $currency_decimal_digits, $dec_point, $thousands_sep) . ' ' . $currency_code;
    } else {
        $retval = $currency_code . ' ' . number_format($amount, $currency_decimal_digits, $dec_point, $thousands_sep);
    }
    return $retval;
}

function validate_phone_number($phone)
{
    // Allow +, - and . in phone number
    $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
    // Remove "-" from number
    $phone_to_check = str_replace("-", "", $filtered_phone_number);

    // Check the length of number
    // This can be customized if you want phone number from a specific country
    if (strlen($phone_to_check) < 7 || strlen($phone_to_check) > 16) {
        return false;
    } else {
        return true;
    }
}


function get_operator_count($coverage_id){
    return \App\Operator::where('coverage_id',$coverage_id)->count();
}

function get_contact_info($phone){
    $contact = \App\ContactList::where('phone_number',$phone)->select('first_name')->first();
    if ($contact){
        if ($contact->first_name != ''){
            if (strlen($contact->first_name) > 8){
                return substr($contact->first_name, 0, 8).'..';
            }
            return $contact->first_name;
        }
        return $phone;
    }
    return $phone;
}


function get_unread_notification($history_id){
    $count_id = \App\SMSInbox::where('msg_id',$history_id)->where('mark_read','no')->count();
    if ($count_id){
        return $count_id;
    }

    return null;
}


function remove_element($array,$value) {
    foreach (array_keys($array, $value) as $key) {
        unset($array[$key]);
    }
    return $array;
}
