<?php
/**
 * Diagnostic: Multibyte String Support
 *
 * Checks if PHP mbstring extension is installed and configured.
 * Required for proper handling of non-ASCII characters.
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
 * Class Diagnostic_Multibyte_String_Support
 *
 * Tests mbstring extension availability and configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Multibyte_String_Support extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'multibyte-string-support';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Multibyte String Support';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP mbstring extension is available';

	/**
	 * Check multibyte string support.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if mbstring extension is loaded.
		if ( ! extension_loaded( 'mbstring' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP mbstring extension is not installed. This is required for proper handling of non-ASCII characters (UTF-8, accents, emojis).', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multibyte_string_support',
				'meta'        => array(
					'extension_loaded' => false,
				),
			);
		}

		// Check mbstring.func_overload (deprecated in PHP 7.2, removed in PHP 8.0).
		if ( function_exists( 'ini_get' ) ) {
			$func_overload = ini_get( 'mbstring.func_overload' );

			// If func_overload is enabled (non-zero), it can cause issues.
			if ( $func_overload && (int) $func_overload > 0 ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'mbstring.func_overload is enabled. This deprecated setting can cause compatibility issues with WordPress and plugins.', 'wpshadow' ),
					'severity'    => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/multibyte_string_support',
					'meta'        => array(
						'extension_loaded'     => true,
						'func_overload'        => $func_overload,
						'func_overload_value'  => (int) $func_overload,
					),
				);
			}
		}

		// Check if mb_detect_order is set.
		$detect_order = mb_detect_order();
		if ( empty( $detect_order ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'mbstring encoding detection order is not configured. This may cause character encoding issues.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multibyte_string_support',
				'meta'        => array(
					'extension_loaded' => true,
					'detect_order'     => $detect_order,
				),
			);
		}

		// mbstring is properly configured.
		return null;
	}
}
