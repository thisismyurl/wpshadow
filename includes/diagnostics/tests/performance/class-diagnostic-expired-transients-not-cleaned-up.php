<?php
/**
 * Expired Transients Not Cleaned Up Diagnostic
 *
 * Checks if expired transients are being cleaned up.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expired Transients Not Cleaned Up Diagnostic Class
 *
 * Detects accumulated expired transients.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Expired_Transients_Not_Cleaned_Up extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'expired-transients-not-cleaned-up';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Expired Transients Not Cleaned Up';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if expired transients are cleaned';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count expired transients in options table
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name NOT LIKE %s",
				'%transient_%',
				'%transient_timeout_%'
			)
		);

		if ( $expired_transients > 100 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Database has %d expired transients. Clean up these entries to improve database performance.', 'wpshadow' ),
					absint( $expired_transients )
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/expired-transients-not-cleaned-up',
			);
		}

		return null;
	}
}
