@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Language Manage')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @include('notification.notify')
            <div class="row">

                <div class="col-lg-6">
                    <div class="panel">

                        <div class="panel-heading">
                            <h3 class="panel-title"> {{language_data('Language Manage')}}</h3>
                        </div>

                        <div class="panel-body">
                            <form class="" role="form" action="{{url('settings/language-settings-manage-post')}}" method="post" enctype="multipart/form-data">

                                <div class="form-group">
                                    <label>{{language_data('Language')}} {{language_data('Name')}}</label>
                                    <select class="selectpicker form-control" name="language_name" data-live-search="true">
                                        <option value="af_Afrikaans" @if($lan->language == 'Afrikaans') selected @endif>Afrikaans</option>
                                        <option value="sq_Albanian" @if($lan->language == 'Albanian') selected @endif>Albanian</option>
                                        <option value="am_Amharic" @if($lan->language == 'Amharic') selected @endif>Amharic</option>
                                        <option value="ar_Arabic" @if($lan->language == 'Arabic') selected @endif>Arabic</option>
                                        <option value="hy_Armenian" @if($lan->language == 'Armenian') selected @endif>Armenian</option>
                                        <option value="az_Azerbaijan" @if($lan->language == 'Azerbaijan') selected @endif>Azerbaijan</option>
                                        <option value="bn_Bengali" @if($lan->language == 'Bengali') selected @endif>Bengali</option>
                                        <option value="eu_Basque" @if($lan->language == 'Basque') selected @endif>Basque</option>
                                        <option value="be_Belarusian" @if($lan->language == 'Belarusian') selected @endif>Belarusian</option>
                                        <option value="bg_Bulgarian" @if($lan->language == 'Bulgarian') selected @endif>Bulgarian</option>
                                        <option value="ca_Catalan" @if($lan->language == 'Catalan') selected @endif>Catalan</option>
                                        <option value="zh_Chinese" @if($lan->language == 'Chinese') selected @endif>Chinese</option>
                                        <option value="hr_Croatian" @if($lan->language == 'Croatian') selected @endif>Croatian</option>
                                        <option value="cs_Czech" @if($lan->language == 'Czech') selected @endif>Czech</option>
                                        <option value="da_Danish" @if($lan->language == 'Danish') selected @endif>Danish</option>
                                        <option value="nl_Dutch" @if($lan->language == 'Dutch') selected @endif>Dutch</option>
                                        <option value="en_English" @if($lan->language == 'English') selected @endif>English</option>
                                        <option value="et_Estonian" @if($lan->language == 'Estonian') selected @endif>Estonian</option>
                                        <option value="fi_Finnish" @if($lan->language == 'Finnish') selected @endif>Finnish</option>
                                        <option value="fr_French" @if($lan->language == 'French') selected @endif>French</option>
                                        <option value="gl_Galician" @if($lan->language == 'Galician') selected @endif>Galician</option>
                                        <option value="ka_Georgian" @if($lan->language == 'Georgian') selected @endif>Georgian</option>
                                        <option value="de_German" @if($lan->language == 'German') selected @endif>German</option>
                                        <option value="el_Greek" @if($lan->language == 'Greek') selected @endif>Greek</option>
                                        <option value="gu_Gujarati" @if($lan->language == 'Gujarati') selected @endif>Gujarati</option>
                                        <option value="he_Hebrew" @if($lan->language == 'Hebrew') selected @endif>Hebrew</option>
                                        <option value="hi_Hindi" @if($lan->language == 'Hindi') selected @endif>Hindi</option>
                                        <option value="hu_Hungarian" @if($lan->language == 'Hungarian') selected @endif>Hungarian</option>
                                        <option value="is_Icelandic" @if($lan->language == 'Icelandic') selected @endif>Icelandic</option>
                                        <option value="id_Indonesian" @if($lan->language == 'Indonesian') selected @endif>Indonesian</option>
                                        <option value="ga_Irish" @if($lan->language == 'Irish') selected @endif>Irish</option>
                                        <option value="it_Italian" @if($lan->language == 'Italian') selected @endif>Italian</option>
                                        <option value="ja_Japanese" @if($lan->language == 'Japanese') selected @endif>Japanese</option>
                                        <option value="kk_Kazakh" @if($lan->language == 'Kazakh') selected @endif>Kazakh</option>
                                        <option value="ko_Korean" @if($lan->language == 'Korean') selected @endif>Korean</option>
                                        <option value="lv_Latvian" @if($lan->language == 'Latvian') selected @endif>Latvian</option>
                                        <option value="lt_Lithuanian" @if($lan->language == 'Lithuanian') selected @endif>Lithuanian</option>
                                        <option value="mk_Macedonian" @if($lan->language == 'Macedonian') selected @endif>Macedonian</option>
                                        <option value="ms_Malay" @if($lan->language == 'Malay') selected @endif>Malay</option>
                                        <option value="mn_Mongolian" @if($lan->language == 'Mongolian') selected @endif>Mongolian</option>
                                        <option value="ne_Nepali" @if($lan->language == 'Nepali') selected @endif>Nepali</option>
                                        <option value="nb_Norwegian-Bokmal" @if($lan->language == 'Norwegian-Bokmal') selected @endif>Norwegian-Bokmal</option>
                                        <option value="nn_Norwegian-Nynorsk" @if($lan->language == 'Norwegian-Nynorsk') selected @endif>Norwegian-Nynorsk</option>
                                        <option value="fa_Persian" @if($lan->language == 'Persian') selected @endif>Persian</option>
                                        <option value="pl_Polish" @if($lan->language == 'Polish') selected @endif>Polish</option>
                                        <option value="pt_Portuguese" @if($lan->language == 'Portuguese') selected @endif>Portuguese</option>
                                        <option value="ro_Romanian" @if($lan->language == 'Romanian') selected @endif>Romanian</option>
                                        <option value="ru_Russian" @if($lan->language == 'Russian') selected @endif>Russian</option>
                                        <option value="sr_Serbian" @if($lan->language == 'Serbian') selected @endif>Serbian</option>
                                        <option value="si_Sinhala" @if($lan->language == 'Sinhala') selected @endif>Sinhala</option>
                                        <option value="sk_Slovak" @if($lan->language == 'Slovak') selected @endif>Slovak</option>
                                        <option value="sl_Slovenian" @if($lan->language == 'Slovenian') selected @endif>Slovenian</option>
                                        <option value="es_Spanish" @if($lan->language == 'Spanish') selected @endif>Spanish</option>
                                        <option value="sw_Swahili" @if($lan->language == 'Swahili') selected @endif>Swahili</option>
                                        <option value="sv_Swedish" @if($lan->language == 'Swedish') selected @endif>Swedish</option>
                                        <option value="ta_Tamil" @if($lan->language == 'Tamil') selected @endif>Tamil</option>
                                        <option value="te_Telugu" @if($lan->language == 'Telugu') selected @endif>Telugu</option>
                                        <option value="th_Thai" @if($lan->language == 'Thai') selected @endif>Thai</option>
                                        <option value="tr_Turkish" @if($lan->language == 'Turkish') selected @endif>Turkish</option>
                                        <option value="uk_Ukrainian" @if($lan->language == 'Ukrainian') selected @endif>Ukrainian</option>
                                        <option value="ur_Urdu" @if($lan->language == 'Urdu') selected @endif>Urdu</option>
                                        <option value="uz_Uzbek" @if($lan->language == 'Uzbek') selected @endif>Uzbek</option>
                                        <option value="vi_Vietnamese" @if($lan->language == 'Vietnamese') selected @endif>Vietnamese</option>
                                        <option value="cy_Welsh" @if($lan->language == 'Welsh') selected @endif>Welsh</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="selectpicker form-control" name="status">
                                        <option value="Active" @if($lan->status=='Active') selected @endif>{{language_data('Active')}}</option>
                                        <option value="Inactive"  @if($lan->status=='Inactive') selected @endif>{{language_data('Inactive')}}</option>
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Flag')}}</label>
                                    <div class="input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control" name="flag">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control" readonly="">
                                    </div>
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" value="{{$lan->id}}" name="cmd">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update')}} </button>
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
