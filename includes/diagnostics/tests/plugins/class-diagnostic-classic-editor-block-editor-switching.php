<?php
/**
 * Classic Editor Block Editor Switching Diagnostic
 *
 * Classic Editor Block Editor Switching issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1434.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic Editor Block Editor Switching Diagnostic Class
 *
 * @since 1.1434.0000
 */
class Diagnostic_ClassicEditorBlockEditorSwitching extends Diagnostic_Base {

	protected static $slug = 'classic-editor-block-editor-switching';
	protected static $title = 'Classic Editor Block Editor Switching';
	protected static $description = 'Classic Editor Block Editor Switching issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'classic_editor_init_actions' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Allow users to switch setting
		$allow_switch = get_option( 'classic-editor-allow-users', 'allow' );
		if ( 'disallow' === $allow_switch ) {
			return null; // No switching enabled
		}
		
		// Check 2: Default editor setting
		$default_editor = get_option( 'classic-editor-replace', 'classic' );
		
		// Check 3: Per-post editor preferences
		$mixed_editors = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->postmeta} pm
				 WHERE pm.meta_key = %s AND pm.meta_value != %s",
				'classic-editor-remember',
				$default_editor
			)
		);
		
		if ( $mixed_editors > 50 ) {
			$issues[] = sprintf( __( '%d posts use non-default editor (inconsistent editing)', 'wpshadow' ), $mixed_editors );
		}
		
		// Check 4: Block validation errors from switching
		$block_errors = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_content LIKE '%<!-- wp:html -->%'
			 AND post_content LIKE '%Classic block%'"
		);
		
		if ( $block_errors > 10 ) {
			$issues[] = sprintf( __( '%d posts with classic block wrappers (editor switching artifacts)', 'wpshadow' ), $block_errors );
		}
		
		// Check 5: User preference consistency
		$user_prefs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_value, COUNT(*) as count FROM {$wpdb->usermeta}
				 WHERE meta_key = %s GROUP BY meta_value",
				'classic-editor-settings'
			)
		);
		
		if ( count( $user_prefs ) > 1 ) {
			$issues[] = __( 'Inconsistent editor preferences across users', 'wpshadow' );
		}
		
		// Check 6: Gutenberg plugin conflict
		if ( defined( 'GUTENBERG_VERSION' ) && version_compare( GUTENBERG_VERSION, '10.0', '>=' ) ) {
			$issues[] = __( 'Gutenberg plugin active with Classic Editor (potential conflicts)', 'wpshadow' );
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
				/* translators: %s: list of editor switching issues */
				__( 'Classic Editor switching has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/classic-editor-block-editor-switching',
		);
	}
}
