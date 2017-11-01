<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/stoyan0v
 * @since             1.0.0
 * @package           Wordlift_For_Dialogflow
 *
 * @wordpress-plugin
 * Plugin Name:       Wordlift for Dialogflow
 * Plugin URI:        https://github.com/wordlift/wordlift-for-dialogflow
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            WordLift
 * Author URI:        https://github.com/stoyan0v
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordlift-for-dialogflow
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wordlift-for-dialogflow-activator.php
 */
function activate_wordlift_for_dialogflow() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordlift-for-dialogflow-activator.php';
	Wordlift_For_Dialogflow_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wordlift-for-dialogflow-deactivator.php
 */
function deactivate_wordlift_for_dialogflow() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordlift-for-dialogflow-deactivator.php';
	Wordlift_For_Dialogflow_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wordlift_for_dialogflow' );
register_deactivation_hook( __FILE__, 'deactivate_wordlift_for_dialogflow' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wordlift-for-dialogflow.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wordlift_for_dialogflow() {
	$plugin = new Wordlift_For_Dialogflow();
}
run_wordlift_for_dialogflow();
