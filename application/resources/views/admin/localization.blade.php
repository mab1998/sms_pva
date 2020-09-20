@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Localization')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @include('notification.notify')
            <div class="row">

                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title"> {{language_data('Localization')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" action="{{url('settings/localization-post')}}" method="post">
                                <div class="form-group">
                                    <label for="Country">{{language_data('Default Country')}}</label>
                                    <select name="country" class="form-control selectpicker" data-live-search="true">
                                        {!!countries(app_config('Country'))!!}
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Date Format')}}</label>
                                    <select class="form-control selectpicker" data-live-search="true" name="date_format">
                                        <option value="d/m/Y" @if(app_config('DateFormat') == 'd/m/Y') selected="selected" @endif>15/05/2016</option>
                                        <option value="d.m.Y" @if(app_config('DateFormat') == 'd.m.Y') selected="selected" @endif>15.05.2016</option>
                                        <option value="d-m-Y" @if(app_config('DateFormat') == 'd-m-Y') selected="selected" @endif>15-05-2016</option>
                                        <option value="m/d/Y" @if(app_config('DateFormat') == 'm/d/Y') selected="selected" @endif>05/15/2016</option>
                                        <option value="Y/m/d" @if(app_config('DateFormat') == 'Y/m/d') selected="selected" @endif>2016/05/15</option>
                                        <option value="Y-m-d" @if(app_config('DateFormat') == 'Y-m-d') selected="selected" @endif>2016-05-15</option>
                                        <option value="M d Y" @if(app_config('DateFormat') == 'M d Y') selected="selected" @endif>May 15 2016</option>
                                        <option value="d M Y" @if(app_config('DateFormat') == 'd M Y') selected="selected" @endif>15 May 2016</option>
                                        <option value="jS M y" @if(app_config('DateFormat') == 'jS M y') selected="selected" @endif>15th May 16</option>
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label for="tzone">{{language_data('Timezone')}}</label>
                                    <select name="timezone" class="form-control selectpicker" data-live-search="true">
                                        @foreach (timezoneList() as $value => $label)
                                            <option value="{{$value}}" @if(config('app.timezone')==$value) selected @endif>{{$label}}</option>
                                        @endforeach

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Send SMS')}} {{language_data('Country Code')}}</label>
                                    <select class="selectpicker form-control" name="country_code" data-live-search="true">
                                        <option value="0">{{language_data('Exist on phone number')}}</option>
                                        @foreach($country_code as $code)
                                            <option value="{{$code->country_code}}" @if(app_config('send_sms_country_code') == $code->country_code) selected @endif >{{$code->country_name}} ({{$code->country_code}})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Default Language')}}</label>
                                    <select class="form-control selectpicker" name="language">
                                        @foreach($language_data as $l)
                                            <option value="{{$l->id}}">{{$l->language}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Currency Code')}}</label>
                                    <input type="text" class="form-control" required name="currency_code" value="{{app_config('Currency')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Currency Symbol')}}</label>
                                    <input type="text" class="form-control" required name="currency_symbol" value="{{app_config('CurrencyCode')}}">
                                </div>


                                <div class="form-group">
                                    <label for="currency_symbol_position">{{language_data('Currency Symbol Position')}}</label>
                                    <select class="form-control selectpicker" name="currency_symbol_position">
                                        <option value="left" @if(app_config('currency_symbol_position') == 'left') selected="selected" @endif>{{language_data('Left')}}</option>
                                        <option value="right" @if(app_config('currency_symbol_position') == 'right') selected="selected" @endif>{{language_data('Right')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="cformat">{{language_data('Currency Format')}}</label>
                                    <select class="form-control selectpicker" name="cformat">
                                        <option value="1" @if (app_config('dec_point') == '.' AND app_config('thousands_sep') == '') selected="selected" @endif>1234.56</option>
                                        <option value="2" @if (app_config('dec_point') == '.' AND app_config('thousands_sep') == ',')  selected="selected" @endif>1,234.56</option>
                                        <option value="3" @if (app_config('dec_point') == ',' AND app_config('thousands_sep') == '') selected="selected" @endif>1234,56</option>
                                        <option value="4" @if (app_config('dec_point') == ',' AND app_config('thousands_sep') == '.')  selected="selected" @endif>1.234,56</option>
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label for="currency_decimal_digits">{{language_data('Currency Decimal Digits')}}</label>
                                    <select class="form-control selectpicker" name="currency_decimal_digits">
                                        <option value="0" @if(app_config('currency_decimal_digits') == '0')  selected="selected" @endif>0 (e.g. 100)</option>
                                        <option value="1" @if(app_config('currency_decimal_digits') == '1') selected="selected" @endif>1 (e.g. 100.0)</option>
                                        <option value="2" @if(app_config('currency_decimal_digits') == '2') selected="selected" @endif>2 (e.g. 100.00)</option>
                                        <option value="3" @if(app_config('currency_decimal_digits') == '3') selected="selected" @endif>3 (e.g. 100.000)</option>
                                        <option value="4" @if(app_config('currency_decimal_digits') == '4') selected="selected" @endif>4 (e.g. 100.0000)</option>
                                    </select>
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update')}}</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>


@endsection

{{--External Style Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
@endsection
