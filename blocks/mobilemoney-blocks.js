/**
 * Mobile Money Blocks JavaScript
 * Enhanced functionality for WooCommerce Blocks
 */

(function($) {
    'use strict';
    
    // Check if we're in a Blocks environment
    const isBlocksEnvironment = typeof wp !== 'undefined' && wp.blocks;
    
    // Mobile Money Blocks functionality
    const MobileMoneyBlocks = {
        
        init: function() {
            this.bindEvents();
            this.initializeBlocksSupport();
        },
        
        bindEvents: function() {
            // Operator selection change
            $(document).on('change', '.mobile-money-operator-select', function() {
                MobileMoneyBlocks.updateInstruction($(this));
            });
            
            // Form validation
            $(document).on('submit', 'form.checkout, form.woocommerce-cart-form', function(e) {
                if (MobileMoneyBlocks.shouldValidateForm()) {
                    if (!MobileMoneyBlocks.validateFields()) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
            
            // Blocks-specific events
            if (isBlocksEnvironment) {
                this.bindBlocksEvents();
            }
        },
        
        bindBlocksEvents: function() {
            // Listen for Blocks checkout updates
            $(document).on('updated_checkout', function() {
                MobileMoneyBlocks.initializeBlocksFields();
            });
            
            // Listen for Blocks cart updates
            $(document).on('updated_cart_totals', function() {
                MobileMoneyBlocks.updateCartTotal();
            });
        },
        
        initializeBlocksSupport: function() {
            if (isBlocksEnvironment) {
                // Register with Blocks if available
                if (typeof wp !== 'undefined' && wp.hooks) {
                    wp.hooks.addAction('woocommerce-blocks-checkout-block-registry', 'mobile-money', function(registry) {
                        MobileMoneyBlocks.registerBlocksIntegration(registry);
                    });
                }
            }
        },
        
        registerBlocksIntegration: function(registry) {
            // This would be handled by the PHP integration class
            console.log('Mobile Money Blocks integration registered');
        },
        
        updateInstruction: function(selectElement) {
            const selectedOption = selectElement.find('option:selected');
            const instruction = selectedOption.data('instruction');
            const instructionContainer = $('.mobile-money-instruction');
            
            if (instruction) {
                instructionContainer.html('<p><strong>Instruction:</strong> ' + instruction + '</p>');
                instructionContainer.show();
            } else {
                instructionContainer.hide();
            }
        },
        
        shouldValidateForm: function() {
            // Check if Mobile Money is selected as payment method
            const selectedMethod = $('input[name="payment_method"]:checked').val();
            return selectedMethod === 'wc_mmpayment_blocks' || selectedMethod === 'wc_mmpayment';
        },
        
        validateFields: function() {
            let isValid = true;
            const errors = [];
            
            // Validate operator selection
            const operator = $('select[name="mm_operator"]').val();
            if (!operator) {
                errors.push('Veuillez sélectionner un opérateur Mobile Money');
                isValid = false;
            }
            
            // Validate phone number
            const phoneNumber = $('input[name="mm_sender_msisdn"]').val();
            if (!phoneNumber || phoneNumber.trim() === '') {
                errors.push('Le numéro de téléphone est obligatoire');
                isValid = false;
            }
            
            // Validate transaction ID
            const transactionId = $('input[name="mm_transaction_id"]').val();
            if (!transactionId || transactionId.trim() === '') {
                errors.push("Veuillez préciser l'ID de la transaction");
                isValid = false;
            }
            
            // Display errors if any
            if (!isValid) {
                errors.forEach(function(error) {
                    if (typeof wc_add_to_cart_params !== 'undefined') {
                        // WooCommerce error display
                        $(document.body).trigger('checkout_error', [error]);
                    } else {
                        // Fallback error display
                        alert(error);
                    }
                });
            }
            
            return isValid;
        },
        
        initializeBlocksFields: function() {
            // Initialize any Blocks-specific field behaviors
            $('.mobile-money-blocks-fields').each(function() {
                const container = $(this);
                
                // Add Blocks-specific classes
                container.addClass('wc-block-components-payment-method');
                
                // Initialize operator selection
                const operatorSelect = container.find('.mobile-money-operator-select');
                if (operatorSelect.length) {
                    MobileMoneyBlocks.updateInstruction(operatorSelect);
                }
            });
        },
        
        updateCartTotal: function() {
            // Update cart total display in Blocks
            const cartTotal = $('.wc-block-components-totals-footer-item__value').last().text();
            if (cartTotal) {
                $('.mobile-money-blocks-fields .cart-total').text(cartTotal);
            }
        },
        
        // Enhanced Blocks API support
        getBlocksData: function() {
            if (typeof mmpayment_data !== 'undefined') {
                return mmpayment_data;
            }
            return {};
        },
        
        // Handle Blocks checkout submission
        handleBlocksCheckout: function(formData) {
            const mobileMoneyData = {
                operator: formData.get('mm_operator'),
                sender_msisdn: formData.get('mm_sender_msisdn'),
                transaction_id: formData.get('mm_transaction_id')
            };
            
            // Validate Blocks data
            if (!this.validateBlocksData(mobileMoneyData)) {
                return false;
            }
            
            // Return data for Blocks processing
            return mobileMoneyData;
        },
        
        validateBlocksData: function(data) {
            return data.operator && data.sender_msisdn && data.transaction_id;
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        MobileMoneyBlocks.init();
    });
    
    // Export for Blocks usage
    if (typeof window !== 'undefined') {
        window.MobileMoneyBlocks = MobileMoneyBlocks;
    }
    
    // Blocks-specific initialization
    if (isBlocksEnvironment && typeof wp !== 'undefined' && wp.domReady) {
        wp.domReady(function() {
            MobileMoneyBlocks.initializeBlocksSupport();
        });
    }
    
})(jQuery);
