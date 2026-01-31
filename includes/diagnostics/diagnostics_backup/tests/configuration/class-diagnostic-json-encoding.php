<?php
/**
 * Diagnostic: JSON Encoding Support
 *
 * Verifies PHP JSON extension is available and functional.
 * JSON is critical for REST API, Gutenberg, and modern WordPress features.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Json_Encoding
 *
 * Tests JSON encoding/decoding functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Json_Encoding extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'json-encoding';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'JSON Encoding Support';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies PHP JSON extension is available and functional';

	/**
	 * Check JSON encoding support.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if JSON functions exist.
		if ( ! function_exists( 'json_encode' ) || ! function_exists( 'json_decode' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'JSON extension is not available. This will break REST API, Gutenberg, and many modern WordPress features.', 'wpshadow' ),
				'severity'    => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/json_encoding',
				'meta'        => array(
					'json_encode_exists' => function_exists( 'json_encode' ),
					'json_decode_exists' => function_exists( 'json_decode' ),
				),
			);
		}

		// Test JSON encoding.
		$test_data = array(
			'string'  => 'Hello World',
			'number'  => 12345,
			'boolean' => true,
			'array'   => array( 1, 2, 3 ),
			'unicode' => 'Ü��icodé Tęst',
		);

		$encoded = json_encode( $test_data );

		if ( false === $encoded || JSON_ERROR_NONE !== json_last_error() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: JSON error message */
					__( 'JSON encoding failed: %s', 'wpshadow' ),
					json_last_error_msg()
				),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/json_encoding',
				'meta'        => array(
					'error' => json_last_error_msg(),
					'code'  => json_last_error(),
				),
			);
		}

		// Test JSON decoding.
		$decoded = json_decode( $encoded, true );

		if ( null === $decoded || JSON_ERROR_NONE !== json_last_error() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: JSON error message */
					__( 'JSON decoding failed: %s', 'wpshadow' ),
					json_last_error_msg()
				),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/json_encoding',
				'meta'        => array(
					'error' => json_last_error_msg(),
					'code'  => json_last_error(),
				),
			);
		}

		// Verify decoded data matches original.
		if ( $decoded !== $test_data ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'JSON encoding/decoding is lossy. Data integrity cannot be guaranteed.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/json_encoding',
				'meta'        => array(
					'original' => $test_data,
					'decoded'  => $decoded,
				),
			);
		}

		// JSON encoding is fully functional.
		return null;
	}
}
