<?php
/**
 * Expired Transients Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1730
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Expired_Transients extends Diagnostic_Base {

	protected static $slug        = 'expired-transients';
	protected static $title       = 'Expired Transients Not Deleted';
	protected static $description = 'Detects expired transients causing bloat';
	protected static $family      = 'database';

	public static function check() {
		$cache_key = 'wpshadow_expired_transients';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		$expired_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_value < %d",
				'_transient_timeout_%',
				time()
			)
		);

		if ( $expired_count > 1000 ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d expired transients detected. Clean up for better database performance.', 'wpshadow' ),
					$expired_count
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/database-expired-transients',
				'data'         => array(
					'expired_count' => (int) $expired_count,
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
