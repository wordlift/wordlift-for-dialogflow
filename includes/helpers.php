<?php
/**
 * The file that defines the helper functions
 *
 * @link       https://github.com/stoyan0v
 * @since      1.0.0
 *
 * @package    Wordlift_For_Dialogflow
 * @subpackage Wordlift_For_Dialogflow/includes
 */

/**
 * Description
 *
 * @param string   $text The text that needs to be splitted.
 * @param int      $offset The offset.
 * @param int|bool $length The number of sentences.
 *
 * @return string The chunk of text.
 */
function get_sentences( $text, $offset = 0, $length = false ) {
	// Split the text into separate sentences.
	$sentences = preg_split( '/(?<=[.?!])\s+(?=[a-z])/i', $text );

	// Set the maximum length if it's empty.
	if ( empty( $length ) ) {
		$length = count( $sentences );
	}

	// Get sentences.
	$sentences = array_slice( $sentences, $offset, $length );

	// Build the text again.
	return implode( '', $sentences );
}
