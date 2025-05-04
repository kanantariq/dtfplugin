<?php
/*
Plugin Name: DTF Custom Designer
Description: Product-based custom DTF designer for WooCommerce.
Version: 1.0
Author: Custom Plugin Dev
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Constants
define( 'DTF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DTF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Includes
include_once DTF_PLUGIN_PATH . 'includes/hooks.php';
include_once DTF_PLUGIN_PATH . 'includes/functions.php';
include_once DTF_PLUGIN_PATH . 'includes/pricing-logic.php';

// Admin settings page
if ( is_admin() ) {
  include_once DTF_PLUGIN_PATH . 'admin/settings-page.php';
}

// Enqueue Scripts & Styles
function dtf_enqueue_assets() {
  wp_enqueue_style( 'dtf-style', DTF_PLUGIN_URL . 'assets/css/style.css' );
  wp_enqueue_script( 'dtf-script', DTF_PLUGIN_URL . 'assets/js/designer.js', array('jquery'), null, true );
  wp_localize_script( 'dtf-script', 'dtf_vars', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
  ) );
}
add_action( 'wp_enqueue_scripts', 'dtf_enqueue_assets' );

add_action('woocommerce_before_single_product_summary', 'dtf_custom_designer_display', 15);
function dtf_custom_designer_display() {
    if (!get_post_meta(get_the_ID(), '_dtf_enabled', true)) return;
    include plugin_dir_path(__FILE__) . 'partials/dtf-static-layout.php';
}


// Add settings link on plugin page
function dtf_custom_designer_settings_link($links) {
  $settings_link = '<a href="admin.php?page=dtf-custom-designer-settings">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}
add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'dtf_custom_designer_settings_link');

// Add settings menu
function dtf_custom_designer_add_admin_menu() {
  add_menu_page('DTF Designer Settings', 'DTF Designer', 'manage_options', 'dtf-custom-designer-settings', 'dtf_custom_designer_settings_page');
}
add_action('admin_menu', 'dtf_custom_designer_add_admin_menu');

// Register settings
function dtf_custom_designer_settings_init() {
  register_setting('dtfCustomDesigner', 'dtf_custom_designer_pricing');
  register_setting('dtfCustomDesigner', 'dtf_custom_min_size_enabled');
}
add_action('admin_init', 'dtf_custom_designer_settings_init');

// Settings page content
function dtf_custom_designer_settings_page() {
  ?>
  <div class="wrap">
      <h1>DTF Custom Designer Settings</h1>
      <form method="post" action="options.php">
          <?php
          settings_fields('dtfCustomDesigner');
          do_settings_sections('dtfCustomDesigner');
          ?>
          <h2>Pricing Slabs (JSON format)</h2>
          <textarea name="dtf_custom_designer_pricing" rows="10" cols="80"><?php echo esc_textarea(get_option('dtf_custom_designer_pricing')); ?></textarea>
          <h2>Minimum Size Restriction</h2>
          <label><input type="checkbox" name="dtf_custom_min_size_enabled" value="1" <?php checked(1, get_option('dtf_custom_min_size_enabled'), true); ?> /> Enable 2x2 inch minimum restriction</label>
          <?php submit_button(); ?>
      </form>
  </div>
  <?php
}

?>