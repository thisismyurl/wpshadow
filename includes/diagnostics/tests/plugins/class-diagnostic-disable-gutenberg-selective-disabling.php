<?php
/**
 * Disable Gutenberg Selective Disabling Diagnostic
 *
 * Disable Gutenberg Selective Disabling issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1436.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable Gutenberg Selective Disabling Diagnostic Class
 *
 * @since 1.1436.0000
 */
class Diagnostic_DisableGutenbergSelectiveDisabling extends Diagnostic_Base {

	protected static $slug = 'disable-gutenberg-selective-disabling';
	protected static $title = 'Disable Gutenberg Selective Disabling';
	protected static $description = 'Disable Gutenberg Selective Disabling issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'use_block_editor_for_post_type' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify post types with selective Gutenberg disabling
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		$selective_disable = get_option( 'disable_gutenberg_post_types', array() );
		if ( empty( $selective_disable ) && count( $post_types ) > 0 ) {
			$issues[] = __( 'No post types configured for selective Gutenberg disabling', 'wpshadow' );
		}

		// Check 2: Check Gutenberg status per post type
		$gutenberg_enabled_count = 0;
		foreach ( $post_types as $post_type ) {
			if ( use_block_editor_for_post_type( $post_type ) ) {
				$gutenberg_enabled_count++;
			}
		}
		if ( $gutenberg_enabled_count === 0 ) {
			$issues[] = __( 'Gutenberg disabled for all post types', 'wpshadow' );
		}

		// Check 3: Verify user role restrictions for Gutenberg
		$disable_for_roles = get_option( 'disable_gutenberg_user_roles', array() );
		if ( empty( $disable_for_roles ) ) {
			$issues[] = __( 'No user role restrictions configured for Gutenberg', 'wpshadow' );
		}

		// Check 4: Check Gutenberg template support
		$template_lock = get_option( 'disable_gutenberg_template_lock', '' );
		if ( empty( $template_lock ) ) {
			$issues[] = __( 'Gutenberg template locking not configured', 'wpshadow' );
		}

		// Check 5: Verify block editor default setting
		$default_editor = get_option( 'classic-editor-replace', 'block' );
		if ( 'classic' === $default_editor ) {
			$issues[] = __( 'Classic editor set as default instead of selective disabling', 'wpshadow' );
		}

		// Check 6: Check for Classic Editor plugin conflict
		if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			$issues[] = __( 'Classic Editor plugin may conflict with selective disabling', 'wpshadow' );
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
