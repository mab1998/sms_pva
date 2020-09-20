<div class="modal fade modal_edit_client_group_{{$cg->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{language_data('Edit',Auth::guard('client')->user()->lan_id)}} {{language_data('Client Group',Auth::guard('client')->user()->lan_id)}}</h4>
            </div>
            <form class="form-some-up form-block" role="form" action="{{url('users/update-group')}}" method="post">


                <div class="modal-body">

                    <div class="form-group">
                        <label>{{language_data('Group Name',Auth::guard('client')->user()->lan_id)}} :</label>
                        <input type="text" class="form-control" required="" name="group_name" value="{{$cg->group_name}}">
                    </div>
                    <br>
                    <div class="form-group">
                        <label>{{language_data('Status',Auth::guard('client')->user()->lan_id)}} :</label>
                        <select class="selectpicker form-control" name="status">
                            <option value="Yes" @if($cg->status=='Yes') selected @endif>{{language_data('Active',Auth::guard('client')->user()->lan_id)}}</option>
                            <option value="No" @if($cg->status=='No') selected @endif>{{language_data('Inactive',Auth::guard('client')->user()->lan_id)}}</option>
                        </select>
                    </div>


                </div>
                <div class="modal-footer">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <input type="hidden" name="cmd" value="{{$cg->id}}">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{language_data('Close',Auth::guard('client')->user()->lan_id)}}</button>
                    <button type="submit" class="btn btn-primary">{{language_data('Update',Auth::guard('client')->user()->lan_id)}}</button>
                </div>

            </form>
        </div>
    </div>

</div>

