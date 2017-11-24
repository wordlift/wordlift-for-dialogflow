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
	 * The SPARQL query.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var The sparql query that we will use to retrieve people, events, topcis etc.
	 */
	private $sparql_query;

	/**
	 * {@inheritDoc}
	 */
	public function __construct( $request ) {
		parent::__construct( $request );

		// Build the query.
		$query = $this->build_sparql_query();

		// Set the SPARQL query.
		$this->set_sparql_query( $query );
	}

	/**
	 * Make the SPARQL request and return the result if there is such
	 *
	 * @return mixed The response body or false if it fails.
	 */
	public function get_result() {
		// Get query result.
		$result = $this->select( $this->sparql_query );
		// Retrieve the request body.
		$body = wp_remote_retrieve_body( $result );

		$body = json_decode( $body, true );

		// Bail if the query fails.
		if ( 'query not supported' == $body ) {
			return false;
		}

		return $body['results']['bindings'];
	}

	/**
	 * This is a copy of WordLift Sprql service select method
	 * but it return json response.
	 *
	 * @param string $query The SELECT query to execute.
	 *
	 * @return WP_Error|array The response or WP_Error on failure.
	 */
	public function select( $query ) {

		// Prepare the SPARQL statement by prepending the default namespaces.
		$sparql = rl_sparql_prefixes() . "\n" . $query;

		// Get the SPARQL SELECT URL.
		$url = 'https://api.redlink.io/1.0/data/wl0301/sparql/select';

		$url = add_query_arg(
			array(
				'key' => WL_DIALOGFLOW_KEY,
				'out' => 'json',
			),
			$url
		);

		$args = array(
			'method' => 'POST',
			'body'   => array(
				'query' => $sparql,
			),
		);

		error_log( $sparql );

		return wp_remote_post( $url, $args );
	}

	/**
	 * Sets the The SPARQL query.
	 *
	 * @param string $sparql_query The sparql query.
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

	/**
	 * Generate new sparql query based on user request.
	 *
	 * @return string The new sparql query
	 */
	public function build_sparql_query() {
		return
			$this->get_select_clause() .
			$this->get_where_clause() .
			$this->get_group_clause() .
			$this->get_order_clause() .
			$this->get_limit_clause();
	}

	/**
	 * Set in SPARQL query which fields the message needs
	 */
	abstract public function get_select_clause();

	/**
	 * Set in SPARQL query which fields the message needs
	 */
	abstract public function get_where_clause();

	/**
	 * Set the filter in SPARQL query so the events can be filtered by different params.
	 */
	abstract public function get_filter_clause();

	/**
	 * Generate the limit clause for sparql query.
	 */
	abstract public function get_limit_clause();

	/**
	 * Generate the group clause for sparql query.
	 */
	abstract public function get_group_clause();

	/**
	 * Generate the order clause for sparql query.
	 */
	abstract public function get_order_clause();
}
