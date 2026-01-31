<?php
/**
 * Siteground Optimizer Conflicts Diagnostic
 *
 * Siteground Optimizer Conflicts needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1000.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Siteground Optimizer Conflicts Diagnostic Class
 *
 * @since 1.1000.0000
 */
class Diagnostic_SitegroundOptimizerConflicts extends Diagnostic_Base {

	protected static $slug = 'siteground-optimizer-conflicts';
	protected static $title = 'Siteground Optimizer Conflicts';
	protected static $description = 'Siteground Optimizer Conflicts needs attention';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'SITEGROUND_OPTIMIZER_VERSION' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify optimizer enabled
		$optimizer_enabled = get_option( 'siteground_optimizer_enabled', false );
		if ( ! $optimizer_enabled ) {
			$issues[] = __( 'SiteGround Optimizer not enabled', 'wpshadow' );
		}

		// Check 2: Check caching conflicts
		$other_cache = is_plugin_active( 'wp-super-cache/wp-cache.php' ) || 
		              is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ||
		              is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' );
		if ( $other_cache ) {
			$issues[] = __( 'Conflicting caching plugin detected', 'wpshadow' );
		}

		// Check 3: Verify dynamic cache settings
		$dynamic_cache = get_option( 'siteground_optimizer_dynamic_cache', false );
		if ( ! $dynamic_cache ) {
			$issues[] = __( 'SiteGround dynamic cache not configured', 'wpshadow' );
		}

		// Check 4: Check optimization conflicts
		$minification_conflicts = get_option( 'siteground_minification_conflicts', false );
		if ( $minification_conflicts ) {
			$issues[] = __( 'Minification optimization conflicts detected', 'wpshadow' );
		}

		// Check 5: Verify performance monitoring
		$monitoring = get_option( 'siteground_optimizer_monitoring', false );
		if ( ! $monitoring ) {
			$issues[] = __( 'Performance monitoring not enabled', 'wpshadow' );
		}

		// Check 6: Check update compatibility
		$update_compat = get_option( 'siteground_optimizer_update_compatible', false );
		if ( ! $update_compat ) {
			$issues[] = __( 'SiteGround Optimizer update compatibility not verified', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'SiteGround Optimizer conflicts detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/siteground-optimizer-conflicts',
			);
		}

		return null;
	}
}
