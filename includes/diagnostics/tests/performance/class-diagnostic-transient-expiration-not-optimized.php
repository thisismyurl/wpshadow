<?php
/**
 * Transient Expiration Not Optimized Diagnostic
 *
 * Checks if transient expiration is optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2346
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transient Expiration Not Optimized Diagnostic Class
 *
 * Detects unoptimized transients.
 *
 * @since 1.2601.2346
 */
class Diagnostic_Transient_Expiration_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'transient-expiration-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Transient Expiration Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if transient expiration is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2346
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for expired transients still in database
		$expired_transients = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			 WHERE option_name LIKE '%_transient_timeout_%' 
			 AND option_value < UNIX_TIMESTAMP()"
		);

		if ( absint( $expired_transients ) > 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d expired transients remain in the database. Clean them up to improve database performance.', 'wpshadow' ),
					absint( $expired_transients )
				),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/transient-expiration-not-optimized',
			);
		}

		return null;
	}
}
