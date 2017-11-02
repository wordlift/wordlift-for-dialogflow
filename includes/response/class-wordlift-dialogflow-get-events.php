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
			LIMIT 5";

		// Set the SPARQL service.
		$this->set_sparql_query( $query );
	}

	/**
	 * Return the response.
	 *
	 * @access public
	 * @abstract
	 */
	public function get_response() {
		$events = $this->get_events();

		return $events;
	}

	/**
	 * Get events from SPARQL request
	 * @return type
	 */
	public function get_events() {

		$events = $this->get_result();

		// Return error message if there are no events found.
		if ( empty( $events ) ) {
			return 'I am sorry, there are no upcoming events on this website.';
		}

		$events = str_getcsv($events, PHP_EOL);

		foreach ($events as $number => $event) {
			if ( ! $number ) {
				continue;
			}

			$event = explode(',', $event);

			$response .= $number . ". \n\r" . $event[2] . "\n\r";
		}
		
		$response .= ". \n\rWould you like me to read you about one of these events? \n\r";


		return $response;
	}
}