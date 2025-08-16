(function($) {

    $(document).ready(function () {
        $(document.body).on('updated_checkout', function() {
            var defaultValue = $('#mm_operator_field select').val();
            checkValue(defaultValue);
    
            $('#mm_operator_field select').on('change', function() {
               checkValue(this.value);
            });
        })
    })

    function checkValue(value){
        var instruction = mmpayment_data.operators[value];
        
        if(instruction && instruction !== ""){
            var message = "Composez <b>" + instruction + "</b>";
            $("#mm_instruction").html(message);
        } else {
            $("#mm_instruction").html("");
        }
    }

})( jQuery );