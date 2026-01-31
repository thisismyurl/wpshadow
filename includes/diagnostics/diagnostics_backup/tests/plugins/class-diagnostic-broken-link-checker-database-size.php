<?php
/**
 * Broken Link Checker Database Size Diagnostic
 *
 * Broken Link Checker Database Size issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1422.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broken Link Checker Database Size Diagnostic Class
 *
 * @since 1.1422.0000
 */
class Diagnostic_BrokenLinkCheckerDatabaseSize extends Diagnostic_Base {

	protected static $slug = 'broken-link-checker-database-size';
	protected static $title = 'Broken Link Checker Database Size';
	protected static $description = 'Broken Link Checker Database Size issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'BLC_ACTIVE' ) && ! get_option( 'wsblc_options', array() ) ) {
			return null;
		}

		$issues = array();
		$options = get_option( 'wsblc_options', array() );

		// Check 1: Link limit configured
		$max_links = isset( $options['max_links'] ) ? absint( $options['max_links'] ) : 0;
		if ( $max_links <= 0 ) {
			$issues[] = 'Max link limit not configured';
		}

		// Check 2: Cleanup enabled
		$cleanup_enabled = isset( $options['cleanup_enabled'] ) ? (bool) $options['cleanup_enabled'] : false;
		if ( ! $cleanup_enabled ) {
			$issues[] = 'Database cleanup not enabled';
		}

		// Check 3: Cleanup retention
		$retention_days = isset( $options['cleanup_days'] ) ? absint( $options['cleanup_days'] ) : 0;
		if ( $retention_days <= 0 ) {
			$issues[] = 'Cleanup retention not configured';
		}

		// Check 4: Check interval
		$check_interval = isset( $options['check_interval'] ) ? absint( $options['check_interval'] ) : 0;
		if ( $check_interval <= 0 ) {
			$issues[] = 'Link check interval not configured';
		}

		// Check 5: Stale link pruning
		$prune_stale = isset( $options['prune_stale_links'] ) ? (bool) $options['prune_stale_links'] : false;
		if ( ! $prune_stale ) {
			$issues[] = 'Stale link pruning not enabled';
		}

		// Check 6: Database overhead control
		$overhead_limit = isset( $options['db_overhead_limit'] ) ? absint( $options['db_overhead_limit'] ) : 0;
		if ( $overhead_limit <= 0 ) {
			$issues[] = 'Database overhead limit not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Broken Link Checker DB issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/broken-link-checker-database-size',
			);
		}

		return null;
	}
}
