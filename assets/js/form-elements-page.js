/*
 --------------------------------------
 ---------- Input Group File ----------
 --------------------------------------
 */

$.fn.inputFile = function () {
    var $this = $(this);
    $this.find('input[type="file"]').on('change', function () {
        $this.find('input[type="text"]').val($(this).val());
    });
}

$('.input-group-file').inputFile();


/*
 --------------------------------------
 ---------- Date Time Picker ----------
 --------------------------------------
 */

if ($.fn.datetimepicker) {

    $('.datePicker').datetimepicker({
        keepOpen: true,
        format: 'YYYY-MM-DD'
    });

    $('.timePicker').datetimepicker({
        keepOpen: true,
        format: 'LT'
    });

    $('.dateTimePicker').datetimepicker({
        keepOpen: true
    });
}

function splitMulti(str, tokens){
  var tempChar = tokens[0]; // We can use the first token as a temporary join character
  for(var i = 1; i < tokens.length; i++){
    str = str.split(tokens[i]).join(tempChar);
  }
  str = str.split(tempChar);
  return str;
}