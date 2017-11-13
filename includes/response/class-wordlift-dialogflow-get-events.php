<?php

/**
 *
 * @link       https://github.com/stoyan0v
 * @since      1.0.0
 *
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/response
*/
class Wordlift_For_Dialogflow_Get_Events extends Wordlift_For_Dialogflow_Response_Spqrql {
	/**
	 * Return the response.
	 *
	 * @access public
	 * @abstract
	 */
	public function generate_response() {
		$events = $this->get_event_messages();

		// Add error message if there are no events found.
		if ( empty( $events ) ) {
			$this->set_speech( 'I am sorry, there are no upcoming events on this website.' );
			return;
		}

		// Add intro message.
		$this->add_text_message( 'Here is a list with all upcoming events.' . $this->get_event_names() . '. Would you like me to read you about one of these events?' );

		// Add the list of events.
		$this->add_list_message( $events );

		// Add a follow up question.
		// TODO: We need to find a way to create this prompt message dynamically.
		$this->add_prompt_message( array(
			'Sure',
			'No thanks'
		) );
	}

	/**
	 * Get events from SPARQL request
	 * @return type
	 */
	public function get_events() {
		// Get query result.
		$events = $this->get_result();

		// Return error message if there are no events found.
		if ( empty( $events ) ) {
			return false;
		}
		// Parse the response into an array.
		$events = str_getcsv( $events, PHP_EOL );

		// Return the events.
		return $events;
	}

	/**
	 * Get events objects
	 * @return array $messages Array of message objects
	 */
	public function get_event_messages() {
		// Messages array where we will store each event message.
		$messages = array();

		// Ge the events.
		$events = $this->get_events();

		// Loop through all events and get formatted dates.
		foreach ( $events as $index => $event ) {
			// Bail if this is the first row 
			// because it contains the headings only, not an event data.
			if ( ! $index ) {
				continue;
			}

			// Put event information in messages array.
			$messages[] = $this->get_message_object( $event );
		}

		// Return the response.
		return $messages;
	}

	/**
	 * Creates event message object that will be passed to Google.
	 *
	 * @param array $event Array of event information
	 *
	 * @return string $message_object Array of event details.
	 */
	public function get_message_object( $event ) {
		// Convert the event data into an array.
		$event = explode( ',', $event );

		// Generate unique key id, based on event title.
		$key = strtoupper( sanitize_title( $event[2] ) );

		// Message object that will be read from Google.
		$message_object =  array(
			'title'       => $event[2], // Add message title.
			'description' => $this->get_event_message( $event ), // Add the message.
			'optionInfo'  => array(
				'key' => $key, // Add the event key.
			),
		);

		//Check if the event has an image and add it to the message object.
		if ( ! empty( $event[4] ) ) {
			$message_object['image'] = array(
				'url'               => $event[4],  // Add event image.
				'accessibilityText' => ' ', // We need to add this, because the assistant triggers an error.
			);
		}

		// Finally return the object.
		return $message_object;
	}

	/**
	 * Return the event names.
	 *
	 *  @return string The event names
	 */
	public function get_event_names() {

		$events = $this->get_events();

		foreach ( $events as $index => $event ) {
			if ( ! $index ) {
				continue;
			}
			$event = explode( ',', $event );

			$names[] = $event[2];
		}

		// Finally return the names.
		return implode(', ', $names);
	}

	/**
	 * Creates event message from event data.
	 *
	 * @param array $event Array of event information
	 *
	 * @return string $message Human readable message.
	 */
	public function get_event_message( $event ) {
		// Convert the date to timestamp.
		$timestamp = strtotime( $event[3] );

		// Build event message.
		$message = sprintf(
			'It will be held on %s at %s',
			date( 'F j', $timestamp ), // Add the event data.
			date( 'g:ia', $timestamp ) // Add the start hour.
		);

		return $message;
	}

	/**
	 * Set in SPARQL query which fields the message needs
	 * @return int The fields.
	 */
	public function get_select_clause() {
		return 'SELECT * ';
	}

	// TODO: Add conditional logic, based on questions
	/**
	 * Set in SPARQL query which fields the message needs
	 * @return int The fields.
	 */
	public function get_where_clause() {
		$where = "
			WHERE {
				BIND( <http://schema.org/Event> as ?type )
				{$this->get_response_fields()}
				{$this->get_filter_clause()}
			}
		";

		return $where;
	}

	/**
	 * Set the filter in SPARQL query
	 * so the events can be filtered by different params.
	 *
	 * @return string The filter.
	 */
	public function get_filter_clause() {
		return 'FILTER ( xsd:dateTime( ?startDate ) > now() )';
	}

	/**
	 * Generate the limit clause for sparql query.
	 *
	 * @return string The limit clause.
	 */
	public function get_limit_clause() {
		// Return the limit clause.
		return 'LIMIT 5';
	}

	/**
	 * Generate the limit clause for sparql query.
	 *
	 * @return string The limit clause.
	 */
	public function get_response_fields() {
		$fields = "
			?subject a ?type ;
			rdfs:label ?label ;
			schema:startDate ?startDate .
			OPTIONAL { ?subject schema:image ?image }
		";
		// Return the fields.

		return $fields;
	}
}