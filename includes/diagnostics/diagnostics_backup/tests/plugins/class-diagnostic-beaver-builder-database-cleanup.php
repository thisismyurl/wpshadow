<?php
/**
 * Beaver Builder Database Cleanup Diagnostic
 *
 * Beaver Builder leaving database bloat.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.348.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Database Cleanup Diagnostic Class
 *
 * @since 1.348.0000
 */
class Diagnostic_BeaverBuilderDatabaseCleanup extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-database-cleanup';
	protected static $title = 'Beaver Builder Database Cleanup';
	protected static $description = 'Beaver Builder leaving database bloat';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Auto cleanup.
		$auto_cleanup = get_option( '_fl_builder_auto_cleanup', '0' );
		if ( '0' === $auto_cleanup ) {
			$issues[] = 'auto cleanup disabled';
		}

		// Check 2: Cleanup revisions.
		$cleanup_revisions = get_option( '_fl_builder_cleanup_revisions', '1' );
		if ( '0' === $cleanup_revisions ) {
			$issues[] = 'revision cleanup disabled';
		}

		// Check 3: Cleanup drafts.
		$cleanup_drafts = get_option( '_fl_builder_cleanup_drafts', '1' );
		if ( '0' === $cleanup_drafts ) {
			$issues[] = 'draft cleanup disabled';
		}

		// Check 4: Cleanup unused data.
		$cleanup_unused = get_option( '_fl_builder_cleanup_unused_data', '0' );
		if ( '0' === $cleanup_unused ) {
			$issues[] = 'unused data not cleaned';
		}

		// Check 5: Cleanup frequency.
		$cleanup_freq = get_option( '_fl_builder_cleanup_frequency', 'monthly' );
		if ( 'never' === $cleanup_freq ) {
			$issues[] = 'cleanup never runs';
		}

		// Check 6: Max revisions.
		$max_revisions = get_option( '_fl_builder_max_revisions', -1 );
		if ( -1 === $max_revisions ) {
			$issues[] = 'unlimited revisions';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 55, 40 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Beaver Builder database issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-database-cleanup',
			);
		}

		return null;
	}
}
