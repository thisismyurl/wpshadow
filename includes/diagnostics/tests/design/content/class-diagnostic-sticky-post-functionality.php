<?php
/**
 * Sticky Post Functionality Diagnostic
 *
 * Tests if sticky posts remain at top of listings. Verifies sticky flag persistence
 * and proper display on front-end archives.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sticky Post Functionality Diagnostic Class
 *
 * Checks for issues with sticky posts not displaying correctly.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Sticky_Post_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sticky-post-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sticky Post Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if sticky posts remain at top of listings and persist correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Get all sticky posts.
		$sticky_posts = get_option( 'sticky_posts', array() );

		if ( ! empty( $sticky_posts ) ) {
			// Verify sticky posts actually exist and are published.
			$placeholders = implode( ',', array_fill( 0, count( $sticky_posts ), '%d' ) );
			$valid_sticky = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE ID IN ($placeholders) AND post_status = 'publish' AND post_type = 'post'", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					...$sticky_posts
				)
			);

			$invalid_count = count( $sticky_posts ) - count( $valid_sticky );
			if ( $invalid_count > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of invalid sticky posts */
					__( '%d sticky posts are no longer published or don\'t exist', 'wpshadow' ),
					$invalid_count
				);
			}

			// Check if theme supports sticky posts properly (has sticky class handling).
			if ( ! current_theme_supports( 'post-formats' ) ) {
				// Check if theme stylesheet has .sticky selector (basic validation).
				$stylesheet = get_stylesheet_directory() . '/style.css';
				if ( file_exists( $stylesheet ) ) {
					$css_content = file_get_contents( $stylesheet ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					if ( false === strpos( $css_content, '.sticky' ) ) {
						$issues[] = __( 'Theme stylesheet doesn\'t contain .sticky CSS class', 'wpshadow' );
					}
				}
			}

			// Check for excessive sticky posts (WordPress recommends <= 3).
			if ( count( $valid_sticky ) > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of sticky posts */
					__( '%d sticky posts (recommended: 3 or fewer for better UX)', 'wpshadow' ),
					count( $valid_sticky )
				);
			}

			// Check if sticky posts have been modified recently (if not, might be stale).
			$stale_sticky = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->posts}
					WHERE ID IN ($placeholders) 
					AND post_modified < DATE_SUB(NOW(), INTERVAL %d DAY)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					...array_merge( $valid_sticky, array( 365 ) )
				)
			);

			if ( $stale_sticky > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of stale posts */
					__( '%d sticky posts haven\'t been updated in over 1 year', 'wpshadow' ),
					$stale_sticky
				);
			}
		}

		// Check if pre_get_posts filters might interfere with sticky posts.
		$pre_get_posts_hooks = $GLOBALS['wp_filter']['pre_get_posts'] ?? null;
		if ( $pre_get_posts_hooks && count( $pre_get_posts_hooks ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of hooks */
				__( '%d pre_get_posts filters registered (may interfere with sticky logic)', 'wpshadow' ),
				count( $pre_get_posts_hooks )
			);
		}

		// Check if ignore_sticky_posts is being forced globally (bad practice).
		$query_hooks = $GLOBALS['wp_filter']['parse_query'] ?? null;
		if ( $query_hooks ) {
			foreach ( $query_hooks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					// Check if callback function name suggests ignoring sticky posts.
					$function_name = '';
					if ( is_array( $callback['function'] ) && is_string( $callback['function'][1] ) ) {
						$function_name = $callback['function'][1];
					} elseif ( is_string( $callback['function'] ) ) {
						$function_name = $callback['function'];
					}

					if ( false !== stripos( $function_name, 'ignore_sticky' ) ) {
						$issues[] = __( 'Custom query hook may be globally disabling sticky posts', 'wpshadow' );
						break 2;
					}
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/sticky-post-functionality',
			);
		}

		return null;
	}
}
