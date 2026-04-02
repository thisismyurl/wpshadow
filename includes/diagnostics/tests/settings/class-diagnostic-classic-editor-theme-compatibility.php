<?php
/**
 * Classic Editor Theme Compatibility Diagnostic
 *
 * Validates that the theme properly supports the Classic Editor plugin
 * and traditional WordPress editing workflows.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic Editor Theme Compatibility Diagnostic Class
 *
 * Checks Classic Editor plugin compatibility.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Classic_Editor_Theme_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'classic-editor-theme-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Classic Editor Theme Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates Classic Editor support in theme';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run if Classic Editor plugin is active.
		if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			return null;
		}

		$issues = array();

		// Check Classic Editor settings.
		$default_editor = get_option( 'classic-editor-replace', 'classic' );
		$allow_users    = get_option( 'classic-editor-allow-users', 'allow' );

		// Check theme support for editor-style.
		if ( ! current_theme_supports( 'editor-style' ) ) {
			$issues[] = __( 'Theme does not declare editor-style support', 'wpshadow' );
		}

		// Check for editor stylesheet.
		$template_dir   = get_template_directory();
		$editor_css     = $template_dir . '/editor-style.css';
		$editor_css_alt = $template_dir . '/css/editor-style.css';

		if ( ! file_exists( $editor_css ) && ! file_exists( $editor_css_alt ) ) {
			$issues[] = __( 'Missing editor-style.css (Classic Editor styling may be inconsistent)', 'wpshadow' );
		}

		// Check if theme has Gutenberg-specific styles that conflict.
		$style_css = $template_dir . '/style.css';
		if ( file_exists( $style_css ) ) {
			$content = file_get_contents( $style_css );
			if ( false !== stripos( $content, '.block-editor' ) || false !== stripos( $content, '.wp-block' ) ) {
				// Theme has Gutenberg styles - check if it also has Classic Editor styles.
				if ( false === stripos( $content, '.mce-content-body' ) && false === stripos( $content, '#tinymce' ) ) {
					$issues[] = __( 'Theme has Gutenberg styles but no Classic Editor TinyMCE styles', 'wpshadow' );
				}
			}
		}

		// Check for theme functions that depend on Gutenberg.
		$functions_file = $template_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			// Check for Gutenberg-specific functions without Classic Editor fallback.
			if ( false !== stripos( $content, 'register_block_type' ) ) {
				$issues[] = __( 'Theme registers custom blocks (may not work with Classic Editor)', 'wpshadow' );
			}
		}

		// Check if users are confused by editor choice.
		if ( 'allow' === $allow_users && 'classic' !== $default_editor ) {
			$issues[] = __( 'Users can choose editor but default is not Classic (may cause confusion)', 'wpshadow' );
		}

		// Check for posts created with both editors.
		global $wpdb;
		$block_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'post'
			AND post_status = 'publish'
			AND post_content LIKE '%<!-- wp:%'"
		);

		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'post'
			AND post_status = 'publish'"
		);

		if ( $block_posts > 0 && $total_posts > $block_posts ) {
			$classic_posts = $total_posts - $block_posts;
			$issues[] = sprintf(
				/* translators: 1: block posts count, 2: classic posts count */
				__( 'Site has mixed content: %1$d Gutenberg posts and %2$d Classic Editor posts (content consistency issue)', 'wpshadow' ),
				$block_posts,
				$classic_posts
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of compatibility issues */
					__( 'Found %d Classic Editor compatibility issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'default_editor' => $default_editor,
					'block_posts'    => $block_posts,
					'classic_posts'  => $total_posts - $block_posts,
					'recommendation' => __( 'Ensure theme includes editor-style.css and supports both Classic Editor and Gutenberg styling.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
