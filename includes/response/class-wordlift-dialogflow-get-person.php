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
		global $wpdb;
		// Get all valid entity types.
		$types = Wordlift_Entity_Service::valid_entity_post_types();

		// Implode the types, so they can be passed to SQL query.
		$types = implode("', '", $types);

		// The person title, that we are looking for.
		$title = $this->get_param( 'person' );

		// SQL query that will retrieve the person description.
		$query = "
			SELECT p.post_content
			FROM $wpdb->posts AS p
			INNER JOIN $wpdb->terms AS t
			INNER JOIN $wpdb->term_taxonomy AS tt
				ON t.term_id = tt.term_id
			INNER JOIN $wpdb->term_relationships AS r
				ON r.term_taxonomy_id = tt.term_taxonomy_id
			WHERE p.ID = r.object_id
			AND p.post_type IN ('{$types}')
			AND tt.taxonomy = 'wl_entity_type'
			AND t.slug = 'person'
			AND p.post_title LIKE '%{$title}%'
			LIMIT 1
		";

		// Get the result
		$result = $wpdb->get_results( $query );

		// Return the person or empty array.
		return ( ! empty( $result ) ) ? $result[0] : array() ;
	}
}