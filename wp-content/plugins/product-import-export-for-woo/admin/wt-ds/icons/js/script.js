var wbte_pimpexp_ds = {
	getAsset:function( args ) {
		let params = wbte_pimpexp_ds_js_params;
		let type = args.hasOwnProperty('type') ? args['type'] : '';
		let name = args.hasOwnProperty('name') ? args['name'] : '';

		if ('icon' === type ) {
			return params.icon_base_url + name + '.svg';
		} else if('image' === type) {
			return params.img_base_url + name;
		}
		return '';
	},
	getIconSvg: async function( icon ) {
		return await fetch( wbte_pimpexp_ds_js_params.icon_base_url + icon + '.svg')
		.then(response => response.text())
		.then(svgData => {
			return this.sanitizeSVG(svgData);
		});
	},
	sanitizeSVG:function( svgData ) {
			
		/* Parse the SVG string into an XML Document. */
		const parser = new DOMParser();
		const svgDoc = parser.parseFromString( svgData, 'image/svg+xml' );
	
		/* Start sanitizing from the root <svg> element. */
		const svgRoot = svgDoc.documentElement;
		this.sanitizeNode( svgRoot );
	
		/* Return sanitized SVG as a string */
		const serializer = new XMLSerializer();
		return serializer.serializeToString( svgDoc );
	},
	sanitizeNode:function( node ) {
		let allowedTags= ['svg', 'g', 'path', 'rect', 'circle', 'line', 'polyline', 'polygon', 'text', 'use', 'defs', 'symbol', 'title'];
		let allowedAttrs= ['x', 'y', 'viewBox', 'fill', 'stroke', 'd', 'class', 'id', 'width', 'height', 'cx', 'cy', 'r', 'rx', 'ry', 'xlink:href', 'style', 'transform'];
		
		/* Remove any tag that is not allowed. */
		if ( ! allowedTags.includes( node.tagName ) ) {
			node.remove();
			return;
		}

		/* Loop through attributes and remove those not allowed. */
		for (let i = node.attributes.length - 1; i >= 0; i--) {
			const attr = node.attributes[i].name;
			if ( ! allowedAttrs.includes( attr ) ) {
				node.removeAttribute( attr );
			}
		}

		/* Recursively sanitize child nodes. */
		Array.from(node.children).forEach(wbte_pimpexp_ds.sanitizeNode);
	}
}

var wbte_pimpexp_checkboxes = {
    Set:function(){
        jQuery(document).ready(function(){
            wbte_pimpexp_checkboxes.set_first_state();
            wbte_pimpexp_checkboxes.reg_click();
        });
    },
    set_first_state:function(){
        jQuery('.wbte_pimpexp_checkbox-master').each(function(){
            var group_id = jQuery(this).attr('data-checkbox-group-id');
            wbte_pimpexp_checkboxes.toggle_master_check( group_id );
        });
    },
    reg_click:function(){
        jQuery(document).on('click', '.wbte_pimpexp_checkbox input[type="checkbox"]', function() {
            
            let parent_elm = jQuery(this).parents('.wbte_pimpexp_checkbox');
            let group_id = parent_elm.attr('data-checkbox-group-id');

            if(parent_elm.hasClass('wbte_pimpexp_checkbox-master')){
                if(jQuery(this).is(':checked')){
                    jQuery('.wbte_pimpexp_checkbox[data-checkbox-group-id="'+group_id+'"] input[type="checkbox"]').prop('checked', true).attr('aria-checked', 'true');
                }else{
                    jQuery('.wbte_pimpexp_checkbox[data-checkbox-group-id="'+group_id+'"] input[type="checkbox"]').prop('checked', false).attr('aria-checked', 'false');
                }
            }
            
            wbte_pimpexp_checkboxes.toggle_master_check( group_id );
        });
    },
    toggle_master_check:function( group_id ) {
        let normal_checkboxes = jQuery('.wbte_pimpexp_checkbox-normal[data-checkbox-group-id="'+group_id+'"]');
        let master_checkbox = jQuery('.wbte_pimpexp_checkbox-master[data-checkbox-group-id="'+group_id+'"]');
        let checked = normal_checkboxes.find('input[type="checkbox"]:checked').length;
        let total = normal_checkboxes.find('input[type="checkbox"]').length;
        
        master_checkbox.find('label .checkbox-indicator').hide();
        
        if(checked > 0) {
            master_checkbox.find('input[type="checkbox"]').prop('checked', true).attr('aria-checked', 'true');                  
            
            if ( checked === total ) {   
                master_checkbox.find('label .checkbox-indicator.checked').show();
            }else{
                master_checkbox.find('label .checkbox-indicator.partially-checked').show();
            }
        }else{
            master_checkbox.find('input[type="checkbox"]').prop('checked', false).attr('aria-checked', 'false');
            master_checkbox.find('label .checkbox-indicator.not-checked').show();
        }

        /* Set checked labels */
        if(master_checkbox.find('.total').length){
            master_checkbox.find('.total').text(total);
        }
        if(master_checkbox.find('.selected').length){
            master_checkbox.find('.selected').text(checked);
        }
    }
}

wbte_pimpexp_checkboxes.Set();

/**
 *  Help widget
 */
var wbte_pimpexp_help_widget={
	Set:function() {
		jQuery(document).on('click', function (e) {
			var widget = jQuery('.wbte_pimpexp_help-widget');
			var checkbox = jQuery('#wt_ds_help-widget_hidden_checkbox');

			if (checkbox.is(':checked') && !widget.has(e.target).length && !widget.is(e.target)) {
			  checkbox.prop('checked', false);
			}
		});
	}
};

wbte_pimpexp_help_widget.Set();

/**
 *  Popup creator
 */
var wbte_pimpexp_popup={
	Set:function() {	
		jQuery(function() {
			if( jQuery('.wbte_pimpexp_blanket').length ) {
				jQuery('.wbte_pimpexp_blanket').prependTo('body');
			} else {	
				jQuery('body').prepend('<div class="wbte_pimpexp_blanket"></div>');
			}

			wbte_pimpexp_popup.regPopupOpen();
			wbte_pimpexp_popup.regPopupClose();
		});
	},
	regPopupOpen:function() {
		jQuery(document).on('click', '[data-wbte_pimpexp_popup]', function(){
			var elm_id=jQuery(this).attr('data-wbte_pimpexp_popup');
			var elm=jQuery('[data-id="'+elm_id+'"]');
			if ( elm.length ) {

				/* Trigger a custom event after the popup trigger was clicked. */
				let popup_trigger_clicked = jQuery.Event('wbte_pimpexp_popup_trigger_clicked', {
			        detail: {
			          trigger_element: jQuery(this),
			          target_element: elm,
			          target_id: elm_id,
			        },
			    });
			    jQuery(document).trigger(popup_trigger_clicked);

				wbte_pimpexp_popup.showPopup(elm);
			}
		});
	},
	showPopup:function( popup_elm ) {
		popup_elm.show();
        if ( popup_elm.attr('data-overlay') === '1' ) {
		    jQuery('.wbte_pimpexp_blanket').show();
        }
	},
	hidePopup:function() {
		jQuery('.wbte_pimpexp_popup-close').trigger('click');
	},
	regPopupClose:function( popup_elm ) {
		jQuery(document).on('keyup', function( e ) {
			if ( 'Escape' === e.key ) {
				wbte_pimpexp_popup.hidePopup();
			}
		});
		jQuery(document).on('click', '.wbte_pimpexp_popup-close, .wbte_pimpexp_popup-cancel, .wbte_pimpexp_blanket', function(){
			jQuery('.wbte_pimpexp_blanket, .wbte_pimpexp_popup').hide();
		});
	}
}

wbte_pimpexp_popup.Set();

var wbte_pimpexp_product_tour = {
	elements:[],
	Set:function( group_id, step ) {
		jQuery(document).ready(function(){
			wbte_pimpexp_product_tour.initTour( group_id, step );
		});
	},
	initTour:function( group_id, step ) {
		if ( ! group_id ) {
			return;
		}
		if ( ! step ) {
			step = 1;
		}

		this.elements = jQuery('.wbte_pimpexp_product-tour[data-group-id="' + group_id + '"]');
		let totalElements = this.elements.length;

		if( ! totalElements ) {
			return;
		}

		/* Add step summary */
		this.elements.each(function(index){
			jQuery(this).find('.wbte_pimpexp_product-tour-step-count .total').text( totalElements );
			jQuery(this).find('.wbte_pimpexp_product-tour-step-count .current').text( jQuery(this).attr('data-step') );
		})

		jQuery('.wbte_pimpexp_product-tour').hide(); /* Hide all tours. */

		let elm = this.elements.eq( step - 1 );
		if ( elm.length ) {
			wbte_pimpexp_product_tour.showBox( elm );
		}
		this.regNext( group_id );
		this.regSkip( group_id );
	},
	regNext:function( group_id ) {
		jQuery(document).off('click', '.wbte_pimpexp_product-tour[data-group-id="' + group_id + '"] .wbte_pimpexp_product-tour-next-btn').on( 'click', '.wbte_pimpexp_product-tour[data-group-id="' + group_id + '"] .wbte_pimpexp_product-tour-next-btn', function() {
			let current_step = parseInt( jQuery(this).parents('.wbte_pimpexp_product-tour').attr('data-step') );
			let next_elm = wbte_pimpexp_product_tour.elements.eq( current_step );
			wbte_pimpexp_product_tour.elements.animate({'opacity':0}, 100, function(){
				jQuery(this).hide().css({'opacity':1});
			});

			setTimeout(function() {
				if ( next_elm.length ) {				
					wbte_pimpexp_product_tour.showBox( next_elm );
				}
			}, 200);
		});
	},
	regSkip:function( group_id ) {
		jQuery(document).off('click', '.wbte_pimpexp_product-tour[data-group-id="' + group_id + '"] .wbte_pimpexp_product-tour-skip-btn').on( 'click', '.wbte_pimpexp_product-tour[data-group-id="' + group_id + '"] .wbte_pimpexp_product-tour-skip-btn', function() {
			wbte_pimpexp_product_tour.elements.hide();

			let popupElm = jQuery(this).parents('.wbte_pimpexp_product-tour');
			let target_id = popupElm.attr('data-target-id');
			let targetElm = jQuery('#' + target_id);

			// Trigger a custom event.
			let product_tour_item_skipped_event = jQuery.Event('wbte_pimpexp_product_tour_item_skipped', {
		        detail: {
		          target_element: targetElm,
		          popup_element: popupElm,
		        },
		    });
		    jQuery(document).trigger(product_tour_item_skipped_event);

		});
	},
	showBox:function( popupElm ) {
		 
		
		let target_id = popupElm.attr('data-target-id');
		let targetElm = jQuery('#' + target_id);
		if ( ! targetElm.length ) {
 			return;
		}

		let targetRect = targetElm[0].getBoundingClientRect();
		let viewportWidth = jQuery(window).width();
		let viewportHeight = jQuery(window).height();
		let popupWidth = popupElm.outerWidth();
		let popupHeight = popupElm.outerHeight();

		popupElm.show();
		
		// Reset classes.
		popupElm.removeClass('top right bottom left arrow_start arrow_end');

		// Calculate available space around the target element.
	    let spaceTop = targetRect.top;
	    let spaceBottom = viewportHeight - targetRect.bottom;
	    let spaceLeft = targetRect.left;
	    let spaceRight = viewportWidth - targetRect.right;


	    // Create an array of positions with their corresponding space.
	    let positions = [
	      { position: 'top', value: spaceTop },
	      { position: 'bottom', value: spaceBottom },
	      { position: 'left', value: spaceLeft },
	      { position: 'right', value: spaceRight }
	    ];

	    // Sort positions by available space in descending order.
	    positions.sort(function(a, b) {
	      return b.value - a.value;
	    });

	    let bestPosition = positions[0].position;
	    let arrowPos = '';
	    let halfWidth = popupWidth / 2;
	    let targetLeft = targetElm.offset().left;
	    let targetTop = targetElm.offset().top;
	    let newTop = targetTop;
	    let newLeft = targetLeft;
	    

	    if ( 'top' === bestPosition || 'bottom' === bestPosition ) {
	    	// --- Dup0 ---
	    	newTop = ( 'top' === bestPosition ? ( newTop - ( popupHeight + 20 ) ) : ( newTop + targetRect.height + 20 ) );
	    	newLeft = newLeft - ( halfWidth - ( targetRect.width / 2 ) );
	    	
	    	if ( newLeft < 0 ) {
	    		newLeft = targetLeft;
	    		arrowPos = 'arrow_start';
	    	}else if( ( newLeft + popupWidth ) > viewportWidth ) {
	    		newLeft = ( targetLeft + targetRect.width ) - popupWidth;
	    		arrowPos = 'arrow_end';
	    	}
	    	// --- Dup0 ---
	    } else {
	    	newLeft = 'left' === bestPosition ? ( newLeft - ( popupWidth + 20 ) ) : ( newLeft + targetRect.width + 20 );
		    
	    	if ( newLeft >= 0 ) { 
	    		// --- Dup1 ---
		    	newTop =  Math.max( (newTop - 20 ), 0 );

		    	if( ( newTop + popupHeight ) > jQuery(document).height() ) {
		    		newTop = jQuery(document).height() - ( popupHeight + 20 );
		    		arrowPos = 'arrow_end';
		    	}
		    	// --- Dup1 ---

		    } else { 
		    	bestPosition = positions[1].position
		    	if ( 'top' === bestPosition || 'bottom' === bestPosition ) {

		    		// --- Dup0 ---
		    		newTop = ( 'top' === bestPosition ? ( newTop - ( popupHeight + 20 ) ) : ( newTop + targetRect.height + 20 ) );
			    	newLeft = targetLeft - ( halfWidth - ( targetRect.width / 2 ) );
			    	
			    	if ( newLeft < 0 ) {
			    		newLeft = 0;
			    		arrowPos = 'arrow_start';
			    	}else if( ( newLeft + popupWidth ) > viewportWidth ) {
			    		newLeft = ( targetLeft + targetRect.width ) - popupWidth;
			    		arrowPos = 'arrow_end';
			    	}
			    	// --- Dup0 ---

		    	} else{
		    		bestPosition = positions[0].position

		    		// --- Dup1 ---
		    		newTop =  Math.max( (newTop - 20 ), 0 );

			    	if( ( newTop + popupHeight ) > jQuery(document).height() ) {
			    		newTop = jQuery(document).height() - ( popupHeight + 20 );
			    		arrowPos = 'arrow_end';
			    	}
			    	// --- Dup1 ---
		    	}
		    }
	    }
	    
	    // Popup element is absolute positioned so convert absolute position to relative position.
	    let leftDiff = popupElm.offset().left - popupElm.position().left;
	    let topDiff = popupElm.offset().top - popupElm.position().top;
	    newLeft = newLeft - leftDiff;
	    newTop = newTop - topDiff;

	    popupElm.css({ top: newTop, left: newLeft, 'opacity':0 }).addClass( bestPosition + ' ' + arrowPos );
	    

	    // Trigger a custom event.
		let product_tour_item_visible_event = jQuery.Event('wbte_pimpexp_product_tour_item_visible', {
	        detail: {
	          target_element: targetElm,
	          popup_element: popupElm,
	        },
	    });
	    
	    jQuery(document).trigger(product_tour_item_visible_event);
	    popupElm.animate({ 'opacity': 1 });
	}
}

var wbte_pimpexp_segments = {
	Set:function(){
		jQuery(document).ready(function() {
			wbte_pimpexp_segments.register_click();
			wbte_pimpexp_segments.load_segment_state();
		});
	},
	register_click:function(){
		/* Create a custom event to trigger after segment was clicked. */
		let segment_clicked_event = jQuery.Event('wbte_pimpexp_segment_clicked', {
	        detail: {
	          element: null,
	          segment_target_id: '',
	          segment_item_target_id: '',
	        },
	    });

		jQuery(document).on('click', '.wbte_pimpexp_segment', function(e){
			let elm = jQuery(this);

			/* Set segment state */
			let segment_parent = elm.parent('.wbte_pimpexp_segments');
			segment_parent.find('.wbte_pimpexp_segment').removeClass('active');
			elm.addClass('active');


			/* Display the content. */
			let segment_target_id = typeof segment_parent.attr('data-target-id') === 'undefined' ? '' : segment_parent.attr('data-target-id');
			if( '' === segment_target_id || ( segment_target_id && ! jQuery( '.wbte_pimpexp_segment_content_main[data-id="' + segment_target_id + '"]' ).length ) ) { /* Segment target element id not found, or element not exists. */
				return;
			}

			let segment_target_elm = jQuery( '.wbte_pimpexp_segment_content_main[data-id="' + segment_target_id + '"]' );
			segment_target_elm.find('.wbte_pimpexp_segment_content').hide(); /* Hide all containers first. */

			let segment_item_target_id = typeof elm.attr('data-target-id') === 'undefined' ? '' : elm.attr('data-target-id');
			if( '' === segment_item_target_id ) { /* Segment target element id not found */
				return;
			}
			segment_target_elm.find( '.wbte_pimpexp_segment_content[data-id="' + segment_item_target_id + '"]' ).fadeIn();


			/* Trigger a custom event. */
			segment_clicked_event.element = elm;
			segment_clicked_event.segment_target_id = segment_target_id;
			segment_clicked_event.segment_item_target_id = segment_item_target_id;						
			jQuery(document).trigger(segment_clicked_event);
		});
	},
	load_segment_state:function(){
		jQuery('.wbte_pimpexp_segment.active').each(function(e){
			jQuery(this).trigger('click');
		});
	}
}
wbte_pimpexp_segments.Set();

var wbte_pimpexp_submenu = {
	Set:function(){
		jQuery(document).ready(function() {
			wbte_pimpexp_submenu.register_click();
			wbte_pimpexp_submenu.load_menu_state();
		});
	},
	register_click:function() {		
		
		/* Create a custom event to trigger after tab was loaded. */
		let tab_loaded_event = jQuery.Event('wbte_pimpexp_tab_loaded', {
	        detail: {
	          element: null,
	          menu_target_id: '',
	          menu_item_target_id: '',
	        },
	    });

		jQuery(document).on('click', '.wbte_pimpexp_submenu_item', function(e){
			
			let elm = jQuery(this);
			let href= typeof elm.attr('href') === 'undefined' ? '' : elm.attr('href');
			
			if('' === href){
				e.preventDefault();
			}else{
				return;
			}


			if( elm.hasClass('active') && ! elm.hasClass('has_submenu') ) {
				//return;
			}

			if( elm.hasClass('has_submenu') ) {
				
				/* Disable arrow toggling for horizontal menu. */
				if(elm.hasClass('wbte_pimpexp_horizontal_submenu_main_item')) {
					return;
				}

				let submenu = elm.siblings('.wbte_pimpexp_subsubmenu');
				if( submenu.is(':visible') ) {
					submenu.hide();
					elm.find('.wbte_pimpexp_submenu_arrow').removeClass('open');
				}else{
					submenu.show();
					elm.find('.wbte_pimpexp_submenu_arrow').addClass('open');
				}

				return;
			}

			let menu_parent = elm.parents('.wbte_pimpexp_submenu_main');
			menu_parent.find('.wbte_pimpexp_submenu_item').removeClass('active');
			elm.addClass('active');

			/* For horizontal menu */
			if( elm.hasClass('wbte_pimpexp_horizontal_subsubmenu_item') ) {
				elm.parents('.wbte_pimpexp_horizontal_subsubmenu').siblings('.wbte_pimpexp_submenu_item').addClass('active');
			}


			/* Display the content. */
			let menu_target_id = typeof menu_parent.attr('data-target-id') === 'undefined' ? '' : menu_parent.attr('data-target-id');
			if( '' === menu_target_id || ( menu_target_id && ! jQuery( '.wbte_pimpexp_submenu_content_main[data-id="' + menu_target_id + '"]' ).length ) ) { /* Menu target element id not found, or element not exists. */
				return;
			}

			let menu_target_elm = jQuery( '.wbte_pimpexp_submenu_content_main[data-id="' + menu_target_id + '"]' );
			menu_target_elm.find('.wbte_pimpexp_submenu_content').hide(); /* Hide all containers first. */

			let menu_item_target_id = typeof elm.attr('data-target-id') === 'undefined' ? '' : elm.attr('data-target-id');
			if( '' === menu_item_target_id ) { /* Menu target element id not found */
				return;
			}
			menu_target_elm.find( '.wbte_pimpexp_submenu_content[data-id="' + menu_item_target_id + '"]' ).show();
			

			/* Trigger a custom event. */
			tab_loaded_event.element = elm;
			tab_loaded_event.menu_target_id = menu_target_id;
			tab_loaded_event.menu_item_target_id = menu_item_target_id;						
			jQuery(document).trigger(tab_loaded_event);

		});		
	},
	load_menu_state:function(){
		jQuery('.wbte_pimpexp_submenu_item.active').each(function(e){
			jQuery(this).trigger('click');
		});
	}
}
wbte_pimpexp_submenu.Set();

var wbte_pimpexp_notify_msg = {
	error_icon: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">'
				+'<circle cx="10" cy="10" r="10" fill="#D63638"/>'
				+'<path d="M10.0996 5V11" stroke="white" stroke-width="2.2" stroke-linecap="round"/>'
				+'<circle cx="10.2" cy="15.2" r="1.2" fill="white"/>'
				+'</svg>',
	success_icon: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">'
				+'<circle cx="10" cy="10" r="10" fill="#20B93E"/>'
				+'<path d="M14.0931 7.21515L8.29143 13.0168L5.6543 10.3797" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
				+'</svg>',
	warn_icon:  '<svg width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg">'
				+'<path d="M8.35477 1.52476C9.50517 -0.508251 12.4948 -0.508255 13.6452 1.52476L21.6179 15.6141C22.7325 17.5838 21.2752 20 18.9727 20H3.02734C0.724759 20 -0.732486 17.5839 0.382104 15.6141L8.35477 1.52476Z" fill="#DBA617"/>'
				+'<path d="M11.0996 5V11" stroke="white" stroke-width="2.2" stroke-linecap="round"/>'
				+'<circle cx="11.2" cy="15.2" r="1.2" fill="white"/>'
				+'</svg>',
	error:function( message, auto_close ) {
		var auto_close = (auto_close!== undefined ? auto_close : true);
		var er_elm=jQuery('<div class="wbte_pimpexp_notify_msg wbte_pimpexp_notify_msg_error">' + this.error_icon + message + '</div>');				
		this.set_notify(er_elm, auto_close);
	},
	success:function( message, auto_close ) {
		var auto_close = (auto_close!== undefined ? auto_close : true);
		var suss_elm = jQuery('<div class="wbte_pimpexp_notify_msg wbte_pimpexp_notify_msg_success">' + this.success_icon + message + '</div>');				
		this.set_notify(suss_elm, auto_close);
	},
	warning:function( message, auto_close ) {
		var auto_close = (auto_close!== undefined ? auto_close : true);
		var suss_elm = jQuery('<div class="wbte_pimpexp_notify_msg wbte_pimpexp_notify_msg_warning">' + this.warn_icon + message + '</div>');				
		this.set_notify(suss_elm, auto_close);
	},
	progress:function( message ) {
		var prog_elm = jQuery('<div class="wbte_pimpexp_notify_msg wbte_pimpexp_notify_msg_progress"><span class="spinner"></span> ' + message + '</div>');				
		this.set_notify(prog_elm, false, true);
		return prog_elm;
	},
	progress_complete:function( elm, message, auto_close ) {
		var auto_close = (auto_close!== undefined ? auto_close : true);
		elm.removeClass('wbte_pimpexp_notify_msg_progress').addClass('wbte_pimpexp_notify_msg_success');
		elm.html( this.success_icon + message );				
		this.set_notify(elm, auto_close);
	},
	progress_error:function( elm, message, auto_close ) {
		var auto_close = (auto_close!== undefined ? auto_close : true);
		elm.removeClass('wbte_pimpexp_notify_msg_progress').addClass('wbte_pimpexp_notify_msg_error');
		elm.html( this.error_icon + message );				
		this.set_notify(elm, auto_close);
	},
	set_notify:function( elm, auto_close, is_static ) {
		jQuery('body').append(elm);
		elm.stop(true, true).animate({'opacity':1, 'top':'50px'}, 1000);
		if(is_static) { return; }
		
		elm.on('click',function(){
			wbte_pimpexp_notify_msg.fade_out(elm);
		});
		
		if(auto_close) {
			setTimeout(function(){
				wbte_pimpexp_notify_msg.fade_out(elm);
			},5000);
		}else{
			jQuery('body').on('click',function(){
				wbte_pimpexp_notify_msg.fade_out(elm);
			});
		}
	},
	fade_out:function(elm) {
		elm.animate({'opacity':0,'top':'100px'}, 1000, function(){
			elm.remove();
		});
	}
}