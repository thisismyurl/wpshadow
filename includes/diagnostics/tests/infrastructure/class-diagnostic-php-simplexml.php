<?php
/**
 * Diagnostic: PHP SimpleXML Support
 *
 * Checks if PHP SimpleXML extension is available.
 * Required for parsing XML feeds, sitemaps, and API responses.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Simplexml
 *
 * Tests PHP SimpleXML extension availability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Simplexml extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-simplexml';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP SimpleXML Support';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP SimpleXML extension is available';

	/**
	 * Check PHP SimpleXML support.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if SimpleXML extension is loaded.
		if ( ! extension_loaded( 'SimpleXML' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP SimpleXML extension is not installed. This is required for parsing XML feeds, sitemaps, and many API integrations. Some WordPress features and plugins may not work correctly.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_simplexml',
				'meta'        => array(
					'extension_loaded' => false,
				),
			);
		}

		// Check if simplexml_load_string function is available.
		if ( ! function_exists( 'simplexml_load_string' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP SimpleXML extension is loaded but simplexml_load_string() function is not available. SimpleXML may not be properly configured.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_simplexml',
				'meta'        => array(
					'extension_loaded'      => true,
					'simplexml_load_string' => false,
				),
			);
		}

		// Test SimpleXML functionality with sample XML.
		$test_xml = '<?xml version="1.0"?><root><item>test</item></root>';

		try {
			$xml = simplexml_load_string( $test_xml );

			if ( false === $xml ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'PHP SimpleXML extension is installed but failed to parse test XML. There may be a configuration issue.', 'wpshadow' ),
					'severity'    => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_simplexml',
					'meta'        => array(
						'extension_loaded' => true,
						'parse_test'       => false,
					),
				);
			}

			// Verify we can access the parsed data.
			if ( ! isset( $xml->item ) || (string) $xml->item !== 'test' ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'PHP SimpleXML extension is installed but cannot properly access parsed XML data.', 'wpshadow' ),
					'severity'    => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_simplexml',
					'meta'        => array(
						'extension_loaded' => true,
						'parse_test'       => true,
						'data_access'      => false,
					),
				);
			}
		} catch ( \Exception $e ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Exception message */
					__( 'PHP SimpleXML extension test failed: %s', 'wpshadow' ),
					$e->getMessage()
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_simplexml',
				'meta'        => array(
					'extension_loaded' => true,
					'exception'        => $e->getMessage(),
				),
			);
		}

		// PHP SimpleXML is properly configured and functional.
		return null;
	}
}
