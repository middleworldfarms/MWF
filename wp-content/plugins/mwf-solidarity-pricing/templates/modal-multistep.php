<?php
/**
 * Multi-Step Modal Template
 * Complete solidarity pricing story with financial transparency
 */

if (!defined('ABSPATH')) exit;

$bg_image = !empty($settings['background_image']) 
    ? $settings['background_image'] 
    : 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=1200';
?>

<div class="mwf-solidarity-overlay">
    <div class="mwf-solidarity-modal mwf-multistep">
        
        <!-- Progress Indicator -->
        <div class="mwf-step-progress">
            <div class="mwf-step-indicator active" data-step="1"></div>
            <div class="mwf-step-indicator" data-step="2"></div>
            <div class="mwf-step-indicator" data-step="3"></div>
            <div class="mwf-step-indicator" data-step="4"></div>
            <div class="mwf-step-indicator" data-step="5"></div>
        </div>
        
        <!-- Step 1: The Story - Why We Do This -->
        <div class="mwf-step mwf-step-1 active">
            <div class="mwf-solidarity-hero" style="background-image: url('<?php echo esc_url($bg_image); ?>');">
                <div class="mwf-hero-overlay">
                    <h1>🥬 Food Belongs to Everyone 🥬</h1>
                </div>
            </div>
            
            <div class="mwf-solidarity-content">
                <h2>A Different Way of Doing Food</h2>
                
                <div class="mwf-story-section">
                    <p class="mwf-lead">In our current food system, prices are set by supermarkets, middlemen, and global markets. Farmers struggle to earn fair wages while families struggle to afford fresh, organic produce.</p>
                    
                    <p>We're building something different: <strong>Community Supported Agriculture</strong> where you're not just a customer - you're a partner in the farm.</p>
                    
                    <div class="mwf-visual-stats">
                        <div class="mwf-stat">
                            <span class="mwf-stat-icon">🌾</span>
                            <strong>100% Organic</strong>
                            <span>No chemicals, ever</span>
                        </div>
                        <div class="mwf-stat">
                            <span class="mwf-stat-icon">👨‍🌾</span>
                            <strong>Fair Wages</strong>
                            <span>Living income for farmers</span>
                        </div>
                        <div class="mwf-stat">
                            <span class="mwf-stat-icon">🤝</span>
                            <strong>Community First</strong>
                            <span>Everyone eats well</span>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="mwf-btn-next">Next: How It Works →</button>
            </div>
        </div>
        
        <!-- Step 2: What You Get -->
        <div class="mwf-step mwf-step-2">
            <div class="mwf-solidarity-content">
                <h2>🥗 Your Weekly Vegetable Box</h2>
                
                <div class="mwf-harvest-info">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=800" alt="Fresh vegetables" class="mwf-harvest-img">
                    
                    <p class="mwf-lead">Depending on the season, your box includes: fresh greens, root vegetables, herbs, edible flowers, and seasonal specialties.</p>
                    
                    <div class="mwf-season-breakdown">
                        <div class="mwf-season">
                            <strong>🌸 Spring/Summer</strong>
                            <p>Salads, tomatoes, cucumbers, herbs, strawberries, flowers</p>
                        </div>
                        <div class="mwf-season">
                            <strong>🍂 Autumn/Winter</strong>
                            <p>Root veg, squash, kale, leeks, Brussels sprouts, stored produce</p>
                        </div>
                    </div>
                    
                    <div class="mwf-box-sizes">
                        <div class="mwf-box-option">
                            <strong>Single/Couple's Box</strong>
                            <p>Perfect for 1-2 people</p>
                        </div>
                        <div class="mwf-box-option">
                            <strong>Family Box</strong>
                            <p>Feeds a family of 4+</p>
                        </div>
                    </div>
                </div>
                
                <div class="mwf-step-buttons">
                    <button type="button" class="mwf-btn-prev">← Back</button>
                    <button type="button" class="mwf-btn-next">Next: Our Costs →</button>
                </div>
            </div>
        </div>
        
        <!-- Step 3: Financial Transparency - Our Real Costs -->
        <div class="mwf-step mwf-step-3">
            <div class="mwf-solidarity-content">
                <h2>💰 Financial Transparency</h2>
                
                <p class="mwf-lead">Unlike supermarkets, we show you exactly where your money goes. Here's what it costs to run this farm:</p>
                
                <div class="mwf-cost-breakdown">
                    <h3>Annual Operating Costs</h3>
                    
                    <div class="mwf-cost-table">
                        <div class="mwf-cost-row mwf-cost-header">
                            <span>Item</span>
                            <span>Annual Cost</span>
                        </div>
                        
                        <div class="mwf-cost-row">
                            <span>👨‍🌾 Farmer Labor (2 full-time)</span>
                            <span>£45,000</span>
                        </div>
                        <div class="mwf-cost-row">
                            <span>🌱 Seeds & Supplies</span>
                            <span>£8,500</span>
                        </div>
                        <div class="mwf-cost-row">
                            <span>🚜 Equipment & Maintenance</span>
                            <span>£6,200</span>
                        </div>
                        <div class="mwf-cost-row">
                            <span>🏠 Land Rent & Utilities</span>
                            <span>£12,000</span>
                        </div>
                        <div class="mwf-cost-row">
                            <span>📦 Packaging & Delivery</span>
                            <span>£4,800</span>
                        </div>
                        <div class="mwf-cost-row">
                            <span>🔧 Insurance & Admin</span>
                            <span>£3,500</span>
                        </div>
                        
                        <div class="mwf-cost-row mwf-cost-total">
                            <span><strong>Total Annual Costs</strong></span>
                            <span><strong>£80,000</strong></span>
                        </div>
                    </div>
                    
                    <div class="mwf-cost-math">
                        <div class="mwf-math-box">
                            <p><strong>The Math:</strong></p>
                            <p>£80,000 ÷ 150 members ÷ 48 weeks = <strong>£11.11 per box per week</strong></p>
                            <p class="mwf-small">This is our absolute minimum to survive</p>
                        </div>
                    </div>
                    
                    <div class="mwf-wage-breakdown">
                        <h4>What "Fair Wage" Actually Means</h4>
                        <p>Our £45,000 farmer salary = £21.60/hour (£45k ÷ 2 farmers ÷ 2080 hours)</p>
                        <p class="mwf-small">That's <strong>GROSS</strong> - before tax, pensions, holiday, sick pay, insurance</p>
                        <p class="mwf-small">Take-home is closer to £15-16/hour - barely a living wage for skilled farming work</p>
                    </div>
                </div>
                
                <div class="mwf-step-buttons">
                    <button type="button" class="mwf-btn-prev">← Back</button>
                    <button type="button" class="mwf-btn-next">Next: Solidarity Pricing →</button>
                </div>
            </div>
        </div>
        
        <!-- Step 4: How Solidarity Pricing Works -->
        <div class="mwf-step mwf-step-4">
            <div class="mwf-solidarity-content">
                <h2>🤝 How Solidarity Pricing Works</h2>
                
                <p class="mwf-lead">A fixed price doesn't consider everyone's economic situation. Solidarity pricing means you choose based on your own circumstances.</p>
                
                <div class="mwf-solidarity-explainer">
                    <div class="mwf-explainer-box">
                        <h3>The Income-Based Model</h3>
                        <p>Think about your own hourly wage. How many hours would you work to pay for a week of organic vegetables?</p>
                        
                        <div class="mwf-wage-examples">
                            <div class="mwf-wage-example">
                                <strong>Living on benefits (£8/hr equivalent)</strong>
                                <p>£11 box = 1.4 hours of your income</p>
                                <span class="mwf-badge solidarity">Solidarity Price</span>
                            </div>
                            
                            <div class="mwf-wage-example">
                                <strong>Minimum wage (£11.44/hr)</strong>
                                <p>£15 box = 1.3 hours of your income</p>
                                <span class="mwf-badge standard">Standard Price</span>
                            </div>
                            
                            <div class="mwf-wage-example">
                                <strong>Professional salary (£25/hr+)</strong>
                                <p>£20 box = 0.8 hours of your income</p>
                                <span class="mwf-badge supporter">Supporter Price</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mwf-how-it-helps">
                        <h3>How Your Payment Helps</h3>
                        <ul>
                            <li><strong>Standard Price (£15):</strong> Covers our costs, keeps farm sustainable</li>
                            <li><strong>Supporter Price (£18-25+):</strong> Builds emergency fund, pays for improvements, subsidizes others</li>
                            <li><strong>Solidarity Price (£11-14):</strong> Made possible by supporters - you still get full box, same quality</li>
                        </ul>
                        
                        <p class="mwf-highlight">💚 This only works if most people pay standard or above. We need 60% at standard, 20% supporters, 20% solidarity.</p>
                    </div>
                </div>
                
                <div class="mwf-step-buttons">
                    <button type="button" class="mwf-btn-prev">← Back</button>
                    <button type="button" class="mwf-btn-next">Next: Choose Your Price →</button>
                </div>
            </div>
        </div>
        
        <!-- Step 5: Choose Your Price -->
        <div class="mwf-step mwf-step-5">
            <div class="mwf-solidarity-content">
                <h2>💚 Choose What Feels Right</h2>
                
                <p class="mwf-lead">Consider your income, household size, and what feels fair. There's no judgment - we trust you.</p>
                
                <div class="mwf-solidarity-tiers">
                    
                    <div class="mwf-solidarity-tier solidarity">
                        <span class="mwf-tier-icon">💚</span>
                        <h3>Solidarity Price</h3>
                        <div class="mwf-tier-price">£10.50 - £14</div>
                        
                        <div class="mwf-tier-details">
                            <p><strong>For those who need support</strong></p>
                            <ul>
                                <li>Unemployed, on benefits, low income</li>
                                <li>Single parents, students</li>
                                <li>Facing financial hardship</li>
                            </ul>
                            <p class="mwf-small">You receive the exact same box as everyone else. Same quality, same quantity.</p>
                        </div>
                    </div>
                    
                    <div class="mwf-solidarity-tier standard">
                        <span class="mwf-tier-icon">🌱</span>
                        <h3>Standard Price</h3>
                        <div class="mwf-tier-price">£15</div>
                        
                        <div class="mwf-tier-details">
                            <p><strong>The true cost</strong></p>
                            <ul>
                                <li>Covers all farm operating costs</li>
                                <li>Fair (though modest) farmer wages</li>
                                <li>Sustainable baseline</li>
                            </ul>
                            <p class="mwf-small">This is what it actually costs to grow your food ethically.</p>
                        </div>
                        <span class="mwf-recommended">✓ Recommended if you can afford it</span>
                    </div>
                    
                    <div class="mwf-solidarity-tier supporter">
                        <span class="mwf-tier-icon">🌳</span>
                        <h3>Supporter Price</h3>
                        <div class="mwf-tier-price">£18 - £25+</div>
                        
                        <div class="mwf-tier-details">
                            <p><strong>Pay it forward</strong></p>
                            <ul>
                                <li>Professional salary or comfortable income</li>
                                <li>Your extra helps others access good food</li>
                                <li>Builds farm resilience & improvements</li>
                            </ul>
                            <p class="mwf-small">Every £1 above standard helps someone else eat well.</p>
                        </div>
                    </div>
                    
                </div>
                
                <div class="mwf-pricing-note">
                    <p><strong>A note on fairness:</strong> If everyone paid solidarity price, we'd shut down in 3 months. If everyone paid supporter price, we could offer free boxes to food banks. The system only works with balance.</p>
                    
                    <p>We expect most people to pay £15 (standard). Some will pay less because they need to. Some will pay more because they can. Together, we all eat.</p>
                </div>
                
                <div class="mwf-commitment">
                    <label class="mwf-checkbox-large">
                        <input type="checkbox" id="mwf-solidarity-understand" required />
                        <span>✓ I understand this system relies on honesty and community trust. I'll choose a price that reflects my true circumstances.</span>
                    </label>
                </div>
                
                <div class="mwf-step-buttons">
                    <button type="button" class="mwf-btn-prev">← Back</button>
                    <button type="button" class="mwf-btn-finish" disabled>I'm Ready to Choose →</button>
                </div>
                
                <div class="mwf-solidarity-checkbox">
                    <label>
                        <input type="checkbox" id="mwf-solidarity-dont-show" />
                        Don't show this again (I've read and understood)
                    </label>
                </div>
            </div>
        </div>
        
    </div>
</div>
