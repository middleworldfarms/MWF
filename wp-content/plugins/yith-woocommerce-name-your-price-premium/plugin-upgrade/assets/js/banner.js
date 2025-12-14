/* globals yith, ajaxurl, jQuery */
( function ( $ ) {

    if ( typeof yith === 'undefined' ) {
        return;
    }

    $( function () {
        var content  = $( '.yith-plugin-upgrade-licence-banner--modal' ),
            slug     = content.data( 'slug' ),
            security = content.data( 'security' );

        if ( content.length ) {
            yith.ui.modal(
                {
                    content                   : content.clone(),
                    classes                   : {
                        wrap: 'yith-plugin-upgrade-licence-modal'
                    },
                    width                     : '600px',
                    closeWhenClickingOnOverlay: false,
                    onClose                   : function () {
                        content.removeClass( 'yith-plugin-upgrade-licence-banner--modal' ).addClass( 'yith-plugin-upgrade-licence-banner--inline' );
                        $.post( ajaxurl, {
                            action  : 'yith_plugin_upgrade_licence_modal_dismiss',
                            slug    : slug,
                            security: security
                        } );
                    }
                }
            );
        }
    });
} )( jQuery );
