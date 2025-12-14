<?php
/**
 * Modal Template
 * Beautiful splash screen for solidarity pricing
 */

if (!defined('ABSPATH')) exit;

$bg_image = !empty($settings['background_image']) 
    ? $settings['background_image'] 
    : 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=1200'; // Placeholder farmer image
?>

<div class="mwf-solidarity-overlay">
    <div class="mwf-solidarity-modal mwf-solidarity-compact">
        
        <!-- Hero Section with Background -->
        <div class="mwf-solidarity-hero" style="background-image: url('<?php echo esc_url($bg_image); ?>');">
            <button class="mwf-solidarity-close" type="button" aria-label="Close">&times;</button>
        </div>
        
        <!-- Content Section -->
        <div class="mwf-solidarity-content">
            
            <!-- Compact Headline -->
            <h2 class="mwf-solidarity-headline">
                Choose Your Price
            </h2>
            
            <p class="mwf-solidarity-subheadline">
                Click a box to learn more about each option
            </p>
            
            <!-- Price Tiers - Clickable -->
            <div class="mwf-solidarity-tiers">
                
                <!-- Solidarity Tier -->
                <div class="mwf-solidarity-tier solidarity clickable" data-tier="solidarity">
                    <span class="mwf-tier-icon">💚</span>
                    <h3 class="mwf-tier-label"><?php echo esc_html($settings['solidarity_label']); ?></h3>
                    <div class="mwf-tier-price"><?php echo esc_html($settings['solidarity_price']); ?></div>
                    <p class="mwf-tier-desc"><?php echo esc_html($settings['solidarity_desc']); ?></p>
                    <span class="mwf-tier-click-hint">Click me →</span>
                </div>
                
                <!-- Standard Tier -->
                <div class="mwf-solidarity-tier standard clickable" data-tier="standard">
                    <span class="mwf-tier-icon">🌱</span>
                    <h3 class="mwf-tier-label"><?php echo esc_html($settings['standard_label']); ?></h3>
                    <div class="mwf-tier-price"><?php echo esc_html($settings['standard_price']); ?></div>
                    <p class="mwf-tier-desc"><?php echo esc_html($settings['standard_desc']); ?></p>
                    <span class="mwf-tier-click-hint">Click me →</span>
                </div>
                
                <!-- Supporter Tier -->
                <div class="mwf-solidarity-tier supporter clickable" data-tier="supporter">
                    <span class="mwf-tier-icon">🌳</span>
                    <h3 class="mwf-tier-label"><?php echo esc_html($settings['supporter_label']); ?></h3>
                    <div class="mwf-tier-price"><?php echo esc_html($settings['supporter_price']); ?></div>
                    <p class="mwf-tier-desc"><?php echo esc_html($settings['supporter_desc']); ?></p>
                    <span class="mwf-tier-click-hint">Click me →</span>
                </div>
                
            </div>
            
            <!-- Tier Detail Popups -->
            <div class="mwf-tier-popup" id="mwf-popup-solidarity">
                <div class="mwf-tier-popup-content">
                    <button class="mwf-popup-close" type="button">&times;</button>
                    <span class="mwf-tier-icon-large">💚</span>
                    <h3>Solidarity Price: £10.50 - £14</h3>
                    <div class="mwf-popup-body">
                        <p><strong>For those who need support right now</strong></p>
                        <ul>
                            <li>💚 Unemployed, on benefits, or low income</li>
                            <li>💚 Single parents managing tight budgets</li>
                            <li>💚 Students, pensioners on fixed income</li>
                            <li>💚 Facing temporary financial hardship</li>
                        </ul>
                        <div class="mwf-popup-highlight">
                            <p><strong>You get the EXACT SAME box</strong> as everyone else:</p>
                            <p>✓ Same quantity of vegetables<br>
                            ✓ Same organic quality<br>
                            ✓ Same variety and freshness<br>
                            ✓ Zero difference in what you receive</p>
                        </div>
                        <p class="mwf-popup-note">This price is made possible by supporters paying a bit more. When your situation improves, you can pay it forward too.</p>
                    </div>
                    <button type="button" class="mwf-popup-btn">Got it!</button>
                </div>
            </div>
            
            <div class="mwf-tier-popup" id="mwf-popup-standard">
                <div class="mwf-tier-popup-content">
                    <button class="mwf-popup-close" type="button">&times;</button>
                    <span class="mwf-tier-icon-large">🌱</span>
                    <h3>Standard Price: £15</h3>
                    <div class="mwf-popup-body">
                        <p><strong>This is what it actually costs to grow your food</strong></p>
                        
                        <div class="mwf-popup-breakdown">
                            <div class="mwf-popup-cost">£5.50 → Farmer wages (2 people)</div>
                            <div class="mwf-popup-cost">£3.20 → Seeds, supplies, packaging</div>
                            <div class="mwf-popup-cost">£2.50 → Land rent & utilities</div>
                            <div class="mwf-popup-cost">£2.30 → Equipment, maintenance, insurance</div>
                            <div class="mwf-popup-cost">£1.50 → Delivery, admin, contingency</div>
                            <div class="mwf-popup-total">= £15.00 total per box</div>
                        </div>
                        
                        <p><strong>What "fair wage" means:</strong></p>
                        <p>Our farmers earn £21.60/hour GROSS (before tax, pension, holiday, sick pay). Take-home is closer to £15-16/hour - this is barely a living wage for skilled agricultural work.</p>
                        
                        <div class="mwf-popup-highlight">
                            <p>If you can afford £15, this is the right choice. It keeps the farm sustainable and farmers fed.</p>
                        </div>
                    </div>
                    <button type="button" class="mwf-popup-btn">Got it!</button>
                </div>
            </div>
            
            <div class="mwf-tier-popup" id="mwf-popup-supporter">
                <div class="mwf-tier-popup-content">
                    <button class="mwf-popup-close" type="button">&times;</button>
                    <span class="mwf-tier-icon-large">🌳</span>
                    <h3>Supporter Price: £18 - £25+</h3>
                    <div class="mwf-popup-body">
                        <p><strong>Pay it forward - your extra helps others</strong></p>
                        
                        <p>If you earn a professional salary or have comfortable disposable income, choosing supporter price means:</p>
                        
                        <div class="mwf-popup-impact">
                            <div class="mwf-impact-item">
                                <strong>£18 per box</strong>
                                <p>Your £3 extra subsidizes someone on solidarity price</p>
                            </div>
                            <div class="mwf-impact-item">
                                <strong>£20 per box</strong>
                                <p>Helps build emergency fund for crop failures</p>
                            </div>
                            <div class="mwf-impact-item">
                                <strong>£25+ per box</strong>
                                <p>Funds farm improvements & enables free boxes for food banks</p>
                            </div>
                        </div>
                        
                        <div class="mwf-popup-highlight">
                            <p><strong>The reality check:</strong></p>
                            <p>If everyone paid solidarity price, we'd close in 3 months.<br>
                            If everyone paid supporter price, we could give away free boxes.<br>
                            The system works when we balance each other out.</p>
                        </div>
                        
                        <p class="mwf-popup-note">Every £1 above standard price directly helps someone else access organic food. That's the solidarity model.</p>
                    </div>
                    <button type="button" class="mwf-popup-btn">Got it!</button>
                </div>
            </div>
            
            <!-- Simple close button -->
            <div class="mwf-solidarity-buttons">
                <button type="button" class="mwf-solidarity-btn mwf-solidarity-btn-primary">
                    ✓ I Understand
                </button>
            </div>
            
            <!-- Don't Show Again -->
            <div class="mwf-solidarity-checkbox">
                <label>
                    <input type="checkbox" id="mwf-solidarity-dont-show" />
                    Don't show this message again
                </label>
            </div>
            
        </div>
        
    </div>
</div>
