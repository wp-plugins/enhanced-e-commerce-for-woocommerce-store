<?php

/*  Copyright 2014 Tatvic

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
/*
  Plugin Name: Enhanced E-commerce for Woocommerce store
  Plugin URI: http://www.tatvic.com/enhanced-ecommerce-google-analytics-plugin-woocommerce/
  Description: Allows Enhanced E-commerce Google Analytics tracking code to be inserted into WooCommerce store pages.
  Author: Tatvic
  Author URI: http://www.tatvic.com
  Version: 1.0.13
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add the integration to WooCommerce
function wc_enhanced_ecommerce_google_analytics_add_integration($integrations) {
    global $woocommerce;

    if (is_object($woocommerce)) {
        include_once( 'includes/class-wc-enhanced-ecommerce-google-analytics-integration.php' );
        $integrations[] = 'WC_Enhanced_Ecommerce_Google_Analytics';
    }
    return $integrations;
}

//function to call controller
function send_email_to_tatvic($email, $status) {
    $url = "http://dev.tatvic.com/leadgen/woocommerce-plugin/store_email/";
    //set POST variables
    $fields = array(
        "email" => urlencode($email),
        "domain_name" => urlencode(get_site_url()),
        "status" => urlencode($status)
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

add_filter('woocommerce_integrations', 'wc_enhanced_ecommerce_google_analytics_add_integration', 10);

//plugin action links on plugin page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tvc_plugin_action_links');

function tvc_plugin_action_links($links) {
    global $woocommerce;
    if (version_compare($woocommerce->version, "2.1", ">=")) {
        $setting_url = 'admin.php?page=wc-settings&tab=integration';
    } else {
        $setting_url = 'admin.php?page=woocommerce_settings&tab=integration';
    }
    $links[] = '<a href="' . get_admin_url(null, $setting_url) . '">Settings</a>';
    $links[] = '<a href="https://wordpress.org/plugins/enhanced-e-commerce-for-woocommerce-store/faq/" target="_blank">FAQ</a>';
    return $links;
}

//Custom Admin Header Notifications
add_action('admin_notices', 'tvc_admin_notice');

function tvc_admin_notice() {
    global $current_user, $pagenow;
    $user_id = $current_user->ID;
    global $woocommerce;
    if (version_compare($woocommerce->version, "2.1", ">=")) {
        $setting_url = 'admin.php?page=wc-settings&tab=integration';
    } else {
        $setting_url = 'admin.php?page=woocommerce_settings&tab=integration';
    }
    /* Check that the user hasn't already clicked to ignore the message */
    if (!get_user_meta($user_id, 'tvc_hide_msg') && $pagenow == 'plugins.php') {
        echo '<div class="updated" style="background: #913428; color: #fff; padding-left: 16px;"><p>';
        printf(__('Important Note: Kindly Save Again Your Enhanced Ecommerce WooCommerce Plugin Settings by visiting this <a href="' . get_admin_url(null, $setting_url) . '" style="color: #F9F76B; text-decoration: underline; font-weight: bold;">Tab</a> - Tatvic | <a href="%1$s" style="color: #fff;">Hide Notice</a>'), '?tvc_hide_msg=0');
        echo "</p></div>";
    }
}

add_action('admin_init', 'tvc_custom_header_notification');

function tvc_custom_header_notification() {
    global $current_user;
    $user_id = $current_user->ID;
    /* If user clicks to ignore the notice, add that to their user meta */
    if (isset($_GET['tvc_hide_msg']) && '0' == $_GET['tvc_hide_msg']) {
        add_user_meta($user_id, 'tvc_hide_msg', 'true', true);
    }
}

//function to catch Plugin activation
function ee_plugin_activate() {
    $PID = "enhanced_ecommerce_google_analytics";
    $chk_Settings = get_option('woocommerce_' . $PID . '_settings');
    if ($chk_Settings) {
        if (array_key_exists("ga_email", $chk_Settings)) {
            send_email_to_tatvic($chk_Settings['ga_email'], 'active');
        }
    }
}

//function to catch Plugin deactivation
function ee_plugin_dectivate() {
    if (!current_user_can('activate_plugins'))
        return;
    $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
    $chk_nonce = check_admin_referer("deactivate-plugin_{$plugin}");

    $PID = "enhanced_ecommerce_google_analytics";
    $chk_Settings = get_option('woocommerce_' . $PID . '_settings');

    if ($chk_nonce && $chk_Settings) {
        if (array_key_exists("ga_email", $chk_Settings)) {
            send_email_to_tatvic($chk_Settings['ga_email'], 'inactive');
        }
    }
}

//function to catch Plugin deletion
function ee_plugin_delete() {

    if (!current_user_can('activate_plugins'))
        return;

    $chk_nonce = check_admin_referer('bulk-plugins');

    if ($_GET['action'] == 'delete-selected') {
        $PID = "enhanced_ecommerce_google_analytics";
        $chk_Settings = get_option('woocommerce_' . $PID . '_settings');
        if ($chk_nonce && $chk_Settings) {
            if (array_key_exists("ga_email", $chk_Settings)) {
                send_email_to_tatvic($chk_Settings['ga_email'], 'delete');
            }
        }
    }
}

register_activation_hook(__FILE__, 'ee_plugin_activate');
register_deactivation_hook(__FILE__, 'ee_plugin_dectivate');
register_uninstall_hook(__FILE__, 'ee_plugin_delete');
?>
