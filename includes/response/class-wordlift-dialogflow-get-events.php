<?php
/**
 * The file that defines the Wordlift_For_Dialogflow_Get_Events.
 * Next events query will be handled by this class.
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

		$intro    = 'Here is a list with all upcoming events.';
		$question = 'Would you like me to read you about one of these events?';

		// Add intro message.
		$this->add_text_message( $intro . $this->get_event_names() . '. ' . $question );

		// Add the list of events.
		$this->add_list_message( $events, $intro );

		// Add a follow up question.
		// TODO: We need to find a way to create this prompt message dynamically.
		$this->add_prompt_message(
			array(
				'Yes please',
				'No thanks',
			),
			$question
		);
	}

	/**
	 * Get events from SPARQL request
	 *
	 * @return array $events Array of all upcoming events.
	 */
	public function get_events() {
		// Get query result.
		$events = $this->get_result();

		// Return error message if there are no events found.
		if ( empty( $events ) ) {
			return false;
		}

		// Return the events.
		return $events;
	}

	/**
	 * Get events objects
	 *
	 * @return array $messages Array of message objects
	 */
	public function get_event_messages() {
		// Messages array where we will store each event message.
		$messages = array();

		// Ge the events.
		$events = $this->get_events();

		// Loop through all events and get formatted dates.
		foreach ( $events as $event ) {
			// Put event information in messages array.
			$messages[] = $this->get_message_object( $event );
		}

		// Return the response.
		return $messages;
	}

	/**
	 * Creates event message object that will be passed to Google.
	 *
	 * @param array $event Array of event information.
	 *
	 * @return string $message_object Array of event details.
	 */
	public function get_message_object( $event ) {
		// Generate unique key id, based on event title.
		$key = strtoupper( sanitize_title( $event['label']['value'] ) );

		// Message object that will be read from Google.
		$message_object = array(
			'title'       => $event['label']['value'], // Add message title.
			'description' => $this->get_event_message( $event ), // Add the message.
			'optionInfo'  => array(
				'key' => $key, // Add the event key.
			),
		);

		// Check if the event has an image and add it to the message object.
		if ( ! empty( $event['image']['value'] ) ) {
			$message_object['image'] = array(
				'url'               => $event['image']['value'],  // Add event image.
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
		// Get events data.
		$events = $this->get_events();

		// Loop through all events and get event names.
		foreach ( $events as $event ) {
			$names[] = $event['label']['value'];
		}

		// Finally return the names.
		return implode( ', ', $names );
	}

	/**
	 * Creates event message from event data.
	 *
	 * @param array $event Array of event information.
	 *
	 * @return string $message Human readable message.
	 */
	public function get_event_message( $event ) {
		// Convert the date to timestamp.
		$timestamp = strtotime( $event['startDate']['value'] );

		// Build event message.
		$message = sprintf(
			'It will be held on %s at %s',
			date( 'F j', $timestamp ), // Add the event data.
			date( 'g:ia', $timestamp ) // Add the start hour.
		);

		return $message;
	}

	/**
	 * Returns timestamp for San Francisco
	 *
	 * @return string San Francisco time.
	 */
	public function now() {
		$now = date_format( date_create(), "Y-m-d\TH:i:s+09:00" );

		return $now;
	}

	/**
	 * Adds sparql query select clause.
	 *
	 * @return string The select statement.
	 */
	public function get_select_clause() {
		return "SELECT
			?subject
			?label
			?startDate
			?endDate
			?description
			?image
			( SAMPLE( ?place ) AS ?place )
			( SAMPLE( ?speaker ) AS ?speaker )
		";
	}

	/**
	 * Adds sparql query where clause.
	 *
	 * @return string The where statement.
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
	 * Adds sparql query filter clause.
	 *
	 * @return string The filter.
	 */
	public function get_filter_clause() {
		$date = $this->now();

		return "
			FILTER( STR( ?startDate ) != '' ) .
			FILTER( STR( ?endDate ) != '' ) .
			FILTER( xsd:dateTime( ?endDate ) > '{$date}'^^xsd:dateTime )
		";
	}

	/**
	 * Adds sparql query group clause.
	 *
	 * @return string The group clause.
	 */
	public function get_group_clause() {
		return 'GROUP BY ?subject ?label ?startDate ?endDate ?description ?image ';
	}

	/**
	 * Adds sparql query limit clause.
	 *
	 * @return string The limit clause.
	 */
	public function get_limit_clause() {
		// Return the limit clause.
		return 'LIMIT 5 ';
	}

	/**
	 * Adds sparql query order clause.
	 *
	 * @return string The order clause.
	 */
	public function get_order_clause() {
		// Return the order clause.
		return 'ORDER BY ASC (?startDate) ';
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
			schema:description ?description ;
			schema:location/dct:title ?place ;
			schema:startDate ?startDate ;
			schema:endDate ?endDate .
			OPTIONAL { ?subject schema:image ?image } .
			OPTIONAL { ?subject dct:relation ?relation . FILTER( REGEX( ?relation, '/speaker/' ) ) } .
			OPTIONAL { ?relation rdfs:label ?speaker } .
		";

		// Return the fields.
		return $fields;
	}
}
