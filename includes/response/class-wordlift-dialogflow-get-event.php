<?php

/**
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
			$this->set_speech( 'I am sorry, there are no upcoming events on this website' );
			return;
		}

		$this->add_event_message( $events );

	}
	/**
	 * Set the event message depending of the question
	 * @param array $events The event data
	 * @return void
	 */
	public function add_event_message( $events ) {
		if ( $this->get_param( 'event-info' ) ) {
			$message = $events[1];
		} elseif ( $this->get_param( 'when' ) ) {
			$message = $this->set_when_message( $events[1] );
		} elseif ( $this->get_param( 'where' ) ) {
			$message = $this->set_where_message( $events[1] );
		}

		$this->add_text_message( $message );
	}

	/**
	 * Set the message for "Where" questions
	 * @param array $event The event
	 * @return string The message
	 */
	public function set_where_message( $event ) {
		$event = explode( ',', $event );
		$message = sprintf(
			'The %s will be at %s',
			$event[2],
			$event[3]
		);

		return $message;
	}

	/**
	 * Set the message for "When" questions
	 * @param array $event The event
	 * @return string The message
	 */
	public function set_when_message( $event ) {
		$event = explode( ',', $event );
		$message = sprintf(
			'%s will start at %s',
			$event[2],
			date( 'g:ia', strtotime( $event[4] ) )
		);

		return $message;
	}

	/**
	 * Set in SPARQL query which fields the message needs
	 * @return int The select clause.
	 */
	public function get_select_clause() {
		$select = '*';

		if ( $this->get_param( 'event-info' ) ) {
			$select = '?description';
		}

		return "SELECT $select";
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
			return "FILTER ( ?label='{$title}'@en )";
		}
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
		";

		if ( $this->get_param( 'event-info' ) ) {
			$fields .= "schema:description ?description ;";
		} elseif ( $this->get_param( 'where' ) ) {
			$fields .= "schema:location/dct:title ?place";
		} else {
			$fields .= "
				schema:location ?location ;
                schema:startDate ?startDate;
            ";
		}
		// Return the fields.

		return $fields;
	}

}
