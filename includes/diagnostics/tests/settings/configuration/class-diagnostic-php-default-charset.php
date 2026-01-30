<?php
/**
 * Diagnostic: PHP default_charset
 *
 * Checks if PHP default_charset is set to UTF-8.
 * Ensures proper character encoding for international content.
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
 * Class Diagnostic_Php_Default_Charset
 *
 * Tests PHP default_charset configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Default_Charset extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-default-charset';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP default_charset';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP default_charset is set to UTF-8';

	/**
	 * Check PHP default_charset setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get default_charset setting.
		$default_charset = ini_get( 'default_charset' );

		// Check if charset is set.
		if ( empty( $default_charset ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP default_charset is not set. Set it to "UTF-8" in php.ini to ensure proper character encoding for international content.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_default_charset',
				'meta'        => array(
					'default_charset' => '',
				),
			);
		}

		// Normalize charset for comparison.
		$charset_normalized = strtolower( str_replace( array( '-', '_' ), '', $default_charset ) );

		// Check if charset is UTF-8.
		if ( 'utf8' !== $charset_normalized ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Current charset */
					__( 'PHP default_charset is set to "%s" but should be "UTF-8" for WordPress. This may cause character encoding issues with international content.', 'wpshadow' ),
					$default_charset
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_default_charset',
				'meta'        => array(
					'default_charset' => $default_charset,
				),
			);
		}

		// PHP default_charset is properly set to UTF-8.
		return null;
	}
}
