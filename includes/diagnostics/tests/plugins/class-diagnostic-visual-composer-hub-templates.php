<?php
/**
 * Visual Composer Hub Templates Diagnostic
 *
 * Visual Composer Hub Templates issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.832.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual Composer Hub Templates Diagnostic Class
 *
 * @since 1.832.0000
 */
class Diagnostic_VisualComposerHubTemplates extends Diagnostic_Base {

	protected static $slug = 'visual-composer-hub-templates';
	protected static $title = 'Visual Composer Hub Templates';
	protected static $description = 'Visual Composer Hub Templates issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'vc_map' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Hub templates enabled
		$hub = get_option( 'vc_hub_templates_enabled', 0 );
		if ( ! $hub ) {
			$issues[] = 'Hub templates feature not enabled';
		}

		// Check 2: Template library
		$library = get_option( 'vc_template_library_synced', 0 );
		if ( ! $library ) {
			$issues[] = 'Template library not synchronized';
		}

		// Check 3: Cloud integration
		$cloud = get_option( 'vc_cloud_integration_enabled', 0 );
		if ( ! $cloud ) {
			$issues[] = 'Cloud integration not enabled';
		}

		// Check 4: Auto-update templates
		$auto_update = get_option( 'vc_auto_update_templates_enabled', 0 );
		if ( ! $auto_update ) {
			$issues[] = 'Auto-update for templates not enabled';
		}

		// Check 5: Cache optimization
		$cache = get_option( 'vc_template_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Template caching not enabled';
		}

		// Check 6: Template preview
		$preview = get_option( 'vc_template_preview_optimization_enabled', 0 );
		if ( ! $preview ) {
			$issues[] = 'Template preview optimization not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d hub template issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/visual-composer-hub-templates',
			);
		}

		return null;
	}
}
