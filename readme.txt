===  Enhanced Ecommerce Google Analytics Plugin for WooCommerce ===
Contributors: Tatvic
Plugin Name: Enhanced E-commerce for Woocommerce store
Plugin URI: https://wordpress.org/plugins/enhanced-ecommerce-for-woocommerce-store/
Tags: Google Analytics, Universal Analytics, Enhanced E-commerce, E-commerce, e-commerce, woo-commerce
Author URI: http://www.tatvic.com/
Author: Tatvic
Requires at least: 3.6
Tested up to: 3.8
Stable tag: 1.0.6
Version: 1.0.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Provides integration between Google Analytics Enhanced Ecommerce and WooCommerce.

== Description ==
This plugin provides the integration between Enhanced Ecommerce feature of Google Analytics and the WooCommerce plugin. You can track the user beahviour across your e-commerce store starting from product views to thank you page. It only supports the new Universal Analytics.

= Things to keep in mind before enabling the Enhanced E-commerce plugin =
* Enable Enhanced E-commerce for your profile/view. This is a profile / view level setting and can be accessed under Admin > View > E-commerce Settings

* Also, add meaningful labels for your checkout steps. We recommend you to label as, Step 1 : Checkout View; Step 2 : Login; Step 3 : Proceed to payment
* Remove standard E-commerce code from thank you along with the ecommerce.js which is included by <code>ga('require', 'ecommerce', 'ecommerce.js');</code>. If you are using a third party plugin for e-commerce tracking, you would have to disable the plugin.
* You need to include ec.js. This can be done by adding a single line of code below your default Google Analytics code snippet <code>ga('require', 'ec', 'ec.js');</code>
* If you are using the Universal Analytics Tag of Google Tag Manager, you need to replace it with a custom HTML tag with the following code. Replace UA-XXXX-Y with your property ID
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
* Next, you need to activate your plugin from the Settings page for the plugin accessed under, WooCommerce > Settings > Integration > Enhanced Ecommerce Google Analytics
* Check the “Enable Enhanced E-commerce tracking”
* For tracking the Guest users, you need to check the box. Unchecking the box might cause an uneven funnel reported to Google Analytics.

= Events captured by the Enhanced E-commerce plugin =
* Add to Cart event captured on all pages of the website
* Product Impressions event captured on the load of the product page and Add-to-Cart on category pages
* Standard three steps checkout process consisting of a) Checkout Page View; b) Login; and c) Proceed to payment
* Login step can be optional for the stores who have Guest checkout functionality enabled
* Standard thank you page event that captures transaction data


== Installation ==
1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation’s wp-content/plugins/ directory
3. Activate the plugin from the Plugins menu within the WordPress admin
4. Enter your email-address and Google Analytics ID for the plugin to enable the tracking code

== Screenshots ==
1. Enable Enhanced E-commerce for your profile/view. This is a profile / view level setting and can be accessed under Admin > View > E-commerce Settings. Also, add meaningful labels for your checkout steps. We recommend you to label as, Step 1 : Checkout View; Step 2 : Login; Step 3 : Proceed to payment;
2. Next, you need to activate your plugin from the Settings page for the plugin accessed under, WooCommerce > Settings > Integration > Enhanced Ecommerce Google Analytics
3. For tracking the Guest users, you need to check the box. Unchecking the box might cause an uneven funnel reported to Google Analytics.


== Frequently Asked Questions ==
= Where can I find the setting for this plugin? =

This plugin will add the settings to the Integration tab, to be found in the WooCommerce > Settings menu.

= Does this conflict with the WooCommerce? =

Starting the WooCommerce 2.1 release there are no conflicts. However for earlier the plugin might conflict with the default Google Analytics integration for WooCommerce.

== Changelog ==

= 1.0 - 25/06/2014 =
 * Initial release

= 1.0.6 - 11/08/2014 =
 * Added new feature - Product impressions on category page view