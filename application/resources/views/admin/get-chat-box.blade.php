<ul class="media-list user_list">
    @foreach($sms_history as $history)
        <li class="media" id="{{$history->id}}">    <!-- active visitor -->
            <div class="media-left">
            <span class="avatar avatar-md avatar-online">
                <img class="media-object rounded-circle" src="{{asset('assets/img/avatar.jpg')}}" alt="Image">
            </span>
            </div>
            <div class="media-body">
                <h6 class="list-group-item-heading">{{get_contact_info($history->sender)}}
                    <span class="font-small-3 pull-right primary chat-time">{{date('y-m-d h:m A', strtotime($history->updated_at)) }}</span>
                </h6>
                <p class="list-group-item-text">
                    {{$history->receiver}}
                    <span class="badge chat-bg-primary pull-right">{{get_unread_notification($history->id)}}</span>
                </p>
            </div>
        </li>
    @endforeach

</ul>
