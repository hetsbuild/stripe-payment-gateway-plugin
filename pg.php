<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://example.com/
 * @since             1.0.0
 * @package           Pg
 *
 * @wordpress-plugin
 * Plugin Name:       Stripe Payments
 * Plugin URI:        https://https://example.com/
 * Description:       Stripe Payments integration enables secure online payments through Stripe Checkout.
 * Version:           1.0.0
 * Author:            Het Agravat
 * Author URI:        https://https://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pg
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PG_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pg-activator.php
 */
function activate_pg() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pg-activator.php';
	Pg_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pg-deactivator.php
 */
function deactivate_pg() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pg-deactivator.php';
	Pg_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pg' );
register_deactivation_hook( __FILE__, 'deactivate_pg' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pg.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pg() {

	$plugin = new Pg();
	$plugin->run();

}
run_pg();

