<?php

namespace App\Http\Controllers;

use App\BlackListContact;
use App\Classes\Permission;
use App\ContactList;
use App\ImportPhoneNumber;
use App\IntCountryCodes;
use App\SpamWord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use libphonenumber\PhoneNumberUtil;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Datatables;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function phoneBook()
    {
        $self = 'phone-book';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $clientGroups = ImportPhoneNumber::where('user_id', 0)->orderBy('updated_at', 'DESC')->get();
        return view('admin.phone-book', compact('clientGroups'));
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postPhoneBook(Request $request)
    {

        $self = 'phone-book';
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
            'list_name' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/phone-book')->withErrors($v->errors());
        }

        $exist = ImportPhoneNumber::where('group_name', $request->list_name)->where('user_id', 0)->first();

        if ($exist) {
            return redirect('sms/phone-book')->with([
                'message' => language_data('List name already exist'),
                'message_important' => true
            ]);
        }

        $phone_book = new ImportPhoneNumber();
        $phone_book->user_id = '0';
        $phone_book->group_name = $request->list_name;
        $phone_book->save();

        return redirect('sms/phone-book')->with([
            'message' => language_data('List added successfully')
        ]);
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function updatePhoneBook(Request $request)
    {

        $self = 'phone-book';
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
            'list_name' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/phone-book')->withErrors($v->errors());
        }

        $cmd = $request->cmd;

        $phone_book = ImportPhoneNumber::find($cmd);

        if ($phone_book == '') {
            return redirect('sms/phone-book')->with([
                'message' => language_data('Contact list not found'),
                'message_important' => true
            ]);
        }

        if ($phone_book->group_name != $request->list_name) {

            $exist = ImportPhoneNumber::where('group_name', $request->list_name)->where('user_id', 0)->first();

            if ($exist) {
                return redirect('sms/phone-book')->with([
                    'message' => language_data('List name already exist'),
                    'message_important' => true
                ]);
            }
        }

        $phone_book->group_name = $request->list_name;
        $phone_book->save();

        return redirect('sms/phone-book')->with([
            'message' => language_data('List updated successfully')
        ]);
    }


    public function addContact($id)
    {
        $self = 'phone-book';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $exist = ImportPhoneNumber::where('user_id', 0)->find($id);

        if ($exist) {

            $contact_list = ContactList::where('pid', $id)->get();
            $country_code = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();

            return view('admin.add-contact', compact('contact_list', 'id', 'country_code'));
        } else {
            return redirect('sms/phone-book')->with([
                'message' => language_data('Invalid Phone book'),
                'message_important' => true
            ]);
        }

    }


    public function postNewContact(Request $request)
    {


        $self = 'phone-book';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $cmd = $request->cmd;

        $exist = ImportPhoneNumber::where('user_id', 0)->find($cmd);

        if ($exist) {

            $v = \Validator::make($request->all(), [
                'number' => 'required', 'country_code' => 'required'
            ]);

            if ($v->fails()) {
                return redirect('sms/add-contact/' . $cmd)->withErrors($v->errors());
            }

            $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($request->number));
            if ($request->country_code != 0) {
                $phone = $request->country_code . $phone;
            }

            if (is_numeric($phone)) {
                $phoneUtil = PhoneNumberUtil::getInstance();
                $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                $isValid = $phoneUtil->isValidNumber($phoneNumberObject);

                if ($isValid) {

                    $exist = ContactList::where('phone_number', $phone)->where('pid', $cmd)->first();
                    if ($exist) {
                        return redirect('sms/add-contact/' . $cmd)->with([
                            'message' => language_data('Contact number already exist'),
                            'message_important' => true
                        ]);
                    }

                    $contact = new ContactList();
                    $contact->pid = $cmd;
                    $contact->phone_number = $phone;
                    $contact->first_name = $request->first_name;
                    $contact->last_name = $request->last_name;
                    $contact->email_address = $request->email;
                    $contact->user_name = $request->username;
                    $contact->company = $request->company;
                    $contact->save();

                    return redirect('sms/view-contact/' . $cmd)->with([
                        'message' => language_data('Contact added successfully')
                    ]);
                }

                return redirect('sms/add-contact/' . $cmd)->with([
                    'message' => 'Invalid phone number',
                    'message_important' => true
                ]);
            }
            return redirect('sms/add-contact/' . $cmd)->with([
                'message' => 'Invalid phone number',
                'message_important' => true
            ]);

        } else {
            return redirect('sms/phone-book')->with([
                'message' => language_data('Invalid Phone book'),
                'message_important' => true
            ]);
        }
    }

    public function postSingleContact(Request $request)
    {
        $self = 'phone-book';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $cmd = $request->cmd;

        $contact = ContactList::find($cmd);

        if ($contact) {

            $v = \Validator::make($request->all(), [
                'number' => 'required'
            ]);

            if ($v->fails()) {
                return redirect('sms/view-contact/' . $contact->pid)->withErrors($v->errors());
            }

            $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($request->number));

            if (is_numeric($phone)) {
                if ($phone != $contact->phone_number) {
                    $exist = ContactList::where('phone_number', $phone)->where('pid', $contact->pid)->first();
                    if ($exist) {
                        return redirect('sms/view-contact/' . $contact->pid)->with([
                            'message' => language_data('Contact number already exist'),
                            'message_important' => true
                        ]);
                    }
                }

                $phoneUtil = PhoneNumberUtil::getInstance();
                $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                $isValid = $phoneUtil->isValidNumber($phoneNumberObject);

                if ($isValid) {
                    $contact->phone_number = $request->number;
                    $contact->first_name = $request->first_name;
                    $contact->last_name = $request->last_name;
                    $contact->email_address = $request->email;
                    $contact->user_name = $request->username;
                    $contact->company = $request->company;
                    $contact->save();

                    return redirect('sms/view-contact/' . $contact->pid)->with([
                        'message' => language_data('Contact updated successfully')
                    ]);
                }

                return redirect('sms/view-contact/' . $contact->pid)->with([
                    'message' => 'Invalid phone number',
                    'message_important' => true
                ]);
            }

            return redirect('sms/view-contact/' . $contact->pid)->with([
                'message' => 'Invalid phone number',
                'message_important' => true
            ]);

        } else {
            return redirect('sms/phone-book')->with([
                'message' => language_data('Contact info not found'),
                'message_important' => true
            ]);
        }
    }


    public function viewContact($id)
    {
        $self = 'phone-book';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $exist = ImportPhoneNumber::where('user_id', 0)->find($id);

        if ($exist) {

            return view('admin.view-contact', compact('id'));

        } else {
            return redirect('sms/phone-book')->with([
                'message' => language_data('Invalid Phone book'),
                'message_important' => true
            ]);
        }
    }

    public function editContact($id)
    {

        $self = 'phone-book';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $cl = ContactList::find($id);

        if ($cl) {
            return view('admin.edit-contact', compact('cl'));
        } else {
            return redirect('sms/phone-book')->with([
                'message' => language_data('Contact info not found'),
                'message_important' => true
            ]);
        }
    }


    public function deleteContact($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/phone-book')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'phone-book';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $contact = ContactList::find($id);

        if ($contact) {

            $exist = ImportPhoneNumber::where('user_id', 0)->find($contact->pid);

            if ($exist) {
                $pid = $contact->pid;
                $contact->delete();

                return redirect('sms/view-contact/' . $pid)->with([
                    'message' => language_data('Contact deleted successfully')
                ]);

            } else {
                return redirect('sms/phone-book')->with([
                    'message' => language_data('Invalid Phone book'),
                    'message_important' => true
                ]);
            }


        } else {

            return redirect('sms/phone-book')->with([
                'message' => language_data('Contact info not found'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // importContacts Function Start Here
    //======================================================================
    public function importContacts()
    {


        $self = 'import-contacts';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $phone_book = ImportPhoneNumber::where('user_id', 0)->get();
        $country_code = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();

        return view('admin.import-contact', compact('phone_book', 'country_code'));

    }

    //======================================================================
    // downloadContactSampleFile Function Start Here
    //======================================================================
    public function downloadContactSampleFile()
    {
        return response()->download('assets/test_file/sms.csv');
    }

    //======================================================================
    // postImportContact Function Start Here
    //======================================================================
    public function postImportContact(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/import-contacts')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'import-contacts';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        if (function_exists('ini_set') && ini_get('max_execution_time')) {
            ini_set('max_execution_time', '-1');
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'group_name' => 'required', 'country_code' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/import-contacts')->withErrors($v->errors());
        }

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('sms/import-contacts')->with([
                'message' => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true
            ]);
        }

        $all_data = Excel::load($request->import_numbers)->noHeading()->all()->toArray();

        if ($all_data && is_array($all_data) && array_empty($all_data)) {
            return redirect('sms/import-contacts')->with([
                'message' => language_data('Empty field'),
                'message_important' => true
            ]);
        }


        $counter = "A";

        if ($request->header_exist == 'on') {

            $header = array_shift($all_data);

            foreach ($header as $key => $value) {
                if (!$value) {
                    $header[$key] = "Column " . $counter;
                }

                $counter++;
            }

        } else {

            $header_like = $all_data[0];

            $header = array();

            foreach ($header_like as $h) {
                array_push($header, "Column " . $counter);
                $counter++;
            }

        }


        if (count($header) == count($header, COUNT_RECURSIVE)) {
            $all_data = array_map(function ($row) use ($header) {
                return array_combine($header, $row);
            }, $all_data);
        } else {
            return redirect('sms/import-contacts')->with([
                'message' => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true
            ]);
        }

        $valid_phone_numbers = [];
        $get_data = [];

        $blacklist = BlackListContact::where('user_id', 0)->select('numbers')->get()->toArray();

        if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {
            $blacklist = array_column($blacklist, 'numbers');
        }


        $number_column = $request->number_column;
        $email_address_column = $request->email_address_column;
        $user_name_column = $request->user_name_column;
        $company_column = $request->company_column;
        $first_name_column = $request->first_name_column;
        $last_name_column = $request->last_name_column;

        array_filter($all_data, function ($data) use ($number_column, $email_address_column, $user_name_column, $company_column, $first_name_column, $last_name_column, &$get_data, &$valid_phone_numbers, $blacklist) {
            $a = array_map('trim', array_keys($data));
            $b = array_map('trim', $data);
            $data = array_combine($a, $b);

            if ($data[$number_column]) {
                if (!in_array($data[$number_column], $blacklist)) {

                    $email_address = null;
                    if ($email_address_column != '0') {
                        $email_address = $data[$email_address_column];
                    }

                    $user_name = null;
                    if ($user_name_column != '0') {
                        $user_name = $data[$user_name_column];
                    }

                    $company = null;
                    if ($company_column != '0') {
                        $company = $data[$company_column];
                    }

                    $first_name = null;
                    if ($first_name_column != '0') {
                        $first_name = $data[$first_name_column];
                    }

                    $last_name = null;
                    if ($last_name_column != '0') {
                        $last_name = $data[$last_name_column];
                    }


                    array_push($valid_phone_numbers, $data[$number_column]);
                    array_push($get_data, [
                        'phone_number' => $data[$number_column],
                        'email_address' => $email_address,
                        'user_name' => $user_name,
                        'company' => $company,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                    ]);

                }

            }
        });


        if (isset($valid_phone_numbers) && is_array($valid_phone_numbers) && count($valid_phone_numbers) <= 0) {
            return redirect('sms/import-contacts')->with([
                'message' => language_data('Invalid phone numbers'),
                'message_important' => true
            ]);
        }

        $get_data = unique_multidim_array($get_data, 'phone_number');

        foreach (array_chunk($get_data, 100) as $rdata) {

            foreach ($rdata as $r) {
                $data = array_values($r);

                $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($data['0']));
                if ($request->country_code != 0) {
                    $phone = $request->country_code . $phone;
                }

                if (is_numeric($phone)) {
                    $phoneUtil = PhoneNumberUtil::getInstance();
                    $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                    $isValid = $phoneUtil->isValidNumber($phoneNumberObject);
                    if ($isValid) {
                        $exist = ContactList::where('phone_number', $phone)->where('pid', $request->group_name)->first();
                        if (!$exist){
                            $contact = new ContactList();
                            $contact->pid = $request->group_name;
                            $contact->phone_number = $phone;
                            $contact->email_address = $data['1'];
                            $contact->user_name = $data['2'];
                            $contact->company = $data['3'];
                            $contact->first_name = $data['4'];
                            $contact->last_name = $data['5'];
                            $contact->save();
                        }
                        continue;
                    }
                    continue;
                }
                continue;
            }
        }

        return redirect('sms/import-contacts')->with([
            'message' => language_data('Phone number imported successfully')
        ]);
    }

    //======================================================================
    // postMultipleContact Function Start Here
    //======================================================================
    public function postMultipleContact(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/import-contacts')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'import-contacts';
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
            'import_numbers' => 'required', 'group_name' => 'required', 'country_code' => 'required', 'delimiter' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/import-contacts')->withErrors($v->errors());
        }

        try {

            if ($request->delimiter == 'automatic') {
                $results = multi_explode(array(",", "\n", ";","|"), $request->import_numbers);
            } elseif ($request->delimiter == ';') {
                $results = explode(';', $request->import_numbers);
            } elseif ($request->delimiter == ',') {
                $results = explode(',', $request->import_numbers);
            } elseif ($request->delimiter == '|') {
                $results = explode('|', $request->import_numbers);
            } elseif ($request->delimiter == 'tab') {
                $results = explode(' ', $request->import_numbers);
            } elseif ($request->delimiter == 'new_line') {
                $results = explode("\n", $request->import_numbers);
            } else {
                return redirect('sms/import-contacts')->with([
                    'message' => 'Invalid delimiter',
                    'message_important' => true
                ]);
            }

            $results = array_unique($results, SORT_REGULAR);
            $results = array_filter($results);

            foreach ($results as $r) {

                $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($r));

                if ($request->country_code != 0) {
                    $phone = $request->country_code . $phone;
                }

                if (is_numeric($phone)) {
                    $phoneUtil = PhoneNumberUtil::getInstance();
                    $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                    $isValid = $phoneUtil->isValidNumber($phoneNumberObject);
                    if ($isValid) {

                        $exist = ContactList::where('phone_number', $phone)->where('pid', $request->group_name)->first();
                        if (!$exist){
                            $contact = new ContactList();
                            $contact->pid = $request->group_name;
                            $contact->phone_number = $phone;
                            $contact->save();
                        }
                        continue;
                    }
                    continue;
                }
                continue;
            }

            return redirect('sms/import-contacts')->with([
                'message' => language_data('Phone number imported successfully')
            ]);
        } catch (\Exception $e) {
            return redirect('sms/import-contacts')->with([
                'message' => $e->getMessage(),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // getRecipients Function Start Here
    //======================================================================
    public function getRecipients(Request $request)
    {
        if ($request->pb_id != '') {
            $ids = explode(',', $request->pb_id);

            $final_contacts = [];

            $all_contacts = ContactList::whereIn('pid', $ids)->get();
            foreach ($all_contacts as $contact) {
                array_push($final_contacts, $contact->phone_number);
            }

            $final_contacts = array_unique($final_contacts);
            $final_contacts = implode(',', $final_contacts);

            return $final_contacts;

        } else {
            return false;
        }

    }



    //======================================================================
    // deleteImportPhoneNumber Function Start Here
    //======================================================================
    public function deleteImportPhoneNumber($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/phone-book')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'phone-book';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $clientGroup = ImportPhoneNumber::find($id);

        if ($clientGroup) {

            ContactList::where('pid', $id)->delete();

            $clientGroup->delete();

            return redirect('sms/phone-book')->with([
                'message' => language_data('Client group deleted successfully')
            ]);

        } else {
            return redirect('sms/phone-book')->with([
                'message' => language_data('Client Group not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // getAllContact Function Start Here
    //======================================================================
    public function getAllContact($id)
    {
        $contact_list = ContactList::where('pid', $id)->getQuery();

        return Datatables::of($contact_list)
            ->addColumn('action', function ($cl) {
                return '
               <a href="#" class="btn btn-warning btn-xs optout" id="' . $cl->phone_number . '"><i class="fa fa-stop"></i> Opt Out</a>
               <a class="btn btn-success btn-xs" href="' . url("sms/edit-contact/$cl->id") . '" ><i class="fa fa-edit"></i>' . language_data('Edit') . '</a>
               <a href="#" class="btn btn-danger btn-xs cdelete" id="' . $cl->id . '"><i class="fa fa-trash"></i> ' . language_data("Delete") . '</a>';
            })
            ->addColumn('id', function ($cl) {
                return "<div class='coder-checkbox'>
                             <input type='checkbox'  class='deleteRow' value='$cl->id'/>
                                            <span class='co-check-ui'></span>
                                        </div>";

            })
            ->escapeColumns([])
            ->make(true);

    }

    //======================================================================
    // deleteBulkContact Function Start Here
    //======================================================================
    public function deleteBulkContact(Request $request)
    {


        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/phone-book')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'phone-book';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));

            if (isset($all_ids) && is_array($all_ids) && count($all_ids) > 0) {
                ContactList::destroy($all_ids);
            }
        }
    }


    //======================================================================
    // Version 2.3
    //======================================================================

    //======================================================================
    // spamWord Function Start Here
    //======================================================================
    public function spamWords()
    {
        $self = 'spam-words';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        return view('admin.spam-word');
    }


    //======================================================================
    // getBlacklistContacts Function Start Here
    //======================================================================
    public function getSpamWords()
    {
        $spam_word = SpamWord::query();
        return Datatables::of($spam_word)
            ->addColumn('action', function ($bl) {
                return '
            <a href="#" class="btn btn-danger btn-xs cdelete" id="' . $bl->id . '"><i class="fa fa-trash"></i> ' . language_data("Delete") . '</a>';
            })
            ->escapeColumns([])
            ->make(true);
    }


    //======================================================================
    // postSpamWord Function Start Here
    //======================================================================
    public function postSpamWord(Request $request)
    {

        $self = 'spam-words';
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
            'spam_word' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/spam-words')->withErrors($v->errors());
        }

        $exist = SpamWord::where('word', $request->spam_word)->first();

        if ($exist) {
            return redirect('sms/spam-words')->with([
                'message' => language_data('Word already exist'),
                'message_important' => true
            ]);
        }

        SpamWord::create([
            'word' => $request->spam_word
        ]);

        return redirect('sms/spam-words')->with([
            'message' => language_data('Word added on Spam word list'),
        ]);

    }

    //======================================================================
    // deleteSpamWord Function Start Here
    //======================================================================
    public function deleteSpamWord($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/spam-words')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'spam-words';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $spam_word = SpamWord::find($id);
        if ($spam_word) {
            $spam_word->delete();
            return redirect('sms/spam-words')->with([
                'message' => language_data('Word deleted from list'),
            ]);
        } else {
            return redirect('sms/spam-words')->with([
                'message' => language_data('Word not found on list'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // addToBlacklist Function Start Here
    //======================================================================
    public function addToBlacklist($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/blacklist-contacts')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'blacklist-contacts';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $phone = str_replace(['(', ')', '+', '-', ' '], '', $id);

        $exist = BlackListContact::where('numbers', $phone)->where('user_id', 0)->first();

        if (!$exist) {
            BlackListContact::create([
                'user_id' => '0',
                'numbers' => $phone
            ]);
        }

        return redirect('sms/blacklist-contacts')->with([
            'message' => language_data('Number added on blacklist'),
        ]);

    }


}
