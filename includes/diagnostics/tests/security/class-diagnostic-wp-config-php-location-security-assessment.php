<?php
/**
 * wp-config.php Location Security Assessment Diagnostic
 *
 * Validates wp-config.php is in optimal location for security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wp-config.php Location Security Assessment Class
 *
 * Tests wp-config.php location security.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Wp_Config_Php_Location_Security_Assessment extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-config-php-location-security-assessment';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp-config.php Location Security Assessment';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates wp-config.php is in optimal location for security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$location_check = self::check_wp_config_location();
		
		if ( $location_check['has_concerns'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $location_check['concerns'] ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-config-php-location-security-assessment',
				'meta'         => array(
					'in_web_root'    => $location_check['in_web_root'],
					'backup_found'   => $location_check['backup_found'],
					'sample_found'   => $location_check['sample_found'],
				),
			);
		}

		return null;
	}

	/**
	 * Check wp-config.php location security.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_wp_config_location() {
		$check = array(
			'has_concerns' => false,
			'concerns'     => array(),
			'in_web_root'  => false,
			'backup_found' => false,
			'sample_found' => false,
		);

		// Check if wp-config.php is in web root.
		$config_in_root = file_exists( ABSPATH . 'wp-config.php' );
		$check['in_web_root'] = $config_in_root;

		if ( $config_in_root ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'wp-config.php in web root (can be moved one level up for better security)', 'wpshadow' );
		}

		// Check for backup copies.
		$backup_patterns = array(
			'wp-config.php.bak',
			'wp-config.php~',
			'wp-config.php.old',
			'wp-config.php.backup',
			'wp-config.backup.php',
		);

		foreach ( $backup_patterns as $pattern ) {
			if ( file_exists( ABSPATH . $pattern ) ) {
				$check['has_concerns'] = true;
				$check['backup_found'] = true;
				$check['concerns'][] = sprintf(
					/* translators: %s: filename */
					__( 'Backup file %s found (exposes credentials without protection)', 'wpshadow' ),
					$pattern
				);
			}
		}

		// Check if sample file exists.
		if ( file_exists( ABSPATH . 'wp-config-sample.php' ) ) {
			$check['sample_found'] = true;
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'wp-config-sample.php still present (should be deleted after installation)', 'wpshadow' );
		}

		return $check;
	}
}
