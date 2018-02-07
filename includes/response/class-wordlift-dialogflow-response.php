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
	 *
	 * @return Wordlift_For_Dialogflow_Response $field
	 */
	static function factory( $request ) {

		$type = $request['result']['action'];

		$type = str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $type ) ) );

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
	 * @param string $action The request action.
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
	 * @param array $params the params.
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
	 * @param string $contexts the contexts.
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
	 * @param string $paramether The paramether we are looking for.
	 *
	 * @return array
	 */
	public function get_param( $paramether ) {
		return ( isset( $this->params[ $paramether ] ) ) ? $this->params[ $paramether ] : false ;
	}

	/**
	 * Return the response.
	 *
	 * @access public
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 * Sets the Response speech.
	 *
	 * @param string $speech The response speech.
	 */
	public function set_speech( $speech ) {
		$this->response['speech'] = $this->remove_tags( $speech );
	}

	/**
	 * Add text message.
	 *
	 * @param text $text The message text.
	 *
	 * @return void
	 */
	public function add_text_message( $text ) {
		// Build the response message.
		$message = array(
			'type'         => 'simple_response',
			'platform'     => 'google',
			'textToSpeech' => $this->remove_tags( $text ),
			'speech'       => $this->remove_tags( $text ),
			'displayText'  => $this->remove_tags( $text ),
		);

		// Set the response messages.
		$this->response['messages'][] = $message;
	}

	/**
	 * Add prompt help message after each response.
	 *
	 * @return void
	 */
	public function add_help_message() {
		$this->add_text_message( 'What else would you like to know?' );
	}

	/**
	 * Add list message.
	 *
	 * @param array $list The list that will be displayed.
	 *
	 * @return void
	 */
	public function add_list_message( $list, $title ) {
		// Build the response message.
		$message = array(
			'type'     => 'list_card',
			'items'    => $list,
			'title'    => $title,
		);

		// Set the response messages.
		$this->response['messages'][] = $message;
	}

	/**
	 * Add list message.
	 *
	 * @param string $title Card title.
	 * @param string $text Card text.
	 * @param string $link Card link.
	 * @param string $image The featured image (optional).
	 */
	public function add_basic_card_message( $title, $text, $link, $image = '' ) {
		// Build the response message.
		$message = array(
			'type'           => 'basic_card',
			'platform'       => 'google',
			'title'          => $title,
			'formattedText'  => $this->remove_tags( $text ),
			'buttons'        => array(
				array(
					'openUrlAction' => array(
						'url' => $link,
					),
					'title' => $title,
				),
			),
		);

		// Add image to the message.
		if ( ! empty( $image ) ) {
			$message['image'] = array(
				'url'               => $image,
				'accessibilityText' => ' ', // We need to add this, because the assistant triggers an error.
			);
		}

		// Set the response messages.
		$this->response['messages'][] = $message;
	}

	/**
	 * Add response messages.
	 *
	 * @param array $labels The reply options.
	 *
	 * @return void
	 */
	public function add_prompt_message( $labels, $title ) {
		// The replies array.
		$replies = array();

		// Loop through all label and fill the replies array.
		foreach ( $labels as $label ) {
			$replies[] = array(
				'title' => $this->remove_tags( $label ),
			);
		}

		// Build message object.
		$message = array(
			'type'        => 'suggestion_chips',
			'platform'    => 'google',
			'suggestions' => $replies,
			'title'       => $title,
		);

		// Set the response messages.
		$this->response['messages'][] = $message;
	}

	/**
	 * Clean all tags from text.
	 *
	 * @param string $text Text to be cleaned.
	 *
	 * @return Plain text, without any tags
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
