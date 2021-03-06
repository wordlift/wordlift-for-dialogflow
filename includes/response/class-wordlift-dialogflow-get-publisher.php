<?php

/**
 *
 * @link       https://github.com/stoyan0v
 * @since      1.0.0
 *
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/response
 */
class Wordlift_For_Dialogflow_Get_Publisher extends Wordlift_For_Dialogflow_Response {

	/**
	 * The {@link Wordlift_Configuration_Service} instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var \Wordlift_Configuration_Service $configuration_service The {@link Wordlift_Configuration_Service} instance.
	 */
	private $configuration_service;

	/**
	 * {@inheritDoc}
	 */
	public function __construct( $request ) {
		parent::__construct( $request );

		// Set the SPARQL service.
		$this->set_configuration_service();
	}

	/**
	 * Return the response.
	 *
	 * @access public
	 */
	public function generate_response() {
		$publisher = $this->get_publisher();

		// Return error message if the publisher is not configured.
		if ( empty( $publisher ) ) {
			$this->add_text_message( 'Sorry, the publisher has not yet being configured on this website' );
			$this->add_help_message();
		}

		// Get publisher content.
		$publisher_content = get_sentences( $publisher->post_content, 0, 1 );
		$question          = 'Would you like to know more about them?';
		$message           = $publisher->post_title . ' is the publisher of this website.';

		// Check if we should display the full publisher info.
		if ( empty( $this->get_param( 'full-info' ) ) ) {
			// Build the response adding prompt.
			$this->add_text_message( $message );

			if ( ! empty( $publisher_content ) ) {
				// Add prompt question.
				$this->add_text_message( $question );

				// Add promp options.
				// TODO: We need to find a way to create this prompt message dynamically.
				$this->add_prompt_message(
					array(
						'Yes please',
						'No thank you',
					),
					$message . ' ' . $question
				);
			}
		} else {
			if ( ! empty( $publisher_content ) ) {
				$this->add_text_message( $publisher_content );
				$this->add_help_message();
			}
		}
	}

	/**
	 * Setup configuration service
	 *
	 * @access private
	 */
	private function set_configuration_service() {
		$this->configuration_service = new Wordlift_Configuration_Service();
	}

	/**
	 * Retrive the website publisher info.
	 *
	 * @return array Publisher post object or empty array if publisher is not found
	 */
	private function get_publisher() {
		// Get publisher ID.
		$publisher_id = $this->configuration_service->get_publisher_id();

		// Get publisher.
		$publisher = get_post( $publisher_id );

		return ( ! empty( $publisher ) ) ? $publisher : array() ;
	}
}
