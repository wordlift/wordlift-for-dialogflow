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
	 * The {@link Wordlift_Sparql_Service} instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var \Wordlift_Sparql_Service $sparql_service The {@link Wordlift_Sparql_Service} instance.
	 */
	private $sparql_service;

	public function __construct( $request ) {
		parent::__construct( $request );

		// Set the SPARQL service.
		$this->set_sparql_service();
	}

	/**
	 * Return the response.
	 *
	 * @access public
	 * @abstract
	 */
	public function get_response() {
		$topics = $this->get_topics();

		// Return error message if there are no topics found.
		if ( empty( $topics ) ) {
			return 'I am afraid WordLift has not been used yet to analyze the content of this website';
		}

		return $topics;
	}

	/**
	 * Setup the sparql service.
	 *
	 * @access public
	 * @abstract
	 */
	public function set_sparql_service() {
		$this->sparql_service = new Wordlift_Sparql_Service();
	}

	/**
	 * Retrive the main website topics.
	 *
	 * @return string The website topics
	 */
	public function get_topics() {
		$sparql = "
			PREFIX dct: <http://purl.org/dc/terms/>
			SELECT * WHERE {
				SELECT ?o (COUNT( * ) as ?count) 
				WHERE { 
					{ [] dct:references ?o } 
					UNION { [] dct:relation ?o } 
				}
			GROUP BY ?o
			}
			ORDER BY DESC(?count) 
			LIMIT 5";

		$result = $this->sparql_service->select( $sparql );
		$body   = wp_remote_retrieve_body( $result );

		if ( $body !== 'query not supported' ) {
			$topics   = str_getcsv($body, PHP_EOL);

			return 'The primary topics of this website include ' . $topics;;
		}
	}
}