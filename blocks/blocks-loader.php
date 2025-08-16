<?php
/**
 * Mobile Money Blocks Loader
 * 
 * This file handles the initialization of the Blocks implementation
 * and ensures proper loading order and compatibility checks
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mobile Money Blocks Loader Class
 */
class WC_MobileMoney_Blocks_Loader {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'), 20);
        add_action('woocommerce_blocks_loaded', array($this, 'register_blocks_support'));
    }
    
    /**
     * Initialize Blocks support
     */
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Check if we're in a Blocks environment
        if ($this->should_load_blocks()) {
            $this->load_blocks_implementation();
        }
    }
    
    /**
     * Check if we should load Blocks implementation
     */
    private function should_load_blocks() {
        // Check WooCommerce version
        if (!function_exists('WC') || !WC()->version) {
            return false;
        }
        
        $wc_version = WC()->version;
        
        // Load Blocks for WooCommerce 8.3.0 and above
        if (version_compare($wc_version, '8.3.0', '>=')) {
            return true;
        }
        
        // Also check if Blocks are explicitly enabled
        if (class_exists('Automattic\WooCommerce\Blocks\Package')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Load Blocks implementation
     */
    private function load_blocks_implementation() {
        // Load the Blocks gateway class
        require_once plugin_dir_path(__FILE__) . 'class-wc-mobile-money-blocks-gateway.php';
        
        // Load the Blocks integration class
        require_once plugin_dir_path(__FILE__) . 'class-wc-mobile-money-blocks-integration.php';
        
        // Initialize the Blocks gateway
        add_filter('woocommerce_payment_gateways', array($this, 'add_blocks_gateway'));
        
        // Enqueue Blocks-specific assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_blocks_assets'));
        
        // Add Blocks-specific hooks
        add_action('woocommerce_blocks_loaded', array($this, 'setup_blocks_hooks'));
    }
    
    /**
     * Add Blocks gateway to WooCommerce
     */
    public function add_blocks_gateway($gateways) {
        $gateways[] = 'WC_MobileMoney_Blocks_Gateway';
        return $gateways;
    }
    
    /**
     * Enqueue Blocks-specific assets
     */
    public function enqueue_blocks_assets() {
        // Only enqueue on checkout or cart pages
        if (!is_checkout() && !is_cart()) {
            return;
        }
        
        // Enqueue Blocks-specific CSS
        wp_enqueue_style(
            'mobile-money-blocks-css',
            plugins_url('mobilemoney-blocks.css', __FILE__),
            array(),
            '1.0.0'
        );
        
        // Enqueue Blocks-specific JavaScript
        wp_enqueue_script(
            'mobile-money-blocks-js',
            plugins_url('mobilemoney-blocks.js', __FILE__),
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script with Blocks data
        $this->localize_blocks_script();
    }
    
    /**
     * Localize Blocks script with data
     */
    private function localize_blocks_script() {
        $blocks_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mobile_money_blocks_nonce'),
            'is_blocks' => true,
            'strings' => array(
                'select_operator' => __('Veuillez sélectionner un opérateur Mobile Money', 'woocommerce'),
                'phone_required' => __('Le numéro de téléphone est obligatoire', 'woocommerce'),
                'transaction_id_required' => __("Veuillez préciser l'ID de la transaction", 'woocommerce'),
                'instruction' => __('Instruction', 'woocommerce')
            )
        );
        
        wp_localize_script('mobile-money-blocks-js', 'mobile_money_blocks_data', $blocks_data);
    }
    
    /**
     * Setup Blocks-specific hooks
     */
    public function setup_blocks_hooks() {
        // Register Blocks payment method
        add_action('woocommerce_blocks_payment_method_type_registration', array($this, 'register_blocks_payment_method'));
        
        // Add Blocks checkout block support
        add_action('woocommerce_blocks_checkout_block_registry', array($this, 'register_checkout_block_support'));
        
        // Add Blocks cart block support
        add_action('woocommerce_blocks_cart_block_registry', array($this, 'register_cart_block_support'));
    }
    
    /**
     * Register Blocks payment method
     */
    public function register_blocks_payment_method($payment_method_registry) {
        if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            require_once plugin_dir_path(__FILE__) . 'class-wc-mobile-money-blocks-integration.php';
            $payment_method_registry->register(new WC_MobileMoney_Blocks_Integration());
        }
    }
    
    /**
     * Register checkout block support
     */
    public function register_checkout_block_support($registry) {
        // Add custom checkout block support if needed
        add_filter('woocommerce_blocks_checkout_block_registry', array($this, 'add_checkout_block_features'));
    }
    
    /**
     * Register cart block support
     */
    public function register_cart_block_support($registry) {
        // Add custom cart block support if needed
        add_filter('woocommerce_blocks_cart_block_registry', array($this, 'add_cart_block_features'));
    }
    
    /**
     * Add checkout block features
     */
    public function add_checkout_block_features($registry) {
        // Add any custom checkout block features here
        return $registry;
    }
    
    /**
     * Add cart block features
     */
    public function add_cart_block_features($registry) {
        // Add any custom cart block features here
        return $registry;
    }
    
    /**
     * Check if current page is using Blocks
     */
    public static function is_blocks_page() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Check if the post content contains Blocks
        if (has_block('woocommerce/checkout', $post) || has_block('woocommerce/cart', $post)) {
            return true;
        }
        
        // Check if we're in a Blocks context
        if (function_exists('WC') && WC()->version && version_compare(WC()->version, '8.3.0', '>=')) {
            return true;
        }
        
        return false;
    }
}

// Initialize the Blocks loader
new WC_MobileMoney_Blocks_Loader();
