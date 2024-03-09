<?php
/**
 * Plugin Name: VHC WP Show IDs
 * Plugin URI: https://github.com/vijayhardaha/vhc-wp-show-ids
 * Description: Shows IDs on all post, page, media list, user and taxonomy pages.
 * Version: 1.0.0
 * Author: Vijay Hardaha
 * Author URI: https://twitter.com/vijayhardaha/
 * License: GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vhc-wp-show-ids
 * Domain Path: /languages/
 * Requires at least: 5.4
 * Requires PHP: 5.6
 * Tested up to: 6.0
 *
 * @package VHC_WP_Show_Ids
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! defined( 'VHC_WP_SHOW_IDS_PLUGIN_FILE' ) ) {
	define( 'VHC_WP_SHOW_IDS_PLUGIN_FILE', __FILE__ );
}

// Include the main VHC_WP_Show_Ids class.
if ( ! class_exists( 'VHC_WP_Show_Ids', false ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-vhc-wp-show-ids.php';
}

new VHC_WP_Show_Ids();
