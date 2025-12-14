/**
 * MWF Email Validation System
 * Prevents email typos during checkout without impeding user experience
 */

// Real-time email validation with typo detection
function mwfEmailValidator() {
    return {
        // Common username typos and corrections (new!)
        usernameTypos: {
            // Character substitutions that are commonly mistyped
            'gir1': 'girl1',        // Pauline's exact case
            'gir2': 'girl2', 
            'gir3': 'girl3',
            'gir': 'girl',
            'recieve': 'receive',
            'seperate': 'separate',
            'occured': 'occurred',
            'buisness': 'business',
            'managment': 'management',
            'acounts': 'accounts',
            'cusomer': 'customer',
            'servce': 'service',
            'offce': 'office',
            'addres': 'address',
            'contac': 'contact',
            'suport': 'support',
            'admn': 'admin',
            'uer': 'user',
            'usr': 'user',
            'adm': 'admin'
        },

        // Common similar-looking character swaps
        characterSwaps: {
            '1': 'l',   // number 1 vs letter l
            'l': '1',   // letter l vs number 1  
            '0': 'o',   // number 0 vs letter o
            'o': '0',   // letter o vs number 0
            'rn': 'm',  // rn looks like m
            'm': 'rn',  // m could be rn
            'cl': 'd',  // cl looks like d
            'vv': 'w',  // vv looks like w
            'ii': 'u'   // ii could be u
        },

        // Common email domain typos and corrections
        typoMap: {
            // Hotmail variations
            'hotmai.co.uk': 'hotmail.co.uk',
            'hotmial.co.uk': 'hotmail.co.uk', 
            'hotamil.co.uk': 'hotmail.co.uk',
            'hotmail.co.k': 'hotmail.co.uk',
            'hotmail.com.uk': 'hotmail.co.uk',
            'hotmailco.uk': 'hotmail.co.uk',
            'hotmal.co.uk': 'hotmail.co.uk',
            'hotmil.co.uk': 'hotmail.co.uk',
            'hotmail.com.uk': 'hotmail.co.uk',
            'hotmailco.uk': 'hotmail.co.uk',
            'hotmal.co.uk': 'hotmail.co.uk',
            'hotmil.co.uk': 'hotmail.co.uk',
            
            // Gmail variations
            'gmai.com': 'gmail.com',
            'gmial.com': 'gmail.com',
            'gmail.co.uk': 'gmail.com',
            'gamil.com': 'gmail.com',
            'gmaill.com': 'gmail.com',
            'gmail.co': 'gmail.com',
            'gmai.co.uk': 'gmail.com',
            
            // Yahoo variations
            'yahoo.co.k': 'yahoo.co.uk',
            'yaho.co.uk': 'yahoo.co.uk',
            'yahooo.co.uk': 'yahoo.co.uk',
            'yahoo.com.uk': 'yahoo.co.uk',
            
            // Outlook variations
            'outlook.co.k': 'outlook.co.uk',
            'outloo.co.uk': 'outlook.co.uk',
            'outlok.co.uk': 'outlook.co.uk',
            'outlook.com.uk': 'outlook.co.uk',
            
            // BT Internet variations
            'btinternet.co.k': 'btinternet.com',
            'bt.co.uk': 'btinternet.com',
            'btinternet.co.uk': 'btinternet.com',
            
            // Other common UK typos
            'sky.co.k': 'sky.com',
            'virgin.co.k': 'virgin.net',
            'tiscali.co.k': 'tiscali.co.uk'
        },

        // Check for username typos (new function!)
        checkUsernameTypos: function(email) {
            const [username, domain] = email.split('@');
            if (!username || !domain) return null;

            // Check direct username typo matches
            for (const [typo, correction] of Object.entries(this.usernameTypos)) {
                if (username.toLowerCase().includes(typo)) {
                    const correctedUsername = username.toLowerCase().replace(typo, correction);
                    return {
                        suggestion: correctedUsername + '@' + domain,
                        confidence: 'high',
                        reason: `Did you mean "${correction}" instead of "${typo}"?`
                    };
                }
            }

            // Check character swap possibilities
            for (const [wrong, right] of Object.entries(this.characterSwaps)) {
                if (username.includes(wrong)) {
                    const testUsername = username.replace(wrong, right);
                    // Only suggest if it makes a more "word-like" pattern
                    if (this.looksMoreLikeWord(testUsername, username)) {
                        return {
                            suggestion: testUsername + '@' + domain,
                            confidence: 'medium',
                            reason: `Did you mean "${right}" instead of "${wrong}"?`
                        };
                    }
                }
            }

            return null;
        },

        // Helper to determine if one username looks more word-like than another
        looksMoreLikeWord: function(option1, option2) {
            // Simple heuristic: prefer versions that end with common patterns
            const commonEndings = ['girl', 'boy', 'man', 'woman', 'lady', 'guy', 'end', 'son', 'ton'];
            
            for (const ending of commonEndings) {
                if (option1.toLowerCase().includes(ending) && !option2.toLowerCase().includes(ending)) {
                    return true;
                }
            }
            
            return false;
        },

        validateEmail: function(email) {
            const result = {
                isValid: false,
                suggestion: null,
                warning: null,
                shouldBlock: false
            };

            // Basic format check
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                result.warning = 'Please enter a valid email address';
                result.shouldBlock = true;
                return result;
            }

            const lowerEmail = email.toLowerCase();

            // Check for USERNAME typos FIRST (new!)
            const usernameCheck = this.checkUsernameTypos(lowerEmail);
            if (usernameCheck) {
                result.suggestion = usernameCheck.suggestion;
                result.warning = usernameCheck.reason;
                return result;
            }

            // Check for DOMAIN typos
            for (const typo in this.typoMap) {
                if (lowerEmail.includes(typo)) {
                    const corrected = lowerEmail.replace(typo, this.typoMap[typo]);
                    result.suggestion = corrected;
                    result.warning = `Did you mean: ${corrected}?`;
                    return result;
                }
            }

            // Check for suspicious patterns
            if (this.hasSuspiciousPatterns(lowerEmail)) {
                result.warning = 'Please double-check your email address';
                return result;
            }

            result.isValid = true;
            return result;
        },

        // Detect suspicious patterns
        hasSuspiciousPatterns: function(email) {
            const suspiciousPatterns = [
                /\.\./, // Double dots
                /@\./, // @ followed by dot
                /\.@/, // Dot followed by @
                /@.*@/, // Multiple @
                /\s/, // Spaces
                /[^a-z0-9@._-]/, // Invalid characters
                /^\./, // Starts with dot
                /\.$/, // Ends with dot
                /@$/, // Ends with @
                /^@/ // Starts with @
            ];

            return suspiciousPatterns.some(pattern => pattern.test(email));
        },

        // Real-time domain validation (DNS check simulation)
        validateDomain: async function(email) {
            const domain = email.split('@')[1];
            if (!domain) return false;

            // Common valid domains (for instant validation)
            const knownDomains = [
                'gmail.com', 'hotmail.co.uk', 'yahoo.co.uk', 'outlook.co.uk',
                'btinternet.com', 'sky.com', 'virgin.net', 'tiscali.co.uk',
                'aol.com', 'icloud.com', 'me.com', 'live.co.uk'
            ];

            if (knownDomains.includes(domain.toLowerCase())) {
                return true;
            }

            // For unknown domains, we could add a lightweight DNS check
            // but for now, we'll assume valid to avoid checkout delays
            return true;
        }
    };
}

// Initialize email validation on checkout
jQuery(document).ready(function($) {
    const validator = mwfEmailValidator();
    let validationTimeout;

    // Add email validation to checkout fields
    function initEmailValidation() {
        const emailFields = $('#billing_email, #account_email, input[type="email"]');
        
        emailFields.each(function() {
            const $field = $(this);
            const $wrapper = $field.closest('.form-row, .woocommerce-form-row');
            
            // Create validation message container
            if (!$wrapper.find('.mwf-email-validation').length) {
                $wrapper.append('<div class="mwf-email-validation" style="margin-top: 5px;"></div>');
            }

            // Real-time validation (with 500ms delay)
            $field.on('input blur', function() {
                clearTimeout(validationTimeout);
                const email = $(this).val().trim();
                
                if (email.length === 0) {
                    $wrapper.find('.mwf-email-validation').empty();
                    return;
                }

                validationTimeout = setTimeout(function() {
                    validateEmailField($field, email, validator);
                }, 500);
            });
        });
    }

    // Validate email field and show suggestions
    function validateEmailField($field, email, validator) {
        const $wrapper = $field.closest('.form-row, .woocommerce-form-row');
        const $validation = $wrapper.find('.mwf-email-validation');
        
        const result = validator.validateEmail(email);

        if (result.suggestion) {
            // Show suggestion with accept button
            $validation.html(`
                <div class="mwf-email-suggestion" style="background: #fff3cd; padding: 8px; border-radius: 4px; border: 1px solid #ffeeba;">
                    <span style="color: #856404;">📧 Did you mean: </span>
                    <button type="button" class="mwf-accept-suggestion" data-suggestion="${result.suggestion}" 
                            style="background: #007cba; color: white; border: none; padding: 2px 8px; border-radius: 3px; cursor: pointer; margin: 0 5px;">
                        ${result.suggestion}
                    </button>
                    <button type="button" class="mwf-dismiss-suggestion" 
                            style="background: transparent; border: none; color: #856404; cursor: pointer; text-decoration: underline;">
                        No, keep as is
                    </button>
                </div>
            `);

            // Handle suggestion acceptance
            $validation.find('.mwf-accept-suggestion').on('click', function() {
                const suggestion = $(this).data('suggestion');
                $field.val(suggestion).trigger('input');
                $validation.html('<div style="color: #155724; background: #d4edda; padding: 5px; border-radius: 3px;">✓ Email corrected</div>');
                setTimeout(() => $validation.fadeOut(), 2000);
            });

            // Handle suggestion dismissal
            $validation.find('.mwf-dismiss-suggestion').on('click', function() {
                $validation.fadeOut();
            });

        } else if (result.warning) {
            // Show warning
            $validation.html(`
                <div style="color: #721c24; background: #f8d7da; padding: 5px; border-radius: 3px;">
                    ⚠️ ${result.warning}
                </div>
            `);

        } else if (result.isValid) {
            // Show success
            $validation.html('<div style="color: #155724; background: #d4edda; padding: 5px; border-radius: 3px;">✓ Email looks good</div>');
            setTimeout(() => $validation.fadeOut(), 2000);
        }

        // Add validation state to field
        $field.data('mwf-email-valid', result.isValid || !result.shouldBlock);
    }

    // Initialize when page loads
    initEmailValidation();

    // Re-initialize after AJAX updates (for dynamic checkout)
    $(document.body).on('updated_checkout', initEmailValidation);

    // Prevent checkout if email is invalid
    $('form.checkout').on('submit', function(e) {
        let hasInvalidEmail = false;

        $('#billing_email, #account_email').each(function() {
            if ($(this).data('mwf-email-valid') === false) {
                hasInvalidEmail = true;
                $(this).focus();
                return false;
            }
        });

        if (hasInvalidEmail) {
            e.preventDefault();
            alert('Please check your email address before continuing.');
            return false;
        }
    });
});

// CSS styles for email validation
const styles = `
<style>
.mwf-email-validation {
    font-size: 14px;
    transition: all 0.3s ease;
}

.mwf-email-suggestion button:hover {
    opacity: 0.8;
}

.mwf-accept-suggestion:hover {
    background: #005a87 !important;
}

@media (max-width: 768px) {
    .mwf-email-validation {
        font-size: 13px;
    }
    
    .mwf-email-suggestion {
        padding: 6px !important;
    }
    
    .mwf-accept-suggestion {
        display: block !important;
        width: 100% !important;
        margin: 5px 0 !important;
    }
}
</style>
`;

// Inject styles
if (!document.getElementById('mwf-email-validation-styles')) {
    const styleElement = document.createElement('div');
    styleElement.id = 'mwf-email-validation-styles';
    styleElement.innerHTML = styles;
    document.head.appendChild(styleElement);
}
