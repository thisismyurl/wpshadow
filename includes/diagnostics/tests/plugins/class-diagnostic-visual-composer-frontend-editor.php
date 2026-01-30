<?php
/**
 * Visual Composer Frontend Editor Diagnostic
 *
 * Visual Composer Frontend Editor issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.835.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual Composer Frontend Editor Diagnostic Class
 *
 * @since 1.835.0000
 */
class Diagnostic_VisualComposerFrontendEditor extends Diagnostic_Base {

	protected static $slug = 'visual-composer-frontend-editor';
	protected static $title = 'Visual Composer Frontend Editor';
	protected static $description = 'Visual Composer Frontend Editor issues found';
	protected static $family = 'functionality';

	public static function check() {
		// Check if Visual Composer is installed
		if ( ! defined( 'WPB_VC_VERSION' ) && ! class_exists( 'Vc_Frontend_Editor' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check frontend editor access
		$editor_role = get_option( 'wpb_js_role', 'administrator' );
		if ( $editor_role === 'contributor' || $editor_role === 'author' ) {
			$issues[] = 'frontend_editor_too_permissive';
			$threat_level += 25;
		}

		// Check auto-save configuration
		$auto_save = get_option( 'wpb_js_frontend_editor_auto_save', 'on' );
		if ( $auto_save === 'off' ) {
			$issues[] = 'auto_save_disabled';
			$threat_level += 15;
		}

		// Check editor preview mode
		$preview_mode = get_option( 'wpb_js_frontend_editor_preview_mode', 'desktop' );
		if ( $preview_mode === 'desktop' ) {
			$issues[] = 'mobile_preview_not_default';
			$threat_level += 10;
		}

		// Check draft management
		global $wpdb;
		$draft_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_status = %s 
				 AND post_content LIKE %s",
				'auto-draft',
				'%vc_row%'
			)
		);
		if ( $draft_count > 50 ) {
			$issues[] = 'excessive_auto_drafts';
			$threat_level += 20;
		}

		// Check frontend editor assets loading
		$load_assets = get_option( 'wpb_js_less_version', '' );
		if ( empty( $load_assets ) ) {
			$issues[] = 'frontend_assets_not_optimized';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of frontend editor issues */
				__( 'Visual Composer frontend editor has issues: %s. This affects security and performance of the page builder interface.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/visual-composer-frontend-editor',
			);
		}
		
		return null;
	}
}
