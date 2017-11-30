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
class Wordlift_For_Dialogflow_Get_Related_Events extends Wordlift_For_Dialogflow_Get_Events {
	/**
	 * Return the response.
	 *
	 * @access public
	 * @abstract
	 */
	public function generate_response() {
		$events = $this->get_event_messages();

		// Add error message if there are no events found.
		if ( empty( $events ) || empty( $this->get_param( 'relation' ) ) ) {
			$this->set_speech( 'I am sorry, there are no upcoming events. Maybe try something else.' );
			return;
		}

		$intro = 'Here is a list with all upcoming events.';

		// Add intro message.
		$this->add_text_message( $intro . $this->get_event_names() );

		// Add the list of events.
		$this->add_list_message( $events, $intro );

	}

	/**
	 * Adds sparql query filter clause.
	 *
	 * @return string The filter.
	 */
	public function get_filter_clause() {
		$date     = $this->now();
		$relation = $this->get_param( 'relation' );

		return "
			FILTER( STR( ?startDate ) != '' ) .
			FILTER( STR( ?endDate ) != '' ) .
			FILTER( xsd:dateTime( ?endDate ) > '{$date}'^^xsd:dateTime )
			FILTER ( REGEX( ?relation_1, '{$relation}', 'i' )  ) .
		";
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
			schema:endDate ?endDate ;
			dct:relation/rdfs:label ?relation_1 .
			OPTIONAL { ?subject schema:image ?image } .
			OPTIONAL { ?subject dct:relation ?relation . FILTER( REGEX( ?relation, '/speaker/' ) ) } .
			OPTIONAL { ?relation rdfs:label ?speaker } .
		";

		// Return the fields.
		return $fields;
	}
}
