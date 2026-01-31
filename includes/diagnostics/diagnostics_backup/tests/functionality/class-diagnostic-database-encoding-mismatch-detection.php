<?php
/**
 * Database Encoding Mismatch Detection Diagnostic
 *
 * Checks if database encoding matches WordPress configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Encoding Mismatch Detection Diagnostic Class
 *
 * Detects database encoding mismatches.
 *
 * @since 1.2601.2315
 */
class Diagnostic_Database_Encoding_Mismatch_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-encoding-mismatch-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Encoding Mismatch Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks database encoding configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check database charset
		if ( defined( 'DB_CHARSET' ) && DB_CHARSET !== 'utf8mb4' ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Database charset is %s but should be utf8mb4 for full emoji and special character support.', 'wpshadow' ),
					DB_CHARSET
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-encoding-mismatch-detection',
			);
		}

		return null;
	}
}
