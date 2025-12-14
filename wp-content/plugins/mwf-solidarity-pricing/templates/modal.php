<?php
/**
 * Modal Template - 2-Step Flow
 * Step 1: Choose your price with clickable tiers
 * Step 2: Financial transparency
 */

if (!defined('ABSPATH')) exit;

$bg_image = !empty($settings['background_image']) 
    ? $settings['background_image'] 
    : 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=1200';
?>

<div class="mwf-solidarity-overlay">
    <div class="mwf-solidarity-modal mwf-solidarity-compact">
        
        <!-- Hero Section with Background -->
        <div class="mwf-solidarity-hero" style="background-image: url('<?php echo esc_url($bg_image); ?>');">
            <button class="mwf-solidarity-close" type="button" aria-label="Close">&times;</button>
        </div>
        
        <!-- Step 1: Choose Your Price -->
        <div class="mwf-solidarity-step mwf-step-1 active" id="step1">
            <div class="mwf-solidarity-content">
                
                <h2 class="mwf-solidarity-headline">
                    Choose Your Price
                </h2>
                
                <p class="mwf-solidarity-subheadline">
                    Click a box to learn more about each option
                </p>
                
                <p style="margin: -5px 0 15px 0; font-style: italic; color: #555; font-size: 14px;">
                    Prices based on our most popular box size (Couple's Box)
                </p>
                
                <!-- Price Tiers -->
                <div class="mwf-solidarity-tiers">
                    
                    <div class="mwf-solidarity-tier solidarity clickable" data-tier="solidarity">
                        <span class="mwf-tier-icon">💚</span>
                        <h3 class="mwf-tier-label"><?php echo esc_html($settings['solidarity_label']); ?></h3>
                        <div class="mwf-tier-price"><?php echo esc_html($settings['solidarity_price']); ?></div>
                        <p class="mwf-tier-desc"><?php echo esc_html($settings['solidarity_desc']); ?></p>
                        <span class="mwf-tier-click-hint">Click me →</span>
                    </div>
                    
                    <div class="mwf-solidarity-tier standard clickable" data-tier="standard">
                        <span class="mwf-tier-icon">🌱</span>
                        <h3 class="mwf-tier-label"><?php echo esc_html($settings['standard_label']); ?></h3>
                        <div class="mwf-tier-price"><?php echo esc_html($settings['standard_price']); ?></div>
                        <p class="mwf-tier-desc"><?php echo esc_html($settings['standard_desc']); ?></p>
                        <span class="mwf-tier-click-hint">Click me →</span>
                    </div>
                    
                    <div class="mwf-solidarity-tier supporter clickable" data-tier="supporter">
                        <span class="mwf-tier-icon">🌳</span>
                        <h3 class="mwf-tier-label"><?php echo esc_html($settings['supporter_label']); ?></h3>
                        <div class="mwf-tier-price"><?php echo esc_html($settings['supporter_price']); ?></div>
                        <p class="mwf-tier-desc"><?php echo esc_html($settings['supporter_desc']); ?></p>
                        <span class="mwf-tier-click-hint">Click me →</span>
                    </div>
                    
                </div>
                
                <div class="mwf-solidarity-buttons">
                    <button type="button" class="mwf-solidarity-btn mwf-solidarity-btn-next">
                        Next: See How It Works →
                    </button>
                </div>
                
            </div>
        </div>
        
        <!-- Step 2: Financial Transparency -->
        <div class="mwf-solidarity-step mwf-step-2" id="step2">
            <div class="mwf-solidarity-content mwf-content-scrollable">
                
                <h2 class="mwf-solidarity-headline">
                    💰 Financial Transparency
                </h2>
                
                <p class="mwf-solidarity-subheadline">
                    Here's exactly where your money goes
                </p>
                
                <div class="mwf-transparency-section">
                    <h3>📊 Our Annual Farm Budget</h3>
                    
                    <div class="mwf-visual-costs">
                        <div class="mwf-cost-item">
                            <div class="mwf-cost-icon">👨‍🌾</div>
                            <div class="mwf-cost-details">
                                <strong>Farmer Wages</strong>
                                <span class="mwf-cost-amount">£45,000</span>
                                <p>2 full-time farmers (fair wage goal)</p>
                            </div>
                        </div>
                        
                        <div class="mwf-cost-item">
                            <div class="mwf-cost-icon">🌱</div>
                            <div class="mwf-cost-details">
                                <strong>Seeds & Compost</strong>
                                <span class="mwf-cost-amount">£<?php echo $pricing['annual_seeds']; ?></span>
                                <p>Scales with box size</p>
                            </div>
                        </div>
                        
                        <div class="mwf-cost-item">
                            <div class="mwf-cost-icon">🏠</div>
                            <div class="mwf-cost-details">
                                <strong>Land & Utilities</strong>
                                <span class="mwf-cost-amount">£4,800</span>
                                <p>Rent, water, electricity</p>
                            </div>
                        </div>
                        
                        <div class="mwf-cost-item">
                            <div class="mwf-cost-icon">🚜</div>
                            <div class="mwf-cost-details">
                                <strong>Equipment</strong>
                                <span class="mwf-cost-amount">£4,000</span>
                                <p>Maintenance, repairs, fuel</p>
                            </div>
                        </div>
                        
                        <div class="mwf-cost-item">
                            <div class="mwf-cost-icon">📦</div>
                            <div class="mwf-cost-details">
                                <strong>Packaging</strong>
                                <span class="mwf-cost-amount">£<?php echo $pricing['annual_packaging']; ?></span>
                                <p>Boxes (delivery billed separately)</p>
                            </div>
                        </div>
                        
                        <div class="mwf-cost-item">
                            <div class="mwf-cost-icon">🛡️</div>
                            <div class="mwf-cost-details">
                                <strong>Insurance & Admin</strong>
                                <span class="mwf-cost-amount">£700</span>
                                <p>Insurance, accounting</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mwf-cost-total-box">
                        <strong>Total Annual Budget:</strong>
                        <span class="mwf-total-amount">£<?php echo $pricing['annual_total']; ?></span>
                    </div>
                </div>
                
                <div class="mwf-transparency-section">
                    <h3>🧮 The Math Behind Our Prices</h3>
                    
                    <div class="mwf-math-visual">
                        <div class="mwf-math-step">
                            <span class="mwf-math-icon">📊</span>
                            <div class="mwf-math-content">
                                <strong>100 members</strong>
                                <p>Our target community</p>
                            </div>
                        </div>
                        
                        <div class="mwf-math-arrow">→</div>
                        
                        <div class="mwf-math-step">
                            <span class="mwf-math-icon">📅</span>
                            <div class="mwf-math-content">
                                <strong>48 weeks/year</strong>
                                <p>Deliveries</p>
                            </div>
                        </div>
                        
                        <div class="mwf-math-arrow">→</div>
                        
                        <div class="mwf-math-step">
                            <span class="mwf-math-icon">💷</span>
                            <div class="mwf-math-content">
                                <strong>£11.81 break-even</strong>
                                <p>Minimum to survive</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mwf-calculation-note">
                        <p>£<?php echo $pricing['annual_total']; ?> ÷ <?php echo $pricing['members']; ?> members ÷ <?php echo $pricing['weeks']; ?> weeks = <strong>£<?php echo $pricing['minimum_per_box']; ?> break-even</strong></p>
                        <p class="mwf-note-small"><strong>£10.50 solidarity minimum</strong> = We take a small loss to help you</p>
                        <p class="mwf-note-small"><strong>£11.81</strong> = Break-even (farm survives, no fair wages yet)</p>
                        <p class="mwf-note-small"><strong>£15 standard</strong> = Minimum wage for farmers</p>
                        <p class="mwf-note-small"><strong>£20 supporter</strong> = Fair wage + helping subsidize others</p>
                        <p class="mwf-note-small"><strong>Above £20</strong> = Really helping both farm and those in need</p>
                    </div>
                </div>
                
                <div class="mwf-transparency-section">
                    <div class="mwf-wage-reality">
                        <p><strong>⚠️ Important:</strong> Our £45,000 farmer wages = £21.60/hour <em>GROSS</em> (before tax, pension, holidays).</p>
                        <p>Take-home is closer to £15-16/hour - barely a living wage for skilled agricultural work.</p>
                    </div>
                    
                    <div class="mwf-trust-box">
                        <p><strong>This system relies on honesty:</strong></p>
                        <p>Below £11.81 = we subsidize you (that's okay if you need the help). At £15 = minimum wage. At £20+ = you enable solidarity pricing for those in genuine need. <strong>Please choose based on what you can truly afford.</strong></p>
                    </div>
                </div>
                
                <div class="mwf-solidarity-buttons">
                    <button type="button" class="mwf-solidarity-btn mwf-solidarity-btn-prev">
                        ← Back
                    </button>
                    <button type="button" class="mwf-solidarity-btn mwf-solidarity-btn-primary">
                        ✓ I Understand
                    </button>
                </div>
                
                <div class="mwf-solidarity-checkbox">
                    <label>
                        <input type="checkbox" id="mwf-solidarity-dont-show" />
                        Don't show this message again
                    </label>
                </div>
                
            </div>
        </div>
        
        <!-- Tier Popups -->
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
                        <p><strong>You get the EXACT SAME box</strong> as everyone else - same quantity, quality, variety, and freshness. Zero difference.</p>
                    </div>
                    <p class="mwf-popup-note">Made possible by supporters. When your situation improves, you can pay it forward too.</p>
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
                        <div class="mwf-popup-cost">£5.50 → Farmer wages</div>
                        <div class="mwf-popup-cost">£3.20 → Seeds & supplies</div>
                        <div class="mwf-popup-cost">£2.50 → Land & utilities</div>
                        <div class="mwf-popup-cost">£2.30 → Equipment</div>
                        <div class="mwf-popup-cost">£1.50 → Delivery & admin</div>
                        <div class="mwf-popup-total">= £15.00 total</div>
                    </div>
                    
                    <p><strong>What "fair wage" means:</strong> £21.60/hour GROSS (before tax, pension, holidays). Take-home closer to £15-16/hour.</p>
                    
                    <div class="mwf-popup-highlight">
                        <p>If you can afford £15, this is the right choice. It keeps the farm sustainable.</p>
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
                    
                    <div class="mwf-popup-impact">
                        <div class="mwf-impact-item">
                            <strong>£18 per box</strong>
                            <p>£3 extra subsidizes someone on solidarity price</p>
                        </div>
                        <div class="mwf-impact-item">
                            <strong>£20 per box</strong>
                            <p>Builds emergency fund for crop failures</p>
                        </div>
                        <div class="mwf-impact-item">
                            <strong>£25+ per box</strong>
                            <p>Farm improvements, free food bank boxes - and the farmers might actually afford a pint!</p>
                        </div>
                    </div>
                    
                    <div class="mwf-popup-highlight">
                        <p>Every £1 above standard directly helps someone else access organic food. That's the solidarity promise.</p>
                    </div>
                </div>
                <button type="button" class="mwf-popup-btn">Got it!</button>
            </div>
        </div>
        
    </div>
</div>
