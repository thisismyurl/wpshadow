<?php
/**
 * Diagnostic: PHP allow_url_fopen Status
 *
 * Checks if allow_url_fopen is enabled (security vs functionality trade-off).
 * Disabled = more secure but breaks some plugins; Enabled = less secure but more compatible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Allow_Url_Fopen
 *
 * Tests PHP allow_url_fopen configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Allow_Url_Fopen extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-allow-url-fopen';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP allow_url_fopen Status';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if allow_url_fopen is enabled';

	/**
	 * Check PHP allow_url_fopen status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get allow_url_fopen setting.
		$allow_url_fopen = ini_get( 'allow_url_fopen' );

		// Convert to boolean.
		$is_enabled = ( '1' === $allow_url_fopen || 'On' === $allow_url_fopen || true === $allow_url_fopen );

		// Check if WP HTTP API functions work (they should use cURL if available).
		$has_curl = function_exists( 'curl_version' );

		if ( ! $is_enabled && ! $has_curl ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP allow_url_fopen is disabled and cURL is not available. WordPress HTTP API will not function, breaking plugin updates, external API calls, and many features. Enable either allow_url_fopen or install cURL.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_allow_url_fopen',
				'meta'        => array(
					'allow_url_fopen' => false,
					'has_curl'        => false,
				),
			);
		}

		if ( ! $is_enabled && $has_curl ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP allow_url_fopen is disabled. This is more secure, but WordPress is relying on cURL for HTTP requests. Ensure cURL remains enabled.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_allow_url_fopen',
				'meta'        => array(
					'allow_url_fopen' => false,
					'has_curl'        => true,
				),
			);
		}

		if ( $is_enabled && ! $has_curl ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP allow_url_fopen is enabled (required since cURL is not available), but this is less secure. Consider installing cURL and disabling allow_url_fopen for better security.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_allow_url_fopen',
				'meta'        => array(
					'allow_url_fopen' => true,
					'has_curl'        => false,
				),
			);
		}

		// Both allow_url_fopen and cURL are available - optimal configuration.
		return null;
	}
}
