<?php

namespace App\Http\Controllers;

use App\Admin;
use App\AdminRole;
use App\AdminRolePermission;
use App\Classes\Permission;
use App\Invoices;
use App\Mail\UserRegistration;
use App\SMSHistory;
use App\SupportTickets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class AdministratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    //======================================================================
    // allAdministrator Function Start Here
    //======================================================================
    public function allAdministrator()
    {
        $self = 'administrators';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $admin       = Admin::where('username', '!=', 'admin')->get();
        $admin_roles = AdminRole::where('status', 'Active')->get();
        return view('admin.administrators', compact('admin', 'admin_roles'));
    }

    //======================================================================
    // addAdministrator Function Start Here
    //======================================================================
    public function addAdministrator(Request $request)
    {

        $self = 'administrators';
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
            'first_name' => 'required', 'username' => 'required', 'email' => 'required|email', 'password' => 'required', 'cpassword' => 'required', 'role' => 'required', 'image' => 'image|mimes:jpeg,jpg,png,gif'
        ]);

        if ($v->fails()) {
            return redirect('administrators/all')->withInput(Input::all())->withErrors($v->errors());
        }

        $username_check = Admin::where('username', $request->username)->first();
        if ($username_check) {
            return redirect('administrators/all')->withInput(Input::all())->with([
                'message' => language_data('User name already exist'),
                'message_important' => true
            ]);
        }

        $email_check = Admin::where('email', $request->email)->first();
        if ($email_check) {
            return redirect('administrators/all')->withInput(Input::all())->with([
                'message' => language_data('Email already exist'),
                'message_important' => true
            ]);
        }

        $password  = $request->password;
        $cpassword = $request->cpassword;

        if ($password !== $cpassword) {
            return redirect('administrators/all')->withInput(Input::all())->with([
                'message' => language_data('Both password does not match'),
                'message_important' => true
            ]);
        } else {
            $password = bcrypt($password);
        }

        $image = $request->image;
        if ($image != '' && app_config('AppStage') != 'Demo') {
            if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg'))) {
                $destinationPath = public_path() . '/assets/admin_pic/';
                $image_name      = $image->getClientOriginalName();
                Input::file('image')->move($destinationPath, $image_name);
            } else {
                return redirect('administrators/all')->withInput(Input::all())->with([
                    'message' => language_data('Upload .png or .jpeg or .jpg or .gif file'),
                    'message_important' => true
                ]);
            }
        } else {
            $image_name = 'profile.jpg';
        }


        $email_notify = $request->email_notify;
        if ($email_notify == 'yes') {
            $email_notify = 'Yes';
        } else {
            $email_notify = 'No';
        }

        $admin              = new Admin();
        $admin->fname       = $request->first_name;
        $admin->lname       = $request->last_name;
        $admin->username    = $request->username;
        $admin->password    = $password;
        $admin->status      = $request->status;
        $admin->email       = $request->email;
        $admin->image       = $image_name;
        $admin->status      = 'Active';
        $admin->roleid      = $request->role;
        $admin->emailnotify = $email_notify;
        $admin->save();

        $admin_id = $admin->id;

        if (is_int($admin_id) && $request->email != '' && $email_notify == 'Yes') {
            $name = $request->first_name . ' ' . $request->last_name;
            \Mail::to($request->email)->send(new UserRegistration($name, $request->username, $cpassword, url('admin')));

            return redirect('administrators/all')->with([
                'message' => language_data('Administrator added successfully')
            ]);
        }

        return redirect('administrators/all')->with([
            'message' => language_data('Administrator added successfully')
        ]);

    }

    //======================================================================
    // manageAdministrator Function Start Here
    //======================================================================
    public function manageAdministrator($id)
    {

        $self = 'administrators';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $admin       = Admin::where('username', '!=', 'admin')->find($id);
        $admin_roles = AdminRole::where('status', 'Active')->get();
        if ($admin) {
            return view('admin.manage-administrator', compact('admin', 'admin_roles'));
        } else {
            return redirect('administrators/all')->with([
                'message' => language_data('Administrator not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // postUpdateAdministrator Function Start Here
    //======================================================================
    public function postUpdateAdministrator(Request $request)
    {

        $cmd = Input::get('cmd');

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('administrators/manage/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'administrators';
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
            'first_name' => 'required', 'username' => 'required', 'email' => 'required|email', 'role' => 'required', 'status' => 'required', 'image' => 'image|mimes:jpeg,jpg,png,gif'
        ]);

        $admin = Admin::find($cmd);

        if ($admin == '') {
            return redirect('administrators/all')->withInput(Input::all())->with([
                'message' => language_data('Administrator not found'),
                'message_important' => true
            ]);
        }

        if ($v->fails()) {
            return redirect('administrators/manage/' . $cmd)->withInput(Input::all())->withErrors($v->errors());
        }

        if ($request->username != $admin->username) {
            $username_check = Admin::where('username', $request->username)->first();
            if ($username_check) {
                return redirect('administrators/manage/' . $cmd)->withInput(Input::all())->with([
                    'message' => language_data('User name already exist'),
                    'message_important' => true
                ]);
            }
        }

        if ($request->email != $admin->email) {
            $email_check = Admin::where('email', $request->email)->first();
            if ($email_check) {
                return redirect('administrators/manage/' . $cmd)->withInput(Input::all())->with([
                    'message' => language_data('Email already exist'),
                    'message_important' => true
                ]);
            }
        }

        $password = $request->password;
        if ($password != '') {
            $cpassword = $request->cpassword;
            if ($password !== $cpassword) {
                return redirect('administrators/manage/' . $cmd)->withInput(Input::all())->with([
                    'message' => language_data('Both password does not match'),
                    'message_important' => true
                ]);
            } else {
                $password = bcrypt($password);
            }
        } else {
            $password = $admin->password;
        }

        $image = $request->image;

        if ($image != '') {
            if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg'))) {
                $destinationPath = public_path() . '/assets/admin_pic/';
                $image_name      = $image->getClientOriginalName();
                Input::file('image')->move($destinationPath, $image_name);
            } else {
                return redirect('administrators/manage/' . $cmd)->withInput(Input::all())->with([
                    'message' => language_data('Upload .png or .jpeg or .jpg or .gif file'),
                    'message_important' => true
                ]);
            }

        } else {
            $image_name = $admin->image;
        }

        $admin->fname    = $request->first_name;
        $admin->lname    = $request->last_name;
        $admin->username = $request->username;
        $admin->password = $password;
        $admin->status   = $request->status;
        $admin->email    = $request->email;
        $admin->image    = $image_name;
        $admin->status   = $request->status;
        $admin->roleid   = $request->role;
        $admin->save();

        return redirect('administrators/all')->with([
            'message' => language_data('Administrator updated successfully')
        ]);
    }


    //======================================================================
    // deleteAdministrator Function Start Here
    //======================================================================
    public function deleteAdministrator($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('administrators/all')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'administrators';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $admin = Admin::where('username', '!=', 'admin')->find($id);

        if ($admin) {
            $ticket = SupportTickets::where('admin_id', $id)->first();
            if ($ticket) {
                return redirect('administrators/all')->with([
                    'message' => language_data('Administrator have support tickets. First delete support ticket'),
                    'message_important' => true
                ]);
            }

            $sms = SMSHistory::where('userid', $id)->first();
            if ($sms) {
                return redirect('administrators/all')->with([
                    'message' => language_data('Administrator have SMS Log. First delete all sms'),
                    'message_important' => true
                ]);
            }
            $invoice = Invoices::where('created_by', $id)->first();
            if ($invoice) {
                return redirect('administrators/all')->with([
                    'message' => language_data('Administrator created invoice. First delete all invoice'),
                    'message_important' => true
                ]);
            }

            $admin->delete();

            return redirect('administrators/all')->with([
                'message' => language_data('Administrator delete successfully')
            ]);

        } else {
            return redirect('administrators/all')->with([
                'message' => language_data('Administrator not found'),
                'message_important' => true
            ]);
        }

    }



    //======================================================================
    // administratorRole Function Start Here
    //======================================================================
    public function administratorRole()
    {
        $self = 'administrator-role';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $admin_roles = AdminRole::all();
        return view('admin.administrator-role', compact('admin_roles'));
    }

    //======================================================================
    // addAdministratorRole Function Start Here
    //======================================================================
    public function addAdministratorRole(Request $request)
    {
        $self = 'administrator-role';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), ['role_name' => 'required', 'status' => 'required']);

        if ($v->fails()) {
            return redirect('administrators/role')->withInput(Input::all())->withErrors($v->errors());
        }

        $exist = AdminRole::where('role_name', $request->role_name)->first();
        if ($exist) {
            return redirect('administrators/role')->withInput(Input::all())->with(['message' => 'Administrator Role already exist', 'message_important' => true]);
        }

        $admin_roles            = new AdminRole();
        $admin_roles->role_name = $request->role_name;
        $admin_roles->status    = $request->status;
        $admin_roles->save();

        return redirect('administrators/role')->with(['message' => language_data('Administrator Role added successfully')]);
    }

    //======================================================================
    // updateAdministratorRole Function Start Here
    //======================================================================
    public function updateAdministratorRole(Request $request)
    {
        $self = 'administrator-role';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $cmd = Input::get('cmd');
        $v   = \Validator::make($request->all(), ['role_name' => 'required', 'status' => 'required']);
        if ($v->fails()) {
            return redirect('administrators/role')->withInput(Input::all())->withErrors($v->errors());
        }

        $admin_roles = AdminRole::find($cmd);

        if ($admin_roles->role_name != $request->role_name) {
            $exist = AdminRole::where('role_name', $request->role_name)->first();
            if ($exist) {
                return redirect('administrators/role')->withInput(Input::all())->with([
                    'message' => language_data('Administrator Role already exist'),
                    'message_important' => true
                ]);
            }
        }

        if ($admin_roles) {
            $admin_roles->role_name = $request->role_name;
            $admin_roles->status    = $request->status;
            $admin_roles->save();

            return redirect('administrators/role')->with(['message' => language_data('Administrator Role updated successfully')]);
        } else {

            return redirect('administrators/role')->with(['message' => language_data('Administrator Role info not found'), 'message_important' => true]);
        }
    }


    //======================================================================
    // setAdministratorRole Function Start Here
    //======================================================================
    public function setAdministratorRole($id)
    {
        $self = 'administrator-role';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $admin_roles = AdminRole::find($id);

        if ($admin_roles) {
            return view('admin.set-administrator-roles', compact('admin_roles'));
        } else {
            return redirect('administrators/role')->with(['message' => language_data('Administrator Role info not found'), 'message_important' => true]);
        }

    }

    //======================================================================
    // updateAdministratorSetRole Function Start Here
    //======================================================================
    public function updateAdministratorSetRole(Request $request)
    {
        $self = 'administrator-role';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $role_id = Input::get('role_id');

        $v = \Validator::make($request->all(), [
            'perms' => 'required', 'role_id' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('administrators/set-role/' . $role_id)->withInput(Input::all())->withErrors($v->errors());
        }

        $perms = Input::get('perms');
        if (count($perms) == 0) {
            return redirect('administrators/set-role/' . $role_id)->withInput(Input::all())->with([
                'message' => language_data('Permission not assigned'),
                'message_important' => true
            ]);
        }

        AdminRolePermission::where('role_id', $role_id)->delete();

        foreach ($perms as $perm) {
            $admin_r_perm          = new AdminRolePermission();
            $admin_r_perm->role_id = $role_id;
            $admin_r_perm->perm_id = $perm;
            $admin_r_perm->save();
        }

        return redirect('administrators/set-role/' . $role_id)->with([
            'message' => language_data('Permission Updated')
        ]);

    }

    //======================================================================
    // deleteAdministratorRole Function Start Here
    //======================================================================
    public function deleteAdministratorRole($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('administrators/role')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }
        $self = 'administrator-role';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $admin_role = AdminRole::find($id);

        if ($admin_role) {
            $admin_check = Admin::where('roleid', $id)->first();

            if ($admin_check) {
                return redirect('administrators/role')->with([
                    'message' => language_data('An Administrator contain this role'),
                    'message_important' => true
                ]);
            }


            AdminRolePermission::where('role_id', $id)->delete();
            $admin_role->delete();

            return redirect('administrators/role')->with([
                'message' => language_data('Administrator role deleted successfully')
            ]);

        } else {
            return redirect('administrators/role')->with([
                'message' => language_data('Administrator Role info not found'),
                'message_important' => true
            ]);
        }

    }


}
