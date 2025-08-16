<?php
/**
 * WooCommerce Mobile Money Blocks Gateway
 * 
 * This class provides Blocks-compatible implementation for WooCommerce 8.3+
 * It extends the legacy gateway and adds Blocks-specific functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_MobileMoney_Blocks_Gateway extends WC_Payment_Gateway {
    
    /**
     * Constructor for the gateway
     */
    public function __construct() {
        $this->id = 'wc_mmpayment_blocks';
        $this->icon = plugins_url('../mmoney-icons.png', __FILE__);
        $this->has_fields = true;
        $this->method_title = 'Mobile Money Payment (Blocks)';
        $this->method_description = 'Payez à partir de votre compte mobile money - Compatible avec les blocs WooCommerce';
        
        // Supports
        $this->supports = array(
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

        // Initialize form fields and settings
        $this->init_form_fields();
        $this->init_settings();
        
        // Set properties
        $this->title = $this->get_option('title');
        $this->icon = $this->get_option('icon_url') != "" ? $this->get_option('icon_url') : $this->icon;
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        
        // Hooks
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
        
        // Blocks support
        add_action('woocommerce_blocks_loaded', array($this, 'register_blocks_support'));
    }
    
    /**
     * Initialize form fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => 'Enable/Disable',
                'label'       => 'Enable Mobile Money Payment (Blocks)',
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default' => 'Mobile Money',
                'desc_tip'    => true,
            ),
            'icon_url' => array(
                'title'       => 'Icon URL',
                'type'        => 'text',
                'description' => "Lien de l'icone que l'utilisateur verra",
            ),
            'description' => array(
                'title'       => 'Description',
                'type'        => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default'     => 'Payez à partir de votre compte mobile money',
            ),
            'operator_1_name' => array(
                'title'       => 'Operator #1 Name',
                'type'        => 'text',
                'description' => 'Enter the name of the first mobile money operator (e.g., Wave, MTN Money)',
                'default'     => 'Wave',
                'desc_tip'    => true,
            ),
            'operator_1_phone' => array(
                'title'       => 'Operator #1 Phone Number',
                'type'        => 'text',
                'description' => 'Enter the phone number for the first operator',
                'default'     => '05000000',
                'desc_tip'    => true,
            ),
            'operator_1_instruction' => array(
                'title'       => 'Operator #1 Payment Instruction',
                'type'        => 'text',
                'description' => 'Enter the payment instruction for the first operator',
                'default'     => 'Faites un transfert à partir de l\'application',
                'desc_tip'    => true,
            ),
            'operator_2_name' => array(
                'title'       => 'Operator #2 Name',
                'type'        => 'text',
                'description' => 'Enter the name of the second mobile money operator',
                'default'     => 'MTN Money',
                'desc_tip'    => true,
            ),
            'operator_2_phone' => array(
                'title'       => 'Operator #2 Phone Number',
                'type'        => 'text',
                'description' => 'Enter the phone number for the second operator',
                'default'     => '05000000',
                'desc_tip'    => true,
            ),
            'operator_2_instruction' => array(
                'title'       => 'Operator #2 Payment Instruction',
                'type'        => 'text',
                'description' => 'Enter the payment instruction for the second operator',
                'default'     => 'Faites un transfert à partir de l\'application ou via USSD ###',
                'desc_tip'    => true,
            ),
            'operator_3_name' => array(
                'title'       => 'Operator #3 Name',
                'type'        => 'text',
                'description' => 'Enter the name of the third mobile money operator',
                'default'     => 'Orange Money',
                'desc_tip'    => true,
            ),
            'operator_3_phone' => array(
                'title'       => 'Operator #3 Phone Number',
                'type'        => 'text',
                'description' => 'Enter the phone number for the third operator',
                'default'     => '07000000',
                'desc_tip'    => true,
            ),
            'operator_3_instruction' => array(
                'title'       => 'Operator #3 Payment Instruction',
                'type'        => 'text',
                'description' => 'Enter the payment instruction for the third operator',
                'default'     => 'Faites un transfert à partir de l\'application ou via USSD ###',
                'desc_tip'    => true,
            ),
            'operator_4_name' => array(
                'title'       => 'Operator #4 Name',
                'type'        => 'text',
                'description' => 'Enter the name of the fourth mobile money operator (leave empty if not needed)',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'operator_4_phone' => array(
                'title'       => 'Operator #4 Phone Number',
                'type'        => 'text',
                'description' => 'Enter the phone number for the fourth operator',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'operator_4_instruction' => array(
                'title'       => 'Operator #4 Payment Instruction',
                'type'        => 'text',
                'description' => 'Enter the USSD code or payment instruction for the fourth operator',
                'default'     => '',
                'desc_tip'    => true,
            )
        );
    }
    
    /**
     * Get active operators data
     */
    private function get_active_operators() {
        $operators = array();
        
        for ($i = 1; $i <= 4; $i++) {
            $name = $this->get_option("operator_{$i}_name");
            $phone = $this->get_option("operator_{$i}_phone");
            $instruction = $this->get_option("operator_{$i}_instruction");
            
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
     * Payment fields for Blocks compatibility
     */
    public function payment_fields() {
        global $woocommerce;
        $active_operators = $this->get_active_operators();
        
        // For Blocks, we'll use a different approach
        if ($this->is_blocks_checkout()) {
            $this->render_blocks_payment_fields($active_operators);
        } else {
            $this->render_legacy_payment_fields($active_operators);
        }
    }
    
    /**
     * Render payment fields for Blocks checkout
     */
    private function render_blocks_payment_fields($active_operators) {
        global $woocommerce;
        
        echo '<div class="mobile-money-blocks-fields">';
        echo '<p class="form-row form-row-wide">';
        echo '<label>Veuillez éffectuer un dépôt de ' . $woocommerce->cart->get_cart_total() . ' sur l\'un des numéros ci-dessous : </label>';
        echo '<select name="mm_operator" class="mobile-money-operator-select">';
        
        foreach ($active_operators as $operator) {
            echo '<option value="' . esc_attr($operator['name']) . '" data-instruction="' . esc_attr($operator['instruction']) . '">';
            echo esc_html($operator['name']) . ' (' . esc_html($operator['phone']) . ')';
            echo '</option>';
        }
        
        echo '</select>';
        echo '</p>';
        
        echo '<p class="form-row form-row-wide">';
        echo '<label>Numéro Mobile Money <abbr class="required" title="obligatoire">*</abbr></label>';
        echo '<input type="text" class="input-text" name="mm_sender_msisdn" placeholder="Numéro ayant éffectué le dépot" value="">';
        echo '</p>';
        
        echo '<p class="form-row form-row-wide">';
        echo '<label>ID de la transaction <abbr class="required" title="obligatoire">*</abbr></label>';
        echo '<input type="text" autocomplete="off" class="input-text" name="mm_transaction_id" placeholder="Retrouvez ce ID dans le SMS de confirmation" value="">';
        echo '</p>';
        
        echo '<div class="mobile-money-instruction" style="display: none;"></div>';
        echo '</div>';
    }
    
    /**
     * Render payment fields for legacy checkout
     */
    private function render_legacy_payment_fields($active_operators) {
        global $woocommerce;
        
        echo "<fieldset>";
        echo "<p id='mm_operator_field' class='form-row form-row-wide'>";
        echo "<label>Veuillez éffectuer un dépôt de " . $woocommerce->cart->get_cart_total() . " sur l'un des numéros ci-dessous : </label>";
        echo "<select name='mm_operator' style='width: 100%;'>";
        
        foreach ($active_operators as $operator) {
            echo '<option value="' . esc_attr($operator['name']) . '">' . esc_html($operator['name']) . ' (' . esc_html($operator['phone']) . ')</option>';
        }
        
        echo '</select>';
        echo '<span id="mm_instruction"></span>';
        echo '</p>';
        
        echo '<p class="form-row form-row-wide validate-required">';
        echo '<label>Numéro Mobile Money <abbr class="required" title="obligatoire">*</abbr></label>';
        echo '<input type="text" class="input-text" name="mm_sender_msisdn" placeholder="Numéro ayant éffectué le dépot" value="">';
        echo '</p>';
        
        echo '<p class="form-row form-row-wide validate-required">';
        echo '<label>ID de la transaction <abbr class="required" title="obligatoire">*</abbr></label>';
        echo '<input type="text" autocomplete="off" class="input-text " name="mm_transaction_id" placeholder="Retrouvez ce ID dans le SMS de confirmation" value="">';
        echo '</p>';
        echo '</fieldset>';
    }
    
    /**
     * Check if current checkout is using Blocks
     */
    private function is_blocks_checkout() {
        return function_exists('WC') && 
               version_compare(WC()->version, '8.3.0', '>=') && 
               (has_block('woocommerce/checkout') || has_block('woocommerce/cart'));
    }
    
    /**
     * Payment scripts
     */
    public function payment_scripts() {
        // CSS
        wp_enqueue_style('mmpayment_style', plugins_url('../mobilemoney-payment.css', __FILE__));
        
        // JS
        wp_register_script('mmpayment_js', plugins_url('../mobilemoney-payment.js', __FILE__), array("jquery"), true);
        wp_enqueue_script('mmpayment_js');
        
        // Get active operators for JavaScript
        $active_operators = $this->get_active_operators();
        $operators_data = array();
        
        foreach ($active_operators as $operator) {
            $operators_data[$operator['name']] = $operator['instruction'];
        }
        
        wp_localize_script('mmpayment_js', 'mmpayment_data', array(
            'operators' => $operators_data,
            'is_blocks' => $this->is_blocks_checkout()
        ));
    }
    
    /**
     * Register Blocks support
     */
    public function register_blocks_support() {
        if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            // Register the Blocks integration
            add_action('woocommerce_blocks_payment_method_type_registration', array($this, 'register_blocks_integration'));
        }
    }
    
    /**
     * Register Blocks integration
     */
    public function register_blocks_integration($payment_method_registry) {
        if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            require_once plugin_dir_path(__FILE__) . 'class-wc-mobile-money-blocks-integration.php';
            $payment_method_registry->register(new WC_MobileMoney_Blocks_Integration());
        }
    }
    
    /**
     * Validate fields
     */
    public function validate_fields() {
        if (empty($_POST['mm_sender_msisdn'])) {
            wc_add_notice('Le numéro de téléphone est obligatoire !', 'error');
            return false;
        }
        
        if (empty($_POST['mm_transaction_id'])) {
            wc_add_notice("Veuillez préciser l'ID de la transaction !", 'error');
            return false;
        }
        
        return true;
    }
    
    /**
     * Process payment
     */
    public function process_payment($order_id) {
        global $woocommerce;
        $order = new WC_Order($order_id);
        
        // Save additional fields
        $order->update_meta_data('Operateur Mobile Money', sanitize_text_field($_POST['mm_operator']));
        $order->update_meta_data('Numéro Mobile Money', sanitize_text_field($_POST['mm_sender_msisdn']));
        $order->update_meta_data('ID transaction Mobile Money', sanitize_text_field($_POST['mm_transaction_id']));
        
        // Mark as on-hold (we're awaiting the cheque)
        $order->update_status('on-hold', __('En attente de confirmation.', 'woocommerce'));
        
        // Remove cart
        $woocommerce->cart->empty_cart();
        
        // Return thankyou redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }
}
