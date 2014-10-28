<?php

/**
 * Enhanced Ecommerce for Woo-commerce stores
 *
 * Allows tracking code to be inserted into store pages.
 *
 * @class 		WC_Enhanced_Ecommerce_Google_Analytics
 * @extends		WC_Integration
 * @author              Sudhir Mishra <sudhir@tatvic.com>
 * @contributor         Jigar Navadiya <jigar@tatvic.com>
 */
class WC_Enhanced_Ecommerce_Google_Analytics extends WC_Integration {

    /**
     * Init and hook in the integration.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        global $homepage_json_fp, $homepage_json_rp;
        $this->id = "enhanced_ecommerce_google_analytics";
        $this->method_title = __("Enhanced Ecommerce Google Analytics", "woocommerce");
        $this->method_description = __("Enhanced Ecommerce is a new feature of Universal Analytics that generates detailed statistics about the users journey from product page to thank you page on your e-store. <br/><a href='http://www.tatvic.com/blog/enhanced-ecommerce/' target='_blank'>Know more about Enhanced Ecommerce.</a>", "woocommerce");

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables -- check for where to read settings
            $this->ga_email = $this->get_option("ga_email");
            $this->ga_id = $this->get_option("ga_id");
            $this->ga_set_domain_name = $this->get_option("ga_set_domain_name");
            $this->ga_local_curr = $this->get_option("ga_local_curr");
            $this->ga_standard_tracking_enabled = $this->get_option("ga_standard_tracking_enabled");
            $this->enable_guest_checkout = get_option("woocommerce_enable_guest_checkout") == "yes" ? true : false; //guest checkout
            $this->track_login_step_for_guest_user = $this->get_option("track_login_step_for_guest_user") == "yes" ? true : false; //guest checkout
            $this->ga_enhanced_ecommerce_tracking_enabled = $this->get_option("ga_enhanced_ecommerce_tracking_enabled");
            $this->ga_display_feature_plugin = $this->get_option("ga_display_feature_plugin") == "yes" ? true : false;
            $this->ga_enhanced_ecommerce_category_page_impression_threshold = $this->get_option("ga_enhanced_ecommerce_category_page_impression_threshold");

        // Actions
        add_action("woocommerce_update_options_integration_".$this->id, array($this, "process_admin_options"));
        // API Call to LS with e-mail
        // Tracking code
        add_action("wp_head", array($this, "google_tracking_code"));
        add_action("woocommerce_thankyou", array($this, "ecommerce_tracking_code"));

        // Enhanced Ecommerce product impression hook
        add_action("wp_footer", array($this, "homepage_impression"));
        add_action("wp_footer", array($this, "cate_page_prod_impression")); // Hook for category page
        add_action("woocommerce_after_shop_loop_item", array($this, "bind_product_metadata"));
        add_action("woocommerce_after_single_product", array($this, "product_detail_view"));
        add_action("woocommerce_after_cart", array($this, "remove_cart_tracking"));
        add_action("woocommerce_before_checkout_billing_form", array($this, "checkout_step_1_tracking"));
        add_action("woocommerce_after_checkout_billing_form", array($this, "checkout_step_2_tracking"));
        add_action("woocommerce_after_checkout_billing_form", array($this, "checkout_step_3_tracking"));

        // Event tracking code
        add_action("woocommerce_after_add_to_cart_button", array($this, "add_to_cart"));
        add_action("woocommerce_before_shop_loop_item", array($this, "add_divwrap_before_product"));
        add_action("woocommerce_after_shop_loop_item", array($this, "add_divwrap_after_product"));
        add_action("wp_footer", array($this, "loop_add_to_cart"));
        
		//Enable display feature code checkbox 
        add_action("admin_footer", array($this, "admin_check_UA_enabled"));

        //add version details in footer
        add_action("wp_footer", array($this, "add_plugin_details"));
		
		//check if plugin is deactivated or not
        add_action("deactivated_plugin", array($this, "detect_plugin_deactivation"));
        
        //Add Dev ID
        add_action("wp_head", array($this, "add_dev_id"), 1);
    }

     /**
     * add dev id
     *
     * @access public
     * @return void
     */
    function add_dev_id() {
        echo "<script>(window.gaDevIds=window.gaDevIds||[]).push('5CDcaG');</script>";
    }

    /**
     * add custom div before product shop loop
     *
     * @access public
     * @return void
     */
    function add_divwrap_before_product() {
        //add div tag before every product data - Restricted page : Home page
        if (!is_home()) {
            echo "<div class=t_singleproduct_cont>";
        }
    }

    /**
     * add custom div after product shop loop
     *
     * @access public
     * @return void
     */
    function add_divwrap_after_product() {
        //add div tag before every product data - Restricted page : Home page
        if (!is_home()) {
            echo "</div>";
        }
    }

    /**
     * display details of plugin
     *
     * @access public
     * @return void
     */
    function add_plugin_details() {
        echo '<!--Enhanced Ecommerce Google Analytics Plugin for Woocommerce by Tatvic.'
        . 'Plugin Version: 1.0.11-->';
    }

    /**
     * Initialise Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {
        $ga_currency_code = array("USD" => "USD", "AED" => "AED", "ARS" => "ARS", "AUD" => "AUD", "BGN" => "BGN", "BOB" => "BOB", "BRL" => "BRL", "CAD" => "CAD", "CHF" => "CHF", "CLP" => "CLP", "CNY" => "CNY", "COP" => "COP", "CZK" => "CZK", "DKK" => "DKK", "EGP" => "EGP", "EUR" => "EUR", "FRF" => "FRF", "GBP" => "GBP", "HKD" => "HKD", "HRK" => "HRK", "HUF" => "HUF", "IDR" => "IDR", "ILS" => "ILS", "INR" => "INR", "JPY" => "JPY", "KRW" => "KRW", "LTL" => "LTL", "MAD" => "MAD", "MXN" => "MXN", "MYR" => "MYR", "NOK" => "NOK", "NZD" => "NZD", "PEN" => "PEN", "PHP" => "PHP", "PKR" => "PKR", "PLN" => "PLN", "RON" => "RON", "RSD" => "RSD", "RUB" => "RUB", "SAR" => "SAR", "SEK" => "SEK", "SGD" => "SGD", "THB" => "THB", "TRY" => "TRY", "TWD" => "TWD", "UAH" => "UAH", "VEF" => "VEF", "VND" => "VND", "ZAR" => "ZAR");
        $this->form_fields = array(
            "ga_email" => array(
                "title" => __("Email Address", "woocommerce"),
                "description" => __("Provide your work email address to receive plugin enhancement updates", "woocommerce"),
                "type" => "email",
                "placeholder" => "example@test.com",
                'custom_attributes' => array(
                    'required' => "required",
                ),
                "default" => get_option("woocommerce_ga_email") // Backwards compat
            ),
            "ga_id" => array(
                "title" => __("Google Analytics ID", "woocommerce"),
                "description" => __("Enter your Google Analytics ID here. You can login into your Google Analytics account to find your ID. e.g.<code>UA-XXXXX-X</code>", "woocommerce"),
                "type" => "text",
                "placeholder" => "UA-XXXXX-X",
                "default" => get_option("woocommerce_ga_id") // Backwards compat
            ),
            "ga_set_domain_name" => array(
                "title" => __("Set Domain Name", "woocommerce"),
                "description" => sprintf(__("Enter your domain name here (Optional)")),
                "type" => "text",
                "placeholder" => "",
                "default" => get_option("woocommerce_ga_set_domain_name")
            ),
            "ga_local_curr" => array(
                "title" => __("Set Currency", "woocommerce"),
                "description" => __("Find your Local Currency Code by visiting this <a href='https://developers.google.com/analytics/devguides/platform/currencies#supported-currencies' target='_blank'>link</a>", "woocommerce"),
                "type" => "select",
                "required" => "required",
                "default" => "USD", // Backwards compat
                "options" => $ga_currency_code
            ),
            "ga_standard_tracking_enabled" => array(
                "title" => __("Tracking code", "woocommerce"),
                "label" => __("Add Universal Analytics Tracking Code (Optional)", "woocommerce"),
                "description" => sprintf(__("This feature adds Universal Analytics Tracking Code to your Store. You don't need to enable this if using a 3rd party analytics plugin. If you chose to add Universal Analytics code snippet via Third party plugins, add <code>ga(\"require\", \"ec\", \"ec.js\");</code> below <code>ga(\"create\",\"UA-XXXXX-X\")</code> in your standard code snippet. Also ensure that the Universal Analytics code is present in the &lt;head&gt; section of the website.", "woocommerce")),
                "type" => "checkbox",
                "checkboxgroup" => "start",
                "default" => get_option("woocommerce_ga_standard_tracking_enabled") ? get_option("woocommerce_ga_standard_tracking_enabled") : "no"  // Backwards compat
            ),
            "ga_display_feature_plugin" => array(
                "label" => __("Add Display Advertising Feature Code (Optional)", "woocommerce"),
                "type" => "checkbox",
                "checkboxgroup" => "",
                "description" => sprintf(__("This feature enables remarketing with Google Analytics & Demographic reports. Adding the code is the first step in a 3 step process. <a href='https://support.google.com/analytics/answer/2819948?hl=en' target='_blank'>Learn More</a><br/>This feature can only be enabled if you have enabled UA Tracking from our Plugin. If not, you can still manually add the display advertising code by following the instructions from this <a href='https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features' target='_blank'>link</a>", "woocommerce")),
                "default" => get_option("woocommerce_ga_display_feature_plugin") ? get_option("woocommerce_ga_display_feature_plugin") : "no"  // Backwards compat
            ),
            "ga_enhanced_ecommerce_tracking_enabled" => array(
                "label" => __("Add Enhanced Ecommerce Tracking Code", "woocommerce"),
                "type" => "checkbox",
                "checkboxgroup" => "",
                "description" => sprintf(__("This feature adds Enhanced Ecommerce Tracking Code to your Store", "woocommerce")),
                "default" => get_option("woocommerce_ga_ecommerce_tracking_enabled") ? get_option("woocommerce_ga_ecommerce_tracking_enabled") : "no"  // Backwards compat
            ),
            "track_login_step_for_guest_user" => array(
                "label" => __("Add Code to Track the Login Step of Guest Users (Optional)", "woocommerce"),
                "type" => "checkbox",
                "checkboxgroup" => "",
                "description" => sprintf(__("If you have Guest Check out enable, we recommend you to add this code", "woocommerce")),
                "default" => get_option("track_login_step_for_guest_user") ? get_option("track_login_step_for_guest_user") : "no"  // Backwards compat
            ),
            "ga_enhanced_ecommerce_category_page_impression_threshold" => array(
                "title" => __("Impression Threshold", "woocommerce"),
                "description" => sprintf(__("This feature sets Impression threshold for category page. It sends hit after these many numbers of products impressions", "woocommerce")),
                "type" => "input",
                "default" => "6"
            )
        );
        /* When user updates the email, post it to the remote server */
        if (isset($_GET["tab"]) && isset($_REQUEST["section"]) && isset($_REQUEST["woocommerce_enhanced_ecommerce_google_analytics_ga_email"])) {

            $current_tab = ( empty($_GET["tab"]) ) ? false : sanitize_text_field(urldecode($_GET["tab"]));
            $current_section = ( empty($_REQUEST["section"]) ) ? false : sanitize_text_field(urldecode($_REQUEST["section"]));

            $save_for_the_plugin = ($current_tab == "integration" ) && ($current_section == "enhanced_ecommerce_google_analytics");
            $update_made_for_email = $_REQUEST["woocommerce_enhanced_ecommerce_google_analytics_ga_email"] != $this->get_option("woocommerce_enhanced_ecommerce_google_analytics_ga_email");

            if ($save_for_the_plugin && $update_made_for_email) {
                if ($_REQUEST["woocommerce_enhanced_ecommerce_google_analytics_ga_email"] != "") {
                    $email = $_REQUEST["woocommerce_enhanced_ecommerce_google_analytics_ga_email"];
                    setcookie("t_store_email_id",$email, 3600 * 1000 * 24 * 365 * 10);
                    $domain_name = get_site_url();//$_REQUEST["woocommerce_enhanced_ecommerce_google_analytics_ga_set_domain_name"];
                    $this->send_email_to_tatvic($email, $domain_name,'active');
                }
            }
        }
    }

    /**
     * Notify server that plugin is deactivated
     *
     * @access public
     * @return void
     */
    function detect_plugin_deactivation() {
        $email_id=$_COOKIE['t_store_email_id'];
        $domain_name = get_site_url();
        $this->send_email_to_tatvic($email_id, $domain_name,'deactivate');
    }

    /**
     * Google Analytics standard tracking
     *
     * @access public
     * @return void
     */
    function google_tracking_code() {
        global $woocommerce;

        //common validation----start
        if (is_admin() || current_user_can("manage_options") || $this->ga_standard_tracking_enabled == "no") {
            return;
        }

        $tracking_id = $this->ga_id;

        if (!$tracking_id) {
            return;
        }

        //common validation----end

        if (!empty($this->ga_set_domain_name)) {
            $set_domain_name = esc_js($this->ga_set_domain_name);
        } else {
            $set_domain_name = "auto";
        }

        //add display features
        if ($this->ga_display_feature_plugin) {
            $ga_display_feature_code = 'ga("require", "displayfeatures");';
        } else {
            $ga_display_feature_code = "";
        }

        $code = '        
(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,"script","//www.google-analytics.com/analytics.js","ga");
                        ga("create", "' . esc_js($tracking_id) . '", "' . $set_domain_name . '");
                        ' . $ga_display_feature_code . '
                        ga("require", "ec", "ec.js");
                        ga("send", "pageview");';

        //include this on all pages except order confirmation page.
        if (!is_order_received_page()) {
            echo "<script>" . $code . "</script>";
        }
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

        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled) || current_user_can("manage_options") || get_post_meta($order_id, "_ga_tracked", true) == 1)
            return;

        $tracking_id = $this->ga_id;

        if (!$tracking_id)
            return;

        // Doing eCommerce tracking so unhook standard tracking from the footer
        remove_action("wp_footer", array($this, "google_tracking_code"));

        // Get the order and output tracking code
        $order = new WC_Order($order_id);
        //Get Applied Coupon Codes
        if ($order->get_used_coupons()) {
            $coupons_count = count($order->get_used_coupons());
            $i = 1;
            $coupons_list = '';
            foreach ($order->get_used_coupons() as $coupon) {
                $coupons_list .= $coupon;
                if ($i < $coupons_count)
                    $coupons_list .= ', ';
                $i++;
            }
        }

        //get domain name if value is set
        if (!empty($this->ga_set_domain_name)) {
            $set_domain_name = esc_js($this->ga_set_domain_name);
        } else {
            $set_domain_name = "auto";
        }

        //add display features
        if ($this->ga_display_feature_plugin) {
            $ga_display_feature_code = 'ga("require", "displayfeatures");';
        } else {
            $ga_display_feature_code = "";
        }

        //add Pageview on order page if user checked Add Standard UA code
        if ($this->ga_standard_tracking_enabled) {
            $ga_pageview = 'ga("send", "pageview");';
        } else {
            $ga_pageview = "";
        }

        $code = '(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,"script","//www.google-analytics.com/analytics.js","ga");
                        
			ga("create", "' . esc_js($tracking_id) . '", "' . $set_domain_name . '");
                        ' . $ga_display_feature_code . '
			ga("require", "ec", "ec.js");
                        ' . $ga_pageview . '
                        ';
        // Order items
        if ($order->get_items()) {
            foreach ($order->get_items() as $item) {
                $_product = $order->get_product_from_item($item);

                //set local currencies
                $code .= 'ga("set", "&cu", "' . $this->ga_local_curr . '");';
                $code .= 'ga("ec:addProduct", {';
                $code .= '"id":  "' . esc_js($_product->get_sku() ? $_product->get_sku() : $_product->id) . '",';
                $code .= '"name": "' . esc_js($item["name"]) . '",';

                if (isset($_product->variation_data)) {

                    $code .= '"category": "' . esc_js(woocommerce_get_formatted_variation($_product->variation_data, true)) . '",';
                } else {
                    $out = array();
                    $categories = get_the_terms($_product->id, "product_cat");
                    if ($categories) {
                        foreach ($categories as $category) {
                            $out[] = $category->name;
                        }
                    }
                    $code .= '"category": "' . esc_js(join(",", $out)) . '",';
                }

                $code .= '"price": "' . esc_js($order->get_item_total($item)) . '",';
                $code .= '"quantity": "' . esc_js($item["qty"]) . '"';
                $code .= "});";
            }
        }

        $code .='ga("ec:setAction","purchase", {
				"id": "' . esc_js($order->get_order_number()) . '",      // Transaction ID. Required
				"affiliation": "' . esc_js(get_bloginfo("name")) . '", // Affiliation or store name
				"revenue": "' . esc_js($order->get_total()) . '",        // Grand Total
                                "tax": "' . esc_js($order->get_total_tax()) . '",        // Tax
				"shipping": "' . esc_js($order->get_shipping()) . '",    // Shipping
                                "coupon":"' . $coupons_list . '"    
			});
                        
        ga("send", "event", "Enhanced-Ecommerce","load", "order_confirmation", {"nonInteraction": 1});      
    ';

        //check woocommerce version
        $this->wc_version_compare($code);
        update_post_meta($order_id, "_ga_tracked", 1);
    }

    /**
     * Enhanced E-commerce tracking for product impressions on category page
     *
     * @access public
     * @return void
     */
    public function cate_page_prod_impression() {
        global $product;
        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }

        //Identify List of product listing page wise : 
        //Included Pages: Home page ,Category Page , Search page

        $t_list = "";
        $t_list_clk = "";
        $t_load_action = "";
        $t_click_action = "";

        if (is_search()) {
            $t_list_clk = '"list":"Search Results"';
            $t_list = $t_list_clk . ',';
            $t_load_action = "product_impression_srch";
            $t_click_action = "product_click_srch";
        } else if (is_product_category()) {
            $t_list_clk = '"list":"Category Page"';
            $t_list = $t_list_clk . ',';
            $t_load_action = "product_impression_cp";
            $t_click_action = "product_click_cp";
        } else if (is_product()) {
            //Considered only Related Products
            $t_list_clk = '"list":"Related Products"';
            $t_list = $t_list_clk . ',';
            $t_load_action = "product_impression_rdp";
            $t_click_action = "product_click_rdp";
        } else if (is_shop()) {
            //Considered only Related Products
            $t_list_clk = '"list":"Shop Page"';
            $t_list = $t_list_clk. ',';
            $t_load_action = "product_impression_sp";
            $t_click_action = "product_click_sp";
        }

        $impression_threshold = $this->ga_enhanced_ecommerce_category_page_impression_threshold;

        $code = 't_cnt=0;
               t_ttl_prod=jQuery(".ls-pro-sku").length;
               jQuery(".t_singleproduct_cont").each(function(index){
                            t_cnt++;
                            ga("ec:addImpression", {
                            "id": jQuery(this).find(".ls-pro-sku").val(),
                            "name": jQuery(this).find(".ls-pro-name").val(),
                            "category": jQuery(this).find(".ls-pro-category").val(),
                            ' . $t_list . '
                            "price": jQuery(this).find(".ls-pro-price").val(),
                            "position": index+1
                        });
                   
                   if(t_ttl_prod > ' . esc_js($impression_threshold) . '){
                        if((t_cnt%' . esc_js($impression_threshold) . ')==0){
                            t_ttl_prod=t_ttl_prod-' . esc_js($impression_threshold) . ';
                            ga("send", "event", "Enhanced-Ecommerce","load","' . $t_load_action . '", {"nonInteraction": 1});  
                        }
                     }else{
                       t_ttl_prod--;
                       if(t_ttl_prod==0){
                        ga("send", "event", "Enhanced-Ecommerce","load", "' . $t_load_action . '", {"nonInteraction": 1});  
                        }
                    }
                        
                  jQuery(this).find("a:not(.add_to_cart_button)").on("click",function(){
                                                                                     
                         ga("ec:addProduct", {
                                    "id": jQuery(this).parents("li").find(".ls-pro-sku").val(),
                                    "name": jQuery(this).parents("li").find(".ls-pro-name").val(),
                                    "category": jQuery(this).parents("li").find(".ls-pro-category").val(),
                                    "price": jQuery(this).parents("li").find(".ls-pro-price").val(),
                                    "position": index+1
                             });
                              ga("ec:setAction", "click", {' . $t_list_clk . '});
                              ga("send", "event", "Enhanced-Ecommerce","click","' . $t_click_action . '", {"nonInteraction": 1});
                              
                        
                            });
                        });
               ';

        if (!is_home()) {
            //check woocommerce version
            $this->wc_version_compare($code);
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
        //return if not product page       
        if (!is_single())
            return;
        global $product;
        $category = get_the_terms($product->ID, "product_cat");
        $categories = "";
        if ($category) {
            foreach ($category as $term) {
                $categories.=$term->name . ",";
            }
        }
        //remove last comma(,) if multiple categories are there
        $categories = rtrim($categories, ",");

        $code = '$(".single_add_to_cart_button").click(function() {
                            
                              // Enhanced E-commerce Add to cart clicks 
                              ga("ec:addProduct", {
                                "id" : "' . esc_js($product->get_sku() ? $product->get_sku() : $product->id ) . '",
                                "name": "' . esc_js($product->get_title()) . '",
                                "category" :"' . $categories . '",   
                                "price": "' . esc_js($product->get_price()) . '",
                                "quantity" :jQuery(this).parent().find("input[name=quantity]").val()
                              });
                              ga("ec:setAction", "add");
                              ga("send", "event", "Enhanced-Ecommerce","click", "add_to_cart_click", {"nonInteraction": 1});                              
			});
		';
        //check woocommerce version
        $this->wc_version_compare($code);
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

        $code = '$(".add_to_cart_button:not(.product_type_variable, .product_type_grouped)").click(function() {
                          
                t_qty=$(this).parent().find("input[name=quantity]").val();
                //default quantity 1 if quantity box is not there             
                if(t_qty=="" || t_qty===undefined){
                    t_qty="1";
                }
                              // Enhanced E-commerce Add to cart clicks 
                              ga("ec:addProduct", {
                                "id": $(this).parents("li").find(".ls-pro-sku").val(),
                                "name": $(this).parents("li").find(".ls-pro-name").val(),
                                "category": $(this).parents("li").find(".ls-pro-category").val(),
                                "price": $(this).parents("li").find(".ls-pro-price").val(),
                                "quantity" :t_qty
                              });
                              ga("ec:setAction", "add");
                              ga("send", "event", "Enhanced-Ecommerce","click", "add_to_cart_click",{"nonInteraction": 1});                              
			});
		';
        //check woocommerce version
        $this->wc_version_compare($code);
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
        $category = get_the_terms($product->ID, "product_cat");
        $categories = "";
        if ($category) {
            foreach ($category as $term) {
                $categories.=$term->name . ",";
            }
        }
        //remove last comma(,) if multiple categories are there
        $categories = rtrim($categories, ",");

        $code = 'ga("ec:addProduct", {
            "id": "' . esc_js($product->get_sku() ? $product->get_sku() : $product->id) . '",                   // Product details are provided in an impressionFieldObject.
            "name": "' . $product->get_title() . '",
            "category": "' . $categories . '",
          });
          ga("ec:setAction", "detail");
          ga("send", "event", "Enhanced-Ecommerce", "load","product_impression_pp", {"nonInteraction": 1});
        ';
        //check woocommerce version
        $this->wc_version_compare($code);
    }

    /**
     * Enhanced E-commerce tracking for product impressions on category pages
     *
     * @access public
     * @return void
     */
    public function bind_product_metadata() {

        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }

        global $product;
        $category = get_the_terms($product->ID, "product_cat");
        $categories = "";
        if ($category) {
            foreach ($category as $term) {
                $categories.=$term->name . ",";
            }
        }
        //remove last comma(,) if multiple categories are there
        $categories = rtrim($categories, ",");

        echo '<input type="hidden" class="ls-pro-price" value="' . esc_html($product->get_price()) . '" />'
        . '<input type="hidden" class="ls-pro-sku" value="' . esc_html($product->get_sku() ? $product->get_sku() : $product->id) . '"/>'
        . '<input type="hidden" class="ls-pro-name" value="' . esc_html($product->get_title()) . '"/>'
        . '<input type="hidden" class="ls-pro-category" value="' . esc_html($categories) . '"/>'
        . '<input type="hidden" class="ls-pro-isfeatured" value="' . $product->is_featured() . '"/> ';

        global $homepage_json_fp, $homepage_json_rp;
        if (is_home()) {
            if (!is_array($homepage_json_fp) && !is_array($homepage_json_rp)) {
                $homepage_json_fp = array();
                $homepage_json_rp = array();
            }
            if ($product->is_featured()) {
                $jsonArr_prod_fp = array(get_permalink($product->id) => array(
                        "id" => esc_html($product->id),
                        "sku" => esc_html($product->get_sku() ? $product->get_sku() : $product->id),
                        "name" => esc_html($product->get_title()),
                        "price" => esc_html($product->get_price()),
                        "category" => esc_html($categories)
                ));
                array_push($homepage_json_fp, $jsonArr_prod_fp);
            } else {
                $jsonArr_prod_rp = array(get_permalink($product->id) => array(
                        "id" => esc_html($product->id),
                        "sku" => esc_html($product->get_sku() ? $product->get_sku() : $product->id),
                        "name" => esc_html($product->get_title()),
                        "price" => esc_html($product->get_price()),
                        "category" => esc_html($categories)
                ));
                array_push($homepage_json_rp, $jsonArr_prod_rp);
            }
        }
    }

    /**
     * Enhanced E-commerce tracking for product impressions,clicks on Home pages
     *
     * @access public
     * @return void
     */
    function homepage_impression() {
        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }
        //get impression threshold
        $impression_threshold = $this->ga_enhanced_ecommerce_category_page_impression_threshold;

        //Product impression on Home Page
        global $homepage_json_fp, $homepage_json_rp;
        $this->wc_version_compare("homepage_json_fp=" . json_encode($homepage_json_fp) . ";");
        $this->wc_version_compare("homepage_json_rp=" . json_encode($homepage_json_rp) . ";");
        $hmpg_impressions_jQ = '

		function hmpg_impressions(t_json_name,t_action,t_list){
                   t_send_threshold=0;
                   t_prod_pos=0;
                   
                    t_json_length=t_json_name.length;
                        
                    for(i = 0;i < t_json_name.length;i++) {
			t_send_threshold++;
			t_prod_url_key=Object.keys(t_json_name[i]);
			t_prod_pos++;
	            			
                 ga("ec:addImpression", {   
                            "id": t_json_name[i][t_prod_url_key]["sku"],
                            "name": t_json_name[i][t_prod_url_key]["name"],
                            "category": t_json_name[i][t_prod_url_key]["category"],
                            "list":t_list,
                            "price": t_json_name[i][t_prod_url_key]["price"],
                            "position": t_prod_pos
                        });
						
		if(t_json_length > ' . esc_js($impression_threshold) . '){
                           if((t_send_threshold%' . esc_js($impression_threshold) . ')==0){
                            t_json_length=t_json_length-' . esc_js($impression_threshold) . ';
                            ga("send", "event", "Enhanced-Ecommerce","load","product_impression_"+t_action , {"nonInteraction": 1});  
                        }
                     }else{
            
                       t_json_length--;
                       if(t_json_length==0){
                        ga("send", "event", "Enhanced-Ecommerce","load", "product_impression_"+t_action, {"nonInteraction": 1});  
                        }
		}	
                }
		}
                jQuery("a").on("click",function(){
			t_url=jQuery(this).attr("href");
			prod_exists_in_JSON(t_url,homepage_json_fp,"Featured Products","fp");
			prod_exists_in_JSON(t_url,homepage_json_rp,"Recent Products","rp");
		});
		//function for comparing urls in json object
		function prod_exists_in_JSON(t_url,t_json_name,t_list,t_action){
			for(i=0;i<t_json_name.length;i++){
				if(t_url==Object.keys(t_json_name[i])){
					t_prod_url_key=Object.keys(t_json_name[i]);
					ga("ec:addProduct", {              
					    "id": t_json_name[i][t_prod_url_key]["sku"],
                        "name": t_json_name[i][t_prod_url_key]["name"],
                        "category": t_json_name[i][t_prod_url_key]["category"],
                        "price": t_json_name[i][t_prod_url_key]["price"],
                        "position": ++i     
					});
					ga("ec:setAction", "click", {     
					  "list": t_list         
					});
					ga("send", "event", "Enhanced-Ecommerce","click", "product_click_"+t_action, {"nonInteraction": 1});  
				}
			}
		}    
                if(homepage_json_fp.length !== 0){
                hmpg_impressions(homepage_json_fp,"fp","Featured Products");		
                }
                if(homepage_json_rp.length !== 0){
                hmpg_impressions(homepage_json_rp,"rp","Recent Products");	
                }
                ';
        if (is_home()) {
            $this->wc_version_compare($hmpg_impressions_jQ);
        }
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
        $cartpage_prod_array_main = array();
        foreach ($woocommerce->cart->cart_contents as $key => $item) {
            $prod_meta = get_product($item["product_id"]);
            $category = get_the_terms($item["product_id"], "product_cat");
            $categories = "";
            if ($category) {
                foreach ($category as $term) {
                    $categories.=$term->name . ",";
                }
            }
            //remove last comma(,) if multiple categories are there
            $categories = rtrim($categories, ",");
            $cartpage_prod_array = array($key => array(
                    "id" => esc_html($prod_meta->id),
                    "sku" => esc_html($prod_meta->get_sku() ? $prod_meta->get_sku() : $prod_meta->id),
                    "name" => esc_html($prod_meta->get_title()),
                    "price" => esc_html($prod_meta->get_price()),
                    "category" => esc_html($categories)
            ));
            array_push($cartpage_prod_array_main, $cartpage_prod_array);
        }

        //Cart Page item Array to Json
        $this->wc_version_compare("cartpage_prod_json=" . json_encode($cartpage_prod_array_main) . ";");

        $code = '
        $.urlParam = function(name,t_url){
        var results = new RegExp("[\?&]" + name + "=([^&#]*)").exec(t_url);
        if (results==null){
            return null;
        }
        else{
        return results[1] || 0;
        }
        }
        
        $(".remove").click(function(){
            t_get_session_id=jQuery(this).attr("href");
            t_get_session_id=$.urlParam("remove_item",t_get_session_id);
                for(i=0;i<cartpage_prod_json.length;i++){
                if(t_get_session_id==Object.keys(cartpage_prod_json[i])){
                    if(cartpage_prod_json[i][t_get_session_id]["sku"]!==""){
                    t_prod_id=cartpage_prod_json[i][t_get_session_id]["sku"];
                }else{
                t_prod_id=cartpage_prod_json[i][t_get_session_id]["id"];
                }
                t_prod_name=cartpage_prod_json[i][t_get_session_id]["name"];
                t_prod_price=cartpage_prod_json[i][t_get_session_id]["price"];
                t_prod_cat=cartpage_prod_json[i][t_get_session_id]["category"];
                               
                }
            }
        ga("ec:addProduct", {                
                "id":t_prod_id,
                "name": t_prod_name,
                "category":t_prod_cat,
                "price": t_prod_price,
                "quantity": $(this).parents("tr").find(".product-quantity .qty").val()
              });         
              ga("ec:setAction", "remove");
              ga("send", "event", "Enhanced-Ecommerce", "click", "remove_from_cart_click",{"nonInteraction": 1});
              });
            ';
        //check woocommerce version
        $this->wc_version_compare($code);
    }

    /**
     * Enhanced E-commerce tracking checkout step 1
     *
     * @access public
     * @return void
     */
    public function checkout_step_1_tracking() {
        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }
        $code = $this->get_ordered_items();

        $code_step_1 = $code . 'ga("ec:setAction","checkout",{"step": 1});';
        $code_step_1 .= 'ga("send", "event", "Enhanced-Ecommerce","load","checkout_step_1",{"nonInteraction": 1});';

        //check woocommerce version and add code
        $this->wc_version_compare($code_step_1);
    }

    /**
     * Enhanced E-commerce tracking checkout step 2
     *
     * @access public
     * @return void
     */
    public function checkout_step_2_tracking() {
        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }
        $code = $this->get_ordered_items();

        $code_step_2 = $code . 'ga("ec:setAction","checkout",{"step": 2});';
        $code_step_2 .= 'ga("send", "event", "Enhanced-Ecommerce","load","checkout_step_2",{"nonInteraction": 1});';

        //if logged in and first name is filled - Guest Check out
        if (is_user_logged_in()) {
            $step2_onFocus = 't_tracked_focus=0;  if(t_tracked_focus===0){' . $code_step_2 . ' t_tracked_focus++;}';
        } else {
            //first name on focus call fire
            $step2_onFocus = 't_tracked_focus=0; jQuery("input[name=billing_first_name]").on("focus",function(){ if(t_tracked_focus===0){' . $code_step_2 . ' t_tracked_focus++;}});';
        }
        //check woocommerce version and add code
        $this->wc_version_compare($step2_onFocus);
    }

    /**
     * Enhanced E-commerce tracking checkout step 3
     *
     * @access public
     * @return void
     */
    public function checkout_step_3_tracking() {
        if ($this->disable_tracking($this->ga_enhanced_ecommerce_tracking_enabled)) {
            return;
        }
        $code = $this->get_ordered_items();

        //check if guest check out is enabled or not
        $step_2_on_proceed_to_pay = (!is_user_logged_in() && !$this->enable_guest_checkout ) || (!is_user_logged_in() && $this->enable_guest_checkout && $this->track_login_step_for_guest_user);

        $code_step_3 = $code . 'ga("ec:setAction","checkout",{"step": 3});';
        $code_step_3 .= 'ga("send", "event", "Enhanced-Ecommerce","load", "checkout_step_3",{"nonInteraction": 1});';

        $inline_js = 't_track_clk=0; jQuery(document).on("click","#place_order",function(e){ if(t_track_clk===0){';
        if ($step_2_on_proceed_to_pay) {
            if (isset($code_step_2))
                $inline_js .= $code_step_2;
        }
        $inline_js .= $code_step_3;
        $inline_js .= "t_track_clk++; }});";

        //check woocommerce version and add code
        $this->wc_version_compare($inline_js);
    }

    /**
     * Get oredered Items for check out page.
     *
     * @access public
     * @return void
     */
    public function get_ordered_items() {
        global $woocommerce;
        $code = "";
        //get all items added into the cart
        foreach ($woocommerce->cart->cart_contents as $item) {
            $p = get_product($item["product_id"]);

            $category = get_the_terms($item["product_id"], "product_cat");
            $categories = "";
            if ($category) {
                foreach ($category as $term) {
                    $categories.=$term->name . ",";
                }
            }
            //remove last comma(,) if multiple categories are there
            $categories = rtrim($categories, ",");

            $code .= 'ga("ec:addProduct", {"id": "' . esc_js($p->get_sku() ? $p->get_sku() : $p->id) . '",';
            $code .= '"name": "' . esc_js($p->get_title()) . '",';
            $code .= '"category": "' . esc_js($categories) . '",';
            $code .= '"price": "' . esc_js($p->get_price()) . '",';
            $code .= '"quantity": "' . esc_js($item["quantity"]) . '"});';
        }
        return $code;
    }

    /**
     * Check if tracking is disabled
     *
     * @access private
     * @param mixed $type
     * @return bool
     */
    private function disable_tracking($type) {
        if (is_admin() || current_user_can("manage_options") || (!$this->ga_id ) || "no" == $type) {
            return true;
        }
    }

    /**
     * woocommerce version compare
     *
     * @access public
     * @return void
     */
    function wc_version_compare($codeSnippet) {
        global $woocommerce;
        if (version_compare($woocommerce->version, "2.1", ">=")) {
            wc_enqueue_js($codeSnippet);
        } else {
            $woocommerce->add_inline_js($codeSnippet);
        }
    }

    /**
     * check UA is enabled or not
     *
     * @access public
     */
    function admin_check_UA_enabled() {
        echo '<script>
               jQuery("#woocommerce_enhanced_ecommerce_google_analytics_ga_standard_tracking_enabled").change(function(){
                t_ga_chk=jQuery(this).is(":checked");
                
                if(t_ga_chk){
                   jQuery("#woocommerce_enhanced_ecommerce_google_analytics_ga_display_feature_plugin").removeAttr("disabled");
                }else{
                    jQuery("#woocommerce_enhanced_ecommerce_google_analytics_ga_display_feature_plugin").attr("disabled",true);
                    t_display_chk=jQuery("#woocommerce_enhanced_ecommerce_google_analytics_ga_display_feature_plugin").is(":checked");
                    if(t_display_chk){
                      jQuery("#woocommerce_enhanced_ecommerce_google_analytics_ga_display_feature_plugin").removeAttr("checked");
                    }                 }
                   });
            </script>';
    }

    /**
     * Sending email to remote server
     *
     * @access public
     * @return void
     */
    public function send_email_to_tatvic($email, $domain_name, $status) {
        //set POST variables
        $url = "http://dev.tatvic.com/leadgen/woocommerce-plugin/store_email/";
        $fields = array(
            "email" => urlencode($email),
            "domain_name" => urlencode($domain_name),
            "status"=>urlencode($status)
        );
        wp_remote_post($url, array(
            "method" => "POST",
            "timeout" => 1,
            "httpversion" => "1.0",
            "blocking" => false,
            "headers" => array(),
            "body" => $fields
                )
        );
    }

}

?>