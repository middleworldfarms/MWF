/**
 * Solidarity Pricing Modal JavaScript
 * Handles modal display, cookie management, and user interactions
 */
(function($) {
    'use strict';
    
    const SolidarityModal = {
        
        init: function() {
            this.bindEvents();
            this.checkAndShow();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;
            
            // Close modal
            $(document).on('click', '.mwf-solidarity-close, .mwf-solidarity-overlay', function(e) {
                if (e.target === this) {
                    self.closeModal();
                }
            });
            
            // Primary button (I Understand)
            $(document).on('click', '.mwf-solidarity-btn-primary', function(e) {
                e.preventDefault();
                self.handleUnderstand();
            });
            
            // Next button (Step 1 → Step 2)
            $(document).on('click', '.mwf-solidarity-btn-next', function(e) {
                e.preventDefault();
                self.goToStep(2);
            });
            
            // Previous button (Step 2 → Step 1)
            $(document).on('click', '.mwf-solidarity-btn-prev', function(e) {
                e.preventDefault();
                self.goToStep(1);
            });
            
            // Clickable tiers - open detail popups
            $(document).on('click', '.mwf-solidarity-tier.clickable', function(e) {
                e.preventDefault();
                const tier = $(this).data('tier');
                self.openTierPopup(tier);
            });
            
            // Close tier popup
            $(document).on('click', '.mwf-popup-close, .mwf-popup-btn', function(e) {
                e.preventDefault();
                self.closeTierPopup();
            });
            
            // Close tier popup on outside click
            $(document).on('click', '.mwf-tier-popup', function(e) {
                if (e.target === this) {
                    self.closeTierPopup();
                }
            });
            
            // Don't show again checkbox
            $(document).on('change', '#mwf-solidarity-dont-show', function() {
                if ($(this).is(':checked')) {
                    self.setCookie('permanent');
                } else {
                    self.deleteCookie();
                }
            });
            
            // Shop page: Click on vegbox products
            if (mwfSolidarity.showOnShop === 'yes' || mwfSolidarity.showOnShop === '1') {
                $(document).on('click', 'a[href*="vegetable-box"]', function(e) {
                    if (!self.hasSeen()) {
                        e.preventDefault();
                        const targetUrl = $(this).attr('href');
                        self.showModal(targetUrl);
                    }
                });
            }
            
            // ESC key to close
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    if ($('.mwf-tier-popup').hasClass('active')) {
                        self.closeTierPopup();
                    } else if ($('.mwf-solidarity-overlay').hasClass('active')) {
                        self.closeModal();
                    }
                }
            });
        },
        
        /**
         * Check if should show modal on page load
         */
        checkAndShow: function() {
            // Show on product pages for vegboxes (if not seen)
            if ($('body').hasClass('single-product') && !this.hasSeen()) {
                const isVegbox = this.isVegboxProduct();
                if (isVegbox) {
                    // Delay slightly for better UX
                    setTimeout(() => {
                        this.showModal();
                    }, 500);
                }
            }
        },
        
        /**
         * Check if current product is a vegbox
         */
        isVegboxProduct: function() {
            // Check if product has vegbox category
            const vegboxCategories = mwfSolidarity.vegboxCategories || [];
            
            // Method 1: Check body classes
            for (let i = 0; i < vegboxCategories.length; i++) {
                if ($('body').hasClass('product_cat-' + vegboxCategories[i])) {
                    return true;
                }
            }
            
            // Method 2: Check for "vegetable" in title or URL
            const title = $('h1.product_title').text().toLowerCase();
            const url = window.location.href.toLowerCase();
            
            if (title.includes('vegetable') || title.includes('veg box') || 
                url.includes('vegetable-box') || url.includes('vegbox')) {
                return true;
            }
            
            return false;
        },
        
        /**
         * Show modal
         */
        showModal: function(targetUrl) {
            const $overlay = $('.mwf-solidarity-overlay');
            
            // Store target URL if provided
            if (targetUrl) {
                $overlay.data('target-url', targetUrl);
            }
            
            $overlay.addClass('active');
            $('body').css('overflow', 'hidden');
            
            // Track view
            this.trackView();
        },
        
        /**
         * Close modal
         */
        closeModal: function() {
            const $overlay = $('.mwf-solidarity-overlay');
            
            $overlay.removeClass('active');
            $('body').css('overflow', '');
            
            // Collapse learn more if expanded
            $('.mwf-solidarity-learn-more').removeClass('expanded');
            $('.mwf-solidarity-btn-secondary').text('ℹ️ Learn More');
        },
        
        /**
         * Handle "I Understand" button click
         */
        handleUnderstand: function() {
            const $checkbox = $('#mwf-solidarity-dont-show');
            
            // Only set cookie if "don't show again" is checked
            // Otherwise, modal will show on every visit
            if ($checkbox.is(':checked')) {
                this.setCookie('permanent');
            }
            
            const $overlay = $('.mwf-solidarity-overlay');
            const targetUrl = $overlay.data('target-url');
            
            this.closeModal();
            
            // If we have a target URL (from shop page), navigate there
            if (targetUrl) {
                window.location.href = targetUrl;
            }
        },
        
        /**
         * Navigate between modal steps
         */
        goToStep: function(stepNum) {
            // Hide all steps
            $('.mwf-solidarity-step').removeClass('active');
            
            // Show target step
            $('#step' + stepNum).addClass('active');
        },
        
        /**
         * Open tier detail popup
         */
        openTierPopup: function(tier) {
            const $popup = $('#mwf-popup-' + tier);
            $popup.addClass('active');
            $('body').css('overflow', 'hidden');
        },
        
        /**
         * Close tier popup
         */
        closeTierPopup: function() {
            $('.mwf-tier-popup').removeClass('active');
            // Don't restore body overflow since main modal is still open
        },
        
        /**
         * Check if user has seen modal
         */
        hasSeen: function() {
            return this.getCookie(mwfSolidarity.cookieName) !== null;
        },
        
        /**
         * Set cookie
         */
        setCookie: function(type) {
            const days = type === 'permanent' ? 365 : parseInt(mwfSolidarity.cookieDays || 30);
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toUTCString();
            
            document.cookie = mwfSolidarity.cookieName + "=1;" + expires + ";path=/;SameSite=Lax";
        },
        
        /**
         * Get cookie
         */
        getCookie: function(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        },
        
        /**
         * Delete cookie
         */
        deleteCookie: function() {
            document.cookie = mwfSolidarity.cookieName + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        },
        
        /**
         * Track modal view (optional analytics)
         */
        trackView: function() {
            // Send to analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'view_solidarity_modal', {
                    'event_category': 'Solidarity Pricing',
                    'event_label': 'Modal Viewed'
                });
            }
            
            // Can also send to WordPress via AJAX for tracking
            $.ajax({
                url: mwfSolidarity.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mwf_solidarity_track',
                    nonce: mwfSolidarity.nonce,
                    event: 'view'
                }
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        SolidarityModal.init();
    });
    
})(jQuery);
