===  Enhanced Ecommerce Google Analytics Plugin for WooCommerce ===
Contributors: Tatvic
Plugin Name: Enhanced Ecommerce for Woocommerce store
Plugin URI: http://wordpress.org/plugins/enhanced-e-commerce-for-woocommerce-store/
Tags: Google Analytics, Universal Analytics, Enhanced E-commerce, E-commerce, e-commerce, woo-commerce,Ecommerce,woocommerce, commerce, Wordpress Enhanced Ecommerce
Author URI: http://www.tatvic.com/
Author: Tatvic
Requires at least: 3.6
Tested up to: 3.9.2
Stable tag: 1.0.9.1
Version: 1.0.10
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Provides integration between Google Analytics Enhanced Ecommerce and WooCommerce.

== Description ==
<a href="http://www.tatvic.com/enhanced-ecommerce-google-analytics-plugin-woocommerce/">Enhanced Ecommerce Google Analytics</a> is a Free WooCommerce Plugin which allows you to use the newly launched feature of Google Analytics – Enhanced Ecommerce.You can track the user behavior across your e-commerce store starting from product views to thank you page.Enhanced Ecommerce is still in beta and supports only Universal Analytics.

= Features of Plugin =
1. Quick & Easy installation from the wordpress interface
2. Supports four New Reports in Enhanced Ecommerce
     * Shopping Behaviour Report
     * Checkout Behaviour Report
     * Product Performance Report
     * Sales Performance Report
3. Supports Guest checkout functionality
4. Supports Display Advertising Feature
5. Captures Product Impressions, Add to Cart & Product Clicks events on category page 
6. Captures Product Impressions, Add to Cart & Product Clicks events on product page
7. Captures Product Impressions, Add to Cart & Product Clicks events on featured Product Section on Homepage
8. Captures Product Impressions, Add to Cart & Product Clicks events on Recent Product Section on Homepage
9. Captures Product Impressions, Add to Cart & Product Clicks events on Related Product Section on Productpage 
10. Set your local currency

= Things to keep in mind before enabling the Enhanced E-commerce plugin =
* Enable Enhanced E-commerce for your profile/view. This is a profile / view level setting and can be accessed under Admin > View > E-commerce Settings

* Also, add meaningful labels for your checkout steps. We recommend you to label as, Step 1 : Checkout View; Step 2 : Login; Step 3 : Proceed to payment

* Remove standard E-commerce code from thank you along with the ecommerce.js which is included by <code>ga('require', 'ecommerce', 'ecommerce.js');</code>. If you are using a third party plugin for e-commerce tracking, you would have to disable the plugin.

* You need to include ec.js. This can be done by adding a single line of code below your default Google Analytics code snippet <code>ga('require', 'ec', 'ec.js');</code>

* Users who are using Universal Analytics Tag in GTM, you will have to replace it with a custom HTML tag. Add the following code in your customer HTML tag. After adding the code, kindly replace UA-XXXXXXX-Y with your Google Analytics Property ID.
<pre>
<!-- Google Analytics tag -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	
ga('create', 'UA-XXXX-Y', 'auto');
ga('send', 'pageview');
ga('require','ec','ec.js');
</script>
<!-- End Google Analytics -->
</pre>
* After adding the above code, you will have to activate your plug-in from the Settings page. You can access the setting page from here WooCommerce -> Settings ->Integration ->Enhanced Ecommerce Google Analytics.

* Find “Add Enhanced Ecommerce Tracking Code” in the settings page and check the box to enable the plugin

* If you have a guest checkout on your WooCommerce store, then Check the box “Add Code to Track the Login Step of Guest Users”. If you have a guest login but you do not check the box, then it might cause an uneven funnel reporting in Google Analytics.

== Installation ==
1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation’s wp-content/plugins/ directory
3. Activate the plugin from the Plugins menu within the WordPress admin
4. Enter your email-address and Google Analytics ID for the plugin to enable the tracking code

== Screenshots ==
1. Enable Enhanced E-commerce for your profile/view. This is a profile / view level setting and can be accessed under Admin > View > E-commerce Settings. Also, add meaningful labels for your checkout steps. We recommend you to label as, Step 1 : Checkout View; Step 2 : Login; Step 3 : Proceed to payment;
2. Next, you need to activate your plugin from the Settings page by clicking the checkbox – “Add Enhanced Ecommerce Tracking Code". You can access the same from: WooCommerce > Settings > Integration > Enhanced Ecommerce Google Analytics.
3. To Track Guest Users, Check the box – Add Code to Track the Login Steps of Guest Users. If you have a Guest Check out & if it’s Unchecked, then it might cause an uneven funnel reporting in Google Analytics.


== Frequently Asked Questions ==
= Where can I find the setting for this plugin? =

This plugin will add the settings to the Integration tab, to be found in the WooCommerce > Settings menu.

= Does this conflict with the WooCommerce? =

Starting the WooCommerce 2.1 release there are no conflicts. However for earlier the plugin might conflict with the default Google Analytics integration for WooCommerce.

== Changelog ==

= 1.0 - 25/06/2014 =
 * Initial release

= 1.0.6.1 - 15/08/2014 =
 * Added new feature - Product impressions and Product click on category page view , including the    default pagination
 * Fixed-Allow Special Characters in javascript

= 1.0.7 - 28/08/2014 =
 * Added new feature - Display Advertising Feature
 * Fixed-Allow back quotes and single quotes in product name, category name etc.

= 1.0.8 - 09/09/2014 =
 * Fixed- Minor bugs 

= 1.0.9.1 - 09/11/2014 =
 * Fixed- Minor bug on order page

= 1.0.10 - 09/26/2014 =
 * Allows user to set local currency
 * Captures Impressions, Product Clicks and Add to Cart on Featured Product section and Recent Product section  (Home Page)
 * Captures Impressions, Product Clicks and Add to Cart on Related Product section  (Product Page)
