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
	public function get_response() {
		$person = $this->get_person();

		if ( empty( $person ) ) {
			return 'I am so sorry but I am afraid I don\'t have an answer to your question. Would you like to know what this website is about instead?';
		}

		// Get all sentences except the first one.
		$response = get_sentences( $person->post_content, 1 );

		if ( empty( $this->get_param( 'full-info' ) ) ) {
			// Get first sentence only.
			$response = get_sentences( $person->post_content, 0, 1 );

			// Add a follow up question.
			$response .= "\nWould you like to hear another fact?";
		}

		return $response;
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