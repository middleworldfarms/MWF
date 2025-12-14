/**
 * Created by Your Inspiration on 25/09/2015.
 */

jQuery(function($){

   var  getEnhancedSelectFormatString = function() {
        var formatString = {
            formatMatches: function( matches ) {
                if ( 1 === matches ) {
                    return ywcnp_enhanceselect.i18n_matches_1;
                }

                return ywcnp_enhanceselect.i18n_matches_n.replace( '%qty%', matches );
            },
            formatNoMatches: function() {
                return ywcnp_enhanceselect.i18n_no_matches;
            },
            formatAjaxError: function( jqXHR, textStatus, errorThrown ) {

                return ywcnp_enhanceselect.i18n_ajax_error;
            },
            formatInputTooShort: function( input, min ) {
                var number = min - input.length;

                if ( 1 === number ) {
                    return ywcnp_enhanceselect.i18n_input_too_short_1;
                }

                return ywcnp_enhanceselect.i18n_input_too_short_n.replace( '%qty%', number );
            },
            formatInputTooLong: function( input, max ) {
                var number = input.length - max;

                if ( 1 === number ) {
                    return ywcnp_enhanceselect.i18n_input_too_long_1;
                }

                return ywcnp_enhanceselect.i18n_input_too_long_n.replace( '%qty%', number );
            },
            formatSelectionTooBig: function( limit ) {
                if ( 1 === limit ) {
                    return ywcnp_enhanceselect.i18n_selection_too_long_1;
                }

                return ywcnp_enhanceselect.i18n_selection_too_long_n.replace( '%qty%', limit );
            },
            formatLoadMore: function( pageNumber ) {
                return ywcnp_enhanceselect.i18n_load_more;
            },
            formatSearching: function() {
                return ywcnp_enhanceselect.i18n_searching;
            }
        };

        return formatString;
    }

    $( 'body' ).on( 'ywcnp-enhanced-select-init', function() {

        $( ':input.ywcnp_enhanced_select' ).filter( ':not(.enhanced)' ).each( function() {
            var select2_args = {
                allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
                placeholder: $( this ).data( 'placeholder' ),
                minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
                escapeMarkup: function( m ) {
                    return m;
                },
                ajax: {
                    method: 'GET',
                    url:         ywcnp_enhanceselect.ajax_url,
                    dataType:    'json',
                    quietMillis: 250,
                    data: function( term, page ) {
                        return {
                            term:     term,
                            action:   $( this ).data( 'action' ) || 'yith_json_search_product_categories',
                            security: ywcnp_enhanceselect.search_categories_nonce,
                            plugin: ywcnp_enhanceselect.plugin_nonce
                        };
                    },
                    results: function( data, page ) {

                        var terms = [];
                        if ( data ) {
                            $.each( data, function( id, text ) {
                                terms.push( { id: id, text: text } );
                            });
                        }
                        return { results: terms };
                    },
                    cache: true
                }
            };


            if ( $( this ).data( 'multiple' ) === true ) {
                select2_args.multiple = true;
                select2_args.initSelection = function( element, callback ) {
                    var data     = $.parseJSON( element.attr( 'data-selected' ) );
                    var selected = [];

                    $( element.val().split( "," ) ).each( function( i, val ) {
                        selected.push( { id: val, text: data[ val ] } );
                    });
                    return callback( selected );
                };
                select2_args.formatSelection = function( data ) {
                    return '<div class="selected-option" data-id="' + data.id + '">' + data.text + '</div>';
                };
            } else {
                select2_args.multiple = false;
                select2_args.initSelection = function( element, callback ) {

                    var data = {id: element.val(), text: element.attr( 'data-selected' )};
                    return callback( data );
                };
            }

            select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

            $( this ).select2( select2_args ).addClass( 'enhanced' );
        });


    }).trigger( 'ywcnp-enhanced-select-init' );

});
