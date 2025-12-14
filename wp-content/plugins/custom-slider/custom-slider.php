<?php
/**
 * Plugin Name: Custom Slider
 * Description: A custom plugin to add a carousel slider to pages.
 * Version: 1.0
 * Author: Your Name
 */

// Add a shortcode to display the slider
function custom_slider_shortcode_plugin() {
    ob_start();
    ?>
    <div class="custom-slider-container" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div class="custom-slider" style="position: relative; overflow: hidden; height: 800px;">
            <div class="slider-wrapper" style="display: flex; transition: transform 0.5s ease;">
                <div class="slide active" style="min-width: 100%; position: relative; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; height: 100%; padding: 20px 0;">
                    <img src="<?php echo plugins_url('images/health.webp', __FILE__); ?>" alt="Health Benefits" style="width: 100px; height: auto; max-height: 80px; object-fit: cover; margin-bottom: 20px;">
                    <div class="slide-content" style="background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px; max-width: 500px; text-align: center;">
                        <h3>"Organic is healthier for you." — Not necessarily</h3>
                        <p>Nutrient density depends on soil biology, not certification. Organic farms often till heavily, disturbing fungi and depleting micronutrients. Middle World Farms focuses on microbial richness, mineral balance, and living soil ecosystems — producing nutrient-dense food through biology, not rules.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; height: 100%; padding: 20px 0;">
                    <img src="<?php echo plugins_url('images/better-taste-and-quality.webp', __FILE__); ?>" alt="Better Taste & Quality" style="width: 100px; height: auto; max-height: 80px; object-fit: cover; margin-bottom: 20px;">
                    <div class="slide-content" style="background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px; max-width: 500px; text-align: center;">
                        <h3>"Organic is more climate-friendly." — Not always</h3>
                        <p>Organic often imports manure from intensive livestock farms, ships composts long distances, and uses plastic mulches that break down into microplastics. Middle World Farms produces massive amounts of biochar and grows carbon-rich systems, meaning our crops can be carbon-negative without certification.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; height: 100%; padding: 20px 0;">
                    <img src="<?php echo plugins_url('images/avoiding-gmos.webp', __FILE__); ?>" alt="Avoiding GMOs" style="width: 100px; height: auto; max-height: 80px; object-fit: cover; margin-bottom: 20px;">
                    <div class="slide-content" style="background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px; max-width: 500px; text-align: center;">
                        <h3>"Organic avoids GMOs and unnatural systems." — True but irrelevant</h3>
                        <p>Not using GMOs doesn't automatically mean the system is ecological. Organic farms can still sterilise soil, destroy fungal networks and remove habitats. Middle World Farms respects natural systems not by rule, but by practice — working with succession, microbes, mycelium, insects, animals and wild ecology.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; height: 100%; padding: 20px 0;">
                    <img src="<?php echo plugins_url('images/environmental-benefits.webp', __FILE__); ?>" alt="Environmental Benefits" style="width: 100px; height: auto; max-height: 80px; object-fit: cover; margin-bottom: 20px;">
                    <div class="slide-content" style="background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px; max-width: 500px; text-align: center;">
                        <h3>"Organic is better for the soil." — Often false</h3>
                        <p>Organic still permits deep tillage, rotovating, soil inversion, and plastic-based weed control. All of these destroy microbial networks and carbon. Middle World Farms builds soil, doesn't destroy it: fungal-rich systems, compost extracts, biochar, cover-layers, reduced disturbance, and continuous feeding of soil life.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; height: 100%; padding: 20px 0;">
                    <img src="<?php echo plugins_url('images/biodiversity.webp', __FILE__); ?>" alt="Biodiversity" style="width: 100px; height: auto; max-height: 80px; object-fit: cover; margin-bottom: 20px;">
                    <div class="slide-content" style="background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px; max-width: 500px; text-align: center;">
                        <h3>"Organic protects wildlife." — Not guaranteed</h3>
                        <p>Organic standards do not require maintaining habitats, hedges, wetlands, wild corridors or pollinator homes. Plenty of certified organic farms are ecological deserts. At Middle World Farms we design for wildlife first — bees, birds, amphibians, beetles, fungi, predators and pollinators. Agriculture here is woven through living ecosystems.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; height: 100%; padding: 20px 0;">
                    <img src="<?php echo plugins_url('images/supporting-local-farmers.webp', __FILE__); ?>" alt="Supporting Local Farmers" style="width: 100px; height: auto; max-height: 80px; object-fit: cover; margin-bottom: 20px;">
                    <div class="slide-content" style="background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px; max-width: 500px; text-align: center;">
                        <h3>"Organic avoids artificial fertiliser." — Misleading</h3>
                        <p>Organic farms still import fertility: pelletised chicken manure, mined rock phosphate, trucked composts. "Natural" doesn't mean sustainable. Middle World Farms feeds soil life, not bags: on-farm composts, fungal brews, biochar, local biomass, deep-rooted plants, and natural succession.</p>
                    </div>
                </div>
                <div class="slide" style="min-width: 100%; position: relative; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; height: 100%; padding: 20px 0;">
                    <img src="<?php echo plugins_url('images/soil-health.webp', __FILE__); ?>" alt="Soil Health" style="width: 100px; height: auto; max-height: 80px; object-fit: cover; margin-bottom: 20px;">
                    <div class="slide-content" style="background: rgba(0,0,0,0.7); color: white; padding: 20px; border-radius: 5px; max-width: 500px; text-align: center;">
                        <h3>"Organic means no pesticides." — Not true</h3>
                        <p>Organic allows many pesticides — copper, pyrethrum, spinosad, soaps, acids, approved synthetic minerals, and more. Some harm bees, microbes or aquatic life. Middle World Farms uses biological design instead of sprays: habitat planting, companion species, wild predators, fungal-based immunity, and microbial balance. Our fields are alive enough that we don't need the "organic-approved" chemicals.</p>
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