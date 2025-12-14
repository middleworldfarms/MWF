<?php
/**
 * Plugin Name: Custom Slider
 * Description: A custom plugin to add the Smart Slider 3 to pages.
 * Version: 1.0
 * Author: Your Name
 */

// Enqueue the slider's CSS and JS files
function custom_slider_enqueue_assets() {
    wp_enqueue_style('smartslider-css', plugins_url('css/smartslider.min.css', __FILE__));
    wp_enqueue_script('smartslider-js', plugins_url('js/n2.min.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('smartslider-frontend-js', plugins_url('js/smartslider-frontend.min.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('smartslider-carousel-js', plugins_url('js/ss-carousel.min.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('smartslider-arrow-reveal-js', plugins_url('js/w-arrow-reveal.min.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('smartslider-bullet-js', plugins_url('js/w-bullet.min.js', __FILE__), array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'custom_slider_enqueue_assets');

// Add a shortcode to display the slider
function custom_slider_shortcode() {
    ob_start();
    ?>
    <div class="n2-section-smartslider fitvidsignore n2_clear" data-ssid="2">
        <div id="n2-ss-2-align" class="n2-ss-align">
            <div class="n2-padding">
                <div id="n2-ss-2" data-creator="Smart Slider 3" data-responsive="auto" class="n2-ss-slider n2-ow n2-has-hover n2notransition n2-ss-slider-carousel-animation-horizontal">
                    <!-- Slider content is dynamically loaded by Smart Slider 3 -->
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
if (!shortcode_exists('custom_slider')) {
    add_shortcode('custom_slider', 'custom_slider_shortcode');
}

// Add a unique shortcode function
function custom_slider_shortcode_plugin() {
    ob_start();
    ?>
    <div class="custom-slider-container" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div class="custom-slider" style="position: relative; overflow: hidden;">
            <div class="slider-wrapper" style="display: flex; transition: transform 0.5s ease;">
                <div class="slide active" style="min-width: 100%; position: relative;">
                    <img src="<?php echo plugins_url('images/health.webp', __FILE__); ?>" alt="Health Benefits" style="width: 400px; height: auto; max-height: 200px; object-fit: cover;">
                    <div class="slide-content" style="position: absolute; bottom: 20px; left: 20px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px;">
                        <h3>Health Benefits</h3>
                        <p>Our farming practices prioritize natural health benefits for you and your family.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative;">
                    <img src="<?php echo plugins_url('images/better-taste-and-quality.webp', __FILE__); ?>" alt="Better Taste & Quality" style="width: 400px; height: auto; max-height: 200px; object-fit: cover;">
                    <div class="slide-content" style="position: absolute; bottom: 20px; left: 20px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px;">
                        <h3>Better Taste & Quality</h3>
                        <p>Experience superior taste and quality in every bite from our carefully grown produce.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative;">
                    <img src="<?php echo plugins_url('images/avoiding-gmos.webp', __FILE__); ?>" alt="Avoiding GMOs" style="width: 400px; height: auto; max-height: 200px; object-fit: cover;">
                    <div class="slide-content" style="position: absolute; bottom: 20px; left: 20px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px;">
                        <h3>Avoiding GMOs</h3>
                        <p>We maintain natural growing practices without genetically modified organisms.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative;">
                    <img src="<?php echo plugins_url('images/environmental-benefits.webp', __FILE__); ?>" alt="Environmental Benefits" style="width: 400px; height: auto; max-height: 200px; object-fit: cover;">
                    <div class="slide-content" style="position: absolute; bottom: 20px; left: 20px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px;">
                        <h3>Environmental Benefits</h3>
                        <p>Our sustainable farming methods protect and enhance the environment.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative;">
                    <img src="<?php echo plugins_url('images/animal-welfare.webp', __FILE__); ?>" alt="Animal Welfare" style="width: 400px; height: auto; max-height: 200px; object-fit: cover;">
                    <div class="slide-content" style="position: absolute; bottom: 20px; left: 20px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px;">
                        <h3>Animal Welfare</h3>
                        <p>We prioritize the well-being and ethical treatment of all animals on our farm.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative;">
                    <img src="<?php echo plugins_url('images/supporting-local-farmers.webp', __FILE__); ?>" alt="Supporting Local Farmers" style="width: 400px; height: auto; max-height: 200px; object-fit: cover;">
                    <div class="slide-content" style="position: absolute; bottom: 20px; left: 20px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px;">
                        <h3>Supporting Local Farmers</h3>
                        <p>By choosing our farm, you're supporting local agriculture and sustainable communities.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative;">
                    <img src="<?php echo plugins_url('images/no-chemicals.webp', __FILE__); ?>" alt="No Chemicals" style="width: 400px; height: auto; max-height: 200px; object-fit: cover;">
                    <div class="slide-content" style="position: absolute; bottom: 20px; left: 20px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px;">
                        <h3>No Chemicals</h3>
                        <p>We grow our produce without harmful chemicals, ensuring pure and natural food.</p>
                    </div>
                </div>
            </div>
            
            <button class="slider-btn prev" style="position: absolute; top: 50%; left: 10px; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 10px; cursor: pointer; border-radius: 50%;">&larr;</button>
            <button class="slider-btn next" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 10px; cursor: pointer; border-radius: 50%;">&rarr;</button>
            
            <div class="slider-dots" style="text-align: center; margin-top: 10px;">
                <span class="dot active" style="display: inline-block; width: 10px; height: 10px; background: #333; border-radius: 50%; margin: 0 5px; cursor: pointer;"></span>
                <span class="dot" style="display: inline-block; width: 10px; height: 10px; background: #ccc; border-radius: 50%; margin: 0 5px; cursor: pointer;"></span>
                <span class="dot" style="display: inline-block; width: 10px; height: 10px; background: #ccc; border-radius: 50%; margin: 0 5px; cursor: pointer;"></span>
                <span class="dot" style="display: inline-block; width: 10px; height: 10px; background: #ccc; border-radius: 50%; margin: 0 5px; cursor: pointer;"></span>
                <span class="dot" style="display: inline-block; width: 10px; height: 10px; background: #ccc; border-radius: 50%; margin: 0 5px; cursor: pointer;"></span>
                <span class="dot" style="display: inline-block; width: 10px; height: 10px; background: #ccc; border-radius: 50%; margin: 0 5px; cursor: pointer;"></span>
                <span class="dot" style="display: inline-block; width: 10px; height: 10px; background: #ccc; border-radius: 50%; margin: 0 5px; cursor: pointer;"></span>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('.custom-slider-container').each(function() {
            var $container = $(this);
            var $wrapper = $container.find('.slider-wrapper');
            var $slides = $container.find('.slide');
            var $dots = $container.find('.dot');
            var currentIndex = 0;
            var slideCount = $slides.length;
            
            function showSlide(index) {
                $wrapper.css('transform', 'translateX(-' + (index * 100) + '%)');
                $slides.removeClass('active');
                $slides.eq(index).addClass('active');
                $dots.removeClass('active').css('background', '#ccc');
                $dots.eq(index).addClass('active').css('background', '#333');
                currentIndex = index;
            }
            
            $container.find('.next').click(function() {
                var nextIndex = (currentIndex + 1) % slideCount;
                showSlide(nextIndex);
            });
            
            $container.find('.prev').click(function() {
                var prevIndex = (currentIndex - 1 + slideCount) % slideCount;
                showSlide(prevIndex);
            });
            
            $dots.click(function() {
                var dotIndex = $(this).index();
                showSlide(dotIndex);
            });
            
            // Auto-play
            setInterval(function() {
                var nextIndex = (currentIndex + 1) % slideCount;
                showSlide(nextIndex);
            }, 5000);
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
if (!shortcode_exists('custom_slider_plugin')) {
    add_shortcode('custom_slider_plugin', 'custom_slider_shortcode_plugin');
}