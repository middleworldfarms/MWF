/**
 * Created by Your Inspiration on 27/10/2015.
 */
jQuery(function( $ ){

    var collapse = $('.ywcnp_collapse');

    collapse.each(function () {
        $(this).toggleClass('expand').nextUntil('tr.ywcnp_collapse').slideToggle(100);
    });

    $(document).on('click','.ywcnp_collapse',function() {
        $(this).toggleClass('expand').nextUntil('tr.ywcnp_collapse').slideToggle(100);
    });

    $('#_ywcnp_enabled_product').on('change',function(){

        var t= $(this);


      if( $('#product-type').val()=='simple' ) {
          if (t.is(':checked')) {
              $('.options_group.pricing').hide();
              $('.group_nameyourprice').show();
              $('.yith-wcmcs-multi-currency').hide();
          }
          else {
              $('.options_group.pricing').show();
              $('.group_nameyourprice').hide();
            $('.yith-wcmcs-multi-currency').show();
          }
      }
    }).change();


    $(document).on('woocommerce-product-type-change',function(e, select_val, select ) {

        var is_name_your_price = $('#_ywcnp_enabled_product'),
            is_checked = is_name_your_price.is(':checked');

        if( ( select_val=='grouped' || select_val=='simple' ) && is_checked ){

            $('.show_if_nameyourprice').show();
        }
        else
            $('.show_if_nameyourprice').hide();

    });

    $('#ywcnp_btn_override').on('click',function(){

        var input_field = $('input[id^="ywcnp"]'),
            override_field = $('#ywcnp_simple_is_override');

        input_field.prop('readonly', false );
        override_field.val('yes');

        $(this).parents('.options_group').hide();

    });

    $(document).on( 'woocommerce_variations_loaded woocommerce_variations_added', function(){

        $('.variable_is_nameyourprice').each(function(index,element){

             toggle_input_price( $( this ), $(this).is(':checked') );
        });
        $(document).on('change','.variable_is_nameyourprice', function(){

            var t = $(this),
                woocommerce_variation_content = t.closest('.woocommerce_variation'),
                content = woocommerce_variation_content.find('.show_if_variation_nameyourprice');

            if(t.is(':checked'))
                content.show();
            else
            content.hide();

            woocommerce_variation_content.addClass('variation-needs-update');
            toggle_input_price( t, t.is(':checked' ) );

        }).change();

        $(document).on('click','.ywcnp_btn_override',function(){
            var t = $(this),
                woocommerce_variation_content = t.closest('.woocommerce_variation'),
                content = woocommerce_variation_content.find('.show_if_variation_nameyourprice'),
                input_fields = content.find('input:text'),
                hide_field = content.find('input:hidden[name^="ywcnp_variation_is_override"]');

            input_fields.prop( 'readonly', false );
            hide_field.val('yes');
            content.find('.ywcnp_container_override').hide();

        });
    });

    var toggle_input_price = function( element, show ){

        var container = element.closest('.woocommerce_variation'),
            variable_price = container.find('.variable_pricing');


        if( show )
            variable_price.hide();
           else
            variable_price.show();


    };

});
