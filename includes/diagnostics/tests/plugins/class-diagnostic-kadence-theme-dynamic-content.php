<?php
/**
 * Kadence Theme Dynamic Content Diagnostic
 *
 * Kadence Theme Dynamic Content needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1302.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kadence Theme Dynamic Content Diagnostic Class
 *
 * @since 1.1302.0000
 */
class Diagnostic_KadenceThemeDynamicContent extends Diagnostic_Base {

	protected static $slug = 'kadence-theme-dynamic-content';
	protected static $title = 'Kadence Theme Dynamic Content';
	protected static $description = 'Kadence Theme Dynamic Content needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Dynamic content enabled
		$dynamic = get_option( 'kadence_dynamic_content_enabled', 0 );
		if ( ! $dynamic ) {
			$issues[] = 'Dynamic content feature not enabled';
		}

		// Check 2: Content blocks configured
		$blocks = absint( get_option( 'kadence_dynamic_content_blocks_count', 0 ) );
		if ( $blocks <= 0 ) {
			$issues[] = 'No dynamic content blocks configured';
		}

		// Check 3: Conditional logic
		$conditional = get_option( 'kadence_dynamic_conditional_logic_enabled', 0 );
		if ( ! $conditional ) {
			$issues[] = 'Conditional logic not enabled';
		}

		// Check 4: Post metadata support
		$metadata = get_option( 'kadence_dynamic_post_metadata_enabled', 0 );
		if ( ! $metadata ) {
			$issues[] = 'Post metadata support not enabled';
		}

		// Check 5: Performance optimization
		$perf = get_option( 'kadence_dynamic_content_performance_optimized', 0 );
		if ( ! $perf ) {
			$issues[] = 'Dynamic content performance not optimized';
		}

		// Check 6: Caching strategy
		$cache = get_option( 'kadence_dynamic_content_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Dynamic content caching not configured';
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
					'Found %d dynamic content issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/kadence-theme-dynamic-content',
			);
		}

		return null;
	}
}
