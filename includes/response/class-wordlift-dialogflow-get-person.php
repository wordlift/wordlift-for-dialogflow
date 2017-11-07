<?php

/**
 *
 * @link       https://github.com/stoyan0v
 * @since      1.0.0
 *
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/response
*/
class Wordlift_For_Dialogflow_Get_Person extends Wordlift_For_Dialogflow_Response {

	/**
	 * Return the response.
	 *
	 * @access public
	 * @abstract
	 */
	public function generate_response() {
		$person = $this->get_person();

		if ( empty( $person ) ) {
			$this->set_speech( 'I am so sorry but I am afraid I don\'t have an answer to your question. Would you like to know what this website is about instead?' );
			return;
		}

		if ( empty( $this->get_param( 'full-info' ) ) ) {
			// Get first sentence only.
			$text = get_sentences( $person->post_content, 0, 1 );

			// Add the message.
			$this->add_text_message( $text );

			// Add promp message
			$this->add_text_message( 'Would you like to hear another fact?' );

			// Add promp options.
			// TODO: We need to find a way to create this prompt message dynamically
			$this->add_prompt_message( array(
				'Yes please',
				'No thanks',
			) );
		} else {
			// Get all sentences except the first one.
			$text = get_sentences( $person->post_content, 1 );

			$this->add_text_message( $text );
		}

	}

	/**
	 * Retrive the person entity from database.
	 * @return objects The entity post object.
	 */
	public function get_person() {
		// Get all valid entity types.
		$types = Wordlift_Entity_Service::valid_entity_post_types();

		// Query args.
		$args = array(
			's'             => $this->get_param( 'person' ), // The search string.
			'post_per_page' => 1,
			'post_type'     => $types,
			'tax_query'     => array(
				array(
					'taxonomy' => 'wl_entity_type',
					'terms'    => 'person',
					'field'    => 'slug'
				)
			)
		);

		// Make the request.
		$posts = get_posts( $args );

		// Return the person or empty array.
		return ( ! empty( $posts ) ) ? $posts[0] : array() ;
	}
}