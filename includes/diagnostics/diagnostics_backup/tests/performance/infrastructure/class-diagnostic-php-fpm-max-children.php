<?php
/**
 * Diagnostic: PHP-FPM Max Children
 *
 * Checks PHP-FPM max_children configuration to ensure adequate process limits.
 * Too few children can cause 502/503 errors under load.
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
 * Class Diagnostic_Php_Fpm_Max_Children
 *
 * Tests PHP-FPM max_children configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Fpm_Max_Children extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-fpm-max-children';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP-FPM Max Children';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks PHP-FPM max_children configuration';

	/**
	 * Check PHP-FPM max_children setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if running PHP-FPM.
		$is_php_fpm = false;

		if ( function_exists( 'php_sapi_name' ) ) {
			$sapi = php_sapi_name();
			if ( strpos( $sapi, 'fpm' ) !== false ) {
				$is_php_fpm = true;
			}
		}

		if ( ! $is_php_fpm ) {
			return null; // Not applicable if not using PHP-FPM.
		}

		// Try to get PHP-FPM pool configuration.
		// Note: This is usually only accessible to system admin, not web process.
		// We'll check available PHP-FPM status page if available.

		// Check for FPM_MAX_CHILDREN environment variable (some hosts set this).
		$max_children = getenv( 'FPM_MAX_CHILDREN' );

		if ( false === $max_children ) {
			// Try to read from php-fpm status page if available.
			// Most hosts don't expose this, so this is informational.
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP-FPM max_children configuration cannot be determined from web context. Contact your hosting provider to verify this setting is adequate for your traffic.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_fpm_max_children',
				'meta'        => array(
					'is_php_fpm'   => true,
					'max_children' => null,
				),
			);
		}

		$max_children = (int) $max_children;

		// Recommended minimum based on typical WordPress sites.
		$recommended_min = 10;

		if ( $max_children < $recommended_min ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: Current max_children, 2: Recommended minimum */
					__( 'PHP-FPM max_children is set to %1$d, which is below the recommended minimum of %2$d. This may cause 502/503 errors under moderate traffic.', 'wpshadow' ),
					$max_children,
					$recommended_min
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_fpm_max_children',
				'meta'        => array(
					'is_php_fpm'      => true,
					'max_children'    => $max_children,
					'recommended_min' => $recommended_min,
				),
			);
		}

		// PHP-FPM max_children is adequate.
		return null;
	}
}
