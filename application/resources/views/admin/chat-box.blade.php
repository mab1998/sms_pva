@extends('admin')
{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/css/chat-application.css") !!}
    {!! Html::style("assets/libs/perfect-scrollbar/perfect-scrollbar.css") !!}
@endsection


@section('content')
    <div class="container-fluid app-content content p-l-0">
        <div class="row">
            <div class="col-md-12 p-lr-0">
                <div class="page-title-area clearfix">
                    <h2 class="page-title pull-left">{{language_data('Chat SMS')}}</h2>
                </div>
            </div>
        </div>
        <!-- Left Sidebar -->
        <div class="sidebar-left sidebar-fixed">
            <div class="sidebar">
                <div class="sidebar-content d-lg-block">
                    <div class="chart-body chat-fixed-search">
                        <fieldset class="form-group position-relative">
                            <input type="text" class="form-control search-query" id="iconLeft4" placeholder="{{language_data('Search')}}">
                            <div class="form-control-position">
                                <i class="fa fa-search"></i>
                            </div>
                        </fieldset>
                    </div>
                    <div id="users-list" class="list-group position-relative sms_history">
                        <div class="users-list-padding member_list"  id="sms-history-data">
                            @include('admin.get-chat-box')
                        </div>

                        @if ($sms_count > 15)
                            <div class="load_div">
                                <a href="#" class="btn btn-success btn-xs" id="load_more">{{language_data('Load More')}}</a>
                            </div>
                        @endif

                        <div class="ajax-load text-center" style="display: none">
                            <p><img src="<?php echo asset('assets/img/loader.gif'); ?>" id="ajax_image_loader"></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- scroollbar -->

        </div>

        <!-- Chat Content Body -->
        <div class="content-right">
            <div class="content-body">
                <section class="chat-app-window">
                    <div class="chats">
                        <div class="chats">
                            <img src="<?php echo asset('assets/img/loader.gif'); ?>" id="img" style="display: none;"/>
                            <div class="chat_area"></div>
                        </div>
                    </div>
                </section>
                <section class="chat-app-form message_write">
                    <form class="chat-app-input d-flex">
                        <div class="row">
                            <form method="POST" action="#" id="ReplySMS">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <textarea class="form-control" placeholder="{{language_data('Type your message')}}" name="message"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-success" ><i class="fa fa-paper-plane-o d-lg-none"></i></button>

                                    <a href="#" class="btn btn-complete blacklist-btn" data-toggle="tooltip" data-placement="top"
                                       title="{{language_data('Add To Blacklist')}}"> <i class="fa fa-user-md d-lg-none"></i>
                                    </a>

                                    <a href="#" class="btn btn-danger remove-btn" data-toggle="tooltip" data-placement="top"
                                       title="{{language_data('Remove History')}}"> <i class="fa fa-trash d-lg-none"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection

{{--External Style Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/libs/perfect-scrollbar/perfect-scrollbar.js")!!}
    {!! Html::script("assets/js/jscroll.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}

    <script>

        if ($('.sidebar-fixed').length > 0) {
            var ps = new PerfectScrollbar('.sidebar-fixed', {
                theme: 'dark',
            });
        }

        window.waitForFinalEvent = (function () {
            var timers = {};
            return function (callback, ms, uniqueId) {
                if (!uniqueId) {
                    uniqueId = "Don't call this twice without a uniqueId";
                }
                if (timers[uniqueId]) {
                    clearTimeout(timers[uniqueId]);
                }
                timers[uniqueId] = setTimeout(callback, ms);
            };
        })();

        var MessageDiv = $('.message_write');
        var request;
        var details;
        var ChatArea = $(".chat_area");
        var _url = $('#_url').val();
        var ProfileImage = "<?php echo asset('assets/img/avatar.jpg'); ?>";
        var SMSHistory = $('.sms_history');
        var LoadMore = $("#load_more");
        var AjaxImageLoader = $("#ajax_image_loader");
        var page = 1;
        var blacklist_request;
        var sms_history_request;

        MessageDiv.hide();

        $(".search-query").on("keyup", function () {
            window.waitForFinalEvent(function () {

                var value = $(this).val().toLowerCase();
                var loadValue = 'usms_search_'+value;
                loadMoreData(loadValue);
                $(".users-list-padding li").filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });

            }.bind(this), 800, "get_chat_box_history");
        });

        LoadMore.on('click',function (e) {
            e.preventDefault();
            page++;
            loadMoreData(page);
        });

        function loadMoreData(page) {

            $.ajax(
                {
                    url: '?page=' + page,
                    type: "get",
                    beforeSend: function()
                    {
                        LoadMore.hide();
                        AjaxImageLoader.show();
                    }

                })
                .done(function (data) {
                    if (data.html == null) {
                        $('.load_div').html("No more records found");
                        return;
                    }
                    LoadMore.show();
                    AjaxImageLoader.hide();
                    $("#sms-history-data").append(data.html);
                })
                .fail(function (jqXHR, ajaxOptions, thrownError) {
                    alertify.log('server not responding...', 'error');
                });
        }


        SMSHistory.delegate('li', 'click', function (e) {
            e.preventDefault();

            if (request) {
                request.abort();
            }

            ChatArea.empty();

            $('#img').show();
            var id = this.id;

            $(".user_list li").not(this).removeClass('active');
            $(this).addClass('active');

            request = $.ajax({
                type: "POST",
                url: _url + "/sms/view-reports",
                data: {
                    id: id
                },
                cache: false
            });

            $(this).find('.badge').text('');

            // Callback handler that will be called on success
            request.done(function (response, textStatus, jqXHR) {

                $('#img').hide();
                if (response.status == 'error') {
                    alertify.log(response.message, 'error');
                } else {

                    var cwData = response.data;

                    details = '<input type="hidden" value="' + response.sms_id + '" name="sms_id" class="sms_id">';

                    $.each(cwData, function (i, sms) {

                        if (sms.send_by == 'receiver') {
                            details += '<div class="chat">' +
                                '<div class="chat-avatar pull-right">'+
                                '<a class="avatar" href="#">'+
                                '<img src="' + ProfileImage + '" alt="chat-avatar">'+
                                '</a>'+
                                '</div>'+
                                '<div class="chat-body">'+
                                '<div class="chat-content">'+
                                '<p>' + sms.message + '</p>\n' +
                                '</div>'+
                                '</div>'+
                                '</div>';
                        } else {
                            details += '<div class="chat chat-left">' +
                                '<div class="chat-avatar">'+
                                '<a class="avatar" href="#">'+
                                '<img src="' + ProfileImage + '" alt="chat-avatar">'+
                                '</a>'+
                                '</div>'+
                                '<div class="chat-body">'+
                                '<div class="chat-content">'+
                                '<p>' + sms.message + '</p>\n' +
                                '</div>'+
                                '</div>'+
                                '</div>';
                        }
                    });

                    ChatArea.append(details);
                    MessageDiv.show();
                }
            });

            // Callback handler that will be called on failure
            request.fail(function (jqXHR, textStatus, errorThrown) {
                $('#img').hide();
                // Log the error to the console
                alertify.log("The following error occurred: " + textStatus + " " + errorThrown, 'error');
            });

        });

        $("form").submit(function (event) {

            // Prevent default posting of form - put here to work in case of errors
            event.preventDefault();
            // Abort any pending request
            if (request) {
                request.abort();
            }
            // setup some local variables
            var $form = $(this);

            // Let's select and cache all the fields
            var $inputs = $form.find("input, button, textarea");

            var sms_id = $('.sms_id').val();

            // Serialize the data in the form
            var serializedData = $form.serialize()+ "&sms_id=" + sms_id;

            // Let's disable the inputs for the duration of the Ajax request.
            // Note: we disable elements AFTER the form data has been serialized.
            // Disabled form elements will not be serialized.
            $inputs.prop("disabled", true);
            // Fire off the request to /form.php
            request = $.ajax({
                url: _url + "/sms/reply-chat-sms",
                type: "post",
                data: serializedData
            });

            // Callback handler that will be called on success
            request.done(function (response, textStatus, jqXHR) {
                if (response.status == 'success') {
                    alertify.log(response.message, 'success');

                    details = '<div class="chat chat-left">' +
                        '<div class="chat-avatar">'+
                        '<a class="avatar" href="#">'+
                        '<img src="' + ProfileImage + '" alt="chat-avatar">'+
                        '</a>'+
                        '</div>'+
                        '<div class="chat-body">'+
                        '<div class="chat-content">'+
                        '<p>' + response.data + '</p>\n' +
                        '</div>'+
                        '</div>'+
                        '</div>';

                    ChatArea.append(details);
                    MessageDiv.show();

                    $('form').trigger("reset");

                } else {
                    alertify.log(response.message, 'error');
                }
            });

            // Callback handler that will be called on failure
            request.fail(function (jqXHR, textStatus, errorThrown) {
                alertify.log("The following error occurred: " + textStatus + " " + errorThrown, 'error');
            });

            // Callback handler that will be called regardless
            // if the request failed or succeeded
            request.always(function () {
                // Reenable the inputs
                $inputs.prop("disabled", false);
            });
        });

        $(".blacklist-btn").on('click', function (event) {
            event.preventDefault();
            var sms_id = $('.sms_id').val();

            blacklist_request = $.ajax({
                type: "POST",
                url: _url + "/sms/add-to-blacklist",
                data: {
                    sms_id: sms_id
                },
                cache: false
            });

            // Callback handler that will be called on success
            blacklist_request.done(function (response, textStatus, jqXHR) {
                if (response.status == 'error') {
                    alertify.log(response.message, 'error');
                } else {
                    alertify.log(response.message, 'success');
                }
            });

            // Callback handler that will be called on failure
            blacklist_request.fail(function (jqXHR, textStatus, errorThrown) {
                alertify.log("The following error occurred: " + textStatus + " " + errorThrown, 'error');
            });


        })
        $(".remove-btn").on('click', function (event) {
            event.preventDefault();
            var sms_id = $('.sms_id').val();
            sms_history_request = $.ajax({
                type: "POST",
                url: _url + "/sms/remove-chat-history",
                data: {
                    sms_id: sms_id
                },
                cache: false
            });

            // Callback handler that will be called on success
            sms_history_request.done(function (response, textStatus, jqXHR) {
                if (response.status == 'error') {
                    alertify.log(response.message, 'error');
                } else {
                    alertify.log(response.message, 'success');
                    setTimeout(function(){
                        window.location.reload(); // then reload the page.(3)
                    }, 3000);
                }
            });

            // Callback handler that will be called on failure
            sms_history_request.fail(function (jqXHR, textStatus, errorThrown) {
                alertify.log("The following error occurred: " + textStatus + " " + errorThrown, 'error');
            });

        })

    </script>

@endsection
