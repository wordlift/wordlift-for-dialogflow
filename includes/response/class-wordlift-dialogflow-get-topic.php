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
			$this->set_speech( 'I am afraid WordLift has not been used yet to analyze the content of this website' );
			return;
		}

		$this->add_text_message( 'Here is some information: ' );

		// Add the topic.
		$this->add_basic_card_message(
			$topic->post_title, // Topic name.
			$topic->post_content, // Topic description.
			$topic->guid, // Link to the topic.
			get_the_post_thumbnail_url( $topic ) // Add the featured image.
		);
	}

	/**
	 * Retrieve single topic from database
	 *
	 * @return array The topic titles
	 */
	public function get_topic() {
		global $wpdb;

		$title = $this->get_param( 'topic' );

		// Topics query.
		$query = "
			SELECT *
			FROM {$wpdb->prefix}posts AS p
			WHERE p.post_title = '{$title}'
			AND p.post_status = 'publish'
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