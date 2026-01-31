<?php
/**
 * Diagnostic: WordPress Transients Health
 *
 * Detects accumulated expired transients bloating the database.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_WordPress_Transients_Health
 *
 * Monitors WordPress transients for expired entries that should be cleaned up
 * to maintain database performance and reduce bloat.
 *
 * @since 1.2601.2148
 */
class Diagnostic_WordPress_Transients_Health extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-transients-health';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Transients Health';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect accumulated expired transients bloating the database';

	/**
	 * Threshold for significant transient bloat.
	 *
	 * @var int
	 */
	private const BLOAT_THRESHOLD = 100;

	/**
	 * Run the diagnostic check.
	 *
	 * Counts expired transients in the database.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if excessive transients, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count expired transients
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		$expired_transients = absint( $expired_transients );

		if ( $expired_transients < self::BLOAT_THRESHOLD ) {
			// Transient health is good
			return null;
		}

		// Calculate approximate database bloat
		$bloat_estimate = $expired_transients * 2; // Each transient has timeout + value

		$description = sprintf(
			/* translators: %d: number of expired transients */
			_n(
				'Found %d expired transient in the database. Expired transients waste database space and slow query performance. These can be safely deleted as they\'re cached data that has already expired.',
				'Found %d expired transients in the database. Expired transients waste database space and slow query performance. These can be safely deleted as they\'re cached data that has already expired.',
				$expired_transients,
				'wpshadow'
			),
			$expired_transients
		) . ' ' . sprintf(
			/* translators: %d: number of database rows affected */
			__( 'This represents approximately %d database rows that can be removed.', 'wpshadow' ),
			$bloat_estimate
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'low',
			'threat_level' => 25,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/database-wordpress-transients-health',
			'meta'        => array(
				'expired_transients' => $expired_transients,
				'bloat_estimate' => $bloat_estimate,
				'threshold' => self::BLOAT_THRESHOLD,
			),
		);
	}
}
