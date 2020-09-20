@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('SMS Gateway Manage')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <form class="" role="form" method="post" action="{{url('sms/post-manage-sms-gateway')}}">

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">{{language_data('Basic Information')}}</h3>
                                    </div>
                                    <div class="panel-body">
                                        @if($gateway->custom=='Yes')
                                            <div class="form-group">
                                                <label>{{language_data('Gateway Name')}}</label>
                                                <input type="text" class="form-control" required name="gateway_name" value="{{$gateway->name}}">
                                            </div>
                                        @else
                                            <div class="form-group">
                                                <label>{{language_data('Gateway Name')}}</label>
                                                <input type="text" class="form-control" name="gateway_name" value="{{$gateway->name}}" required>
                                            </div>
                                        @endif

                                        @if($gateway->settings!='Twilio' && $gateway->settings!='Zang' && $gateway->settings!='Plivo' && $gateway->settings!='PlivoPowerpack' && $gateway->settings!='AmazonSNS' && $gateway->settings!='TeleSign' && $gateway->settings!='TwilioCopilot' && $gateway->settings!='Ovh' && $gateway->settings!='Sendpulse')
                                            <div class="form-group">
                                                <label>{{language_data('Gateway API Link')}}</label>
                                                <input type="text" class="form-control" required name="gateway_link" value="{{$gateway->api_link}}">
                                            </div>
                                        @endif

                                        @if($gateway->settings=='Asterisk' || $gateway->settings=='JasminSMS' || $gateway->settings=='Diafaan' || $gateway->settings=='Ovh' || $gateway->type=='smpp' || $gateway->settings=='Send99' || $gateway->settings=='Mobitel')
                                            <div class="form-group">
                                                @if($gateway->settings=='Ovh')
                                                    <label>API End Point</label>
                                                    <input type="text" class="form-control" name="port" value="{{$gateway->port}}">
                                                @elseif($gateway->settings=='Send99' || $gateway->settings=='Mobitel')
                                                    <label>SMS Type </label>
                                                    <select class="selectpicker form-control" name="port">
                                                        <option value="promotional" @if($gateway->port=='promotional') selected @endif>Promotional</option>
                                                        <option value="transactional" @if($gateway->port=='transactional') selected @endif>Transactional</option>
                                                    </select>

                                                @else
                                                    <label>Port</label>
                                                    <input type="text" class="form-control" name="port" value="{{$gateway->port}}">
                                                @endif
                                            </div>
                                        @endif



                                        @if($gateway->type=='smpp')
                                            <div class="form-group">
                                                <label>{{language_data('Two way')}}</label>
                                                <select class="selectpicker form-control" name="two_way">
                                                    <option value="Yes" @if($gateway->two_way=='Yes') selected @endif>{{language_data('Yes')}}</option>
                                                    <option value="No" @if($gateway->two_way=='No') selected @endif>{{language_data('No')}}</option>
                                                </select>
                                            </div>
                                        @endif


                                        <div class="form-group">
                                            <label>{{language_data('Schedule SMS')}}</label>
                                            <select class="selectpicker form-control" name="schedule">
                                                <option value="Yes" @if($gateway->schedule=='Yes') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="No" @if($gateway->schedule=='No') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Global {{language_data('Status')}}</label>
                                            <select class="selectpicker form-control" name="global_status">
                                                <option value="Active" @if($gateway->status=='Active') selected @endif>{{language_data('Active')}}</option>
                                                <option value="Inactive" @if($gateway->status=='Inactive') selected @endif>{{language_data('Inactive')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-lg-9">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">{{language_data('Credential Setup')}}</h3>
                                    </div>
                                    <div class="panel-body">
                                        <table class="table table-hover" id="sms_gateways">
                                            <tbody>
                                            @foreach($credentials as $credential)
                                                <tr class="info">
                                                    <td>
                                                        <div class="form-group">
                                                            <label>
                                                                @if($gateway->settings=='Telenor')
                                                                    {{language_data('Msisdn')}}
                                                                @elseif($gateway->settings=='Twilio' || $gateway->settings=='Zang' || $gateway->settings=='XOXZO' || $gateway->settings=='Ytel' || $gateway->settings=='ApiFonica')
                                                                    {{language_data('Account Sid')}}
                                                                @elseif($gateway->settings=='Plivo' || $gateway->settings=='PlivoPowerpack' || $gateway->settings=='KarixIO')
                                                                    {{language_data('Auth ID')}}
                                                                @elseif($gateway->settings=='Wavecell')
                                                                    Sub Account ID
                                                                @elseif($gateway->settings=='Ovh')
                                                                    APP Key
                                                                @elseif($gateway->settings=='MessageBird' || $gateway->settings=='AmazonSNS' || $gateway->settings=='FlowRoute')
                                                                    Access Key
                                                                @elseif($gateway->settings=='Clickatell_Touch' || $gateway->settings=='ViralThrob' || $gateway->settings=='CNIDCOM' || $gateway->settings=='SmsBump' || $gateway->settings=='BSG' || $gateway->settings=='Onehop' || $gateway->settings=='TigoBeekun' || $gateway->settings=='Beepsend' || $gateway->settings=='Easy' || $gateway->settings=='Mailjet' || $gateway->settings=='Smsgatewayhub' || $gateway->settings=='MaskSMS'  || $gateway->settings == 'EblogUs' || $gateway->settings == 'MessageWhiz' || $gateway->settings == 'GlobalSMS' || $gateway->settings == 'ElasticEmail'  || $gateway->settings == 'Nexmo' || $gateway->settings == 'PreciseSMS' || $gateway->settings == 'Text Local' || $gateway->settings == 'SMSKitNet' || $gateway->settings == 'PenSMS' || $gateway->settings == 'Clockworksms' || $gateway->settings == 'Mocean' || $gateway->settings == 'Telnyx' || $gateway->settings == 'Bics' || $gateway->settings == 'APIWHA' || $gateway->settings == 'SMSAPIOnline' || $gateway->settings == 'ImapServiceOnline' || $gateway->settings == 'FairPlayerSMS' || $gateway->settings == 'WhereWeChat' || $gateway->settings == 'SinchPortal' || $gateway->settings == 'KSMSUS' || $gateway->settings == 'SMSTO' || $gateway->settings == 'TwizoCom' || $gateway->settings == 'Releans' || $gateway->settings == 'DidForSale' || $gateway->settings == 'SMSEdgeIO' || $gateway->settings == 'sms77IO' || $gateway->settings == 'SilverStreet' || $gateway->settings == 'AdnSMS' || $gateway->settings == 'Voyant' || $gateway->settings == 'SMS123' || $gateway->settings == 'EngageSpark' || $gateway->settings == 'MoviderCo' || $gateway->settings == 'TheTexting' || $gateway->settings == 'VoxoloGy' || $gateway->settings == 'SmartBuyEstoreComAu' || $gateway->settings == 'SMSiPanel' || $gateway->settings == 'TextBelt')
                                                                    API Key
                                                                @elseif($gateway->settings=='Semysms' || $gateway->settings=='Tropo')
                                                                    User Token
                                                                @elseif($gateway->settings=='SendOut')
                                                                    Phone Number
                                                                @elseif($gateway->settings=='SignalWire')
                                                                    Project Key
                                                                @elseif($gateway->settings=='Dialog')
                                                                    API Key For 160 Characters
                                                                @elseif($gateway->settings=='LightSMS' || $gateway->name=='KingTelecom' || $gateway->name=='Tellegroup' || $gateway->name=='BrilliantComBD')
                                                                    Login
                                                                @elseif($gateway->settings=='CheapSMS')
                                                                    Login ID
                                                                @elseif($gateway->settings=='TxtNation')
                                                                    Company
                                                                @elseif($gateway->settings=='CMSMS')
                                                                    Product Token
                                                                @elseif($gateway->settings=='ClxnetworksHTTPRest' || $gateway->settings=='SmsGatewayMe' || $gateway->settings=='WhatsAppChatApi' || $gateway->settings=='Gatewayapi' || $gateway->settings=='Bandwidth' || $gateway->settings=='Envialosimplesms' || $gateway->settings=='SlingComNg' || $gateway->settings=='Wablas' || $gateway->settings=='Waboxapp' || $gateway->settings=='SmsGateWay24')
                                                                    API Token
                                                                @elseif($gateway->settings=='Diamondcard')
                                                                    Account ID
                                                                @elseif($gateway->settings=='BulkGate' || $gateway->settings=='RouteeNet')
                                                                    Application ID
                                                                @elseif($gateway->settings=='msg91' || $gateway->settings=='Freekasms' || $gateway->settings=='MsgOnDND' || $gateway->settings=='EpsinsmsIn')
                                                                    Auth Key
                                                                @elseif($gateway->settings=='AccessYou')
                                                                    Account No
                                                                @elseif($gateway->settings=='Montnets')
                                                                    User ID
                                                                @elseif($gateway->settings=='TheSMSWorks')
                                                                    Customer ID
                                                                @elseif($gateway->settings=='Send99' || $gateway->settings=='Sendpulse')
                                                                    API ID
                                                                @elseif($gateway->settings=='CARMOVOIPSHORT' || $gateway->settings=='CARMOVOIPLONG')
                                                                    CPF
                                                                @elseif($gateway->settings=='SMSHubsNet')
                                                                    Email
                                                                @elseif($gateway->settings=='Apifon')
                                                                    Secret Key
                                                                @elseif($gateway->settings=='TeleAPI' || $gateway->settings=='FortyTwo')
                                                                    Token
                                                                @elseif($gateway->settings=='LinkMobility')
                                                                    Production Account
                                                                @elseif($gateway->settings=='ComAPI')
                                                                    API Space
                                                                @elseif($gateway->settings=='SpeedsmsVN')
                                                                    API Access Token
                                                                @elseif($gateway->settings=='Safaricom')
                                                                    Service ID
                                                                @elseif($gateway->settings=='VoxCpaas')
                                                                    Project ID
                                                                @elseif($gateway->settings=='CellVoz'  || $gateway->settings=='BroadcasterSMS')
                                                                    User
                                                                @elseif($gateway->settings=='SMSLive247')
                                                                    Owner Email
                                                                @else
                                                                    {{language_data('SMS Api User name')}}
                                                                @endif
                                                            </label>
                                                            <input type="text" class="form-control" name="gateway_user_name[]" value="{{$credential->username}}">
                                                        </div>
                                                    </td>
                                                    @if($gateway->settings!='MessageBird' && $gateway->settings!='SmsGatewayMe' && $gateway->settings!='Clickatell_Touch' && $gateway->settings!='Tropo' && $gateway->settings!='SmsBump' && $gateway->settings!='BSG' && $gateway->settings!='Beepsend' && $gateway->settings!='TigoBeekun' && $gateway->settings!='Easy' && $gateway->settings!='CMSMS' && $gateway->settings != 'Mailjet' && $gateway->settings != 'ClxnetworksHTTPRest' && $gateway->settings != 'MaskSMS' && $gateway->settings!='WhatsAppChatApi' && $gateway->settings!='Gatewayapi' && $gateway->settings!='MessageWhiz' && $gateway->settings!='GlobalSMS' && $gateway->settings != 'ElasticEmail' && $gateway->settings != 'PreciseSMS' && $gateway->settings != 'Text Local' && $gateway->settings != 'Clockworksms' && $gateway->settings != 'Bics' && $gateway->settings != 'APIWHA' && $gateway->settings != 'ImapServiceOnline' && $gateway->settings != 'KSMSUS' && $gateway->settings != 'SMSTO' && $gateway->settings != 'TwizoCom' && $gateway->settings != 'TeleAPI' && $gateway->settings != 'Releans' && $gateway->settings != 'SpeedsmsVN' && $gateway->settings != 'sms77IO' && $gateway->settings != 'SilverStreet' && $gateway->settings != 'FortyTwo' && $gateway->settings != 'Envialosimplesms' && $gateway->settings != 'SlingComNg' && $gateway->settings != 'Voyant' && $gateway->settings != 'SMS123' && $gateway->settings != 'BroadcasterSMS' && $gateway->settings != 'VoxoloGy' && $gateway->settings != 'Wablas' && $gateway->settings != 'Waboxapp' && $gateway->settings != 'SMSiPanel' && $gateway->settings != 'TextBelt')
                                                        <td>

                                                            <div class="form-group">
                                                                <label>
                                                                    @if($gateway->settings=='Twilio' || $gateway->settings=='Zang' || $gateway->settings=='Plivo' || $gateway->settings=='PlivoPowerpack' || $gateway->settings=='KarixIO' || $gateway->settings=='XOXZO' || $gateway->settings=='MovileCom' || $gateway->settings=='Ytel' || $gateway->settings=='ApiFonica' || $gateway->settings=='VoxCpaas')
                                                                        {{language_data('Auth Token')}}
                                                                    @elseif($gateway->settings=='SMSKaufen' || $gateway->settings=='NibsSMS' || $gateway->settings=='LightSMS' || $gateway->settings=='Wavecell' || $gateway->settings == 'ClickSend' || $gateway->settings == 'IntelTele' || $gateway->settings == 'SMSHubsNet' || $gateway->settings == 'AfricasTalking'  || $gateway->settings == 'Bulkness'  || $gateway->settings == 'PhilmoreSMS')
                                                                        {{language_data('SMS Api key')}}
                                                                    @elseif($gateway->settings=='Semysms' || $gateway->settings=='SmsGateWay24' || $gateway->settings=='SmartBuyEstoreComAu')
                                                                        Device ID
                                                                    @elseif($gateway->name=='Skebby' || $gateway->name=='KingTelecom' || $gateway->name=='LinkMobility' || $gateway->name=='DidForSale')
                                                                        Access Token
                                                                    @elseif($gateway->settings=='SendOut'  || $gateway->settings == 'SMSKitNet'  || $gateway->settings == 'PenSMS' || $gateway->settings == 'SignalWire' || $gateway->settings == 'Trumpia' || $gateway->settings == 'Apifon' || $gateway->settings == 'ComAPI' || $gateway->settings == 'Thinq')
                                                                        API Token
                                                                    @elseif($gateway->settings=='Ovh'  || $gateway->settings=='CNIDCOM')
                                                                        APP Secret
                                                                    @elseif($gateway->settings=='AmazonSNS' || $gateway->settings=='FlowRoute' )
                                                                        Secret Access Key
                                                                    @elseif($gateway->settings=='ViralThrob')
                                                                        SaaS Account
                                                                    @elseif($gateway->settings=='TxtNation')
                                                                        eKey
                                                                    @elseif($gateway->settings=='MsgOnDND' || $gateway->settings=='SMSAPIOnline' || $gateway->settings=='SMSEdgeIO')
                                                                        Route ID
                                                                    @elseif($gateway->settings=='Onehop')
                                                                        Label/Route
                                                                    @elseif($gateway->settings=='Dialog')
                                                                        API Key For 320 Characters
                                                                    @elseif($gateway->settings=='Smsgatewayhub')
                                                                        Channel
                                                                    @elseif($gateway->settings=='Diamondcard')
                                                                        Pin code
                                                                    @elseif($gateway->settings=='BulkGate')
                                                                        Application Token
                                                                    @elseif($gateway->settings=='Tellegroup')
                                                                        Senha
                                                                    @elseif($gateway->settings == 'Nexmo' || $gateway->settings == 'Mocean' || $gateway->settings == 'Sendpulse' || $gateway->settings == 'SinchPortal' || $gateway->settings == 'AdnSMS' || $gateway->settings == 'TheTexting')
                                                                        API Secret
                                                                    @elseif($gateway->settings == 'EblogUs' || $gateway->settings == 'BudgetSMS')
                                                                        User ID
                                                                    @elseif($gateway->settings=='msg91' || $gateway->settings=='Freekasms' || $gateway->settings=='EpsinsmsIn')
                                                                        Route
                                                                    @elseif($gateway->settings=='TheSMSWorks' || $gateway->settings=='CellVoz')
                                                                        Key
                                                                    @elseif($gateway->settings=='FairPlayerSMS')
                                                                        API Salt
                                                                    @elseif($gateway->settings=='WhereWeChat')
                                                                        Service Type (T,P,S,G)
                                                                    @elseif($gateway->settings=='Bandwidth' || $gateway->settings=='MoviderCo')
                                                                        API Secret
                                                                    @elseif($gateway->settings=='Bondsms')
                                                                        API ID
                                                                    @elseif($gateway->settings=='RouteeNet')
                                                                        Application Secret
                                                                    @elseif($gateway->settings=='EngageSpark')
                                                                        Organization ID
                                                                    @elseif($gateway->settings=='SMSLive247')
                                                                        Sub Account Name
                                                                    @elseif($gateway->settings=='Telnyx')
                                                                        Message Profile ID
                                                                    @else
                                                                        {{language_data('SMS Api Password')}}
                                                                    @endif
                                                                </label>
                                                                <input type="text" class="form-control" name="gateway_password[]" value="{{$credential->password}}">
                                                            </div>
                                                        </td>
                                                    @endif

                                                    @if($gateway->custom=='Yes' || $gateway->settings=='SmsGatewayMe' || $gateway->settings=='GlobexCam' || $gateway->settings=='Ovh' || $gateway->settings=='SMSPRO' || $gateway->settings=='DigitalReach' || $gateway->settings=='AmazonSNS' || $gateway->settings=='ExpertTexting' || $gateway->settings == 'Advansystelecom' || $gateway->settings == 'AlertSMS' || $gateway->settings == 'Clickatell_Central' || $gateway->settings == 'Smsgatewayhub' || $gateway->settings == 'Ayyildiz' || $gateway->settings == 'TwilioCopilot' || $gateway->settings == 'BudgetSMS' || $gateway->settings=='msg91' || $gateway->settings=='Freekasms' || $gateway->settings=='FortyTwo' || $gateway->settings=='TheSMSWorks' || $gateway->settings=='ZamtelCoZm' || $gateway->settings=='Bandwidth' || $gateway->settings=='Safaricom' || $gateway->settings=='TigoBusiness' || $gateway->settings=='RawMobility' || $gateway->settings=='Thinq' || $gateway->settings=='SMSLive247' || $gateway->settings=='SmsGateWay24')
                                                        <td>

                                                            <div class="form-group">
                                                                @if($gateway->settings=='SmsGatewayMe')
                                                                    <label>Device ID</label>
                                                                @elseif($gateway->settings=='GlobexCam' || $gateway->settings == 'Clickatell_Central')
                                                                    <label>{{language_data('SMS Api key')}}</label>
                                                                @elseif($gateway->settings=='Ovh')
                                                                    <label>Consumer Key</label>
                                                                @elseif($gateway->settings=='SMSPRO')
                                                                    <label>Customer ID</label>
                                                                @elseif($gateway->settings=='msg91' || $gateway->settings=='Freekasms' || $gateway->settings=='TigoBusiness')
                                                                    Country Code
                                                                @elseif($gateway->settings=='DigitalReach')
                                                                    <label>MT Port</label>
                                                                @elseif($gateway->settings=='AmazonSNS')
                                                                    <label>Region</label>
                                                                @elseif($gateway->settings == 'Advansystelecom')
                                                                    <label>Operator</label>
                                                                @elseif($gateway->settings == 'Smsgatewayhub' || $gateway->settings == 'FortyTwo' || $gateway->settings == 'RawMobility')
                                                                    <label>Route</label>
                                                                @elseif($gateway->settings == 'AlertSMS')
                                                                    <label>Api Token</label>
                                                                @elseif($gateway->settings=='ExpertTexting')
                                                                    <label> {{language_data('SMS Api key')}}</label>
                                                                @elseif($gateway->settings=='Ayyildiz')
                                                                    <label> BayiKodu</label>
                                                                @elseif($gateway->settings=='TwilioCopilot')
                                                                    <label> Service ID</label>
                                                                @elseif($gateway->settings == 'BudgetSMS')
                                                                    <label>Handle</label>
                                                                @elseif($gateway->settings == 'TheSMSWorks')
                                                                    <label>Secret</label>
                                                                @elseif($gateway->settings == 'ZamtelCoZm')
                                                                    <label>Organization ID</label>
                                                                @elseif($gateway->settings == 'Bandwidth')
                                                                    <label>Application ID</label>
                                                                @elseif($gateway->settings == 'Safaricom')
                                                                    <label>SPID</label>
                                                                @elseif($gateway->settings == 'Thinq')
                                                                    <label>Account ID</label>
                                                                @elseif($gateway->settings == 'SMSLive247')
                                                                    <label>Sub Account Password</label>
                                                                @elseif($gateway->settings == 'SmsGateWay24')
                                                                    <label>SIM</label>
                                                                @else
                                                                    <label>{{language_data('Extra Value')}}</label>
                                                                @endif
                                                                <input type="text" class="form-control" name="extra_value[]" value="{{$credential->extra}}">
                                                            </div>
                                                        </td>
                                                    @endif
                                                    @if($gateway->settings=='Asterisk' )
                                                        <td>
                                                            <div class="form-group">
                                                                <label>Device Name</label>
                                                                <input type="text" class="form-control" name="device_name" value="{{env('SC_DEVICE')}}">
                                                            </div>
                                                        </td>
                                                    @endif

                                                    <td>
                                                        <div class="form-group">
                                                            <label>{{language_data('Credential Base Status')}}</label>
                                                            <select class="selectpicker form-control" name="credential_base_status[]">
                                                                <option value="Active" @if($credential->status=='Active') selected @endif>{{language_data('Active')}}</option>
                                                                <option value="Inactive" @if($credential->status=='Inactive') selected @endif>{{language_data('Inactive')}}</option>
                                                            </select>
                                                            <span class="help">{{language_data('You can only active one credential information')}}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>


                                        <div class="row bottom-inv-con">
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-success" id="blank-add"><i
                                                            class="fa fa-plus"></i> {{language_data('Add New')}}
                                                </button>
                                                <button type="button" class="btn btn-danger" id="item-remove"><i
                                                            class="fa fa-minus-circle"></i> {{language_data('Delete')}}
                                                </button>
                                            </div>
                                            <div class="col-md-6"></div>
                                        </div>

                                        <div class="text-right">
                                            <input type="hidden" value="{{$gateway->id}}" name="cmd">
                                            <input type="hidden" value="{{$gateway->settings}}" id="gateway_name">
                                            <input type="hidden" value="{{$gateway->custom}}" id="gateway_custom">
                                            <input type="hidden" value="{{$gateway->type}}" id="gateway_type">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update')}} </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </section>

@endsection

{{--External Script Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/sms-gateway.js")!!}


@endsection
