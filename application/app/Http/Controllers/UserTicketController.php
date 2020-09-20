<?php

namespace App\Http\Controllers;

use App\Client;
use App\EmailTemplates;
use App\Mail\CreateTicket;
use App\Mail\ReplyTicket;
use App\SupportDepartments;
use App\SupportTicketFiles;
use App\SupportTickets;
use App\SupportTicketsReplies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class UserTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('client');
    }


    /* allSupportTickets  Function Start Here */
    public function allSupportTickets()
    {
        $st = SupportTickets::where('cl_id', Auth::guard('client')->user()->id)->get();
        return view('client.support-tickets', compact('st'));
    }

    /* createNewTicket  Function Start Here */
    public function createNewTicket()
    {
        $sd = SupportDepartments::where('show', 'Yes')->get();
        return view('client.create-new-ticket', compact('sd'));
    }


    /* postTicket  Function Start Here */
    public function postTicket(Request $request)
    {
        $v = \Validator::make($request->all(), [
            'subject' => 'required', 'message' => 'required', 'did' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/tickets/create-new')->withErrors($v->errors());
        }

        $subject    = Input::get('subject');
        $st_message = Input::get('message');
        $did        = Input::get('did');

        $cl       = Client::find(Auth::guard('client')->user()->id);
        $cl_name  = $cl->fname . ' ' . $cl->lname;
        $cl_email = $cl->email;

        $d            = new SupportTickets();
        $d->did       = $did;
        $d->cl_id     = Auth::guard('client')->user()->id;
        $d->admin_id  = '0';
        $d->name      = $cl_name;
        $d->email     = $cl_email;
        $d->date      = date('Y-m-d');
        $d->subject   = $subject;
        $d->message   = $st_message;
        $d->status    = 'Pending';
        $d->admin     = '0';
        $d->replyby   = '';
        $d->closed_by = '';
        $d->save();
        $cmd = $d->id;


        $deprt = SupportDepartments::find($did);

        $sysEmail        = $deprt->email;
        $department_name = $deprt->name;
        $sysUrl          = url('support-tickets/view-ticket/' . $cmd);

        try {
            \Mail::to($sysEmail)->send(new CreateTicket($department_name, $subject, $st_message, $sysUrl, $cl_name, $cl_email));

            return redirect('user/tickets/view-ticket/' . $cmd)->with([
                'message' => language_data('Support Ticket Created Successfully', Auth::guard('client')->user()->lan_id)
            ]);
        } catch (\Exception $ex) {
            return redirect('user/tickets/view-ticket/' . $cmd)->with([
                'message' => $ex->getMessage()
            ]);
        }

    }


    /* viewTicket  Function Start Here */
    public function viewTicket($id)
    {

        $user_id = Auth::guard('client')->user()->id;
        $get_reseller = Client::where('parent', Auth::guard('client')->user()->id)->select('id')->get()->toArray();
        $reseller_ids = array_column($get_reseller, 'id');
        $reseller_ids[]=$user_id;

        $st          = SupportTickets::whereIn('cl_id', $reseller_ids)->find($id);
        if (!$st){
            return redirect('/user/view/'.$user_id)->with([
                'message' => 'Support ticket not found',
                'message_important' => true
            ]);
        }
        $did         = $st->did;
        $td          = SupportDepartments::find($did);
        $trply       = SupportTicketsReplies::where('tid', $id)->orderBy('date', 'desc')->get();
        $department  = SupportDepartments::all();
        $ticket_file = SupportTicketFiles::where('ticket_id', $id)->get();

        return view('client.view-support-ticket', compact('st', 'did', 'td', 'trply', 'department', 'ticket_file'));
    }
    /* replayTicket  Function Start Here */
    public function replayTicket(Request $request)
    {
        $v = \Validator::make($request->all(), [
            'message' => 'required'
        ]);

        $cmd = Input::get('cmd');

        if ($v->fails()) {
            return redirect('user/tickets/view-ticket/' . $cmd)->withErrors($v->errors());
        }

        $message = Input::get('message');

        $st  = SupportTickets::find($cmd);
        $cid = $st->cl_id;
        $did = $st->did;

        $cl       = Client::find($cid);
        $cl_name  = $cl->fname . ' ' . $cl->lname;
        $cl_email = $cl->email;

        SupportTicketsReplies::insert([
            'tid' => $cmd,
            'cl_id' => $cid,
            'admin_id' => '0',
            'name' => $cl_name,
            'date' => date('Y-m-d'),
            'message' => $message,
            'admin' => 'client',
            'image' => $cl->image,
        ]);

        $st->replyby = $cl_name;
        $st->status  = 'Customer Reply';
        $st->save();

        $deprt = SupportDepartments::find($did);

        $sysEmail        = $deprt->email;
        $department_name = $deprt->name;
        $subject         = 'Reply to Ticket [TID-' . $cmd . ']';

        $sysUrl = url('support-tickets/view-ticket/' . $cmd);

        try {
            \Mail::to($sysEmail)->send(new ReplyTicket($department_name, $subject, $message, $sysUrl, $cl_name, $cl_email));

            return redirect('user/tickets/view-ticket/' . $cmd)->with([
                'message' => language_data('Ticket Reply Successfully', Auth::guard('client')->user()->lan_id)
            ]);
        } catch (\Exception $ex) {
            return redirect('user/tickets/view-ticket/' . $cmd)->with([
                'message' => $ex->getMessage()
            ]);
        }

    }


    /* postTicketFiles  Function Start Here */
    public function postTicketFiles(Request $request)
    {
        $cmd = Input::get('cmd');
        $v   = \Validator::make($request->all(), [
            'file_title' => 'required', 'file' => 'required|image|mimes:jpeg,jpg,png,gif'
        ]);

        if ($v->fails()) {
            return redirect('user/tickets/view-ticket/' . $cmd)->withErrors($v->errors());
        }

        $file_title = Input::get('file_title');
        $file       = Input::file('file');

        if ($file != '' && app_config('AppStage') != 'Demo') {

            if (isset($file) && in_array(strtolower($file->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg'))) {
                $destinationPath = public_path() . '/assets/ticket_file/';
                $file_name       = $file->getClientOriginalName();
                $file_size       = $file->getSize();
                Input::file('file')->move($destinationPath, $file_name);

                $tf             = new SupportTicketFiles();
                $tf->ticket_id  = $cmd;
                $tf->cl_id      = Auth::guard('client')->user()->id;
                $tf->admin_id   = '0';
                $tf->admin      = 'client';
                $tf->file_title = $file_title;
                $tf->file_size  = $file_size;
                $tf->file       = $file_name;
                $tf->save();

                return redirect('user/tickets/view-ticket/' . $cmd)->with([
                    'message' => language_data('File Uploaded Successfully', Auth::guard('client')->user()->lan_id)
                ]);
            } else {
                return redirect('user/tickets/view-ticket/' . $cmd)->with([
                    'message' => language_data('Upload .png or .jpeg or .jpg or .gif file', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }


        } else {
            return redirect('user/tickets/view-ticket/' . $cmd)->with([
                'message' => language_data('Please Upload a File', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }

    /* downloadTicketFile  Function Start Here */
    public function downloadTicketFile($id)
    {
        $ticket_file = SupportTicketFiles::find($id)->file;
        return response()->download(public_path('assets/ticket_file/' . $ticket_file));
    }
}
