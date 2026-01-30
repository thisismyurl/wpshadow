<?php
/**
 * Elementor Pro Global Widgets Diagnostic
 *
 * Elementor Pro Global Widgets issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.796.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Global Widgets Diagnostic Class
 *
 * @since 1.796.0000
 */
class Diagnostic_ElementorProGlobalWidgets extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-global-widgets';
	protected static $title = 'Elementor Pro Global Widgets';
	protected static $description = 'Elementor Pro Global Widgets issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify global widgets exist
		$global_widgets = get_posts( array(
			'post_type'      => 'elementor_library',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_key'       => '_elementor_template_type',
			'meta_value'     => 'widget',
		) );
		if ( empty( $global_widgets ) ) {
			$issues[] = 'No global widgets configured';
		}
		
		// Check 2: Check for excessive global widgets
		if ( is_array( $global_widgets ) && count( $global_widgets ) > 50 ) {
			$issues[] = 'Too many global widgets (over 50)';
		}
		
		// Check 3: Verify global widgets caching
		$cache_enabled = get_option( 'elementor_global_widget_cache', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = 'Global widget cache not enabled';
		}
		
		// Check 4: Check for update syncing
		$sync_enabled = get_option( 'elementor_global_widget_sync', 0 );
		if ( ! $sync_enabled ) {
			$issues[] = 'Global widget sync not enabled';
		}
		
		// Check 5: Verify reuse restrictions
		$reuse_restrictions = get_option( 'elementor_global_widget_reuse', 0 );
		if ( ! $reuse_restrictions ) {
			$issues[] = 'Global widget reuse restrictions not configured';
		}
		
		// Check 6: Check for template library caching
		$library_cache = get_option( 'elementor_library_cache', 0 );
		if ( ! $library_cache ) {
			$issues[] = 'Template library cache not enabled';
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
					'Found %d Elementor global widget issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-global-widgets',
			);
		}
		
		return null;
	}
}
