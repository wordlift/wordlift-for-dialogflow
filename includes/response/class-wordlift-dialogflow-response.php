<?php

/**
 * Abstract class that handle and process each request.
 * Should be extended for each Dialogflow request.
 *
 * @link       https://github.com/stoyan0v
 * @since      1.0.0
 *
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/response
*/
abstract class Wordlift_For_Dialogflow_Response {

	/**
	 * Request action.
	 *
	 * @access protected
	 * @var string
	 */
	protected $action;

	/**
	 * Request parameters.
	 *
	 * @access protected
	 * @var array
	 */
	protected $params;

	/**
	 * Request contexts.
	 *
	 * @access protected
	 * @var array
	 */
	protected $contexts;

	/**
	 * Final response message.
	 *
	 * @access protected
	 * @var array
	 */
	protected $response;

	/**
	 * Constructor
	 *
	 * @param array $request The Dialogflow request params.
	 */
	public function __construct( $request ) {
		// Set action.
		$this->set_action( $request['result']['action'] );

		// Set parameters.
		$this->set_params( $request['result']['parameters'] );

		// Set contexts.
		$this->set_contexts( $request['result']['contexts'] );
	}

	/**
	 * Register a new administration settings field of a certain type.
	 *
	 * @static
	 *
	 * @param array $request The Dialogflow request.

	 * @return Wordlift_For_Dialogflow_Response $field
	 */
	static function factory( $request ) {

		$type = $request['result']['action'];

		$type = str_replace( " ", '_', ucwords( str_replace( "-", ' ', $type ) ) );

		// Class name.		
		$class = 'Wordlift_For_Dialogflow_' . $type;

		// Throw error if the field type doens't exists.
		if ( ! class_exists( $class ) ) {
			throw new Exception( 'Unknown request type "' . $type . '".' );
		}

		$field = new $class( $request );

		return $field;
	}

	/**
	 * Sets the Request action.
	 *
	 * @param string $action the action
	 */
	public function set_action( $action ) {
		$this->action = $action;
	}

	/**
	 * Gets the Request action.
	 *
	 * @return string
	 */
	public function get_action() {
		return $this->action;
	}

	/**
	 * Sets the Request parameters.
	 *
	 * @param array $params the params
	 */
	public function set_params( $params ) {
		$this->params = $params;
	}

	/**
	 * Gets the Request parameters.
	 *
	 * @return array
	 */
	public function get_params() {
		return $this->params;
	}

	/**
	 * Sets the Request contexts.
	 *
	 * @param array $contexts the contexts
	 */
	public function set_contexts( $contexts ) {
		$this->contexts = $contexts;
	}

	/**
	 * Gets the Request contexts.
	 *
	 * @return array
	 */
	public function get_contexts() {
		return $this->contexts;
	}

	/**
	 * Gets a single parameter from parameters.
	 *
	 * @return array
	 */
	public function get_param( $paramether ) {
		return ( isset( $this->params[ $paramether ] ) ) ? $this->params[ $paramether ] : false ;;
	}

	/**
	 * Return the response.
	 *
	 * @access public
	 * @abstract
	 */
	public function get_response() {
		return $this->response;
	}
 
	/**
	 * Sets the Response speech.
	 *
	 * @param string $speech The response speech
	 *
	 * @return self
	 */
	public function set_speech( $speech ) {
		$this->response['speech'] = $this->remove_tags( $speech );
	}
 
	/**
	 * Add text message.
	 *
	 * @param text $text The message text
	 *
	 * @return void
	 */
	public function add_text_message( $text ) {
		// Build the response message.
		$message = array(
			'type'   => 0,
			'speech' => $this->remove_tags( $text ),
		);

		// Set the response messages.
		$this->response['messages'][] = $message;
	}

	/**
	 * Add response messages.
	 *
	 * @param text $text The message text
	 * @param array $replies The reply options.
	 *
	 * @return void
	 */
	public function add_prompt_message( $text, $replies ) {
		// Build the response message.
		$message = array(
			'type'    => 2,
			'title'   => $this->remove_tags( $text ),
			'replies' => $replies,
		);

		// Set the response messages.
		$this->response['messages'][] = $message;
	}

	/**
	 * Clean all tags from text.
	 *
	 * @param string $text Text to be cleaned
	 *
	 * @return self
	 */
	public function remove_tags( $text ) {
		return wp_kses( $text, array() );
	}

	/**
	 * Generate the response message.
	 *
	 * @access public
	 * @abstract
	 */
	abstract public function generate_response();

}