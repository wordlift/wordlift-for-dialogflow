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

	public function __construct( $request ) {
		parent::__construct( $request );

		$query = "
			SELECT * WHERE {
				BIND( <http://schema.org/Event> as ?type )
				?subject a ?type ;
				rdfs:label ?label ;
				schema:startDate ?startDate .
				FILTER ( xsd:dateTime( ?startDate ) > now() )
			}
			LIMIT 3";

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
			$this->set_speech( 'I am sorry, there are no upcoming events on this website.' );
			return;
		}

		// Add intro message
		$this->add_text_message( 'Here is a list with all upcoming events:' );

		// Add each event as message.
		foreach ( $events as $message ) {
			$this->add_text_message( $message );
		}
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

		// Loop through all events and get formatted dates.
		foreach ( $events as $number => $event ) {
			// Bail if this is the first row 
			// because it contains the headings only, not an event data.
			if ( ! $number ) {
				continue;
			}

			// Convert the event data into an array.
		    $event = explode( ',', $event );

		    // Convert the date to timestamp.
            $timestamp = strtotime( $event[3] );

            // Build event message.
            $message = sprintf(
            	'%d. %s which will be held on %s at %s',
            	$number, // Add number index.
            	$event[2], // Add event name.
            	date( 'F j', $timestamp ), // Add the event data.
            	date( 'g:ia', $timestamp ) // Add the start hour.
            );

            // Put event information in messages array.
            $messages[] = $message;
		}

		// Return the response.
		return $messages;
	}
}