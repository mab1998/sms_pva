/**
 * Created by SHAMIM on 02-Mar-17.
 */
$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    $(".item-add").on("click", function () {

        var sTable = $("#plan-feature-items");
        var RowAppend = ['<tr class="item-row">',
            '<td data-label="Feature Name"><input type="text" autocomplete="off" name="feature_name[]"  class="form-control feature_name"></td>'+
            '<td data-label="Feature Value"><input type="text" autocomplete="off" name="feature_value[]"  class="form-control feature_value"></td>'+
            '<td data-label="Action"><button class="btn btn-danger btn-sm" id="RemoveITEM" type="button"><i class="fa fa-trash-o"></i> Remove</button></td>'
            , "</tr>"].join("");
        var sLookup = $(RowAppend);

        var feature_name = sLookup.find(".feature_name");
        $(".item-row:last", sTable).after(sLookup);
        $(feature_name).focus();

        sLookup.find("#RemoveITEM").on("click", function () {
            $(this).parents(".item-row").remove();
            if ($(".item-row").length < 2) $("#deleteRow").hide();
        });

        return false
    });

});
