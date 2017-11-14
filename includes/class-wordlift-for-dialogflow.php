<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/stoyan0v
 * @since      1.0.0
 *
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/includes
 * @author     WordLift <stanimir@insideout.io>
 */
class Wordlift_For_Dialogflow {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wordlift-for-dialogflow';

		$this->load_dependencies();
		$this->set_locale();
		$this->load_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 * - Wordlift_For_Dialogflow_i18n. Defines internationalization functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordlift-for-dialogflow-i18n.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wordlift_For_Dialogflow_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Wordlift_For_Dialogflow_i18n();

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}

	/**
	 * Register all of the hooks related to the functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_rest_root' ) );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wordlift_For_Dialogflow_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Register the rest route where the Dialogflow request will .
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function register_rest_root() {
		register_rest_route(
			'wordlift-for-dialogflow/v2', // The namespace.
			'/api_webhook/', // The route.
			array(
				'methods'  => 'POST', // Method.
				'callback' => array( $this, 'handle_request' ), // Callback function.
			)
		);
	}

	/**
	 * Handle Dialogflow request.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function handle_request() {
		// Set the proper headers.
		header( 'Content-Type: application/json' );
		ob_start();

		$json    = file_get_contents( 'php://input' ); // Get the request.
		$request = json_decode( $json, true ); // Decode the input.

		// Build response class.
		$response = Wordlift_For_Dialogflow_Response::factory( $request );

		// Trigger the output generation.
		$response->generate_response();

		// Get the output.
		$output = $response->get_response();

		ob_end_clean();

		// Print the response in json format.
		echo wp_json_encode( $output );

		// Finally exit to prevent other output.
		exit;
	}

}
