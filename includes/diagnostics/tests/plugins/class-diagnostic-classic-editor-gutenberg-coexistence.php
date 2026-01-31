<?php
/**
 * Classic Editor Gutenberg Coexistence Diagnostic
 *
 * Classic Editor Gutenberg Coexistence issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1433.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic Editor Gutenberg Coexistence Diagnostic Class
 *
 * @since 1.1433.0000
 */
class Diagnostic_ClassicEditorGutenbergCoexistence extends Diagnostic_Base {

	protected static $slug = 'classic-editor-gutenberg-coexistence';
	protected static $title = 'Classic Editor Gutenberg Coexistence';
	protected static $description = 'Classic Editor Gutenberg Coexistence issue found';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: Classic Editor plugin active
		$classic_editor = get_option( 'classic_editor_enabled', false );
		if ( ! $classic_editor && ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			$issues[] = 'Classic Editor plugin not active';
		}
		
		// Check 2: User editor preferences configured
		$user_preferences = get_option( 'classic_editor_allow_users_to_switch', false );
		if ( ! $user_preferences ) {
			$issues[] = 'User editor preferences not configured';
		}
		
		// Check 3: Block editor selectively disabled
		$block_editor_disabled = get_option( 'classic_editor_replace', '' );
		if ( empty( $block_editor_disabled ) ) {
			$issues[] = 'Block editor replacement not configured';
		}
		
		// Check 4: Post type editor support
		$post_type_support = get_option( 'classic_editor_post_type_support', array() );
		if ( empty( $post_type_support ) ) {
			$issues[] = 'Post type editor support not configured';
		}
		
		// Check 5: Editor switcher available
		$editor_switcher = get_option( 'classic_editor_show_switcher', false );
		if ( ! $editor_switcher ) {
			$issues[] = 'Editor switcher not available';
		}
		
		// Check 6: Migration plan documented
		$migration_plan = get_option( 'classic_editor_migration_plan', false );
		if ( ! $migration_plan ) {
			$issues[] = 'Migration plan not documented';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Classic Editor/Gutenberg coexistence issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/classic-editor-gutenberg-coexistence',
			);
		}
		
		return null;
	}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
