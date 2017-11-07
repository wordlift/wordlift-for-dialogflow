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

		// Add the topics.
		$this->add_list_message( $topics );
	}

	/**
	 * Retrieve main topics from database
	 *
	 * @return array The topic titles
	 */
	public function get_topics_data() {
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

	/**
	 * Retrive the main topics of the website.
	 *
	 * @return array $topic_messages Array of topic messages that can be read by Google.
	 */
	public function get_topics() {
		// The topic messages.
		$topic_messages = array();

		// Get topic titles from database.
		$topics = $this->get_topics_data();

		// Loop throught all topic and build the message object
		foreach ( $topics as $topic ) {
			$topic_messages[] = $this->get_topic_object( $topic );
		}

		// Return the messages.
		return $topic_messages;
	}

	/**
	 * Creates topic message object that will be passed to Google.
	 *
	 * @param string $topic The topic title
	 *
	 * @return string $message_object Array of topic details.
	 */
	public function get_topic_object( $topic ) {
		// Generate unique key id, based on topic title.
		$key = strtoupper( sanitize_title( $topic ) );

		// Message object that will be read from Google.
		$message_object = array(
			'title'      => $topic, // Add message title.
			'optionInfo' => array(
				'key' => $key, // Add the topic key.
			),
		);

		// Finally return the object.
		return $message_object;
	}
}