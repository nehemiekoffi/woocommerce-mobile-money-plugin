<?php
/**
 * WooCommerce Mobile Money Blocks Integration
 * 
 * This class provides the Blocks API integration for WooCommerce 8.3+
 */

if (!defined('ABSPATH')) {
    exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Mobile Money Blocks Integration
 */
class WC_MobileMoney_Blocks_Integration extends AbstractPaymentMethodType {
    
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'wc_mmpayment_blocks';
    
    /**
     * Initializes the payment method type.
     */
    public function initialize() {
        $this->settings = get_option('woocommerce_wc_mmpayment_blocks_settings', array());
    }
    
    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active() {
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        return isset($payment_gateways[$this->name]) && $payment_gateways[$this->name]->is_available();
    }
    
    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles() {
        $asset_path = WC()->plugin_path() . '/assets/js/blocks/payment-methods.js';
        
        if (file_exists($asset_path)) {
            wp_register_script(
                'wc-mobile-money-blocks',
                plugins_url('../mobilemoney-payment.js', __FILE__),
                array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-hooks', 'wp-i18n', 'wp-polyfill'),
                '1.0.0',
                true
            );
            
            return array('wc-mobile-money-blocks');
        }
        
        return array();
    }
    
    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data() {
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        $gateway = isset($payment_gateways[$this->name]) ? $payment_gateways[$this->name] : null;
        
        if (!$gateway) {
            return array();
        }
        
        // Get active operators
        $active_operators = $this->get_active_operators($gateway);
        
        return array(
            'title' => $this->get_setting('title', 'Mobile Money'),
            'description' => $this->get_setting('description', 'Payez à partir de votre compte mobile money'),
            'supports' => $this->get_supported_features(),
            'operators' => $active_operators,
            'cart_total' => WC()->cart ? WC()->cart->get_cart_total() : '0',
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mobile_money_nonce'),
        );
    }
    
    /**
     * Get active operators data
     */
    private function get_active_operators($gateway) {
        $operators = array();
        
        for ($i = 1; $i <= 4; $i++) {
            $name = $gateway->get_option("operator_{$i}_name");
            $phone = $gateway->get_option("operator_{$i}_phone");
            $instruction = $gateway->get_option("operator_{$i}_instruction");
            
            // Only add operators that have a name (not empty)
            if (!empty($name)) {
                $operators[] = array(
                    'name' => $name,
                    'phone' => $phone,
                    'instruction' => $instruction
                );
            }
        }
        
        return $operators;
    }
    
    /**
     * Get setting value
     */
    private function get_setting($key, $default = '') {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
    
    /**
     * Get supported features
     */
    private function get_supported_features() {
        return array(
            'products',
            'refunds',
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
            'multiple_subscriptions'
        );
    }
}
