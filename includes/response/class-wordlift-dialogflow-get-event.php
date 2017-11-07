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

	public function __construct( $request ) {
		parent::__construct( $request );

		// Build the query.
		$query = $this->build_sparql_query();

		// Set the SPARQL service.
		$this->set_sparql_query( $query );
	}

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

		// Add the event information.
		$this->add_text_message( $events[1] );

		// Add prompt heading.
		$this->add_text_message( 'Would you like me to read you about another upcoming event from this website?' );

		// Add a follow up question.
		$this->add_prompt_message( array(
			'Sure',
			'No thanks',
		) );
	}


	/**
	 * Creates event message from event data.
	 *
	 * @param array $event Array of event information
	 *
	 * @return string $message Human readable message.
	 */
	public function get_event_message( $event ) {
		// Convert the event data into an array.
		$event = explode( ',', $event );

		// Convert the date to timestamp.
		$timestamp = strtotime( $event[3] );

		// Build event message.
		$message = sprintf(
			'%d. %s which will be held on %s at %s',
			$event[2], // Add event name.
			date( 'F j', $timestamp ), // Add the event data.
			date( 'g:ia', $timestamp ) // Add the start hour.
		);

		return $message;
	}

	/**
	 * Generate new sparql query based on user request.
	 *
	 * @return string The new sparql query
	*/
	public function build_sparql_query() {
		$query = "
			SELECT {$this->get_select()} WHERE {
				BIND( <http://schema.org/Event> as ?type )
				?subject a ?type ;
				rdfs:label ?label ;
				schema:description ?description ;
				{$this->get_filter()}
			}
			LIMIT 1
		";

		return $query;
	}

	// TODO: Add conditional logic, based on questions
	/**
	 * Set in SPARQL query which fields the message needs
	 * @return int The fields.
	 */
	function get_select() {
		return '?description';
	}

	// TODO: Add conditional logic, based on questions
	/**
	 * Set the filter in SPARQL query
	 * so the events can be filtered by different params
	 *
	 * @return int The filter.
	 */
	function get_filter() {
		$title = 'Making Websites Talk';
		return "FILTER ( ?label='{$title}'@en )";
	}
}
