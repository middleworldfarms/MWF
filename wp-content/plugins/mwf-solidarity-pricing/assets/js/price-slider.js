/**
 * MWF Price Slider
 * Interactive price slider that overlays YITH Name Your Price
 */

(function($) {
    'use strict';
    
    const MWF_PriceSlider = {
        
        /**
         * Initialize slider on product pages
         */
        init: function() {
            // Only run on product pages with YITH Name Your Price
            if (!this.hasYithPricing()) {
                console.log('MWF Price Slider: No YITH pricing field found');
                return;
            }
            
            console.log('MWF Price Slider: YITH field detected, setting up slider');
            this.setupSlider();
        },
        
        /**
         * Check if YITH Name Your Price is active on this product
         */
        hasYithPricing: function() {
            // Look for YITH Name Your Price Premium's input field
            const yithInput = $('#ywcnp_suggest_price_single, input[name="ywcnp_amount"], input.ywcnp_sugg_price');
            return yithInput.length > 0;
        },
        
        /**
         * Setup the price slider
         */
        setupSlider: function() {
            const self = this;
            
            // Get YITH Name Your Price input
            const $yithInput = $('#ywcnp_suggest_price_single, input[name="ywcnp_amount"], input.ywcnp_sugg_price').first();
            
            if ($yithInput.length === 0) return;
            
            // Get min/max from hidden inputs (YITH stores them separately)
            const $minInput = $('input[name="ywcnp_min"]');
            const $maxInput = $('input[name="ywcnp_max"]');
            
            // Get suggested price (what's in the visible input - this is the per-box price)
            const suggested = parseFloat($yithInput.val() || $yithInput.data('suggest_price') || 22);
            
            // Use YITH's actual min/max values (they validate against these)
            const min = parseFloat($minInput.val() || suggested * 0.70);
            const max = parseFloat($maxInput.val() || suggested * 2.30);
            const step = 0.50;
            
            console.log('MWF Price Slider: suggested=' + suggested + ', YITH min=' + min + ', YITH max=' + max);
            
            // Calculate price zones based on solidarity pricing model
            const breakEven = 11.81;
            const solidarityMax = min + ((suggested - min) * 0.93); // 93% of standard
            const supporterStart = suggested * 1.33; // 133% of standard
            
            // Build slider HTML
            const sliderHTML = `
                <div class="mwf-price-slider-wrapper">
                    <div class="mwf-price-slider-header">
                        <h3 class="mwf-price-slider-title">Choose Your Price</h3>
                        <p class="mwf-price-slider-subtitle">Pay what you can afford</p>
                    </div>
                    
                    <div class="mwf-price-display">
                        <div class="mwf-price-amount" data-zone="standard">£${suggested.toFixed(2)}</div>
                        <span class="mwf-price-label"><span class="zone-icon">🌱</span> <span class="zone-text">Standard Price</span></span>
                    </div>
                    
                    <div class="mwf-slider-container">
                        <input 
                            type="range" 
                            class="mwf-price-slider" 
                            min="${min}" 
                            max="${max}" 
                            step="${step}" 
                            value="${suggested}"
                            data-suggested="${suggested}"
                            data-break-even="${breakEven}"
                            data-solidarity-max="${solidarityMax.toFixed(2)}"
                            data-supporter-start="${supporterStart.toFixed(2)}"
                        />
                    </div>
                    
                    <div class="mwf-slider-labels">
                        <div class="mwf-slider-label">
                            <span class="mwf-slider-label-icon">💚</span>
                            <span class="mwf-slider-label-text">Solidarity</span>
                            <span class="mwf-slider-label-price">£${min.toFixed(2)}</span>
                        </div>
                        <div class="mwf-slider-label">
                            <span class="mwf-slider-label-icon">🌱</span>
                            <span class="mwf-slider-label-text">Standard</span>
                            <span class="mwf-slider-label-price">£${suggested.toFixed(2)}</span>
                        </div>
                        <div class="mwf-slider-label">
                            <span class="mwf-slider-label-icon">🌳</span>
                            <span class="mwf-slider-label-text">Supporter</span>
                            <span class="mwf-slider-label-price">£${max.toFixed(2)}</span>
                        </div>
                    </div>
                    
                    <div class="mwf-price-impact standard">
                        <span class="mwf-price-impact-icon">✓</span>
                        <span class="mwf-price-impact-text">Fair wage for farmers</span>
                    </div>
                    
                    <div class="mwf-quick-select">
                        <button type="button" class="mwf-quick-btn solidarity" data-price="${min}">
                            💚 Min (£${min.toFixed(2)})
                        </button>
                        <button type="button" class="mwf-quick-btn standard" data-price="${suggested}">
                            🌱 Standard (£${suggested.toFixed(2)})
                        </button>
                        <button type="button" class="mwf-quick-btn supporter" data-price="${max}">
                            🌳 Max (£${max.toFixed(2)})
                        </button>
                    </div>
                </div>
            `;
            
            // Insert slider before YITH's input
            $yithInput.before(sliderHTML);
            
            // Get slider element
            const $slider = $('.mwf-price-slider');
            const $priceAmount = $('.mwf-price-amount');
            const $priceLabel = $('.mwf-price-label');
            const $priceImpact = $('.mwf-price-impact');
            
            // Handle slider changes
            $slider.on('input change', function() {
                const price = parseFloat($(this).val());
                self.updatePrice(price, $yithInput, $priceAmount, $priceLabel, $priceImpact, $(this));
            });
            
            // Handle quick select buttons
            $('.mwf-quick-btn').on('click', function(e) {
                e.preventDefault();
                const price = parseFloat($(this).data('price'));
                $slider.val(price).trigger('change');
            });
        },
        
        /**
         * Update price display and YITH input
         */
        updatePrice: function(price, $yithInput, $priceAmount, $priceLabel, $priceImpact, $slider) {
            // Update YITH's hidden input
            $yithInput.val(price.toFixed(2)).trigger('change');
            
            // Update display
            $priceAmount.text('£' + price.toFixed(2));
            
            // Get zone thresholds
            const suggested = parseFloat($slider.data('suggested'));
            const solidarityMax = parseFloat($slider.data('solidarity-max'));
            const supporterStart = parseFloat($slider.data('supporter-start'));
            
            // Determine zone and update styling
            let zone, icon, label, impact;
            
            if (price < suggested) {
                // Solidarity zone
                zone = 'solidarity';
                icon = '💚';
                label = 'Solidarity Price';
                impact = "We subsidize you (that's okay if you need help)";
            } else if (price === suggested) {
                // Standard zone
                zone = 'standard';
                icon = '🌱';
                label = 'Standard Price';
                impact = 'Minimum wage for farmers';
            } else {
                // Supporter zone
                zone = 'supporter';
                icon = '🌳';
                label = 'Supporter Price';
                const extra = (price - suggested).toFixed(2);
                impact = `You're paying £${extra} extra - helping subsidize others! <strong style="color: #2f855a;">Thank you, you're amazing!</strong>`;
            }
            
            // Update classes
            $priceAmount.removeClass('solidarity standard supporter').addClass(zone);
            $priceImpact.removeClass('solidarity standard supporter').addClass(zone);
            
            // Update labels
            $priceLabel.html(`<span class="zone-icon">${icon}</span> <span class="zone-text">${label}</span>`);
            $priceImpact.html(`<span class="mwf-price-impact-icon">${icon}</span> <span class="mwf-price-impact-text">${impact}</span>`);
        }
    };
    
    // Initialize when DOM ready
    $(document).ready(function() {
        console.log('MWF Price Slider initializing...');
        MWF_PriceSlider.init();
        
        // Re-initialize when WooCommerce variation changes (for variable subscription products)
        $('form.variations_form').on('found_variation', function(event, variation) {
            console.log('MWF Price Slider: Variation changed, reinitializing...');
            // Remove old slider
            $('.mwf-price-slider-wrapper').remove();
            // Wait for YITH to update, then reinitialize
            setTimeout(function() {
                MWF_PriceSlider.init();
            }, 100);
        });
    });
    
})(jQuery);
