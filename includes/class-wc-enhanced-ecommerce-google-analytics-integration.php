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
        
         //Set Global Variables
        global $homepage_json_fp,$homepage_json_ATC_link, $homepage_json_rp,$prodpage_json_relProd,$catpage_json,
               $prodpage_json_ATC_link,$catpage_json_ATC_link;
        
        //define plugin ID       
        $this->id = "enhanced_ecommerce_google_analytics";
        $this->method_title = __("Enhanced Ecommerce Google Analytics", "woocommerce");
        $this->method_description = __("Enhanced Ecommerce is a new feature of Universal Analytics that generates detailed statistics about the users journey from product page to thank you page on your e-store. <br/><a href='http://www.tatvic.com/blog/enhanced-ecommerce/' target='_blank'>Know more about Enhanced Ecommerce.</a>", "woocommerce");

        //start session for product position count
        session_start();
        $_SESSION['t_npcnt']=0;
        $_SESSION['t_fpcnt']=0;
        // Load the integration form
        $this->init_form_fields();
        //load all the settings
        $this->init_settings();

            // Define user set variables -- Always use short names    
            $this->ga_email = $this->get_option("ga_email");
            $this->ga_id = $this->get_option("ga_id");
            $this->ga_Dname = $this->get_option("ga_Dname");
            $this->ga_LC = $this->get_option("ga_LC");
            $this->ga_ST = $this->get_option("ga_ST");
            $this->ga_gCkout = $this->get_option("ga_gCkout") == "yes" ? true : false; //guest checkout
            $this->ga_gUser = $this->get_option("ga_gUser") == "yes" ? true : false; //guest checkout
            $this->ga_eeT = $this->get_option("ga_eeT");
            $this->ga_DF = $this->get_option("ga_DF") == "yes" ? true : false;
            $this->ga_imTh = $this->get_option("ga_imTh");

               
         //Save Changes action for admin settings
         add_action("woocommerce_update_options_integration_" . $this->id, array($this, "process_admin_options"));
         
        // API Call to LS with e-mail
        // Tracking code
        add_action("wp_head", array($this, "google_tracking_code"));
        add_action("woocommerce_thankyou", array($this, "ecommerce_tracking_code"));

        // Enhanced Ecommerce product impression hook
        add_action("wp_footer", array($this, "t_products_impre_clicks"));
        
        add_action("woocommerce_after_shop_loop_item", array($this, "bind_product_metadata")); //for cat, shop, prod(related),search and home page
        add_action("woocommerce_after_single_product", array($this, "product_detail_view"));
        add_action("woocommerce_after_cart", array($this, "remove_cart_tracking"));
        add_action("woocommerce_before_checkout_billing_form", array($this, "checkout_step_1_tracking"));
        add_action("woocommerce_after_checkout_billing_form", array($this, "checkout_step_2_tracking"));
        add_action("woocommerce_after_checkout_billing_form", array($this, "checkout_step_3_tracking"));

        // Event tracking code
        add_action("woocommerce_after_add_to_cart_button", array($this, "add_to_cart"));
        
		//Enable display feature code checkbox 
        add_action("admin_footer", array($this, "admin_check_UA_enabled"));

        //add version details in footer
        add_action("wp_footer", array($this, "add_plugin_details"));
		
		//check if plugin is deactivated or not
        add_action("deactivate_plugin", array($this, "detect_plugin_deactivation"));
        
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
     * display details of plugin
     *
     * @access public
     * @return void
     */
    function add_plugin_details() {
        echo '<!--Enhanced Ecommerce Google Analytics Plugin for Woocommerce by Tatvic Plugin Version: 1.0.12-->';
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
                "desc_tip"	=>  true,
                "default" => get_option("ga_email") // Backwards compat
            ),
            "ga_id" => array(
                "title" => __("Google Analytics ID", "woocommerce"),
                "description" => __("Enter your Google Analytics ID here. You can login into your Google Analytics account to find your ID. e.g.<code>UA-XXXXX-X</code>", "woocommerce"),
                "type" => "text",
                "placeholder" => "UA-XXXXX-X",
                "desc_tip"	=>  true,
                "default" => get_option("ga_id") // Backwards compat
            ),
            "ga_Dname" => array(
                "title" => __("Set Domain Name", "woocommerce"),
                "description" => sprintf(__("Enter your domain name here (Optional)")),
                "type" => "text",
                "placeholder" => "",
                "desc_tip"	=>  true,
                "default" => get_option("ga_Dname") ? get_option("ga_Dname") : "auto"
            ),
            "ga_LC" => array(
                "title" => __("Set Currency", "woocommerce"),
                "description" => __("Find your Local Currency Code by visiting this <a href='https://developers.google.com/analytics/devguides/platform/currencies#supported-currencies' target='_blank'>link</a>", "woocommerce"),
                "type" => "select",
                "required" => "required",
                "default" => get_option('ga_LC'),
                "options" => $ga_currency_code
            ),
            "ga_ST" => array(
                "title" => __("Tracking code", "woocommerce"),
                "label" => __("Add Universal Analytics Tracking Code (Optional)", "woocommerce"),
                "description" => sprintf(__("This feature adds Universal Analytics Tracking Code to your Store. You don't need to enable this if using a 3rd party analytics plugin.", "woocommerce")),
                "type" => "checkbox",
                "checkboxgroup" => "start",
                "desc_tip"	=>  true,
                "default" => get_option("ga_ST") ? get_option("ga_ST") : "no"  // Backwards compat
            ),
            "ga_DF" => array(
                "label" => __("Add Display Advertising Feature Code (Optional)", "woocommerce"),
                "type" => "checkbox",
                "checkboxgroup" => "",
                "description" => sprintf(__("This feature enables remarketing with Google Analytics & Demographic reports. Adding the code is the first step in a 3 step process. <a href='https://support.google.com/analytics/answer/2819948?hl=en' target='_blank'>Learn More</a><br/>This feature can only be enabled if you have enabled UA Tracking from our Plugin. If not, you can still manually add the display advertising code by following the instructions from this <a href='https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features' target='_blank'>link</a>", "woocommerce")),
                "default" => get_option("ga_DF") ? get_option("ga_DF") : "no"  // Backwards compat
            ),
            "ga_eeT" => array(
                "label" => __("Add Enhanced Ecommerce Tracking Code", "woocommerce"),
                "type" => "checkbox",
                "checkboxgroup" => "",
                "desc_tip"	=>  true,
                "description" => sprintf(__("This feature adds Enhanced Ecommerce Tracking Code to your Store", "woocommerce")),
                "default" => get_option("ga_eeT") ? get_option("ga_eeT")  : "no"  // Backwards compat
            ),
            "ga_gUser" => array(
                "label" => __("Add Code to Track the Login Step of Guest Users (Optional)", "woocommerce"),
                "type" => "checkbox",
                "checkboxgroup" => "",
                "desc_tip"	=>  true,
                "description" => sprintf(__("If you have Guest Check out enable, we recommend you to add this code", "woocommerce")),
                "default" => get_option("ga_gUser") ? get_option("ga_gUser") : "no"  // Backwards compat
            ),
            "ga_imTh" => array(
                "title" => __("Impression Threshold", "woocommerce"),
                "description" => sprintf(__("This feature sets Impression threshold for category page. It sends hit after these many numbers of products impressions", "woocommerce")),
                "type" => "number",
                "desc_tip" =>  true,
		"css"=>"width:112px !important;",
		'custom_attributes' => array(
                'min' => "1",
                ),
                "default" => get_option("ga_imTh") ? get_option("ga_imTh") : "6"  // Backwards compat
            ),          
        );
        /* When user updates the email, post it to the remote server */
        if (isset($_GET["tab"]) && isset($_REQUEST["section"]) && isset($_REQUEST["woocommerce_".$this->id."_ga_email"])) {

            $current_tab = ( empty($_GET["tab"]) ) ? false : sanitize_text_field(urldecode($_GET["tab"]));
            $current_section = ( empty($_REQUEST["section"]) ) ? false : sanitize_text_field(urldecode($_REQUEST["section"]));
            $save_for_the_plugin = ($current_tab == "integration" ) && ($current_section == $this->id);

            $update_made_for_email = $_REQUEST["woocommerce_".$this->id."_ga_email"] != $this->get_option("ga_email");
            if ($save_for_the_plugin && $update_made_for_email) {
                if ($_REQUEST["woocommerce_".$this->id."_ga_email"] != "") {
                    $email = $_REQUEST["woocommerce_".$this->id."_ga_email"];
                    $this->send_email_to_tatvic($email,'active');
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
        $email_id=$this->get_option('ga_email');
        $this->send_email_to_tatvic($email_id,'deactivate');
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
        if (is_admin() || current_user_can("manage_options") || $this->ga_ST == "no") {
            return;
        }

        $tracking_id = $this->ga_id;

        if (!$tracking_id) {
            return;
        }

        //common validation----end

        if (!empty($this->ga_Dname)) {
            $set_domain_name = esc_js($this->ga_Dname);
        } else {
            $set_domain_name = "auto";
        }

        //add display features
        if ($this->ga_DF) {
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

        if ($this->disable_tracking($this->ga_eeT) || current_user_can("manage_options") || get_post_meta($order_id, "_ga_tracked", true) == 1)
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
        if (!empty($this->ga_Dname)) {
            $set_domain_name = esc_js($this->ga_Dname);
        } else {
            $set_domain_name = "auto";
        }

        //add display features
        if ($this->ga_DF) {
            $ga_display_feature_code = 'ga("require", "displayfeatures");';
        } else {
            $ga_display_feature_code = "";
        }

        //add Pageview on order page if user checked Add Standard UA code
        if ($this->ga_ST) {
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

                if (isset($_product->variation_data)) {
                  $categories=esc_js(woocommerce_get_formatted_variation($_product->variation_data, true));
                } else {
                    $out = array();
                    $categories = get_the_terms($_product->id, "product_cat");
                    if ($categories) {
                        foreach ($categories as $category) {
                            $out[] = $category->name;
                        }
                    }
                    $categories=esc_js(join(",", $out));
                }

                //orderpage Prod json
                $orderpage_prod_Array[get_permalink($_product->id)]=array(
                        "tvc_id" => esc_html($_product->id),
                        "tvc_i" => esc_js($_product->get_sku() ? $_product->get_sku() : $_product->id),
                        "tvc_n" => esc_js($item["name"]),
                        "tvc_p" => esc_js($order->get_item_total($item)),
                        "tvc_c" => $categories,
                        "tvc_q"=>esc_js($item["qty"])
                      );
            }
            //make json for prod meta data on order page
           $this->wc_version_compare("tvc_oc=" . json_encode($orderpage_prod_Array) . ";");
            
        }
            //orderpage transcation data json
                $orderpage_trans_Array=array(
                                "id"=> esc_js($order->get_order_number()),      // Transaction ID. Required
				"affiliation"=> esc_js(get_bloginfo('name')), // Affiliation or store name
				"revenue"=>esc_js($order->get_total()),        // Grand Total
                                "tax"=> esc_js($order->get_total_tax()),        // Tax
				"shipping"=> esc_js($order->get_shipping()),    // Shipping
                                "coupon"=>$coupons_list  
                      );
                 //make json for trans data on order page
           $this->wc_version_compare("tvc_td=" . json_encode($orderpage_trans_Array) . ";");

         $code.='
                //set local currencies
            ga("set", "&cu", "' . $this->ga_LC . '");  
            for(var t_item in tvc_oc){
                ga("ec:addProduct", { 
                    "id": tvc_oc[t_item].tvc_i,
                    "name": tvc_oc[t_item].tvc_n, 
                    "category": tvc_oc[t_item].tvc_c,
                    "price": tvc_oc[t_item].tvc_p,
                    "quantity": tvc_oc[t_item].tvc_q,
			});
            }
            ga("ec:setAction","purchase", {
				"id": tvc_td.id,
				"affiliation": tvc_td.affiliation,
				"revenue": tvc_td.revenue,
                                "tax": tvc_td.tax,
				"shipping": tvc_td.shipping,
                                "coupon": tvc_td.coupon
			});
                        
        ga("send", "event", "Enhanced-Ecommerce","load", "order_confirmation", {"nonInteraction": 1});      
    ';

        //check woocommerce version
        $this->wc_version_compare($code);
        update_post_meta($order_id, "_ga_tracked", 1);
    }


    /**
     * Enhanced E-commerce tracking for single product add to cart
     *
     * @access public
     * @return void
     */
    function add_to_cart() {
        if ($this->disable_tracking($this->ga_eeT))
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

        $code = '
              ga("require", "ec", "ec.js");
            $(".single_add_to_cart_button").click(function() {
                            
                              // Enhanced E-commerce Add to cart clicks 
                              ga("ec:addProduct", {
                                "id" : tvc_po.tvc_i,
                                "name": tvc_po.tvc_n,
                                "category" :tvc_po.tvc_c,
                                "price": tvc_po.tvc_p,
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
     * Enhanced E-commerce tracking for product detail view
     *
     * @access public
     * @return void
     */
    public function product_detail_view() {

        if ($this->disable_tracking($this->ga_eeT)) {
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
        //product detail view json
        $prodpage_detail_json=array(
            "tvc_i" => $product->get_sku() ? $product->get_sku() : $product->id,                   
            "tvc_n"=> $product->get_title(),
            "tvc_c"=>$categories,
            "tvc_p"=>$product->get_price()
        );
        if(empty($prodpage_detail_json)){ //prod page array
            $prodpage_detail_json=array();
        }
        //prod page detail view json
        $this->wc_version_compare("tvc_po=" . json_encode($prodpage_detail_json) . ";");
        $code = '
        ga("require", "ec", "ec.js");    
        ga("ec:addProduct", {
            "id": tvc_po.tvc_i,                   // Product details are provided in an impressionFieldObject.
            "name": tvc_po.tvc_n,
            "category":tvc_po.tvc_c,
          });
          ga("ec:setAction", "detail");
          ga("send", "event", "Enhanced-Ecommerce", "load","product_impression_pp", {"nonInteraction": 1});
        ';
        //check woocommerce version
        if(is_product()){
        $this->wc_version_compare($code);
    }
    }

    /**
     * Enhanced E-commerce tracking for product impressions on category pages (hidden fields) , product page (related section)
     * home page (featured section and recent section)
     *
     * @access public
     * @return void
     */
    public function bind_product_metadata() {

        if ($this->disable_tracking($this->ga_eeT)) {
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
        //declare all variable as a global which will used for make json
        global $homepage_json_fp,$homepage_json_ATC_link, $homepage_json_rp,$prodpage_json_relProd,$catpage_json,$prodpage_json_ATC_link,$catpage_json_ATC_link;
        //is home page then make all necessory json
        if (is_home()) {
            if (!is_array($homepage_json_fp) && !is_array($homepage_json_rp) && !is_array($homepage_json_ATC_link)) {
                $homepage_json_fp = array();
                $homepage_json_rp = array();
                $homepage_json_ATC_link=array();                
            }
                // ATC link Array
                $homepage_json_ATC_link[$product->add_to_cart_url()]=array("ATC-link"=>get_permalink($product->id));
              //check if product is featured product or not  
            if ($product->is_featured()) {
               //check if product is already exists in homepage featured json    
               if(!array_key_exists(get_permalink($product->id),$homepage_json_fp)){
                $homepage_json_fp[get_permalink($product->id)] = array(
                        "tvc_id" => esc_html($product->id),
                        "tvc_i" => esc_html($product->get_sku() ? $product->get_sku() : $product->id),
                        "tvc_n" => esc_html($product->get_title()),
                        "tvc_n" => esc_html($product->get_price()),
                        "tvc_c" => esc_html($categories),
                        "tvc_po" => ++$_SESSION['t_fpcnt'],
                        "ATC-link"=>$product->add_to_cart_url()
                );
                //else add product in homepage recent product json
               }else {
                    $homepage_json_rp[get_permalink($product->id)] =array(
                        "tvc_id" => esc_html($product->id),
                        "tvc_i" => esc_html($product->get_sku() ? $product->get_sku() : $product->id),
                        "tvc_n" => esc_html($product->get_title()),
                        "tvc_p" => esc_html($product->get_price()),
                        "tvc_po" => ++$_SESSION['t_npcnt'],
                        "tvc_c" => esc_html($categories)
                );
               }
                      
            } else {
                //else prod add in homepage recent json    
                $homepage_json_rp[get_permalink($product->id)] =array(
                        "tvc_id" => esc_html($product->id),
                        "tvc_i" => esc_html($product->get_sku() ? $product->get_sku() : $product->id),
                        "tvc_n" => esc_html($product->get_title()),
                        "tvc_p" => esc_html($product->get_price()),
                        "tvc_po" => ++$_SESSION['t_npcnt'],
                        "tvc_c" => esc_html($categories)
                );
            }
        }
        //if product page then related product page array
        else if(is_product()){
            if(!is_array($prodpage_json_relProd) && !is_array($prodpage_json_ATC_link)){
                $prodpage_json_relProd = array();
                $prodpage_json_ATC_link = array();
    }
                // ATC link Array
                $prodpage_json_ATC_link[$product->add_to_cart_url()]=array("ATC-link"=>get_permalink($product->id));

            $prodpage_json_relProd[get_permalink($product->id)] = array(
                        "tvc_id" => esc_html($product->id),
                        "tvc_i" => esc_html($product->get_sku() ? $product->get_sku() : $product->id),
                        "tvc_n" => esc_html($product->get_title()),
                        "tvc_p" => esc_html($product->get_price()),
                        "tvc_c" => esc_html($categories),
                        "tvc_po" => ++$_SESSION['t_npcnt'],
                );
                    
        }
        //category page, search page and shop page json
        else if (is_product_category() || is_search() || is_shop()) {
             if (!is_array($catpage_json) && !is_array($catpage_json_ATC_link)){
                 $catpage_json=array();
                 $catpage_json_ATC_link=array();
             }
             //cat page ATC array
             $catpage_json_ATC_link[$product->add_to_cart_url()]=array("ATC-link"=>get_permalink($product->id));
             
             $catpage_json[get_permalink($product->id)] =array(
                        "tvc_id" => esc_html($product->id),
                        "tvc_i" => esc_html($product->get_sku() ? $product->get_sku() : $product->id),
                        "tvc_n" => esc_html($product->get_title()),
                        "tvc_p" => esc_html($product->get_price()),
                        "tvc_c" => esc_html($categories),
                        "tvc_po" => ++$_SESSION['t_npcnt'], 
                );
            }
        
    }

    /**
     * Enhanced E-commerce tracking for product impressions,clicks on Home pages
     *
     * @access public
     * @return void
     */
    function t_products_impre_clicks() {
        if ($this->disable_tracking($this->ga_eeT)) {
            return;
        }
        //get impression threshold
        $impression_threshold = $this->ga_imTh;

        //Product impression on Home Page
        global $homepage_json_fp,$homepage_json_ATC_link, $homepage_json_rp,$prodpage_json_relProd,$catpage_json,$prodpage_json_ATC_link,$catpage_json_ATC_link;
        //home page json for featured products and recent product sections
        //check if php array is empty
        if(empty($homepage_json_ATC_link)){
            $homepage_json_ATC_link=array(); //define empty array so if empty then in json will be []
        }
        if(empty($homepage_json_fp)){
            $homepage_json_fp=array(); //define empty array so if empty then in json will be []
        }
        if(empty($homepage_json_rp)){ //home page recent product array
            $homepage_json_rp=array(); 
        }
        if(empty($prodpage_json_relProd)){ //prod page related section array
            $prodpage_json_relProd=array();
        }
        if(empty($prodpage_json_ATC_link)){
            $prodpage_json_ATC_link=array(); //prod page ATC link json
        }
        if(empty($catpage_json)){ //category page array
            $catpage_json=array();
        }
        if(empty($catpage_json_ATC_link)){ //category page array
            $catpage_json_ATC_link=array();
        }
        //home page json
        $this->wc_version_compare("homepage_json_ATC_link=" . json_encode($homepage_json_ATC_link) . ";");
        $this->wc_version_compare("tvc_fp=" . json_encode($homepage_json_fp) . ";");
        $this->wc_version_compare("tvc_rcp=" . json_encode($homepage_json_rp) . ";");
        //product page json
        $this->wc_version_compare("tvc_rdp=" . json_encode($prodpage_json_relProd) . ";");
        $this->wc_version_compare("prodpage_json_ATC_link=" . json_encode($prodpage_json_ATC_link) . ";");
        //category page json
        $this->wc_version_compare("tvc_pgc=" . json_encode($catpage_json) . ";");
        $this->wc_version_compare("catpage_json_ATC_link=" . json_encode($catpage_json_ATC_link) . ";");
        
        
        $hmpg_impressions_jQ = '
                  ga("require", "ec", "ec.js");
		function t_products_impre_clicks(t_json_name,t_action,t_list){
                   t_send_threshold=0;
                   t_prod_pos=0;
                   
                    t_json_length=Object.keys(t_json_name).length
                        
                    for(var t_item in t_json_name) {
			t_send_threshold++;
			t_prod_pos++;
	            			
                 ga("ec:addImpression", {   
                            "id": t_json_name[t_item].tvc_i,
                            "name": t_json_name[t_item].tvc_n,
                            "category": t_json_name[t_item].tvc_c,
                            "list":t_list,
                            "price": t_json_name[t_item].tvc_p,
                            "position": t_json_name[t_item].tvc_po,
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
                
		//function for comparing urls in json object
		function prod_exists_in_JSON(t_url,t_json_name,t_list,t_action){
                                    if(t_json_name.hasOwnProperty(t_url)){
                                        t_call_fired=true;
					ga("ec:addProduct", {              
					    "id": t_json_name[t_url].tvc_i,
                                            "name": t_json_name[t_url].tvc_n,
                                            "category": t_json_name[t_url].tvc_c,
                                            "price": t_json_name[t_url].tvc_p,
                                            "position": t_json_name[t_url].tvc_po,
					});
					ga("ec:setAction", "click", {"list": t_list});
					ga("send", "event", "Enhanced-Ecommerce","click", "product_click_"+t_action, {"nonInteraction": 1});  
                                   }else{
                                   t_call_fired=false;
				}
                                return t_call_fired;
			}
                function prod_ATC_link_exists(t_url,t_ATC_json_name,t_prod_data_json,t_qty){
                    t_prod_url_key=t_ATC_json_name[t_url]["ATC-link"];
                    
                         if(t_prod_data_json.hasOwnProperty(t_prod_url_key)){
                                t_call_fired=true;
                            // Enhanced E-commerce Add to cart clicks 
                              ga("ec:addProduct", {
                               "id": t_prod_data_json[t_prod_url_key].tvc_i,
                               "name": t_prod_data_json[t_prod_url_key].tvc_n,
                               "category": t_prod_data_json[t_prod_url_key].tvc_c,
                               "price": t_prod_data_json[t_prod_url_key].tvc_p,
                                "quantity" : t_qty
                              });
                              ga("ec:setAction", "add");
                              ga("send", "event", "Enhanced-Ecommerce","click", "add_to_cart_click",{"nonInteraction": 1});     
                              }else{
                                   t_call_fired=false;
		}    
                         return t_call_fired;
                 
                }
                
                ';
        if(is_home()){
       $hmpg_impressions_jQ .='
                if(tvc_fp.length !== 0){
                    t_products_impre_clicks(tvc_fp,"fp","Featured Products");		
                }
                if(tvc_rcp.length !== 0){
                    t_products_impre_clicks(tvc_rcp,"rp","Recent Products");	
                }
                jQuery("a:not([href*=add-to-cart],.product_type_variable, .product_type_grouped)").on("click",function(){
			t_url=jQuery(this).attr("href");
                        //home page call for click
                        t_call_fired=prod_exists_in_JSON(t_url,tvc_fp,"Featured Products","fp");
                        if(!t_call_fired){
                            prod_exists_in_JSON(t_url,tvc_rcp,"Recent Products","rp");
                        }    
                });
                //ATC click
                jQuery("a[href*=add-to-cart]").on("click",function(){
			t_url=jQuery(this).attr("href");
                        t_qty=$(this).parent().find("input[name=quantity]").val();
                             //default quantity 1 if quantity box is not there             
                            if(t_qty=="" || t_qty===undefined){
                                t_qty="1";
                            }
                        t_call_fired=prod_ATC_link_exists(t_url,homepage_json_ATC_link,tvc_fp,t_qty);
                        if(!t_call_fired){
                            prod_ATC_link_exists(t_url,homepage_json_ATC_link,tvc_rcp,t_qty);
                        }
                    });   
             
                ';
        }else if (is_product()) {
                //product page releted products
                $hmpg_impressions_jQ .='
                if(tvc_rdp.length !== 0){
                    t_products_impre_clicks(tvc_rdp,"rdp","Related Products");	
                }          
                //product click - image and product name
                jQuery("a:not(.product_type_variable, .product_type_grouped)").on("click",function(){
                    t_url=jQuery(this).attr("href");
                     //prod page related call for click
                     prod_exists_in_JSON(t_url,tvc_rdp,"Related Products","rdp");
                });  
                //Prod ATC link click in related product section
                jQuery("a[href*=add-to-cart]").on("click",function(){
			t_url=jQuery(this).attr("href");
                        t_qty=$(this).parent().find("input[name=quantity]").val();
                             //default quantity 1 if quantity box is not there             
                            if(t_qty=="" || t_qty===undefined){
                                t_qty="1";
                            }
                prod_ATC_link_exists(t_url,prodpage_json_ATC_link,tvc_rdp,t_qty);
                });   
            ';
        }else if (is_product_category()) {
            $hmpg_impressions_jQ .='
                //category page json
                if(tvc_pgc.length !== 0){
                    t_products_impre_clicks(tvc_pgc,"cp","Category Page");	
                }
               //Prod category ATC link click in related product section
                jQuery("a:not(.product_type_variable, .product_type_grouped)").on("click",function(){
                     t_url=jQuery(this).attr("href");
                     //cat page prod call for click
                     prod_exists_in_JSON(t_url,tvc_pgc,"Category Page","cp");
                     });
               
        ';
        }else if(is_shop()){
            $hmpg_impressions_jQ .='
                //shop page json
                if(tvc_pgc.length !== 0){
                    t_products_impre_clicks(tvc_pgc,"sp","Shop Page");	
                }
                //shop page prod click
                jQuery("a:not(.product_type_variable, .product_type_grouped)").on("click",function(){
                    t_url=jQuery(this).attr("href");
                     //cat page prod call for click
                     prod_exists_in_JSON(t_url,tvc_pgc,"Shop Page","sp");
                     });
                
                     
        '; 
        }else if(is_search()){
            $hmpg_impressions_jQ .='
                //shop page json
                if(tvc_pgc.length !== 0){
                    t_products_impre_clicks(tvc_pgc,"srch","Search Results");	
                }
                //shop page prod click
                jQuery("a:not(.product_type_variable, .product_type_grouped)").on("click",function(){
                    t_url=jQuery(this).attr("href");
                     //cat page prod call for click
                     prod_exists_in_JSON(t_url,catpage_json,"Search Results","srch");
                     });
                
                     
        '; 
        }
        //common ATC link for Category page , Shop Page and Search Page
        if(is_product_category() || is_shop() || is_search()){
            $hmpg_impressions_jQ .='
                     //ATC link click
                jQuery("a[href*=add-to-cart]").on("click",function(){
			t_url=jQuery(this).attr("href");
                        t_qty=$(this).parent().find("input[name=quantity]").val();
                             //default quantity 1 if quantity box is not there             
                            if(t_qty=="" || t_qty===undefined){
                                t_qty="1";
                            }
                       prod_ATC_link_exists(t_url,catpage_json_ATC_link,tvc_pgc,t_qty);
                    });      
                    ';
        }
        
        //on home page, product page , category page
        if (is_home() || is_product() || is_product_category() || is_search() || is_shop()){
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
        if ($this->disable_tracking($this->ga_eeT)) {
            return;
        }
        global $woocommerce;
        $cartpage_prod_array_main = array();
        //echo "<pre>".print_r($woocommerce->cart->cart_contents,TRUE)."</pre>";
        foreach ($woocommerce->cart->cart_contents as $key => $item) {
            $prod_meta = get_product($item["product_id"]);
            
            $cart_remove_link=html_entity_decode($woocommerce->cart->get_remove_url($key));
                       
            $category = get_the_terms($item["product_id"], "product_cat");
            $categories = "";
            if ($category) {
                foreach ($category as $term) {
                    $categories.=$term->name . ",";
                }
            }
            //remove last comma(,) if multiple categories are there
            $categories = rtrim($categories, ",");
            $cartpage_prod_array_main[$cart_remove_link] =array(
                    "tvc_id" => esc_html($prod_meta->id),
                    "tvc_i" => esc_html($prod_meta->get_sku() ? $prod_meta->get_sku() : $prod_meta->id),
                    "tvc_n" => esc_html($prod_meta->get_title()),
                    "tvc_p" => esc_html($prod_meta->get_price()),
                    "tvc_c" => esc_html($categories),
                    "tvc_q"=>$woocommerce->cart->cart_contents[$key]["quantity"]
            );

        }

        //Cart Page item Array to Json
        $this->wc_version_compare("tvc_cc=" . json_encode($cartpage_prod_array_main) . ";");

        $code = '
        ga("require", "ec", "ec.js");
        $("a[href*=\"?remove_item\"]").click(function(){
            t_url=jQuery(this).attr("href");
        
        ga("ec:addProduct", {                
                "id":tvc_cc[t_url].tvc_i,
                "name": tvc_cc[t_url].tvc_n,
                "category":tvc_cc[t_url].tvc_c,
                "price": tvc_cc[t_url].tvc_p,
                "quantity": tvc_cc[t_url].tvc_q
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
        if ($this->disable_tracking($this->ga_eeT)) {
            return;
        }
        //call fn to make json
        $this->get_ordered_items();
        $code= '
                ga("require", "ec", "ec.js");
                for(var t_item in tvc_ch){
					ga("ec:addProduct", {
						"id": tvc_ch[t_item].tvc_i,
						"name": tvc_ch[t_item].tvc_n,
						"category": tvc_ch[t_item].tvc_c,
						"price": tvc_ch[t_item].tvc_p,
						"quantity": tvc_ch[t_item].tvc_q
					});
					}';

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
        if ($this->disable_tracking($this->ga_eeT)) {
            return;
        }
        $code= '
               
                for(var t_item in tvc_ch){
					ga("ec:addProduct", {
						"id": tvc_ch[t_item].tvc_i,
						"name": tvc_ch[t_item].tvc_n,
						"category": tvc_ch[t_item].tvc_c,
						"price": tvc_ch[t_item].tvc_p,
						"quantity": tvc_ch[t_item].tvc_q
					});
					}';

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
        if ($this->disable_tracking($this->ga_eeT)) {
            return;
        }
        $code= '
            for(var t_item in tvc_ch){
					ga("ec:addProduct", {
						"id": tvc_ch[t_item].tvc_i,
						"name": tvc_ch[t_item].tvc_n,
						"category": tvc_ch[t_item].tvc_c,
						"price": tvc_ch[t_item].tvc_p,
						"quantity": tvc_ch[t_item].tvc_q
					});
					}';

        //check if guest check out is enabled or not
        $step_2_on_proceed_to_pay = (!is_user_logged_in() && !$this->ga_gCkout ) || (!is_user_logged_in() && $this->ga_gCkout && $this->ga_gUser);

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
             $chkout_json[get_permalink($p->id)] = array(
                "tvc_i" => esc_js($p->get_sku() ? $p->get_sku() : $p->id),
                "tvc_n" => esc_js($p->get_title()),
                "tvc_p" => esc_js($p->get_price()),
                "tvc_c" => $categories,
                "tvc_q" => esc_js($item["quantity"]),
                "isfeatured"=>$p->is_featured()
            );

        }
        //return $code;
        //make product data json on check out page
        $this->wc_version_compare("tvc_ch=" . json_encode($chkout_json) . ";");
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
               jQuery("#woocommerce_enhanced_ecommerce_google_analytics_ga_ST").change(function(){
                t_ga_chk=jQuery(this).is(":checked");
                
                if(t_ga_chk){
                   jQuery("#woocommerce_enhanced_ecommerce_google_analytics_ga_DF").removeAttr("disabled");
                }else{
                    jQuery("#woocommerce_enhanced_ecommerce_google_analytics_ga_DF").attr("disabled",true);
                    t_display_chk=jQuery("#woocommerce_enhanced_ecommerce_google_analytics_ga_DF").is(":checked");
                    if(t_display_chk){
                      jQuery("#woocommerce_enhanced_ecommerce_google_analytics_ga_DF").removeAttr("checked");
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
    public function send_email_to_tatvic($email,$status) {
        $url = "http://dev.tatvic.com/leadgen/woocommerce-plugin/store_email/";
        //set POST variables
        $fields = array(
            "email" => urlencode($email),
            "domain_name" => urlencode(get_site_url()),
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