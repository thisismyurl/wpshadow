<?php
/**
 * Post Content Filtering Diagnostic
 *
 * Validates the_content filter chain. Detects conflicts in content filters
 * and performance issues from excessive filtering.
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
 * Post Content Filtering Diagnostic Class
 *
 * Checks for issues with the_content filter chain.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Post_Content_Filtering extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-content-filtering';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Content Filtering';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates the_content filter chain for conflicts and performance issues';

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
		global $wp_filter;

		$issues = array();

		// Check if the_content filter exists and has callbacks.
		if ( ! isset( $wp_filter['the_content'] ) ) {
			$issues[] = __( 'the_content filter is missing (core WordPress issue)', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-content-filtering',
			);
		}

		$content_filters = $wp_filter['the_content'];
		$total_callbacks = 0;
		$priorities = array();

		// Count callbacks and analyze priorities.
		foreach ( $content_filters->callbacks as $priority => $callbacks ) {
			$total_callbacks += count( $callbacks );
			$priorities[] = $priority;
		}

		// Check for excessive number of filters.
		if ( $total_callbacks > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of content filters */
				__( '%d filters attached to the_content (may impact performance)', 'wpshadow' ),
				$total_callbacks
			);
		}

		// Check for critical WordPress core filters.
		$core_filters_present = array();
		$core_filters_expected = array(
			'wptexturize',
			'wpautop',
			'shortcode_unautop',
			'prepend_attachment',
			'wp_filter_content_tags',
			'do_shortcode',
			'convert_smilies',
		);

		foreach ( $core_filters_expected as $filter_name ) {
			foreach ( $content_filters->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( is_string( $callback['function'] ) && $callback['function'] === $filter_name ) {
						$core_filters_present[] = $filter_name;
					}
				}
			}
		}

		$missing_core = array_diff( $core_filters_expected, $core_filters_present );
		if ( ! empty( $missing_core ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of missing filters */
				__( 'Core filters missing from the_content: %s', 'wpshadow' ),
				implode( ', ', $missing_core )
			);
		}

		// Check for filters with very high or low priorities (likely conflicts).
		$extreme_priorities = array();
		foreach ( $priorities as $priority ) {
			if ( $priority < -100 || $priority > 1000 ) {
				$extreme_priorities[] = $priority;
			}
		}

		if ( ! empty( $extreme_priorities ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of extreme priorities */
				__( 'Filters using extreme priorities: %s (execution order conflicts)', 'wpshadow' ),
				implode( ', ', array_unique( $extreme_priorities ) )
			);
		}

		// Test filter execution with sample content.
		$test_content = '<p>Test content for filter validation.</p><!--nextpage--><p>Second page.</p>';
		$start_time = microtime( true );
		$filtered_content = apply_filters( 'the_content', $test_content );
		$execution_time = microtime( true ) - $start_time;

		// Check if filtering is too slow.
		if ( $execution_time > 0.1 ) {
			$issues[] = sprintf(
				/* translators: %f: execution time in seconds */
				__( 'Content filtering takes %.3f seconds (performance issue)', 'wpshadow' ),
				$execution_time
			);
		}

		// Check if wpautop is improperly removed.
		$has_wpautop = false;
		$wpautop_priority = null;
		foreach ( $content_filters->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( is_string( $callback['function'] ) && 'wpautop' === $callback['function'] ) {
					$has_wpautop = true;
					$wpautop_priority = $priority;
					break 2;
				}
			}
		}

		if ( ! $has_wpautop ) {
			$issues[] = __( 'wpautop removed from the_content (paragraphs will not format)', 'wpshadow' );
		} elseif ( $wpautop_priority !== 10 ) {
			$issues[] = sprintf(
				/* translators: %d: wpautop priority */
				__( 'wpautop priority changed from 10 to %d (may cause formatting issues)', 'wpshadow' ),
				$wpautop_priority
			);
		}

		// Check if do_shortcode is present and at correct priority.
		$has_shortcode = false;
		$shortcode_priority = null;
		foreach ( $content_filters->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( is_string( $callback['function'] ) && 'do_shortcode' === $callback['function'] ) {
					$has_shortcode = true;
					$shortcode_priority = $priority;
					break 2;
				}
			}
		}

		if ( ! $has_shortcode ) {
			$issues[] = __( 'do_shortcode removed from the_content (shortcodes will not work)', 'wpshadow' );
		} elseif ( $shortcode_priority !== 11 ) {
			$issues[] = sprintf(
				/* translators: %d: do_shortcode priority */
				__( 'do_shortcode priority changed from 11 to %d (execution order wrong)', 'wpshadow' ),
				$shortcode_priority
			);
		}

		// Check for filters that might be modifying content dangerously.
		$dangerous_patterns = array(
			'eval',
			'base64_decode',
			'exec',
			'system',
			'shell_exec',
			'passthru',
		);

		foreach ( $content_filters->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( is_string( $callback['function'] ) ) {
					foreach ( $dangerous_patterns as $pattern ) {
						if ( stripos( $callback['function'], $pattern ) !== false ) {
							$issues[] = sprintf(
								/* translators: %s: dangerous function name */
								__( 'Dangerous filter detected in the_content: %s (security risk)', 'wpshadow' ),
								esc_html( $callback['function'] )
							);
							break 2;
						}
					}
				}
			}
		}

		// Check for duplicate filters (same function added multiple times).
		$function_names = array();
		$duplicates = array();
		
		foreach ( $content_filters->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$func_name = '';
				if ( is_string( $callback['function'] ) ) {
					$func_name = $callback['function'];
				} elseif ( is_array( $callback['function'] ) && isset( $callback['function'][1] ) ) {
					$func_name = is_string( $callback['function'][1] ) ? $callback['function'][1] : '';
				}
				
				if ( ! empty( $func_name ) ) {
					if ( isset( $function_names[ $func_name ] ) ) {
						$duplicates[] = $func_name;
					}
					$function_names[ $func_name ] = true;
				}
			}
		}

		if ( ! empty( $duplicates ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicate filters */
				__( '%d filters registered multiple times on the_content (redundant)', 'wpshadow' ),
				count( array_unique( $duplicates ) )
			);
		}

		// Check if filters are accepting the correct number of parameters.
		foreach ( $content_filters->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( isset( $callback['accepted_args'] ) && $callback['accepted_args'] > 2 ) {
					// the_content only passes 1 argument by default.
					$issues[] = sprintf(
						/* translators: %d: number of accepted arguments */
						__( 'Filter expects %d arguments but the_content provides 1 (will fail)', 'wpshadow' ),
						$callback['accepted_args']
					);
					break 2;
				}
			}
		}

		// Check related filters for consistency.
		$related_filters = array(
			'the_excerpt',
			'get_the_excerpt',
			'the_content_feed',
		);

		foreach ( $related_filters as $filter_name ) {
			if ( ! isset( $wp_filter[ $filter_name ] ) ) {
				$issues[] = sprintf(
					/* translators: %s: filter name */
					__( 'Related filter "%s" is missing (may cause issues)', 'wpshadow' ),
					$filter_name
				);
			}
		}

		// Check if content_save_pre filter is interfering.
		if ( isset( $wp_filter['content_save_pre'] ) && count( $wp_filter['content_save_pre']->callbacks ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of save filters */
				__( '%d filters on content_save_pre (may modify saved content)', 'wpshadow' ),
				count( $wp_filter['content_save_pre']->callbacks )
			);
		}

		// Test if filters properly handle empty content.
		$empty_result = apply_filters( 'the_content', '' );
		if ( ! is_string( $empty_result ) ) {
			$issues[] = sprintf(
				/* translators: %s: type returned */
				__( 'Filters return %s for empty content (should return string)', 'wpshadow' ),
				gettype( $empty_result )
			);
		}

		// Check if filters are stripping all content (plugin conflict).
		if ( strlen( $filtered_content ) < ( strlen( $test_content ) / 2 ) ) {
			$issues[] = __( 'Filters removing significant content (50%+ lost, plugin conflict)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-content-filtering',
			);
		}

		return null;
	}
}
