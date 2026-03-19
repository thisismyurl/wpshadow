<?php
/**
 * Transient Cleanup Efficiency Diagnostic
 *
 * Checks for a large number of expired transients.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transient Cleanup Efficiency Diagnostic Class
 *
 * Flags excessive expired transients that can bloat the options table.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Transient_Cleanup_Efficiency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'transient-cleanup-efficiency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Transient Cleanup Efficiency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive expired transients in the database';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$now = time();
		$expired = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(1) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
				'_transient_timeout_%',
				$now
			)
		);

		if ( $expired >= 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A large number of expired transients were found. Cleanup can improve database performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'expired_transients' => $expired,
				),
				'kb_link'      => 'https://wpshadow.com/kb/transient-cleanup-efficiency',
			);
		}

		return null;
	}
}
