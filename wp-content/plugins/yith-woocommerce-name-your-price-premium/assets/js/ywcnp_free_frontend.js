/**
 * Created by Your Inspiration on 23/10/2015.
 */
jQuery( function($){

    var woocommerce_message_error =$('<div>',{'class':"woocommerce-error name-your-price-error"}),
        woocommerce_breadcrumb = $('.woocommerce-breadcrumb');

    $('.single_add_to_cart_button').on('click', function(e){

        var price_field = $('.ywcnp_sugg_price');

      if( price_field.length ) {

            var price = price_field.val(),
                regex = new RegExp('[^\-0-9\%\\' + yith_name_your_price.mon_decimal_point + ']+', 'gi');
                newprice = price.replace(regex, ''),
                error_message = '';


            if (price !== newprice) {
                error_message = '<span class="decimal_error">' + yith_name_your_price.mon_decimal_error + '</span>';
            }
            else if (( price * 1 ) < 0) {

                error_message = '<span class="negative_error">' + yith_name_your_price.mon_negative_error + '<span>';
            }

            if (error_message == '') {

                $('.woocommerce-error.name-your-price-error').remove();
            } else {

                woocommerce_message_error.html(error_message);
                woocommerce_breadcrumb.after(woocommerce_message_error);
                $(document).scrollTop(0);
                e.preventDefault();
                return false;
            }
        }

    });
});
