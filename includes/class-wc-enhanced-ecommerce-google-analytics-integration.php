<?php

/**
 * Enhanced Ecommerce for Woo-commerce stores
 *
 * Allows tracking code to be inserted into store pages.
 *
 * @class 		WC_Enhanced_Ecommerce_Google_Analytics
 * @extends		WC_Integration
 * @author Sudhir Mishra <sudhirxps@gmail.com>
 */

class WC_Enhanced_Ecommerce_Google_Analytics extends WC_Integration {

    /**
     * Init and hook in the integration.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->id = 'enhanced_ecommerce_google_analytics';
        $this->method_title = __('Enhanced Ecommerce Google Analytics', 'woocommerce');
        $this->method_description = __('Enhanced Ecommerce is a new feature of Universal Analytics that generates detailed statistics about the users journey from product page to thank you page on your e-store. <br/><a href="http://www.tatvic.com/blog/enhanced-ecommerce/">Know more about enhanced e-commerce.</a>', 'woocommerce');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->ga_email = $this->get_option('ga_email');
        $this->ga_id = $this->get_option('ga_id');
        $this->ga_set_domain_name = $this->get_option('ga_set_domain_name');
        $this->ga_standard_tracking_enabled = $this->get_option('ga_standard_tracking_enabled');
        $this->enable_guest_checkout = get_option('woocommerce_enable_guest_checkout') == 'yes' ? true : false;
        $this->track_login_step_for_guest_user = $this->get_option('track_login_step_for_guest_user') == 'yes' ? true : false;
        $this->ga_enhanced_ecommerce_tracking_enabled = $this->get_option('ga_enhanced_ecommerce_tracking_enabled');
        $this->ga_enhanced_ecommerce_category_page_impression_thresold = $this->get_option('ga_enhanced_ecommerce_category_page_impression_thresold');
        
        // Actions
        add_action('woocommerce_update_options_integration_enhanced_ecommerce_google_analytics', array($this, 'process_admin_options'));
        // API Call to LS with e-mail
        // Tracking code
        add_action('wp_head', array($this, 'google_tracking_code'));
        add_action('woocommerce_thankyou', array($this, 'ecommerce_tracking_code'));

        // Enhanced Ecommerce product impression hook
        add_action('woocommerce_after_shop_loop', array($this, 'cate_page_prod_impression')); // Hook for category page
        add_action('woocommerce_after_shop_loop_item', array($this, 'product_impression'));
        add_action('woocommerce_after_single_product', array($this, 'product_detail_view'));
        add_action('woocommerce_after_cart', array($this, 'remove_cart_tracking'));
        add_action('woocommerce_after_checkout_form', array($this, 'checkout_step_one_tracking'));

        // Event tracking code
        add_action('woocommerce_after_add_to_cart_button', array($this, 'add_to_cart'));
        add_action('wp_footer', array($this, 'loop_add_to_cart'));
        add_action('wp_footer', array($this, 'default_pageview'));
    }

    /**
     * Initialise Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {

        $this->form_fields = array(
            'ga_email' => array(
                'title' => __('Email Address', 'woocommerce'),
                'description' => __('Provide your work email for updates on the plugin enhancement.', 'woocommerce'),
                'type' => 'email',
                'required' => 'required',
                'default' => get_option('woocommerce_ga_email') // Backwards compat
            ),
            'ga_id' => array(
                'title' => __('Google Analytics ID', 'woocommerce'),
                'description' => __('Log into your google analytics account to find your ID. e.g. <code>UA-XXXXX-X</code>', 'woocommerce'),
                'type' => 'text',
                'default' => get_option('woocommerce_ga_id') // Backwards compat
            ),
            'ga_set_domain_name' => array(
                'title' => __('Set Domain Name', 'woocommerce'),
                'description' => sprintf(__('(Optional) Sets the <code>_setDomainName</code> variable. <a href="%s">See here for more information</a>.', 'woocommerce'), 'https://developers.google.com/analytics/devguides/collection/gajs/gaTrackingSite#multipleDomains'),
                'type' => 'text',
                'default' => ''
            ),
            'ga_standard_tracking_enabled' => array(
                'title' => __('Tracking code', 'woocommerce'),
                'label' => __('Add tracking code to your site using this plugin &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Optional).', 'woocommerce'),
                'description' => sprintf(__('You don\'t need to enable this if using a 3rd party analytics plugin. If you chose to add Universal Analytics code snippet via Third party plugins,<br/> Add <code>ga(\'require\', \'ec\', \'ec.js\');</code> below <code>ga(\'create\',\'UA-XXXXX-X\')</code> in your standard code snippet. <br/> Also ensure that the Universal Analytics code is present in the &lt;head&gt; section of the website.', 'woocommerce')),
                'type' => 'checkbox',
                'checkboxgroup' => 'start',
                'default' => get_option('woocommerce_ga_standard_tracking_enabled') ? get_option('woocommerce_ga_standard_tracking_enabled') : 'no'  // Backwards compat
            ),
            'ga_enhanced_ecommerce_tracking_enabled' => array(
                'label' => __('Enable Enhanced eCommerce tracking', 'woocommerce'),
                'type' => 'checkbox',
                'checkboxgroup' => '',
                'default' => get_option('woocommerce_ga_ecommerce_tracking_enabled') ? get_option('woocommerce_ga_ecommerce_tracking_enabled') : 'no'  // Backwards compat
            ),
            'track_login_step_for_guest_user' => array(
                'label' => __('Track Login step for Guest users if Guest Checkout is enabled &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Optional).', 'woocommerce'),
                'type' => 'checkbox',
                'checkboxgroup' => '',
                'description' => sprintf(__('For Guest users Login is not a mandatory step. Checking the box would consider the click event on place order as Login as well as Checkout.', 'woocommerce')),
                'default' => get_option('track_login_step_for_guest_user') ? get_option('track_login_step_for_guest_user') : 'no'  // Backwards compat
            ),
            'ga_enhanced_ecommerce_category_page_impression_thresold' => array(
                'title' => __('Impression Threasold', 'woocommerce'),
                'description' => sprintf(__('Impression thresold for category page. Send hit after these many number of products impressions', 'woocommerce')),
                'type' => 'input',
                'default' => '6'
            )
        );
        /* When user updates the email, post it to the remote server */
        if (isset($_GET['tab']) && isset($_REQUEST['section']) && isset($_REQUEST['woocommerce_enhanced_ecommerce_google_analytics_ga_email'])) {

            $current_tab = ( empty($_GET['tab']) ) ? false : sanitize_text_field(urldecode($_GET['tab']));
            $current_section = ( empty($_REQUEST['section']) ) ? false : sanitize_text_field(urldecode($_REQUEST['section']));

            $save_for_the_plugin = ($current_tab == "integration" ) && ($current_section == "enhanced_ecommerce_google_analytics");
            $update_made_for_email = $_REQUEST['woocommerce_enhanced_ecommerce_google_analytics_ga_email'] != $this->get_option('woocommerce_enhanced_ecommerce_google_analytics_ga_email');

            if ($save_for_the_plugin && $update_made_for_email) {
                if ($_REQUEST['woocommerce_enhanced_ecommerce_google_analytics_ga_email'] != '') {
                    $email = $_REQUEST['woocommerce_enhanced_ecommerce_google_analytics_ga_email'];
                    $this->send_email_to_tatvic($email);
                }
            }
        }
    }

// End init_form_fields()

    /**
     * Google Analytics standard tracking
     *
     * @access public
     * @return void
     */
    function google_tracking_code() {
        if (is_admin() || current_user_can('manage_options') || $this->ga_standard_tracking_enabled == "no") {
            return;
        }

        $tracking_id = $this->ga_id;

        if (!$tracking_id) {
            return;
        }


        if (!empty($this->ga_set_domain_name)) {
            $set_domain_name = esc_js($this->ga_set_domain_name);
        } else {
            $set_domain_name = 'auto';
        }

        echo "<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
                            //Plugin Version :1.0.6
			ga('create', '" . esc_js($tracking_id) . "', '" . $set_domain_name . "');
                        ga('require', 'ec', 'ec.js');
			</script>";
      
      }

    /**
     * Google Analytics eCommerce tracking
     *
     * @access public
     * @param mixed $order_id
     * @return void
     */
    function ecommerce_tracking_code($order_id) {
        global $woocommerce;

        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled) || current_user_can('manage_options') || get_post_meta($order_id, '_ga_tracked', true) == 1)
            return;

        $tracking_id = $this->ga_id;

        if (!$tracking_id)
            return;

        // Doing eCommerce tracking so unhook standard tracking from the footer
        remove_action('wp_footer', array($this, 'google_tracking_code'));

        // Get the order and output tracking code
        $order = new WC_Order($order_id);

        if (!empty($this->ga_set_domain_name)) {
            $set_domain_name = esc_js($this->ga_set_domain_name);
        } else {
            $set_domain_name = 'auto';
        }
        $code = "       (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
                        //Plugin Version :1.0.6
			ga('create', '" . esc_js($tracking_id) . "', '" . $set_domain_name . "');			
			ga('require', 'ec', 'ec.js');
                        
			
			";
        
        
        // Order items
        if ($order->get_items()) {
            foreach ($order->get_items() as $item) {
                $_product = $order->get_product_from_item($item);

                $code .= "ga('ec:addProduct', {";
                $code .= "'name': '" . esc_js($item['name']) . "',";
                $code .= "'id': '" . esc_js($_product->get_sku()) . "',";

                if (isset($_product->variation_data)) {

                    $code .= "'category': '" . esc_js(woocommerce_get_formatted_variation($_product->variation_data, true)) . "',";
                } else {
                    $out = array();
                    $categories = get_the_terms($_product->id, 'product_cat');
                    if ($categories) {
                        foreach ($categories as $category) {
                            $out[] = $category->name;
                        }
                    }
                    $code .= "'category': '" . esc_js(join("/", $out)) . "',";
                }

                $code .= "'price': '" . esc_js($order->get_item_total($item)) . "',";
                $code .= "'quantity': '" . esc_js($item['qty']) . "'";
                $code .= "});";
            }
        }

        $code .="ga('ec:setAction','purchase', {
				'id': '" . esc_js($order->get_order_number()) . "',      // Transaction ID. Required
				'affiliation': '" . esc_js(get_bloginfo('name')) . "', // Affiliation or store name
				'revenue': '" . esc_js($order->get_total()) . "',        // Grand Total
				'shipping': '" . esc_js($order->get_shipping()) . "',    // Shipping
				'tax': '" . esc_js($order->get_total_tax()) . "'         // Tax
			});";

        echo '<script type="text/javascript">' . $code . '</script>';

        update_post_meta($order_id, '_ga_tracked', 1);
    }

    /**
     * Enhanced E-commerce tracking for product impressions on category page
     *
     * @access public
     * @return void
     */
    public function cate_page_prod_impression() {
        
        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }
        $t_category="";
        if(is_search()){
            $t_category="Search Results";
        }
        global $product, $woocommerce;
        $impression_thresold = $this->ga_enhanced_ecommerce_category_page_impression_thresold;
        
        //$parameters = array();
        //$parameters['label'] = "'" . esc_js($product->get_sku() ? __('SKU:', 'woocommerce') . ' ' . $product->get_sku() : "#" . $product->id ) . "'";
        if (version_compare($woocommerce->version, '2.1', '>=')) {
            wc_enqueue_js("
            t_cnt=0;
            t_ttl_prod=jQuery('.products li').length;
            jQuery('.products li').each(function(index){
            t_cnt++;
                         ga('ec:addImpression', {
                            'id': jQuery(this).find('.ls-pro-sku').val(),
                            'name': jQuery(this).find('.ls-pro-name').val(),
                            'category': jQuery(this).find('.ls-pro-category').val(),
                            'price': jQuery(this).find('.ls-pro-price').val(),
                            'position': index+1
                        });
                   
                   if(t_ttl_prod > " .esc_js($impression_thresold)."){
                        if((t_cnt%" .esc_js($impression_thresold).")==0){
                            t_ttl_prod=t_ttl_prod-".esc_js($impression_thresold).";
                            ga('send', 'event', 'ecommerce', 'product_impression_cp', {'nonInteraction': 1});  
                        }
                     }else{
                       t_ttl_prod--;
                       if(t_ttl_prod==0){
                        ga('send', 'event', 'ecommerce', 'product_impression_cp', {'nonInteraction': 1});  
                        }
                    }
                        
                  jQuery(this).find('a:not(.add_to_cart_button)').on('click',function(){
                                                                                        
                       if('".esc_js($t_category)."'==''){    
                            t_category=jQuery(this).parents('li').find('.ls-pro-category').val();
                        }else{
                            t_category='".esc_js($t_category)."';
                            }
                         ga('ec:addProduct', {
                                    'id': jQuery(this).parents('li').find('.ls-pro-sku').val(),
                                    'name': jQuery(this).parents('li').find('.ls-pro-name').val(),
                                    'category': jQuery(this).parents('li').find('.ls-pro-category').val(),
                                    'price': jQuery(this).parents('li').find('.ls-pro-price').val(),
                                    'position': index+1
                             });
                              ga('ec:setAction', 'click', {list: t_category});
                              ga('send', 'event', 'ecommerce', 'product_click', {'nonInteraction': 1});
                              
                        
                            });
                        });
               
               ");
         
        } else {
               
            $woocommerce->add_inline_js("
            t_cnt=0;
           t_ttl_prod=jQuery('.products li').length;
           jQuery('.products li').each(function(index){
           t_cnt++;
                          ga('ec:addImpression', {
                            'id': jQuery(this).find('.ls-pro-sku').val(),
                            'name': jQuery(this).find('.ls-pro-name').val(),
                            'category': jQuery(this).find('.ls-pro-category').val(),
                            'price': jQuery(this).find('.ls-pro-price').val(),
                            'position': index+1
                        });
                   
                    if(t_ttl_prod > " .esc_js($impression_thresold)."){
                        if((t_cnt%" .esc_js($impression_thresold).")==0){
                            t_ttl_prod=t_ttl_prod-".esc_js($impression_thresold).";
                            ga('send', 'event', 'ecommerce', 'product_impression_cp', {'nonInteraction': 1});  
                        }
                     }else{
                       t_ttl_prod--;
                       if(t_ttl_prod==0){
                        ga('send', 'event', 'ecommerce', 'product_impression_cp', {'nonInteraction': 1});  
                        }
                    }
                              
                  jQuery(this).find('a:not(.add_to_cart_button)').on('click',function(){
                                                      
                       if('".esc_js($t_category)."'==''){    
                            t_category=jQuery(this).parents('li').find('.ls-pro-category').val();
                        }else{
                            t_category='".esc_js($t_category)."';
                            }
                        
                         ga('ec:addProduct', {
                                    'id': jQuery(this).parents('li').find('.ls-pro-sku').val(),
                                    'name': jQuery(this).parents('li').find('.ls-pro-name').val(),
                                    'category': jQuery(this).parents('li').find('.ls-pro-category').val(),
                                    'price': jQuery(this).parents('li').find('.ls-pro-price').val(),
                                    'position': index+1
                             });
                              ga('ec:setAction', 'click', {list: t_category});
                              ga('send', 'event', 'ecommerce', 'product_click', {'nonInteraction': 1});
                              
                        
                            });
                        });
                  
              ");
        }
    }

    /**
     * Enhanced E-commerce tracking for single product add to cart
     *
     * @access public
     * @return void
     */
    function add_to_cart() {

        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled))
            return;

        if (!is_single())
            return;
        
        global $product, $woocommerce;

        $parameters = array();

        // Add single quotes to allow jQuery to be substituted into _trackEvent parameters       
        $parameters['label'] = "'" . esc_js($product->get_sku() ? __('SKU:', 'woocommerce') . ' ' . $product->get_sku() : "#" . $product->id ) . "'";

        if (version_compare($woocommerce->version, '2.1', '>=')) {
            wc_enqueue_js("
			$('.single_add_to_cart_button').click(function() {
                            
                              // Enhanced E-commerce Add to cart clicks 
                              ga('ec:addProduct', {
                                'id': '" . esc_js($product->get_sku()) . "',
                                'name': '" . esc_js($product->get_title()) . "',                                
                                'price': '" . esc_js($product->get_price()) . "',
                              });
                              ga('ec:setAction', 'add');
                              ga('send', 'event', 'Enhanced-Ecommerce', 'add-to-cart-click', " . $parameters['label'] . ",{'nonInteraction': 1});                              
			});
		");
        } else {
            $woocommerce->add_inline_js("
			$('.single_add_to_cart_button').click(function() {
                            
                              // Enhanced E-commerce Add to cart clicks 
                              ga('ec:addProduct', {
                                'id': '" . esc_js($product->get_sku()) . "',
                                'name': '" . esc_js($product->get_title()) . "',                                
                                'price': '" . esc_js($product->get_price()) . "',
                              });
                              ga('ec:setAction', 'add');
                              ga('send', 'event', 'Enhanced-Ecommerce', 'add-to-cart-click', " . $parameters['label'] . ",{'nonInteraction': 1});                              
			});
		");
        }
    }

    /**
     * Enhanced E-commerce tracking for loop add to cart
     *
     * @access public
     * @return void
     */
    function loop_add_to_cart() {

        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }
        global $woocommerce;

        $parameters = array();
        // Add single quotes to allow jQuery to be substituted into _trackEvent parameters
        $parameters['category'] = "'" . __('Products', 'woocommerce') . "'";
        $parameters['action'] = "'" . __('Add to Cart', 'woocommerce') . "'";
        $parameters['label'] = "($(this).data('product_sku')) ? ('SKU: ' + $(this).data('product_sku')) : ('#' + $(this).data('product_id'))"; // Product SKU or ID

        if (version_compare($woocommerce->version, '2.1', '>=')) {

            wc_enqueue_js("
                $('.add_to_cart_button:not(.product_type_variable, .product_type_grouped)').click(function() {
                                                       
                              // Enhanced E-commerce Add to cart clicks 
                              ga('ec:addProduct', {
                                'id': $(this).parents('li').find('.ls-pro-sku').val(),
                                'name': $(this).parents('li').find('.ls-pro-name').val(),
                                'category': $(this).parents('li').find('.ls-pro-category').val(),
                                'price': $(this).parents('li').find('.ls-pro-price').val(),
                              });
                              ga('ec:setAction', 'add');
                              ga('send', 'event', 'Enhanced-Ecommerce', 'add-to-cart-click', " . $parameters['label'] . ",{'nonInteraction': 1});                              
			});
		");
        } else {
            $woocommerce->add_inline_js("
			$('.add_to_cart_button:not(.product_type_variable, .product_type_grouped)').click(function() {
                                                   
                              // Enhanced E-commerce Add to cart clicks 
                              ga('ec:addProduct', {
                                'id': $(this).parents('li').find('.ls-pro-sku').val(),
                                'name': $(this).parents('li').find('.ls-pro-name').val(),
                                'category': $(this).parents('li').find('.ls-pro-category').val(),
                                'price': $(this).parents('li').find('.ls-pro-price').val(),
                              });
                              ga('ec:setAction', 'add');
                              ga('send', 'event', 'Enhanced-Ecommerce', 'add-to-cart-click', " . $parameters['label'] . ",{'nonInteraction': 1});                              
			});
		");
        }
    }

    /**
     * Enhanced E-commerce tracking for product detail view
     *
     * @access public
     * @return void
     */
    public function product_detail_view() {
        
        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }

        global $product;
        global $woocommerce;
        $category = get_the_terms($product->ID, 'product_cat');
        $categories = '';
        foreach ($category as $term) {
            $categories.=$term->name . ',';
        }
        if (version_compare($woocommerce->version, '2.1', '>=')) {

            wc_enqueue_js("ga('ec:addImpression', {
            'id': '" . $product->get_sku() . "',                   // Product details are provided in an impressionFieldObject.
            'name': '" . $product->get_title() . "',
            'category': '" . $categories . "',
          });
          ga('ec:setAction', 'detail');
        ");
        } else {
            $woocommerce->add_inline_js("ga('ec:addImpression', {
            'id': '" . $product->get_sku() . "',                   // Product details are provided in an impressionFieldObject.
            'name': '" . $product->get_title() . "',
            'category': '" . $categories . "',
          });
          ga('ec:setAction', 'detail');
        ");
        }
    }

    /**
     * Enhanced E-commerce tracking for product impressions on category pages
     *
     * @access public
     * @return void
     */
    public function product_impression() {

        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }

        if (is_single()) {
            return;
        }

        global $product;

        $category = get_the_terms($product->ID, 'product_cat');
        $categories = '';
        foreach ($category as $term) {
            $categories.=$term->name . ',';
        }
        //remove last comma(,) if multiple categories are there
        $categories = rtrim($categories, ",");

        echo "<input type='hidden' class='ls-pro-price' value='" . esc_html($product->get_price()) . "'/>"
        . "<input type='hidden' class='ls-pro-sku' value='" . $product->get_sku() . "'/>"
        . "<input type='hidden' class='ls-pro-name' value='" . $product->get_title() . "'/>"
        . "<input type='hidden' class='ls-pro-category' value='" . $categories . "'/>";
    }

    /**
     * Enhanced E-commerce tracking for remove from cart
     *
     * @access public
     * @return void
     */
    public function remove_cart_tracking() {
        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }
        global $woocommerce;
        if (version_compare($woocommerce->version, '2.1', '>=')) {
            wc_enqueue_js("$('.remove').click(function(){
            ga('ec:addProduct', {                
                'name': $(this).parents('tr').find('.product-name').text(),
                'price': $(this).parents('tr').find('.product-price').text(),
                'quantity': $(this).parents('tr').find('.product-quantity .qty').val()
              });
              ga('ec:setAction', 'remove');
              ga('send', 'event', 'Enhanced-Ecommerce', 'click', 'remove from cart',{'nonInteraction': 1});
              });"
            );
        } else {
            $woocommerce->add_inline_js("$('.remove').click(function(){
            ga('ec:addProduct', {                
                'name': $(this).parents('tr').find('.product-name').text(),
                'price': $(this).parents('tr').find('.product-price').text(),
                'quantity': $(this).parents('tr').find('.product-quantity .qty').val()
              });
              ga('ec:setAction', 'remove');
              ga('send', 'event', 'Enhanced-Ecommerce', 'click', 'remove from cart',{'nonInteraction': 1});
              });"
            );
        }
    }

    /**
     * Enhanced E-commerce tracking checkout steps
     *
     * @access public
     * @return void
     */
    public function checkout_step_one_tracking() {
        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }

        global $woocommerce;

        foreach ($woocommerce->cart->cart_contents as $item) {
            $p = get_product($item['product_id']);

            $category = get_the_terms($item['product_id'], 'product_cat');
            $categories = '';
            foreach ($category as $term) {
                $categories.=$term->name . ',';
            }

            $code = "ga('ec:addProduct', {" . "'id': '" . esc_js($p->get_sku()) . "',";
            $code .= "'name': '" . esc_js($p->get_title()) . "',";
            $code .= "'category': '" . esc_js($categories) . "',";
            $code .= "'price': '" . esc_js($p->get_price()) . "',";
            $code .= "'quantity': '" . esc_js($item['quantity']) . "'" . "});";
        }

        $code_step_1 = $code . "ga('ec:setAction','checkout',{'step': 1});";
        $code_step_1 .= "ga('send', 'event', 'Enhanced-Ecommerce', 'pageview', 'footer',{'nonInteraction': 1});";
        if (version_compare($woocommerce->version, '2.1', '>=')) {
            wc_enqueue_js($code_step_1);
        } else {
            $woocommerce->add_inline_js($code_step_1);
        }


        $code_step_2 = $code . "ga('ec:setAction','checkout',{'step': 2});";
        $code_step_2 .= "ga('send', 'event', 'Enhanced-Ecommerce', 'pageview', 'footer',{'nonInteraction': 1});";

        if (is_user_logged_in()) {
            if (version_compare($woocommerce->version, '2.1', '>=')) {
                wc_enqueue_js($code_step_2);
            } else {
                $woocommerce->add_inline_js($code_step_2);
            }
        }
        $step_2_on_proceed_to_pay = (!is_user_logged_in() && !$this->enable_guest_checkout ) || (!is_user_logged_in() && $this->enable_guest_checkout && $this->track_login_step_for_guest_user);

        $code_step_3 = $code . "ga('ec:setAction','checkout',{'step': 3});";
        $code_step_3 .= "ga('send', 'event', 'Enhanced-Ecommerce', 'pageview', 'footer',{'nonInteraction': 1});";

        $inline_js = "jQuery(document).on('click','#place_order',function(e){";
        if ($step_2_on_proceed_to_pay) {
            $inline_js .= $code_step_2;
        }
        $inline_js .= $code_step_3;
        $inline_js .= "});";
        if (version_compare($woocommerce->version, '2.1', '>=')) {
            wc_enqueue_js($inline_js);
        } else {
            $woocommerce->add_inline_js($inline_js);
        }
    }

    /**
     * Sending hits with event
     *
     * @access public
     * @return void
     */
    public function default_pageview() {
              
        global $woocommerce;
        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }

        if (!$this->disable_tracking($this->ga_standard_tracking_enabled)) {
            $inline_js = "ga('send', 'event', 'Enhanced-Ecommerce', 'pageview', 'footer',{'nonInteraction': 1})";
        } else {
            $inline_js = "ga('send', 'event', 'Enhanced-Ecommerce', 'pageview', 'footer',{'nonInteraction': 1});";
        }

        if (version_compare($woocommerce->version, '2.1', '>=')) {
            wc_enqueue_js($inline_js);
        } else {
            $woocommerce->add_inline_js($inline_js);
        }
    }

    /**
     * Check if tracking is disabled
     *
     * @access private
     * @param mixed $type
     * @return bool
     */
    private function disable_tracking($type) {
        if (is_admin() || current_user_can('manage_options') || (!$this->ga_id ) || 'no' == $type) {
            return true;
        }
    }

    /**
     * Sending email to remote server
     *
     * @access public
     * @return void
     */
    public function send_email_to_tatvic($email) {
        //set POST variables
        $url = 'http://dev.tatvic.com/leadgen/woocommerce-plugin/store_email/';
        $fields = array(
            'email' => urlencode($email),
        );
        wp_remote_post($url, array(
            'method' => 'POST',
            'timeout' => 1,
            'httpversion' => '1.0',
            'blocking' => false,
            'headers' => array(),
            'body' => $fields
                )
        );
    }

}
?>