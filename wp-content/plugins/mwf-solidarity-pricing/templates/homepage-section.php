<?php
/**
 * Homepage Solidarity Pricing Section
 * Comprehensive section with education, emotion, and CTA
 * Clicking triggers the full modal
 */

if (!defined('ABSPATH')) {
    exit;
}

// Annual budget figures
$annual_wages = 45000;
$annual_seeds = 2000;
$annual_land = 4800;
$annual_equipment = 4000;
$annual_packaging = 200;
$annual_admin = 700;
$annual_total = 56700;
$members = 100;
$weeks = 48;
$minimum_per_box = round($annual_total / $members / $weeks, 2);
?>

<section class="mwf-solidarity-homepage-section">
    <div class="mwf-solidarity-container">
        
        <!-- Header -->
        <div class="mwf-solidarity-header">
            <h2 class="mwf-solidarity-title"><?php echo esc_html($atts['title']); ?></h2>
            <p class="mwf-solidarity-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
        </div>
        
        <!-- Main Content Grid -->
        <div class="mwf-solidarity-grid">
            
            <!-- Left: Emotional Message -->
            <div class="mwf-solidarity-card mwf-solidarity-emotional">
                <div class="mwf-solidarity-card-icon">🌱</div>
                <h3>Pay What You Can</h3>
                <p class="mwf-solidarity-large-text">
                    Everyone deserves access to fresh, organic vegetables — regardless of their income.
                </p>
                <p>
                    Our solidarity pricing model means those who can afford to pay a little more help subsidize 
                    boxes for those who need financial support. Together, we build a community where everyone eats well.
                </p>
                <div class="mwf-solidarity-stats">
                    <div class="mwf-solidarity-stat">
                        <span class="stat-number">100</span>
                        <span class="stat-label">Family Goal</span>
                    </div>
                    <div class="mwf-solidarity-stat">
                        <span class="stat-number">£56.7k</span>
                        <span class="stat-label">Annual Budget</span>
                    </div>
                    <div class="mwf-solidarity-stat">
                        <span class="stat-number">£11.81</span>
                        <span class="stat-label">Break-Even/Box</span>
                    </div>
                </div>
            </div>
            
            <!-- Right: Interactive Pricing Tiers -->
            <div class="mwf-solidarity-card mwf-solidarity-pricing">
                <h3>How It Works</h3>
                <p class="mwf-solidarity-pricing-intro">
                    Choose the price tier that works for you. Every box helps support fair wages and sustainable farming.
                </p>
                
                <div class="mwf-solidarity-pricing-tiers">
                    <!-- Solidarity Tier -->
                    <div class="mwf-solidarity-tier solidarity-tier" data-trigger-modal>
                        <div class="tier-icon">💚</div>
                        <div class="tier-content">
                            <h4>Solidarity Price</h4>
                            <p class="tier-price">From £10.50</p>
                            <p class="tier-description">
                                We subsidize you (that's okay if you need help)
                            </p>
                            <div class="tier-badge">Farm takes a loss</div>
                        </div>
                    </div>
                    
                    <!-- Standard Tier -->
                    <div class="mwf-solidarity-tier standard-tier" data-trigger-modal>
                        <div class="tier-icon">🌱</div>
                        <div class="tier-content">
                            <h4>Standard Price</h4>
                            <p class="tier-price">£15 - £25</p>
                            <p class="tier-description">
                                Covers costs + minimum wage for farmers
                            </p>
                            <div class="tier-badge">Farm survives</div>
                        </div>
                    </div>
                    
                    <!-- Supporter Tier -->
                    <div class="mwf-solidarity-tier supporter-tier" data-trigger-modal>
                        <div class="tier-icon">🌳</div>
                        <div class="tier-content">
                            <h4>Supporter Price</h4>
                            <p class="tier-price">£20 - £50+</p>
                            <p class="tier-description">
                                Fair wage + helping subsidize others
                            </p>
                            <div class="tier-badge">Farm thrives</div>
                        </div>
                    </div>
                </div>
                
                <button class="mwf-solidarity-cta-button" data-trigger-modal>
                    <span>See Full Budget Breakdown</span>
                    <span class="button-arrow">→</span>
                </button>
            </div>
            
        </div>
        
        <!-- Bottom: Transparency Banner -->
        <div class="mwf-solidarity-transparency" data-trigger-modal>
            <div class="transparency-content">
                <h4>💰 Complete Financial Transparency</h4>
                <div class="transparency-breakdown">
                    <div class="breakdown-item">
                        <span class="breakdown-icon">👨‍🌾</span>
                        <span class="breakdown-label">Fair Wages</span>
                        <span class="breakdown-amount">£45,000</span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-icon">🌾</span>
                        <span class="breakdown-label">Seeds</span>
                        <span class="breakdown-amount">£2,000</span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-icon">🏞️</span>
                        <span class="breakdown-label">Land</span>
                        <span class="breakdown-amount">£4,800</span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-icon">🚜</span>
                        <span class="breakdown-label">Equipment</span>
                        <span class="breakdown-amount">£4,000</span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-icon">📦</span>
                        <span class="breakdown-label">Packaging</span>
                        <span class="breakdown-amount">£200</span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-icon">📋</span>
                        <span class="breakdown-label">Admin</span>
                        <span class="breakdown-amount">£700</span>
                    </div>
                </div>
                <p class="transparency-cta">
                    Click to see the full calculation and learn how your payment helps →
                </p>
            </div>
        </div>
        
        <!-- Call to Action -->
        <div class="mwf-solidarity-bottom-cta">
            <h3>Ready to support fair farming?</h3>
            <p>Choose your vegbox and pick a price that works for you</p>
            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="mwf-solidarity-shop-button">
                Browse Vegbox Subscriptions
            </a>
        </div>
        
    </div>
</section>

<script>
// Trigger modal when clicking anywhere on the section
jQuery(document).ready(function($) {
    $('.mwf-solidarity-tier, .mwf-solidarity-transparency, [data-trigger-modal]').on('click', function(e) {
        e.preventDefault();
        // Trigger the modal (using existing modal JS)
        if (window.MWF_SolidarityModal) {
            MWF_SolidarityModal.showModal();
        } else if (typeof mwfShowSolidarityModal === 'function') {
            mwfShowSolidarityModal();
        } else {
            // Fallback: trigger click on shop banner if it exists
            $('.mwf-solidarity-banner').trigger('click');
        }
    });
});
</script>
