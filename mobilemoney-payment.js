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
        const instruction = mmpayment_data.operators[value];
        
        if(instruction && instruction !== ""){
            const message = instruction.length <=7 ? "Composez <b>" + instruction + "</b>" : instruction;
            $("#mm_instruction").html(message);
        } else {
            $("#mm_instruction").html("");
        }
    }

})( jQuery );