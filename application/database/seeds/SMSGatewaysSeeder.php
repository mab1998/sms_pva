<?php

use Illuminate\Database\Seeder;
use App\SMSGateways;
use App\SMSGatewayCredential;

class SMSGatewaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SMSGatewayCredential::truncate();
        SMSGateways::truncate();

        $gateways = [
            [
                'name' => 'Twilio',
                'settings' => 'Twilio',
                'api_link' => '',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'Yes',
                'mms' => 'Yes',
                'voice' => 'Yes'
            ],
            [
                'name' => 'Clickatell_Touch',
                'settings' => 'Clickatell_Touch',
                'api_link' => 'https://platform.clickatell.com/messages/http/send',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'Yes',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Clickatell_Central',
                'settings' => 'Clickatell_Central',
                'api_link' => 'http://api.clickatell.com',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'Yes',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Asterisk',
                'settings' => 'Asterisk',
                'api_link' => 'http://127.0.0.1',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Text Local',
                'settings' => 'Text Local',
                'api_link' => 'https://api.txtlocal.com/send/',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'Yes',
                'mms' => 'Yes',
                'voice' => 'No'
            ],
            [
                'name' => 'Top10sms',
                'settings' => 'Top10sms',
                'api_link' => 'http://trans.websmsapp.com/API/',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'msg91',
                'settings' => 'msg91',
                'api_link' => 'http://api.msg91.com/api/sendhttp.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Plivo',
                'settings' => 'Plivo',
                'api_link' => 'https://api.plivo.com/v1/Account/',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'Yes',
                'mms' => 'No',
                'voice' => 'Yes'
            ],
            [
                'name' => 'SMSGlobal',
                'settings' => 'SMSGlobal',
                'api_link' => 'http://www.smsglobal.com/http-api.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'Yes',
                'mms' => 'Yes',
                'voice' => 'No'
            ],
            [
                'name' => 'Bulk SMS',
                'settings' => 'Bulk SMS',
                'api_link' => 'https://bulksms.vsms.net/eapi',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'Yes',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Nexmo',
                'settings' => 'Nexmo',
                'api_link' => 'https://rest.nexmo.com/sms/json',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'Yes',
                'mms' => 'No',
                'voice' => 'Yes'
            ],
            [
                'name' => 'Route SMS',
                'settings' => 'Route SMS',
                'api_link' => 'http://smsplus1.routesms.com:8080',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SMSKaufen',
                'settings' => 'SMSKaufen',
                'api_link' => 'http://www.smskaufen.com/sms/gateway/sms.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Kapow',
                'settings' => 'Kapow',
                'api_link' => 'http://www.kapow.co.uk/scripts/sendsms.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Zang',
                'settings' => 'Zang',
                'api_link' => '',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'InfoBip',
                'settings' => 'InfoBip',
                'api_link' => 'https://api.infobip.com/sms/1/text/advanced',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'Yes',
                'mms' => 'No',
                'voice' => 'Yes'
            ],
            [
                'name' => 'RANNH',
                'settings' => 'RANNH',
                'api_link' => 'http://rannh.com/sendsms.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SMSIndiaHub',
                'settings' => 'SMSIndiaHub',
                'api_link' => 'http://cloud.smsindiahub.in/vendorsms/pushsms.aspx',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'ShreeWeb',
                'settings' => 'ShreeWeb',
                'api_link' => 'http://sms.shreeweb.com/sendsms/sendsms.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SmsGatewayMe',
                'settings' => 'SmsGatewayMe',
                'api_link' => 'http://smsgateway.me/api/v3/messages/send',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Elibom',
                'settings' => 'Elibom',
                'api_link' => 'https://www.elibom.com/messages',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Hablame',
                'settings' => 'Hablame',
                'api_link' => 'https://api.hablame.co/sms/envio',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Wavecell',
                'settings' => 'Wavecell',
                'api_link' => 'https://api.wavecell.com/sms/v1/',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SIPTraffic',
                'settings' => 'SIPTraffic',
                'api_link' => 'https://www.siptraffic.com',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SMSMKT',
                'settings' => 'SMSMKT',
                'api_link' => 'http://member.smsmkt.com/SMSLink/SendMsg/main.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'MLat',
                'settings' => 'MLat',
                'api_link' => 'https://m-lat.net:8443/axis2/services/SMSServiceWS',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'NRSGateway',
                'settings' => 'NRSGateway',
                'api_link' => 'https://gateway.plusmms.net/send.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Orange',
                'settings' => 'Orange',
                'api_link' => 'http://api.orange.com',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'GlobexCam',
                'settings' => 'GlobexCam',
                'api_link' => 'http://panel.globexcamsms.com/api/mt/SendSMS',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Camoo',
                'settings' => 'Camoo',
                'api_link' => 'https://api.camoo.cm/v1/sms.json',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Kannel',
                'settings' => 'Kannel',
                'api_link' => 'http://127.0.0.1:14002/cgi-bin/sendsms',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Semysms',
                'settings' => 'Semysms',
                'api_link' => 'https://semysms.net/api/3/sms.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Smsvitrini',
                'settings' => 'Smsvitrini',
                'api_link' => 'http://api.smsvitrini.com/main.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Semaphore',
                'settings' => 'Semaphore',
                'api_link' => 'http://api.semaphore.co/api/v4/messages',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Itexmo',
                'settings' => 'Itexmo',
                'api_link' => 'https://www.itexmo.com/php_api/api.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Chikka',
                'settings' => 'Chikka',
                'api_link' => 'https://post.chikka.com/smsapi/request',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => '1s2u',
                'settings' => '1s2u',
                'api_link' => 'https://1s2u.com/sms/sendsms/sendsms.asp',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Kaudal',
                'settings' => 'Kaudal',
                'api_link' => 'http://keudal.com/assmsserver/assmsserver',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'CMSMS',
                'settings' => 'CMSMS',
                'api_link' => 'https://sgw01.cm.nl/gateway.ashx',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SendOut',
                'settings' => 'SendOut',
                'api_link' => 'https://www.sendoutapp.com/api/v2/envia',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'ViralThrob',
                'settings' => 'ViralThrob',
                'api_link' => 'http://cmsprodbe.viralthrob.com/api/sms_outbounds/send_message',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Masterksnetworks',
                'settings' => 'Masterksnetworks',
                'api_link' => 'http://api.masterksnetworks.com/sendsms/bulksms.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'MessageBird',
                'settings' => 'MessageBird',
                'api_link' => 'https://rest.messagebird.com/messages',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'Yes',
                'mms' => 'Yes',
                'voice' => 'Yes'
            ],
            [
                'name' => 'FortDigital',
                'settings' => 'FortDigital',
                'api_link' => 'https://mx.fortdigital.net/http/send-message',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SMSPRO',
                'settings' => 'SMSPRO',
                'api_link' => 'http://smspro.mtn.ci/bms/soap/messenger.asmx/HTTP_SendSms',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'CNIDCOM',
                'settings' => 'CNIDCOM',
                'api_link' => 'http://www.cnid.com.py/api/api_cnid.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Dialog',
                'settings' => 'Dialog',
                'api_link' => 'https://cpsolutions.dialog.lk/main.php/cbs/sms/send',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'VoiceTrading',
                'settings' => 'VoiceTrading',
                'api_link' => 'https://www.voicetrading.com/myaccount/sendsms.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'AmazonSNS',
                'settings' => 'AmazonSNS',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'NusaSMS',
                'settings' => 'NusaSMS',
                'api_link' => 'http://api.nusasms.com/api/v3/sendsms/plain',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SMS4Brands',
                'settings' => 'SMS4Brands',
                'api_link' => 'http://sms4brands.com//api/sms-api.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'CheapGlobalSMS',
                'settings' => 'CheapGlobalSMS',
                'api_link' => 'http://cheapglobalsms.com/api_v1',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'ExpertTexting',
                'settings' => 'ExpertTexting',
                'api_link' => 'https://www.experttexting.com/ExptRestApi/sms/json/Message/Send',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'LightSMS',
                'settings' => 'LightSMS',
                'api_link' => 'https://www.lightsms.com/external/get/send.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Adicis',
                'settings' => 'Adicis',
                'api_link' => 'http://bs1.adicis.cd/gw0/tuma.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Smsconnexion',
                'settings' => 'Smsconnexion',
                'api_link' => 'http://smsc.smsconnexion.com/api/gateway.aspx',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'BrandedSMS',
                'settings' => 'BrandedSMS',
                'api_link' => 'http://www.brandedsms.net//api/sms-api.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Ibrbd',
                'settings' => 'Ibrbd',
                'api_link' => 'http://wdgw.ibrbd.net:8080/bagaduli/apigiso/sender.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'TxtNation',
                'settings' => 'TxtNation',
                'api_link' => 'http://client.txtnation.com/gateway.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'TeleSign',
                'settings' => 'TeleSign',
                'api_link' => '',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'JasminSMS',
                'settings' => 'JasminSMS',
                'api_link' => 'http://127.0.0.1',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Ezeee',
                'settings' => 'Ezeee',
                'api_link' => 'http://my.ezeee.pk/sendsms_url.html',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ], [
                'name' => 'InfoBipSMPP',
                'settings' => 'InfoBipSMPP',
                'api_link' => 'smpp3.infobip.com',
                'type' => 'smpp',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SMSGlobalSMPP',
                'settings' => 'SMSGlobalSMPP',
                'api_link' => 'smpp.smsglobal.com',
                'type' => 'smpp',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'ClickatellSMPP',
                'settings' => 'ClickatellSMPP',
                'api_link' => 'smpp.clickatell.com',
                'type' => 'smpp',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'JasminSmsSMPP',
                'settings' => 'JasminSmsSMPP',
                'api_link' => 'host_name',
                'type' => 'smpp',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'WavecellSMPP',
                'settings' => 'WavecellSMPP',
                'api_link' => 'smpp.wavecell.com',
                'type' => 'smpp',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Moreify',
                'settings' => 'Moreify',
                'api_link' => 'https://mapi.moreify.com/api/v1/sendSms',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Digitalreachapi',
                'settings' => 'Digitalreachapi',
                'api_link' => 'https://digitalreachapi.dialog.lk/camp_req.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Tropo',
                'settings' => 'Tropo',
                'api_link' => 'https://api.tropo.com/1.0/sessions',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'CheapSMS',
                'settings' => 'CheapSMS',
                'api_link' => 'http://198.24.149.4/API/pushsms.aspx',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'CCSSMS',
                'settings' => 'CCSSMS',
                'api_link' => 'http://193.58.235.30:8001/api',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'MyCoolSMS',
                'settings' => 'MyCoolSMS',
                'api_link' => 'http://www.my-cool-sms.com/api-socket.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SmsBump',
                'settings' => 'SmsBump',
                'api_link' => 'https://api.smsbump.com/send',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'BSG',
                'settings' => 'BSG',
                'api_link' => '',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'SmsBroadcast',
                'settings' => 'SmsBroadcast',
                'api_link' => 'https://api.smsbroadcast.co.uk/api-adv.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'BullSMS',
                'settings' => 'BullSMS',
                'api_link' => 'http://portal.bullsms.com/vendorsms/pushsms.aspx',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'Skebby',
                'settings' => 'Skebby',
                'api_link' => 'https://api.skebby.it/API/v1.0/REST/',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
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
                'name' => 'ClxnetworksHTTPBasic',
                'settings' => 'ClxnetworksHTTPBasic',
                'api_link' => 'http://sms1.clxnetworks.net:3800/sendsms',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],
            [
                'name' => 'ClxnetworksHTTPRest',
                'settings' => 'ClxnetworksHTTPRest',
                'api_link' => 'https://api.clxcommunications.com/xms/v1/awfvq1',
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
                'api_link' => 'https://smsmisr.com/api/webapi',
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
            ],
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
                'two_way' => 'Yes',
                'mms' => 'No',
                'voice' => 'No'
            ],

            /*Version 2.7*/
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
            ], [
                'name' => 'PreciseSMS',
                'settings' => 'PreciseSMS',
                'api_link' => 'https://app.precisesms.co.uk/api/sendsms',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ], [
                'name' => 'Otency',
                'settings' => 'Otency',
                'api_link' => 'http://otency.com/components/com_spc/smsapi.php',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ], [
                'name' => 'MrMessaging',
                'settings' => 'MrMessaging',
                'api_link' => 'https://api.mrmessaging.net/sendsms',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ], [
                'name' => 'IntelTele',
                'settings' => 'IntelTele',
                'api_link' => 'http://api.sms.intel-tele.com/message/send',
                'type' => 'http',
                'status' => 'Inactive',
                'two_way' => 'No',
                'mms' => 'No',
                'voice' => 'No'
            ],

        ];

        foreach ($gateways as $g) {
            SMSGateways::create($g);
        }

    }
}
