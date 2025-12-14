/**
 * Created by Your Inspiration on 22/10/2015.
 */

jQuery(function($){

    var check_is_enabled_field = $('#_ywcnp_enabled_product'),
        disabled_field_message = $('#ywcnp_disabled_field_message');

    check_is_enabled_field.on('change',function(){

        if( $(this).is(':checked') ) {
            $('.pricing input').prop('readonly', true);
            disabled_field_message.show();
        }
        else {
            $('.pricing input').prop('readonly', false);
            disabled_field_message.hide();
        }

    }).change();


   $('#product-type').on('change', function(){

        var product_type = $(this).val();

        if( product_type=='simple')
            check_is_enabled_field.change();
        else {
            $('.pricing input').prop('readonly', false);
            disabled_field_message.hide();
        }

    });
});
