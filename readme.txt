===  Enhanced Ecommerce Google Analytics Plugin for WooCommerce ===
Contributors: Tatvic
Plugin Name: Enhanced Ecommerce for Woocommerce store
Plugin URI: http://wordpress.org/plugins/enhanced-e-commerce-for-woocommerce-store/
Tags: Google Analytics, Universal Analytics, Enhanced E-commerce, E-commerce, e-commerce, woo-commerce,Ecommerce,woocommerce, commerce, Wordpress Enhanced Ecommerce
Author URI: http://www.tatvic.com/
Author: Tatvic
Requires at least: 3.6
Tested up to: 3.9.2
Stable tag: 1.0.12
Version: 1.0.12
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

= Installation Instructions  =
* Enable Enhanced E-commerce for your profile/view. This is a profile / view level setting and can be accessed under Admin > View > E-commerce Settings

* Add meaningful labels for your checkout steps. We recommend you to label as, Step 1 : Checkout View; Step 2 : Login; Step 3 : Proceed to payment

* Remove standard E-commerce code from thank you along with the ecommerce.js which is included by <code>ga('require', 'ecommerce', 'ecommerce.js');</code>. If you are using a third party plugin for e-commerce tracking, you would have to disable the plugin.

* Activate our plug-in from the Settings page. You can access the setting page from here WooCommerce -> Settings ->Integration ->Enhanced Ecommerce Google Analytics.

* Find “Add Enhanced Ecommerce Tracking Code” in the settings page and check the box to add the tracking code

* If you have a guest checkout on your WooCommerce store, then Check the box “Add Code to Track the Login Step of Guest Users”. If you have a guest login but you do not check the box, then it might cause an uneven funnel reporting in Google Analytics.

*All the product sections on homepage other than feature product will be fired as Recent Product and will be available in product list performance report.

*All the product sections on product page will be fired as Related Product and will be available in product list performance report.

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

= Why are my PayPal transaction data not getting recorded in GA? =

If you are facing this issue, please check if you have configured auto return in PayPal settings.  Configuring auto return will resolve your issue. Here’s a PayPal <a href="https://www.paypal.com/in/cgi-bin/webscr?cmd=p/mer/express_return_summary-outside" target="_blank">documentation</a> & WooCommerce <a href="http://docs.woothemes.com/document/paypal-standard/#section-5" target="_blank">documentation</a> on understanding & setting up Auto Return.

In case you have already configured auto return for your store, we request you to create a new support thread <a href="https://wordpress.org/support/plugin/enhanced-e-commerce-for-woocommerce-store" target="_blank">here</a> & reach out to us.

= I’ve install the plugin but I do not see any data in my GA =

Following are one or more reasons:

* Please make sure that you have Enabled Enhanced Ecommerce setting in your GA Account. Check out the Step 1 of this <a href="http://www.tatvic.com/blog/enhanced-ecommerce/#steps" target="_blank">blogpost</a>.

* If you have just installed our plugin, then please wait for at-least 24 hours before you 	start seeing any data in your GA. If you still face this issue after 24 hours, please reach out to us via <a href="https://wordpress.org/support/plugin/enhanced-e-commerce-for-woocommerce-store" target="_blank">support thread</a>.

= Products with Multi variant not getting recorded in GA =

Currently our plugin does not support products with multiple variant & hence you may not see their transaction data in GA. Additionally, we have planned to add the same feature in our upcoming release

= My Ecommerce transaction data are not getting recorded in GA =

Please check if you have auto return configured in your payment gateway settings. If a user completes the transaction via a 3rd party payment gateway and is not redirected back to your store’s thank you page, our plugin will not be able to send the transaction data.

Hence, this may result into missing transaction data in your GA. You can resolve this issue by configuring auto return in your payment gateway settings.

= Does your Plugin support Product Refund? =

Our existing plugin does not track product refund data, however we are currently building a pro plugin that gives you access to product Refund data. It's a paid plugin that will give you access to all the important features of Universal Analytics including Access to all the reports of Enhanced Ecommerce, User ID Tracking, Product Refund, I.P Anonymization, etc. If you are interested in our paid Plugin, please reach out to us at <a href="mailto:marketing@tatvic.com">marketing@tatvic.com</a>

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

= 1.0.10 - 26/09/2014 =
 * Allows user to set local currency
 * Captures Impressions, Product Clicks and Add to Cart on Featured Product section and Recent Product section on Homepage
 * Captures Impressions, Product Clicks and Add to Cart on Related Product section on Product Page

= 1.0.11 - 28/10/2014 =
 * Fixed - Minor bugs

= 1.0.12 - 19/11/2014 =
 * Fixed - Settings not getting saved on few stores
 * Fixed - Broken layout issue

Important Note: When you update the plugin, please save your settings again.
