<?php
/**
 * The file that defines the Wordlift_For_Dialogflow_Get_Event.
 * All single event request will be handled by this class.
 *
 * @link       https://github.com/stoyan0v
 * @since      1.0.0
 *
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/response
 */
class Wordlift_For_Dialogflow_Get_Event extends Wordlift_For_Dialogflow_Get_Events {
	/**
	 * Return the response.
	 *
	 * @access public
	 * @abstract
	 */
	public function generate_response() {
		$events = $this->get_events();

		// Add error message if there are no events found.
		if ( empty( $events ) ) {
			$this->add_text_message( 'I am sorry, there are no upcoming events on this website' );
			$this->add_help_message();
			return;
		}

		// Add event massage, based on the request.
		$this->add_event_message( $events[0] );
		$this->add_help_message();
	}

	/**
	 * Set the event message depending of the question
	 *
	 * @param array $event The event data.
	 * @return void
	 */
	public function add_event_message( $event ) {
		if ( $this->get_param( 'event-info' ) ) {

			$text = get_sentences( $event['description']['value'], 0, 2 );

			// Add the event name as first message.
			$this->add_text_message( $event['label']['value'] );

		} elseif ( $this->get_param( 'when' ) ) {
			$text = $this->get_when_message( $event );
		} elseif ( $this->get_param( 'where' ) ) {
			$text = $this->get_where_message( $event );
		} elseif ( $this->get_param( 'speaker' ) ) {
			$text = $this->get_speaker( $event );
		}

		// Add the speech.
		$this->add_text_message( $text );

		// Add the event card iamge.
		$this->add_basic_card_message(
			$event['label']['value'], // Event name.
			$text, // Event description.
			$this->get_permalink( $event['subject']['value'] ), // Link to the topic.
			$event['image']['value'] // Add the featured image.
		);
	}

	public function is_event_running( $event ) {
		// Return the description if the params are missing.
		if (
			empty( $event['startDate']['value'] ) ||
			empty( $event['endDate']['value'] )
		) {
			return;
		}

		$now        = $this->now();
		$start_date = strtotime( $event['startDate']['value'] ) + 3600;
		$end_date   = strtotime( $event['endDate']['value'] ) + 3600;

		if ( $now > $start_date && $now < $end_date ) {
			$text = 'The event is running now, hurry up.';
		}

		return $text;
	}

	/**
	 * Get the message for "Where" questions
	 *
	 * @param array $event The event.
	 * @return string The message
	 */
	public function get_where_message( $event ) {
		$message = sprintf(
			'The %s will be at %s',
			$event['label']['value'],
			$event['place']['value']
		);

		return $message;
	}

	/**
	 * Get the message for "When" questions
	 *
	 * @param array $event The event.
	 * @return string The message
	 */
	public function get_when_message( $event ) {
		// Convert the date string to timestamp.
		$timestamp = strtotime( $event['startDate']['value'] ) + 3600;

		$message = sprintf(
			'%s will start on %s at %s',
			$event['label']['value'],
			date( 'F j', $timestamp ), // Add the event data.
			date( 'g:ia', $timestamp ) // Add the time.
		);

		// Chech is the event is running now.
		$is_running_message = $this->is_event_running( $event );

		if ( ! empty( $is_running_message ) ) {
			$message = $is_running_message;
		}

		return $message;
	}

	/**
	 * Retrieve the event speaker.
	 *
	 * @param  array  $event   The event data. 
	 * @return string $message The speaker message
	 */
	public function get_speaker( $event ) {
		// Add the default message.
		$message = 'Sorry but there is no information about the speaker at this time.';

		// Check if the event has a speaker.
		if ( ! empty( $event['speaker']['value'] ) ) {
			$message = sprintf(
				'%s is the speaker of %s',
				$event['speaker']['value'], // Add the speaker.
				$event['label']['value'] // Add the event name.
			);
		}

		return $message;
	}

	/**
	 * Generate the limit clause for sparql query.
	 *
	 * @return string The limit clause.
	 */
	function get_limit_clause() {
		// It's a single event so we will always return only one event.
		return 'LIMIT 1';
	}

	/**
	 * Set the filter in SPARQL query
	 * so the events can be filtered by different params
	 *
	 * @return int The filter.
	 */
	public function get_filter_clause() {
		if ( $this->get_param( 'event' ) ) {
			$title = $this->get_param( 'event' );
			$filter = "
				FILTER ( STR( ?startDate ) != '' && STR( ?endDate ) != '' ) .
				FILTER ( ?label='{$title}'@en )
			";
		} elseif ( $this->get_param( 'upcoming' ) ) {
			$filter = parent::get_filter_clause();
		}

		return $filter;

	}

	/**
	 * Adds sparql query select clause.
	 *
	 * @return string The select statement.
	 */
	public function get_select_clause() {
		$date = $this->now();

		return "SELECT
			?subject
			?label
			?startDate
			( xsd:dateTime( ?startDate ) >= '{$date}'^^xsd:dateTime AS ?future )
			?endDate
			?description
			?image
			( SAMPLE( ?place ) AS ?place )
			( SAMPLE( ?speaker ) AS ?speaker )
		";
	}

	/**
	 * Adds sparql query order clause.
	 *
	 * @return string The order clause.
	 */
	public function get_order_clause() {
		// Return the order clause.
		return 'ORDER BY DESC(?future) ASC( ?startDate ) ';
	}

	/**
	 * Get the event permalink using entity_url meta value
	 *
	 * @param string $meta_value The entity url.
	 * @return mixed The filter.
	 */
	public function get_permalink( $meta_value ) {
		// Get the global $wpdp.
		global $wpdb;

		// The query to retrieve the post id.
		$sql = "
			SELECT post_id
			FROM $wpdb->postmeta 
			WHERE meta_key = 'entity_url'
			AND meta_value = '{$meta_value}'
		";

		// Make a database request.
		$results = $wpdb->get_results( $sql );

		// Return permalink if the post id is found.
		if ( ! empty( $results ) ) {
			return get_permalink( $results[0]->post_id );
		}

		// There is no event id found, so return false.
		return false;
	}
}
