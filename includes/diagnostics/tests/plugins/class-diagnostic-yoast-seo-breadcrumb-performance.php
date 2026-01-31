<?php
/**
 * Yoast Seo Breadcrumb Performance Diagnostic
 *
 * Yoast Seo Breadcrumb Performance configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.689.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast Seo Breadcrumb Performance Diagnostic Class
 *
 * @since 1.689.0000
 */
class Diagnostic_YoastSeoBreadcrumbPerformance extends Diagnostic_Base {

	protected static $slug = 'yoast-seo-breadcrumb-performance';
	protected static $title = 'Yoast Seo Breadcrumb Performance';
	protected static $description = 'Yoast Seo Breadcrumb Performance configuration issues';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Breadcrumbs enabled
		$breadcrumbs = get_option( 'wpseo_breadcrumbs_enabled', 0 );
		if ( ! $breadcrumbs ) {
			$issues[] = 'Breadcrumbs not enabled';
		}

		// Check 2: Schema markup
		$schema = get_option( 'wpseo_breadcrumb_schema_enabled', 0 );
		if ( ! $schema ) {
			$issues[] = 'Breadcrumb schema markup not enabled';
		}

		// Check 3: Caching
		$cache = get_option( 'wpseo_breadcrumb_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Breadcrumb caching not enabled';
		}

		// Check 4: Trailing slash
		$trailing = get_option( 'wpseo_breadcrumb_trailing_slash_enabled', 0 );
		if ( ! $trailing ) {
			$issues[] = 'Breadcrumb trailing slash not configured';
		}

		// Check 5: Home link display
		$home_link = get_option( 'wpseo_breadcrumb_home_link_enabled', 0 );
		if ( ! $home_link ) {
			$issues[] = 'Home link in breadcrumbs not enabled';
		}

		// Check 6: Rendering optimization
		$render = get_option( 'wpseo_breadcrumb_rendering_optimized', 0 );
		if ( ! $render ) {
			$issues[] = 'Breadcrumb rendering not optimized';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d breadcrumb performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/yoast-seo-breadcrumb-performance',
			);
		}

		return null;
	}
}
