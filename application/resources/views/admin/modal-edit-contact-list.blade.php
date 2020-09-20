<div class="modal fade modal_edit_list_{{$cg->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{language_data('Edit List')}}</h4>
            </div>
            <form class="form-some-up form-block" role="form" action="{{url('sms/update-phone-book')}}" method="post">

                <div class="modal-body">

                    <div class="form-group">
                        <label>{{language_data('List name')}}</label>
                        <input type="text" class="form-control" required="" name="list_name" value="{{$cg->group_name}}">
                    </div>


                </div>
                <div class="modal-footer">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <input type="hidden" name="cmd" value="{{$cg->id}}">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{language_data('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{language_data('Update')}}</button>
                </div>

            </form>
        </div>
    </div>

</div>

