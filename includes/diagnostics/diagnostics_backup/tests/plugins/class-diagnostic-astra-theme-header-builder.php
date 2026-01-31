<?php
/**
 * Astra Theme Header Builder Diagnostic
 *
 * Astra Theme Header Builder needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1292.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Astra Theme Header Builder Diagnostic Class
 *
 * @since 1.1292.0000
 */
class Diagnostic_AstraThemeHeaderBuilder extends Diagnostic_Base {

	protected static $slug = 'astra-theme-header-builder';
	protected static $title = 'Astra Theme Header Builder';
	protected static $description = 'Astra Theme Header Builder needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		$theme = wp_get_theme();
		if ( 'Astra' !== $theme->get( 'Name' ) && 'Astra' !== $theme->parent_theme ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify header builder is enabled
		$header_builder = get_option( 'astra-settings[header-builder]', 'disabled' );
		if ( $header_builder === 'disabled' ) {
			$issues[] = 'Header builder not enabled';
		}

		// Check 2: Check for mobile header configuration
		$mobile_header = get_option( 'astra-settings[mobile-header-type]', 'default' );
		if ( $mobile_header === 'default' ) {
			$issues[] = 'Mobile header not optimized';
		}

		// Check 3: Verify sticky header performance
		$sticky_header = get_option( 'astra-settings[header-main-stick]', 0 );
		$stick_origin = get_option( 'astra-settings[header-main-stick-meta]', '' );
		if ( $sticky_header && empty( $stick_origin ) ) {
			$issues[] = 'Sticky header enabled without performance optimization';
		}

		// Check 4: Check for excessive header widgets
		$header_widgets = get_option( 'astra-settings[header-builder-widgets]', array() );
		if ( count( $header_widgets ) > 5 ) {
			$issues[] = 'Excessive header widgets may impact performance';
		}

		// Check 5: Verify transparent header settings
		$transparent = get_option( 'astra-settings[transparent-header-enable]', 0 );
		if ( $transparent ) {
			$transparent_color = get_option( 'astra-settings[transparent-header-bg-color]', '' );
			if ( empty( $transparent_color ) ) {
				$issues[] = 'Transparent header enabled without proper styling';
			}
		}

		// Check 6: Check for header caching compatibility
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			$cache_compat = get_option( 'astra-settings[header-cache-compatibility]', false );
			if ( ! $cache_compat && $sticky_header ) {
				$issues[] = 'Cache compatibility not configured for sticky header';
			}
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Astra header builder issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/astra-theme-header-builder',
			);
		}

		return null;
	}
}
