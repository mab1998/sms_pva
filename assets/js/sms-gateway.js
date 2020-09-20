/**
 * Created by SHAMIM on 02-Mar-17.
 */
$(document).ready(function () {

    var $sms_gateways = $('#sms_gateways');
    var item_remove = $('#item-remove');
    var blank_add = $('#blank-add');
    var gateway_name = $('#gateway_name').val();
    var gateway_custom = $('#gateway_custom').val();
    var gateway_type = $('#gateway_type').val();

    if (gateway_name == 'Telenor') {
        gateway_user_name = 'Msisdn';
    } else if (gateway_name == 'Twilio' || gateway_name == 'Zang' || gateway_name == 'XOXZO' || gateway_name == 'Ytel' || gateway_name == 'ApiFonica') {
        gateway_user_name = 'Account Sid';
    } else if (gateway_name == 'Plivo' || gateway_name == 'PlivoPowerpack' || gateway_name == 'KarixIO') {
        gateway_user_name = 'Auth ID';
    } else if (gateway_name == 'Wavecell') {
        gateway_user_name = 'Sub Account ID';
    } else if (gateway_name == 'MessageBird' || gateway_name == 'AmazonSNS' || gateway_name == 'FlowRoute') {
        gateway_user_name = 'Access Key';
    } else if (gateway_name == 'Clickatell_Touch' || gateway_name == 'ViralThrob' || gateway_name == 'CNIDCOM' || gateway_name == 'SmsBump' || gateway_name == 'BSG' || gateway_name == 'Onehop' || gateway_name == 'TigoBeekun' || gateway_name == 'Beepsend' || gateway_name == 'Easy' || gateway_name == 'Mailjet' || gateway_name == 'Smsgatewayhub' || gateway_name == 'MaskSMS' || gateway_name == 'EblogUs' || gateway_name == 'MessageWhiz' || gateway_name == 'GlobalSMS' || gateway_name == 'ElasticEmail' || gateway_name == 'Nexmo' || gateway_name == 'PreciseSMS' || gateway_name == 'Text Local' || gateway_name == 'SMSKitNet'  || gateway_name == 'PenSMS' || gateway_name == 'Clockworksms' || gateway_name == 'Mocean' || gateway_name == 'Telnyx'  || gateway_name == 'Bics' || gateway_name == 'APIWHA' || gateway_name == 'SMSAPIOnline' || gateway_name == 'ImapServiceOnline' || gateway_name == 'FairPlayerSMS' || gateway_name == 'WhereWeChat' || gateway_name == 'SinchPortal' || gateway_name == 'KSMSUS' || gateway_name == 'SMSTO' || gateway_name == 'TwizoCom' || gateway_name == 'Releans' || gateway_name == 'DidForSale' || gateway_name == 'SMSEdgeIO' || gateway_name == 'sms77IO' || gateway_name == 'SilverStreet' || gateway_name == 'AdnSMS' || gateway_name == 'Voyant' || gateway_name == 'SMS123' || gateway_name == 'EngageSpark' || gateway_name == 'MoviderCo'  || gateway_name == 'TheTexting' || gateway_name == 'VoxoloGy' || gateway_name == 'SmartBuyEstoreComAu' || gateway_name == 'SMSiPanel' || gateway_name == 'TextBelt') {
        gateway_user_name = 'API Key';
    } else if (gateway_name == 'Semysms' || gateway_name == 'Tropo') {
        gateway_user_name = 'User Token';
    } else if (gateway_name == 'SendOut') {
        gateway_user_name = 'Phone Number';
    } else if (gateway_name == 'SignalWire') {
        gateway_user_name = 'Project Key';
    } else if (gateway_name == 'Dialog') {
        gateway_user_name = 'API Key For 160 Characters';
    } else if (gateway_name == 'LightSMS' || gateway_name == 'KingTelecom' || gateway_name == 'Tellegroup' || gateway_name == 'BrilliantComBD') {
        gateway_user_name = 'Login';
    } else if (gateway_name == 'CheapSMS') {
        gateway_user_name = 'Login id';
    } else if (gateway_name == 'TxtNation') {
        gateway_user_name = 'Company';
    }else if (gateway_name == 'CMSMS'){
        gateway_user_name = 'Product Token'
    }else if (gateway_name == 'msg91' || gateway_name == 'Freekasms' || gateway_name == 'MsgOnDND' || gateway_name == 'EpsinsmsIn'){
        gateway_user_name = 'Auth Key'
    }else if (gateway_name == 'ClxnetworksHTTPRest' || gateway_name == 'SmsGatewayMe' || gateway_name == 'WhatsAppChatApi' || gateway_name == 'Gatewayapi' || gateway_name == 'Bandwidth' || gateway_name == 'Envialosimplesms' || gateway_name == 'SlingComNg' || gateway_name == 'Wablas' || gateway_name == 'Waboxapp' || gateway_name == 'SmsGateWay24'){
        gateway_user_name = 'API Token'
    }else if (gateway_name == 'Diamondcard'){
        gateway_user_name = 'Account ID'
    }else if (gateway_name == 'BulkGate' || gateway_name == 'RouteeNet'){
        gateway_user_name = 'Application ID'
    }else if (gateway_name == 'AccessYou'){
        gateway_user_name = 'Account No'
    }else if (gateway_name == 'Montnets'){
        gateway_user_name = 'User ID'
    }else if (gateway_name == 'CARMOVOIPSHORT' || gateway_name == 'CARMOVOIPLONG'){
        gateway_user_name = 'CPF'
    }else if (gateway_name == 'Ovh'){
        gateway_user_name = 'APP Key'
    }else if (gateway_name == 'TheSMSWorks'){
        gateway_user_name = 'Customer ID'
    } else if (gateway_name == 'Send99' || gateway_name == 'Sendpulse'){
        gateway_user_name = 'API ID'
    } else if (gateway_name == 'SMSHubsNet'){
        gateway_user_name = 'Email'
    } else if (gateway_name == 'Apifon'){
        gateway_user_name = 'Secret Key'
    } else if (gateway_name == 'TeleAPI' || gateway_name == 'FortyTwo'){
        gateway_user_name = 'Token'
    } else if (gateway_name == 'LinkMobility'){
        gateway_user_name = 'Production Account'
    } else if (gateway_name == 'ComAPI'){
        gateway_user_name = 'API Space'
    } else if (gateway_name == 'SpeedsmsVN'){
        gateway_user_name = 'API Access Token'
    }  else if (gateway_name == 'Safaricom'){
        gateway_user_name = 'Service ID'
    }  else if (gateway_name == 'VoxCpaas'){
        gateway_user_name = 'Project ID'
    }  else if (gateway_name == 'CellVoz' || gateway_name == 'BroadcasterSMS'){
        gateway_user_name = 'User'
    }  else if (gateway_name == 'SMSLive247'){
        gateway_user_name = 'Owner Email'
    }  else {
        gateway_user_name = 'SMS API User name'
    }

    appended_data = null;

    if (gateway_name != 'MessageBird' && gateway_name != 'SmsGatewayMe' && gateway_name != 'Clickatell_Touch' && gateway_name != 'Tropo' && gateway_name != 'SmsBump' && gateway_name != 'BSG' && gateway_name != 'Beepsend' && gateway_name != 'TigoBeekun' && gateway_name != 'Easy' && gateway_name != 'CMSMS' && gateway_name != 'Mailjet' && gateway_name != 'ClxnetworksHTTPRest' && gateway_name != 'MaskSMS' && gateway_name != 'WhatsAppChatApi' && gateway_name != 'Gatewayapi' && gateway_name != 'MessageWhiz' && gateway_name != 'GlobalSMS' && gateway_name != 'ElasticEmail' && gateway_name != 'PreciseSMS' && gateway_name != 'Text Local' && gateway_name != 'Clockworksms'  && gateway_name != 'Bics' && gateway_name != 'APIWHA' && gateway_name != 'ImapServiceOnline' && gateway_name != 'KSMSUS' && gateway_name != 'SMSTO' && gateway_name != 'TwizoCom' && gateway_name != 'TeleAPI' && gateway_name != 'Releans' && gateway_name != 'SpeedsmsVN' && gateway_name != 'sms77IO' && gateway_name != 'SilverStreet' && gateway_name != 'FortyTwo' && gateway_name != 'Envialosimplesms' && gateway_name != 'SlingComNg' && gateway_name != 'Voyant' && gateway_name != 'SMS123' && gateway_name != 'BroadcasterSMS' && gateway_name != 'VoxoloGy' && gateway_name != 'Wablas' && gateway_name != 'Waboxapp' && gateway_name != 'SMSiPanel' && gateway_name != 'TextBelt') {

        if (gateway_name == 'Twilio' || gateway_name == 'Zang' || gateway_name == 'Plivo' || gateway_name == 'PlivoPowerpack'  || gateway_name == 'KarixIO' || gateway_name == 'XOXZO' || gateway_name == 'MovileCom' || gateway_name == 'Ytel' || gateway_name == 'ApiFonica' || gateway_name == 'VoxCpaas') {
            gateway_password = 'Auth Token';
        } else if (gateway_name == 'SMSKaufen' || gateway_name == 'NibsSMS' || gateway_name == 'LightSMS' || gateway_name == 'Wavecell' || gateway_name == 'ClickSend' || gateway_name == 'IntelTele' || gateway_name == 'SMSHubsNet' || gateway_name == 'AfricasTalking' || gateway_name == 'Bulkness' || gateway_name == 'PhilmoreSMS') {
            gateway_password = 'SMS Api key';
        } else if (gateway_name == 'Semysms' || gateway_name == 'SmsGateWay24' || gateway_name == 'SmartBuyEstoreComAu') {
            gateway_password = 'Device ID';
        } else if (gateway_name == 'MsgOnDND' || gateway_name == 'SMSAPIOnline' || gateway_name == 'SMSEdgeIO') {
            gateway_password = 'Route ID';
        } else if (gateway_name == 'SendOut' || gateway_name == 'SMSKitNet' || gateway_name == 'PenSMS' || gateway_name == 'SignalWire' || gateway_name == 'Trumpia' || gateway_name == 'Apifon' || gateway_name == 'ComAPI' || gateway_name == 'Thinq') {
            gateway_password = 'API Token';
        } else if (gateway_name == 'Ovh' || gateway_name == 'CNIDCOM') {
            gateway_password = 'APP Secret';
        } else if (gateway_name == 'Skebby' || gateway_name == 'KingTelecom' || gateway_name == 'LinkMobility' || gateway_name == 'DidForSale') {
            gateway_password = 'Access Token';
        } else if (gateway_name == 'AmazonSNS' || gateway_name == 'FlowRoute') {
            gateway_password = 'Secret Access Key';
        } else if (gateway_name == 'ViralThrob') {
            gateway_password = 'SaaS Account';
        } else if (gateway_name == 'TxtNation') {
            gateway_password = 'eKey';
        } else if (gateway_name == 'msg91' || gateway_name == 'Freekasms' || gateway_name == 'EpsinsmsIn') {
            gateway_password = 'Route';
        } else if (gateway_name == 'Onehop') {
            gateway_password = 'Label/Route';
        } else if (gateway_name == 'Dialog') {
            gateway_password = 'API Key For 320 Characters';
        } else if (gateway_name == 'Smsgatewayhub') {
            gateway_password = 'Channel';
        } else if (gateway_name == 'Diamondcard') {
            gateway_password = 'Pin code';
        } else if (gateway_name == 'BulkGate') {
            gateway_password = 'Application Token';
        } else if (gateway_name == 'Tellegroup') {
            gateway_password = 'Senha';
        } else if (gateway_name == 'TheSMSWorks' || gateway_name == 'CellVoz') {
            gateway_password = 'Key';
        } else if (gateway_name == 'FairPlayerSMS') {
            gateway_password = 'API Salt';
        } else if (gateway_name == 'Nexmo' || gateway_name == 'Mocean' || gateway_name == 'Sendpulse' || gateway_name == 'SinchPortal' || gateway_name == 'AdnSMS' || gateway_name == 'MoviderCo' || gateway_name == 'TheTexting') {
            gateway_password = 'API Secret';
        } else if (gateway_name == 'EblogUs' || gateway_name == 'BudgetSMS') {
            gateway_password = 'User ID';
        } else if (gateway_name == 'WhereWeChat') {
            gateway_password = 'Service Type (T,P,S,G)';
        } else if (gateway_name == 'Bandwidth') {
            gateway_password = 'API Secret';
        } else if (gateway_name == 'Bondsms') {
            gateway_password = 'API ID';
        } else if (gateway_name == 'RouteeNet') {
            gateway_password = 'Application Secret';
        } else if (gateway_name == 'EngageSpark') {
            gateway_password = 'Organization ID';
        } else if (gateway_name == 'SMSLive247') {
            gateway_password = 'Sub Account Name';
        } else if (gateway_name == 'Telnyx') {
            gateway_password = 'Message Profile ID';
        } else {
            gateway_password = 'SMS Api Password';
        }

        appended_data = appended_data + '<td><div class="form-group"><label>' + gateway_password + ' </label><input type="text" class="form-control" name="gateway_password[]"></div></td>';
    }

    if (gateway_custom == 'Yes' || gateway_name == 'SmsGatewayMe' || gateway_name == 'GlobexCam' || gateway_name == 'Ovh' || gateway_name == 'SMSPRO' || gateway_name == 'DigitalReach' || gateway_name == 'AmazonSNS' || gateway_name == 'ExpertTexting' || gateway_name == 'Advansystelecom' || gateway_name == 'AlertSMS' || gateway_name == 'Clickatell_Central' || gateway_name == 'Smsgatewayhub' || gateway_name == 'Ayyildiz' || gateway_name == 'TwilioCopilot' || gateway_name == 'BudgetSMS' || gateway_name == 'msg91' || gateway_name == 'Freekasms' || gateway_name == 'FortyTwo' || gateway_name == 'TheSMSWorks' || gateway_name == 'ZamtelCoZm' || gateway_name == 'Bandwidth' || gateway_name == 'Safaricom' || gateway_name == 'TigoBusiness' || gateway_name == 'RawMobility' || gateway_name == 'Thinq' || gateway_name == 'SMSLive247' || gateway_name == 'SmsGateWay24') {
        if (gateway_name == 'SmsGatewayMe') {
            gateway_extra = 'Device ID';
        } else if (gateway_name == 'GlobexCam' || gateway_name == 'Clickatell_Central' ) {
            gateway_extra = 'SMS Api key';
        } else if (gateway_name == 'Ovh') {
            gateway_extra = 'Consumer Key';
        } else if (gateway_name == 'SMSPRO') {
            gateway_extra = 'Customer ID';
        } else if (gateway_name == 'msg91' || gateway_name == 'Freekasms' || gateway_name == 'TigoBusiness' ) {
            gateway_extra = 'Country Code';
        } else if (gateway_name == 'DigitalReach') {
            gateway_extra = 'MT Port';
        } else if (gateway_name == 'AmazonSNS') {
            gateway_extra = 'Region';
        } else if (gateway_name == 'Advansystelecom') {
            gateway_extra = 'Operator';
        } else if (gateway_name == 'AlertSMS') {
            gateway_extra = 'Api Token';
        } else if (gateway_name == 'ExpertTexting') {
            gateway_extra = 'SMS Api key';
        } else if (gateway_name == 'Smsgatewayhub' || gateway_name == 'FortyTwo' || gateway_name == 'RawMobility') {
            gateway_extra = 'Route';
        } else if (gateway_name == 'Ayyildiz') {
            gateway_extra = 'BayiKodu';
        } else if (gateway_name == 'TwilioCopilot') {
            gateway_extra = 'Service ID';
        } else if (gateway_name == 'BudgetSMS') {
            gateway_extra = 'Handle';
        } else if (gateway_name == 'TheSMSWorks') {
            gateway_extra = 'Secret';
        } else if (gateway_name == 'ZamtelCoZm') {
            gateway_extra = 'Organization ID';
        } else if (gateway_name == 'Bandwidth') {
            gateway_extra = 'Application ID';
        } else if (gateway_name == 'Safaricom') {
            gateway_extra = 'SPID';
        } else if (gateway_name == 'Thinq') {
            gateway_extra = 'Account ID';
        } else if (gateway_name == 'SMSLive247') {
            gateway_extra = 'Sub Account Password';
        } else if (gateway_name == 'SmsGateWay24') {
            gateway_extra = 'SIM';
        } else {
            gateway_extra = 'Extra Value';
        }
        appended_data = appended_data + '<td><div class="form-group"><label>' + gateway_extra + ' </label><input type="text" class="form-control" name="extra_value[]"></div></td>';
    }

    if (gateway_name == 'Asterisk') {
        appended_data = appended_data + '<td><div class="form-group"><label>Device Name</label><input type="text" class="form-control" name="device_name[]"></div></td>';
    }

    item_remove.on('click', function () {
        $sms_gateways.find('tr.info').fadeOut(300, function () {
            $(this).remove();
            $sms_gateways.find('tr:last').trigger('click').find('td:first input').focus();
        });
    });
    blank_add.on('click', function () {
        $sms_gateways.find('tbody').append(
            '<tr>' +
            '<td><div class="form-group"><label>' + gateway_user_name + ' </label><input type="text" class="form-control" name="gateway_user_name[]"></div></td>' +
            appended_data +
            '<td><div class="form-group"><label>Status</label><select class="selectpicker form-control" name="credential_base_status[]">' +
            '<option value="Active">Active</option>' +
            '<option value="Inactive">Inactive</option>' +
            '</select>' +
            '<span class="help">You can only active one credential information</span></div></td>'+
            '</tr>'
        );
        $sms_gateways.find('tr:last').trigger('click').find('td:first input').focus();
        $('.selectpicker').selectpicker('refresh');
    });
    item_remove.hide();
    $sms_gateways.find('tbody').on('click', 'tr', function () {
        $(this).addClass("info").siblings("tr").removeClass("info").data("focuson", false);
        if ($(this).data('focuson') != true) {
            $(this).data('focuson', true);
        }
        item_remove.show();
    });
});
