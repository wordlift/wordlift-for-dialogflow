<?php

/**
 *
 * @link       https://github.com/stoyan0v
 * @since      1.0.0
 *
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/response
*/
class Wordlift_For_Dialogflow_Get_Website_Info extends Wordlift_For_Dialogflow_Response {
	/**
	 * Return the response.
	 *
	 * @access public
	 * @abstract
	 */
	public function generate_response() {
		$topics = $this->get_topics();

		// Return error message if there are no topics found.
		if ( empty( $topics ) ) {
			$this->set_speech( 'I am afraid WordLift has not been used yet to analyze the content of this website' );
			return;
		}

		// Add intro message.
		$this->add_text_message( 'The primary topics of this website include: ' );

		// Add each event as message.
		foreach ( $topics as $message ) {
			$this->add_text_message( $message );
		}
	}

	/**
	 * Retrive the main website topics.
	 *
	 * @return string The website topics
	 */
	public function get_topics() {
		global $wpdb;

		// Topics query.
		$query = "
			SELECT p.post_title AS title, COUNT( wlr.object_id ) AS count
			FROM {$wpdb->prefix}wl_relation_instances AS wlr
			INNER JOIN {$wpdb->prefix}posts AS p
			WHERE p.ID = wlr.object_id
			GROUP BY wlr.object_id
			ORDER BY count DESC
			LIMIT 10;
		";

		// Make request to database.
		$result = $wpdb->get_results( $query );

		// Bail if the query return no results.
		if ( empty( $result ) ) {
			return false;
		}

		// Get the topic titles only.
		$topics = wp_list_pluck( $result, 'title' );

		return $topics;
	}
}