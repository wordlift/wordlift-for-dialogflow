<?php

/**
 *
 * @link       https://github.com/stoyan0v
 * @since      1.0.0
 *
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/response
 */
class Wordlift_For_Dialogflow_Get_Topic extends Wordlift_For_Dialogflow_Response {
	/**
	 * Return the response.
	 *
	 * @access public
	 * @abstract
	 */
	public function generate_response() {
		$topic = $this->get_topic();

		// Return error message if there are no topic found.
		if ( empty( $topic ) ) {
			$this->add_text_message( 'I am afraid WordLift has not been used yet to analyze the content of this website' );
			$this->add_help_message();
			return;
		}

		$text = get_sentences( $topic->post_content, 0, 2 );

		$this->add_text_message( $text );

		// Add the topic.
		$this->add_basic_card_message(
			$topic->post_title, // Topic name.
			$text, // Topic description.
			get_permalink( $topic ), // Link to the topic.
			get_the_post_thumbnail_url( $topic ) // Add the featured image.
		);

		$this->add_help_message();
	}

	/**
	 * Retrieve single topic from database
	 *
	 * @return array The topic titles
	 */
	public function get_topic() {
		global $wpdb;

		$title = $this->get_param( 'topic' );

		// Get all valid entity types.
		$types = Wordlift_Entity_Service::valid_entity_post_types();

		// Implode the types, so they can be passed to SQL query.
		$types = implode( "', '", $types );

		// Topics query.
		$query = "
			SELECT *
			FROM {$wpdb->prefix}posts AS p
			WHERE p.post_title = '{$title}'
			AND p.post_status = 'publish'
			AND p.post_type IN ('{$types}')
			LIMIT 1;
		";

		// Make request to database.
		$result = $wpdb->get_results( $query );

		// Bail if the query return no results.
		if ( empty( $result ) ) {
			return false;
		}

		return $result[0];
	}
}
