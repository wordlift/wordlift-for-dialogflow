<?php

/**
 *
 * @link       https://github.com/stoyan0v
 * @since      1.0.0
 *
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/response
*/
abstract class Wordlift_For_Dialogflow_Response_Spqrql extends Wordlift_For_Dialogflow_Response {

	/**
	 * The {@link Wordlift_Sparql_Service} instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var \Wordlift_Sparql_Service $sparql_service The {@link Wordlift_Sparql_Service} instance.
	 */
	private $sparql_service;

	/**
	 * The SPARQL query.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private $sparql_query;

	public function __construct( $request ) {
		parent::__construct( $request );

		// Set the SPARQL service.
		$this->set_sparql_service();
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
	 * Make the SPARQL request and return the result if there is such
	 * @return mixed The response body or false if it fails.
	 */
	public function get_result() {
		// Get query result;
		$result = $this->sparql_service->select( $this->sparql_query );

		// Retrieve the request body.
		$body   = wp_remote_retrieve_body( $result );

		// Bail if the query fails.
		if ( $body == 'query not supported' ) {
			return false;
		}

		return $body;
	}
 
	/**
	 * Sets the The SPARQL query.
	 *
	 * @param string $sparql_query The sparql query
	 *
	 * @return self
	*/
	public function set_sparql_query( $sparql_query ) {
		$this->sparql_query = $sparql_query;
	}
 
	/**
	 * Gets the The SPARQL query.
	 *
	 * @return string The SPARQL query.
	 */
	public function get_sparql_query() {
		return $this->sparql_query;
	}
}
