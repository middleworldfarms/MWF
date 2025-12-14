jQuery(function ($) {

    $(document).on('yith_ywraq_action_before', function (e) {

        var amount = $(document).find('input[name="ywcnp_amount"]').val().toString(),
            min_amount = $(document).find('input[name="ywcnp_min"]').val().toString(),
            max_amount = $(document).find('input[name="ywcnp_max"]').val().toString(),
            woocommerce_notice = $(document).find( ywcnp_raq.woocommerce_notice_anchor ),
            error_message = '';


        if ($(document).find('.woocommerce-nyp-notice').length) {

            $(document).find('.woocommerce-nyp-notice').remove();
        }

        var valid_format = true,
            regex = new RegExp('[^\-0-9\%\\' + ywcnp_raq.decimal_separator + ']+', 'gi');
        newprice = amount.replace(regex, '');


        invalid_format = amount !== newprice;
        amount = amount.replace(',', '.');

        amount = amount !== '' ? parseFloat( amount ) : '';
        min_amount = min_amount !== '' ? parseFloat( min_amount ) : '';
        max_amount = max_amount !== '' ? parseFloat( max_amount ) : '';

        if (  invalid_format ) {

            error_message = ywcnp_raq.messages.errors.format;
        } else if (amount < 0) {

            error_message = ywcnp_raq.messages.errors.negative;
        } else if (min_amount !== '' && ( amount < min_amount && 'no' === ywcnp_raq.send_raq_without_min ) ) {
            error_message = ywcnp_raq.messages.errors.min;
        } else if (max_amount !== '' && amount > max_amount) {
            error_message = ywcnp_raq.messages.errors.max;
        }

        ywcnp_raq.do_submit = true;
        if (error_message !== '') {

            ywcnp_raq.do_submit = false;

            var div_message = $('<div class="woocommerce-message woocommerce-error woocommerce-nyp-notice" role="alert">');

            div_message.html(error_message);
            woocommerce_notice.append(div_message);
            $(document).scrollTop(0);
            return ywcnp_raq;
        }

    });

});
