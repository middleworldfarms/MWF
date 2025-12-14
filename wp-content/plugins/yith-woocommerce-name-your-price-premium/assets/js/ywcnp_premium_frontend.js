/**
 * Created by Your Inspiration on 28/10/2015.
 */
jQuery(  function ( $ ) {

    var is_bundle               = $( '.yith-wcpb-bundle-form' ).length,
        group_table             = $( '.group_table' ),
        nameprice_group         = group_table.find( '.grouped_name_your_price' ),
        name_your_price_form    = $( '#ywcnp_form_name_your_price' ),
        form_add_to_cart        = $( 'form.variations_form' ),
        min_label               = $( '.ywcnp_min_label' ),
        max_label               = $( '.ywcnp_max_label' ),
        add_to_cart_button      = '',
        add_to_cart_button_text = '',
        single_variation_price  = form_add_to_cart.find( '.single_variation' ),
        init_variation_form     = function () {

            if ( form_add_to_cart.length ) {

                name_your_price_form.hide();
                max_label.hide();
                min_label.hide();

                add_to_cart_button      = form_add_to_cart.find( '.single_add_to_cart_button' );
                add_to_cart_button_text = add_to_cart_button.html();
            }

        },
        init_amount_field       = function () {

            $( '.ywcnp_sugg_price' ).on( 'click', function ( e ) {

                $( this ).val( '' );

            } ).focusout( function () {


                var current_value = $( this ).val();

                if ( current_value == '' ) {
                    var old_val = $( this ).data( 'suggest_price' );
                    $( this ).val( old_val );
                }
            } );
        };

    $( 'td.price' ).show();

    if ( nameprice_group ) {

        nameprice_group.next( 'td.price:eq(0)' ).hide();
    }

    //add to cart variation
    if ( is_bundle ) {
        return;
    }

    $( 'body' ).on( 'init-variation-form', function () {
        init_variation_form();
        init_amount_field();
    } ).trigger( 'init-variation-form' );


    $( document ).on( 'found_variation', function ( e, variation ) {

        if ( ( typeof variation.ywcnp_variation != undefined ) && ( variation.ywcnp_variation == 'yes' ) ) {

            var suggest_price = variation.ywcnp_variation_sugg_price,
                min_price     = variation.ywcnp_variation_min_price,
                max_price     = variation.ywcnp_variation_max_price;

            var sugg_field = $( '.ywcnp_sugg_price' ),
                min_field  = $( 'input[name="ywcnp_min"]' ),
                max_field  = $( 'input[name="ywcnp_max"]' );

            sugg_field.val( suggest_price );
            min_field.val( min_price );
            max_field.val( max_price );
            name_your_price_form.show();
            add_to_cart_button.html( variation.add_to_cart_text );
            max_label.hide();
            min_label.hide();

            if ( min_price != '' ) {
                min_label.html( variation.ywcnp_variation_min_price_html );
                min_label.show();
            }
            if ( max_price != '' ) {
                max_label.html( variation.ywcnp_variation_max_price_html );
                max_label.show();
            }
            single_variation_price.find( '.price' ).html( variation.ywcnp_variation_sugg_price_html )

        }
        else {
            name_your_price_form.hide();
            max_label.hide();
            min_label.hide();
            add_to_cart_button.html( add_to_cart_button_text );
        }

    } );

    $( document ).on( 'reset_image', function ( e ) {
        name_your_price_form.hide();
        max_label.hide();
        min_label.hide();
        add_to_cart_button.html( add_to_cart_button_text );
    } );

    $( document ).on( 'click', '.reset_variations', function ( e ) {
        name_your_price_form.hide();
        max_label.hide();
        min_label.hide();
        add_to_cart_button.html( add_to_cart_button_text );

    } );
} );
