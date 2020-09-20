<?php

use Illuminate\Database\Seeder;
use App\EmailTemplates;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmailTemplates::truncate();

        $templates = [
            [
                'tplname' => 'Client SignUp',
                'subject' => 'Welcome to {{business_name}}',
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
                 Hi {{name}},<br>
                 <br>
                Welcome to {{business_name}}! This message is an automated reply to your User Access request. Login to your User panel by using the details below:
            <br>
                <a target="_blank" style="color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none" href="{{sys_url}}">{{sys_url}}</a>.<br>
                                    User Name: {{username}}<br>
                                    Password: {{password}}
            <br>
            Regards,<br>
            {{business_name}}<br>
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
            ],
            [
                'tplname' => 'Client Registration Verification',
                'subject' => 'Registration Verification From {{business_name}}',
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
                 Hi {{name}},<br>
                 <br>
                Welcome to {{business_name}}! This message is an automated reply to your account verification request. Click the following url to verify your account:
            <br>
                <a target="_blank" style="color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none" href="{{sys_url}}">{{sys_url}}</a>
            <br>
            Regards,<br>
            {{business_name}}<br>
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
            ],
            [
                'tplname' => 'Ticket For Client',
                'subject' => 'New Ticket From {{business_name}}',
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
                            <div width="125" height="23" style="display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto" >{{business_name}}</div>
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
                 Hi {{name}},<br>
                 <br>
                Thank you for stay with us! This is a Support Ticket For Yours.. Login to your account to view  your support tickets details:
            <br>
                <a target="_blank" style="color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none" href="{{sys_url}}">{{sys_url}}</a>.<br>
                Ticket ID: {{ticket_id}}<br>
                Ticket Subject: {{ticket_subject}}<br>
                Message: {{message}}<br>
                Created By: {{create_by}}
            <br>
            Regards,<br>
            {{business_name}}<br>
            <br>
          </td>
                    </tr>
                    <tr>
                      <td width="556" height="30" style="font-size:30px;line-height:30px;height:30px;text-align:left">Â </td>
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
<table cellspacing="0" cellpadding="0" width="100%" border="0" bgcolor="#273f47"><tbody><tr><td align="center"> </td></tr></tbody></table>
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
                <td valign="top" style="font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left">Copyright Â© {{business_name}}, All rights reserved.<br><br><br></td>
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
            ],
            [
                'tplname' => 'Admin Password Reset',
                'subject' => '{{business_name}} New Password',
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
                            <p  style="display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto">{{business_name}}</p>
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
                 Hi {{name}},<br>
                 <br>
                Password Reset Successfully!   This message is an automated reply to your password reset request. Login to your account to set up your all details by using the details below:
            <br>
                <a target="_blank" style="color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none" href=" {{sys_url}}"> {{sys_url}}</a>.<br>
                                    User Name: {{username}}<br>
                                    Password: {{password}}
            <br>
            {{business_name}},<br>
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
            ],
            [
                'tplname' => 'Forgot Admin Password',
                'subject' => '{{business_name}} password change request',
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
                            <p  style="display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto" >{{business_name}}</p>
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
                 Hi {{name}},<br>
                 <br>
                Password Reset Successfully!   This message is an automated reply to your password reset request. Click this link to reset your password:
            <br>
                <a target="_blank" style="color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none" href=" {{forgotpw_link}} "> {{forgotpw_link}} </a>.<br>
Notes: Until your password has been changed, your current password will remain valid. The Forgot Password Link will be available for a limited time only.

            <br>
            On behalf of the {{business_name}},<br>
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
            ],
            [
                'tplname' => 'Ticket Reply',
                'subject' => 'Reply to Ticket [TID-{{ticket_id}}]',
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
                            <div width="125" height="23" style="display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto"  {{business_name}} ></div>
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
                 Hi {{name}},<br>
                 <br>
                Thank you for stay with us! This is a Support Ticket Reply. Login to your account to view  your support ticket reply details:
            <br>
                <a target="_blank" style="color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none" href="{{sys_url}}">{{sys_url}}</a>.<br>
                Ticket ID: {{ticket_id}}<br>
                Ticket Subject: {{ticket_subject}}<br>
                Message: {{message}}<br>
                Replyed By: {{reply_by}} <br><br>
                Should you have any questions in regards to this support ticket or any other tickets related issue, please feel free to contact the Support department by creating a new ticket from your Client/User Portal
            <br><br>
            Regards,<br>
            {{business_name}}<br>
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
            ],
            [
                'tplname' => 'Forgot Client Password',
                'subject' => '{{business_name}} password change request',
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
                            <p  style="display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto">{{business_name}} </p>
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
                 Hi {{name}},<br>
                 <br>
                Password Reset Successfully!   This message is an automated reply to your password reset request. Click this link to reset your password:
            <br>
                <a target="_blank" style="color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none" href=" {{forgotpw_link}} "> {{forgotpw_link}} </a>.<br>
Notes: Until your password has been changed, your current password will remain valid. The Forgot Password Link will be available for a limited time only.

            <br>
            {{business_name}}<br>
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
            ],
            [
                'tplname' => 'Client Registrar Activation',
                'subject' => '{{business_name}} Registration Code',
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
                            <p  style="display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto">{{business_name}} </p>
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
                 Hi {{name}},<br>
                 <br>
                Registration Successfully!   This message is an automated reply to your active registration request. Click this link to active your account:
            <br>
                <a target="_blank" style="color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none" href=" {{registration_link}} "> {{registration_link}} </a>.<br>
            <br>
            {{business_name}}<br>
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
            ],
            [
                'tplname' => 'Client Password Reset',
                'subject' => '{{business_name}} New Password',
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
                            <p  style="display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto" >{{business_name}}</p>
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
                 Hi {{name}},<br>
                 <br>
                Password Reset Successfully!   This message is an automated reply to your password reset request. Login to your account to set up your all details by using the details below:
            <br>
                <a target="_blank" style="color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none" href=" {{sys_url}}"> {{sys_url}}</a>.<br>
                                    User Name: {{username}}<br>
                                    Password: {{password}}
            <br>
            {{business_name}}<br>
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
            ],
            [
                'tplname' => 'Ticket For Admin',
                'subject' => 'New Ticket From {{business_name}} Client',
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
                            <div width="125" height="23" style="display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto" >{{business_name}}</div>
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
                 Hi {{name}},<br>{{department_name}},<br>
                 <br>

                Ticket ID: {{ticket_id}}<br>
                Ticket Subject: {{ticket_subject}}<br>
                Message: {{message}}<br>
                Created By: {{create_by}} <br><br>
                Waiting for your quick response.
            <br><br>
            Thank you.
            <br>
            Regards,<br>
            {{name}}<br>
{{business_name}} User.
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
            ],
            [
                'tplname' => 'Client Ticket Reply',
                'subject' => 'Reply to Ticket [TID-{{ticket_id}}]',
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
                 Hi {{name}},<br>{{department_name}},<br>
                 <br>
                 This is a Support Ticket Reply From Client.
            <br>
                Ticket ID: {{ticket_id}}<br>
                Ticket Subject: {{ticket_subject}}<br>
                Message: {{message}}<br>
                Replyed By: {{reply_by}}  <br><br>
                Waiting for your quick response.
            <br><br>
            Thank you.
            <br>
            Regards,<br>
            {{name}}<br>
{{business_name}} User.
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
            ],
            [
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
            ]
        ];

        foreach ($templates as $tp) {
            EmailTemplates::create($tp);
        }

    }
}
