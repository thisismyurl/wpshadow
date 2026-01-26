<?php
/**
 * Diagnostic: wp-config Constants Detection
 *
 * Checks if critical WordPress constants are defined in wp-config.php.
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
 * Class Diagnostic_Wp_Config_Constant_Detection
 *
 * Tests for important constants in wp-config.php.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Config_Constant_Detection extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-config-constant-detection';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'wp-config Constants Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if critical WordPress constants are properly defined';

	/**
	 * Check for critical constants.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Critical constants that should be defined.
		$critical = array(
			'DB_NAME',
			'DB_USER',
			'DB_PASSWORD',
			'DB_HOST',
			'ABSPATH',
			'WP_CONTENT_DIR',
			'WP_CONTENT_URL',
		);

		$missing = array();

		foreach ( $critical as $const ) {
			if ( ! defined( $const ) ) {
				$missing[] = $const;
			}
		}

		if ( ! empty( $missing ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Comma-separated list of missing constants */
					__( 'Critical constants not defined: %s. WordPress may not function correctly. Verify wp-config.php.', 'wpshadow' ),
					implode( ', ', $missing )
				),
				'severity'    => 'high',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_config_constant_detection',
				'meta'        => array(
					'missing_constants' => $missing,
				),
			);
		}

		return null;
	}
}
