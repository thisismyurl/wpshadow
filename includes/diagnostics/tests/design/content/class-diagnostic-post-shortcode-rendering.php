<?php
/**
 * Post Shortcode Rendering Diagnostic
 *
 * Tests if shortcodes in posts render correctly. Detects unprocessed or broken
 * shortcodes and validates shortcode registration.
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
 * Post Shortcode Rendering Diagnostic Class
 *
 * Checks for issues with shortcode processing in posts.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Post_Shortcode_Rendering extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-shortcode-rendering';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Shortcode Rendering';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates shortcodes render correctly and detects unprocessed or broken codes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb, $shortcode_tags;

		$issues = array();

		// Check if shortcodes are registered.
		if ( empty( $shortcode_tags ) ) {
			$issues[] = __( 'No shortcodes registered (do_shortcode will not work)', 'wpshadow' );
		}

		// Find posts containing shortcode patterns.
		$posts_with_shortcodes = $wpdb->get_results(
			"SELECT ID, post_title, post_content
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			AND post_content LIKE '%[%'
			LIMIT 100",
			ARRAY_A
		);

		if ( empty( $posts_with_shortcodes ) ) {
			return null; // No shortcodes in content.
		}

		// Detect unregistered shortcodes.
		$unregistered_shortcodes = array();
		$nested_shortcodes = 0;
		$unclosed_shortcodes = 0;
		$malformed_shortcodes = 0;

		foreach ( $posts_with_shortcodes as $post ) {
			$content = $post['post_content'];

			// Find all shortcode-like patterns.
			preg_match_all( '/\[([a-zA-Z0-9_-]+)/', $content, $matches );

			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $shortcode_name ) {
					// Check if shortcode is registered.
					if ( ! isset( $shortcode_tags[ $shortcode_name ] ) ) {
						$unregistered_shortcodes[] = $shortcode_name;
					}
				}
			}

			// Check for nested shortcodes (may not render correctly).
			if ( preg_match( '/\[[^\]]+\[[^\]]+\]/', $content ) ) {
				++$nested_shortcodes;
			}

			// Check for unclosed shortcodes.
			$open_count = substr_count( $content, '[' );
			$close_count = substr_count( $content, ']' );
			if ( $open_count !== $close_count ) {
				++$unclosed_shortcodes;
			}

			// Check for malformed shortcodes (spaces, special chars).
			if ( preg_match( '/\[\s+[a-zA-Z]/', $content ) || preg_match( '/\[[^\]]*\s\]/', $content ) ) {
				++$malformed_shortcodes;
			}
		}

		if ( ! empty( $unregistered_shortcodes ) ) {
			$unique_unregistered = array_unique( $unregistered_shortcodes );
			$issues[] = sprintf(
				/* translators: 1: count, 2: shortcode names */
				__( '%1$d unregistered shortcodes: %2$s (will display as text)', 'wpshadow' ),
				count( $unique_unregistered ),
				implode( ', ', array_slice( $unique_unregistered, 0, 5 ) )
			);
		}

		if ( $nested_shortcodes > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with nested shortcodes */
				__( '%d posts have nested shortcodes (may not render correctly)', 'wpshadow' ),
				$nested_shortcodes
			);
		}

		if ( $unclosed_shortcodes > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with unclosed shortcodes */
				__( '%d posts have unclosed shortcodes (broken rendering)', 'wpshadow' ),
				$unclosed_shortcodes
			);
		}

		if ( $malformed_shortcodes > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with malformed shortcodes */
				__( '%d posts have malformed shortcodes (spaces or special characters)', 'wpshadow' ),
				$malformed_shortcodes
			);
		}

		// Check if do_shortcode is properly attached to the_content.
		$has_do_shortcode = false;
		$content_filters = $GLOBALS['wp_filter']['the_content'] ?? null;

		if ( $content_filters ) {
			foreach ( $content_filters->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( is_string( $callback['function'] ) && 'do_shortcode' === $callback['function'] ) {
						$has_do_shortcode = true;
						break 2;
					}
				}
			}
		}

		if ( ! $has_do_shortcode ) {
			$issues[] = __( 'do_shortcode not attached to the_content (shortcodes will not render)', 'wpshadow' );
		}

		// Check for excessive shortcode usage in single posts.
		$excessive_shortcodes = 0;
		foreach ( array_slice( $posts_with_shortcodes, 0, 30 ) as $post ) {
			$shortcode_count = preg_match_all( '/\[[a-zA-Z0-9_-]+/', $post['post_content'] );
			if ( $shortcode_count > 20 ) {
				++$excessive_shortcodes;
			}
		}

		if ( $excessive_shortcodes > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with excessive shortcodes */
				__( '%d posts contain 20+ shortcodes (performance impact)', 'wpshadow' ),
				$excessive_shortcodes
			);
		}

		// Test shortcode execution time.
		if ( ! empty( $shortcode_tags ) ) {
			$test_content = '[' . array_key_first( $shortcode_tags ) . ']';
			$start_time = microtime( true );
			$result = do_shortcode( $test_content );
			$execution_time = microtime( true ) - $start_time;

			if ( $execution_time > 0.5 ) {
				$issues[] = sprintf(
					/* translators: %f: execution time */
					__( 'Shortcode execution takes %.3f seconds (very slow)', 'wpshadow' ),
					$execution_time
				);
			}
		}

		// Check for shortcodes with dangerous callbacks.
		$dangerous_functions = array( 'eval', 'exec', 'system', 'passthru', 'shell_exec' );
		foreach ( $shortcode_tags as $tag => $callback ) {
			if ( is_string( $callback ) ) {
				foreach ( $dangerous_functions as $func ) {
					if ( stripos( $callback, $func ) !== false ) {
						$issues[] = sprintf(
							/* translators: 1: shortcode tag, 2: function name */
							__( 'Shortcode [%1$s] uses dangerous function %2$s (security risk)', 'wpshadow' ),
							esc_html( $tag ),
							$func
						);
					}
				}
			}
		}

		// Check for shortcodes with numeric-only tags (invalid).
		foreach ( array_keys( $shortcode_tags ) as $tag ) {
			if ( is_numeric( $tag ) ) {
				$issues[] = sprintf(
					/* translators: %s: shortcode tag */
					__( 'Invalid numeric shortcode tag: %s (WordPress will ignore)', 'wpshadow' ),
					esc_html( $tag )
				);
			}
		}

		// Check for shortcode name conflicts (duplicates).
		$tag_counts = array_count_values( array_keys( $shortcode_tags ) );
		$duplicates = array_filter( $tag_counts, function( $count ) {
			return $count > 1;
		} );

		if ( ! empty( $duplicates ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicate shortcodes */
				__( '%d shortcode tags registered multiple times (conflicts)', 'wpshadow' ),
				count( $duplicates )
			);
		}

		// Check for shortcodes in excerpts (usually should not be there).
		$shortcodes_in_excerpts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_excerpt REGEXP '\\[([a-zA-Z0-9_-]+)'"
		);

		if ( $shortcodes_in_excerpts > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of excerpts with shortcodes */
				__( '%d excerpts contain shortcodes (may not render correctly)', 'wpshadow' ),
				$shortcodes_in_excerpts
			);
		}

		// Check for shortcodes with attributes that might break parsing.
		$problematic_attrs = 0;
		foreach ( array_slice( $posts_with_shortcodes, 0, 30 ) as $post ) {
			// Look for shortcodes with unquoted attributes or special chars.
			if ( preg_match( '/\[[a-zA-Z0-9_-]+\s+[^=]+=(?!["\'])[^\]]+\]/', $post['post_content'] ) ) {
				++$problematic_attrs;
			}
		}

		if ( $problematic_attrs > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with problematic attributes */
				__( '%d posts have shortcodes with unquoted attributes (parsing issues)', 'wpshadow' ),
				$problematic_attrs
			);
		}

		// Check for shortcodes that return nothing (empty output).
		$empty_shortcodes = array();
		foreach ( array_slice( array_keys( $shortcode_tags ), 0, 10 ) as $tag ) {
			$result = do_shortcode( '[' . $tag . ']' );
			if ( empty( trim( $result ) ) || $result === '[' . $tag . ']' ) {
				$empty_shortcodes[] = $tag;
			}
		}

		if ( count( $empty_shortcodes ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of empty shortcodes */
				__( '%d registered shortcodes return empty output (may be broken)', 'wpshadow' ),
				count( $empty_shortcodes )
			);
		}

		// Check if shortcode_atts_* filters are excessively used.
		$atts_filter_count = 0;
		foreach ( array_keys( $GLOBALS['wp_filter'] ) as $filter_name ) {
			if ( strpos( $filter_name, 'shortcode_atts_' ) === 0 ) {
				++$atts_filter_count;
			}
		}

		if ( $atts_filter_count > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of attribute filters */
				__( '%d shortcode attribute filters registered (may slow rendering)', 'wpshadow' ),
				$atts_filter_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-shortcode-rendering?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
