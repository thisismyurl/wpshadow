<?php
/**
 * Uncode Theme Frontend Editor Diagnostic
 *
 * Uncode Theme Frontend Editor needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1332.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uncode Theme Frontend Editor Diagnostic Class
 *
 * @since 1.1332.0000
 */
class Diagnostic_UncodeThemeFrontendEditor extends Diagnostic_Base {

	protected static $slug = 'uncode-theme-frontend-editor';
	protected static $title = 'Uncode Theme Frontend Editor';
	protected static $description = 'Uncode Theme Frontend Editor needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Uncode theme
		$theme = wp_get_theme();
		if ( 'Uncode' !== $theme->get( 'Name' ) && 'Uncode' !== $theme->get_template() ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Frontend editor enabled
		$frontend_editor = get_option( 'uncode_frontend_editor', 'on' );
		if ( 'off' === $frontend_editor ) {
			return null;
		}
		
		// Check 2: Editor access restrictions
		$editor_roles = get_option( 'uncode_frontend_editor_roles', array( 'administrator' ) );
		if ( in_array( 'editor', $editor_roles, true ) || in_array( 'author', $editor_roles, true ) ) {
			$issues[] = __( 'Frontend editor available to non-admins (security risk)', 'wpshadow' );
		}
		
		// Check 3: Auto-save frequency
		$autosave_interval = get_option( 'uncode_autosave_interval', 60 );
		if ( $autosave_interval < 30 ) {
			$issues[] = sprintf( __( 'Auto-save every %ds (server load)', 'wpshadow' ), $autosave_interval );
		}
		
		// Check 4: Revision storage
		global $wpdb;
		$revision_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'revision'
			)
		);
		
		if ( $revision_count > 500 ) {
			$issues[] = sprintf( __( '%d revisions (database bloat)', 'wpshadow' ), $revision_count );
		}
		
		// Check 5: Frontend editor assets
		$load_assets = get_option( 'uncode_frontend_editor_load_all_assets', 'on' );
		if ( 'on' === $load_assets ) {
			$issues[] = __( 'All editor assets loaded (unnecessary overhead)', 'wpshadow' );
		}
		
		// Check 6: Cache compatibility
		$cache_compat = get_option( 'uncode_frontend_editor_cache_compat', 'off' );
		if ( 'off' === $cache_compat ) {
			$issues[] = __( 'Cache compatibility disabled (stale editor content)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of frontend editor issues */
				__( 'Uncode frontend editor has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/uncode-theme-frontend-editor',
		);
	}
}
