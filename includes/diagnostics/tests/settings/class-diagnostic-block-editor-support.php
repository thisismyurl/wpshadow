<?php
/**
 * Block Editor (Gutenberg) Support Diagnostic
 *
 * Validates that the theme properly supports the WordPress block editor
 * with appropriate styling and block patterns.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Editor Support Diagnostic Class
 *
 * Checks Gutenberg/block editor support in theme.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Block_Editor_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'block-editor-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Block Editor (Gutenberg) Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates block editor support in theme';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Skip if Classic Editor is forcing classic mode.
		if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			$default_editor = get_option( 'classic-editor-replace', 'classic' );
			if ( 'classic' === $default_editor && 'disallow' === get_option( 'classic-editor-allow-users', 'allow' ) ) {
				return null; // Block editor is disabled.
			}
		}

		$issues       = array();
		$template_dir = get_template_directory();

		// Check for wide and full alignment support.
		if ( ! current_theme_supports( 'align-wide' ) ) {
			$issues[] = __( 'Theme does not support wide and full width blocks', 'wpshadow' );
		}

		// Check for custom color palette.
		if ( ! current_theme_supports( 'editor-color-palette' ) ) {
			$issues[] = __( 'Theme does not define custom color palette (blocks use default colors)', 'wpshadow' );
		}

		// Check for block styles support.
		if ( ! current_theme_supports( 'wp-block-styles' ) ) {
			$issues[] = __( 'Theme does not load default block styles', 'wpshadow' );
		}

		// Check for responsive embeds.
		if ( ! current_theme_supports( 'responsive-embeds' ) ) {
			$issues[] = __( 'Theme does not support responsive embeds', 'wpshadow' );
		}

		// Check for editor styles.
		if ( ! current_theme_supports( 'editor-styles' ) ) {
			$issues[] = __( 'Theme does not enqueue editor styles (block editor styling may be inconsistent)', 'wpshadow' );
		}

		// Check for block editor stylesheet.
		$block_editor_css     = $template_dir . '/block-editor.css';
		$block_editor_css_alt = $template_dir . '/css/block-editor.css';
		$editor_style         = $template_dir . '/editor-style.css';

		if ( ! file_exists( $block_editor_css ) && ! file_exists( $block_editor_css_alt ) && ! file_exists( $editor_style ) ) {
			$issues[] = __( 'Missing block editor stylesheet (editor appearance may differ from frontend)', 'wpshadow' );
		}

		// Check for block patterns.
		$registered_patterns = \WP_Block_Patterns_Registry::get_instance()->get_all_registered();
		if ( empty( $registered_patterns ) ) {
			$issues[] = __( 'No block patterns registered (consider adding for better user experience)', 'wpshadow' );
		}

		// Check for custom block styles.
		$functions_file = $template_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			// Check if theme registers block styles.
			if ( false === stripos( $content, 'register_block_style' ) ) {
				// Not necessarily an issue, but a recommendation.
			}

			// Check if theme disables blocks.
			if ( false !== stripos( $content, 'use_block_editor_for_post' ) && false !== stripos( $content, 'false' ) ) {
				$issues[] = __( 'Theme disables block editor for posts', 'wpshadow' );
			}
		}

		// Check for block-specific CSS.
		$style_css = $template_dir . '/style.css';
		if ( file_exists( $style_css ) ) {
			$content = file_get_contents( $style_css );

			if ( false === stripos( $content, '.wp-block' ) ) {
				$issues[] = __( 'Theme stylesheet lacks .wp-block styles (blocks may not display correctly)', 'wpshadow' );
			}
		}

		// Check for theme.json (modern block themes).
		$theme_json = $template_dir . '/theme.json';
		if ( ! file_exists( $theme_json ) ) {
			$issues[] = __( 'Missing theme.json (consider adding for modern block theme features)', 'wpshadow' );
		} else {
			// Validate theme.json structure.
			$json_content = file_get_contents( $theme_json );
			$decoded      = json_decode( $json_content, true );

			if ( null === $decoded ) {
				$issues[] = __( 'theme.json has invalid JSON syntax', 'wpshadow' );
			} elseif ( empty( $decoded['version'] ) ) {
				$issues[] = __( 'theme.json missing version property', 'wpshadow' );
			}
		}

		// Check if users are actually using blocks.
		global $wpdb;
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type IN ('post', 'page')
			AND post_status = 'publish'
			AND post_modified > DATE_SUB(NOW(), INTERVAL 90 DAY)"
		);

		$block_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type IN ('post', 'page')
			AND post_status = 'publish'
			AND post_modified > DATE_SUB(NOW(), INTERVAL 90 DAY)
			AND post_content LIKE '%<!-- wp:%'"
		);

		if ( $total_posts > 10 && $block_posts === 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of recent posts */
				__( '%d posts created in last 90 days but none use blocks (users may not understand block editor)', 'wpshadow' ),
				$total_posts
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of block editor support issues */
					__( 'Found %d block editor support issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'total_posts'    => $total_posts,
					'block_posts'    => $block_posts,
					'recommendation' => __( 'Add theme.json, enable align-wide support, and provide block-specific styles for better editor experience.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
