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
	 * Set in SPARQL query which fields the message needs
	 * @return int The select clause.
	 */
	public function get_select_clause() {
		return 'SELECT ?description';
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
		if ( $this->get_param( 'title' ) ) {
			$title = $this->get_param( 'title' );
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
			schema:description ?description ;
		";
		// Return the fields.

		return $fields;
	}

}
