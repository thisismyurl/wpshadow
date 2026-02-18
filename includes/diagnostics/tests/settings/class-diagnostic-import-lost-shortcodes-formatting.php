<?php
/**
 * Import Lost Shortcodes and Formatting Diagnostic
 *
 * Tests whether page builder shortcodes, Gutenberg blocks, and custom formatting
 * survive the import process. Shortcodes often power layouts, galleries, and
 * page builder content. If they are stripped or mangled, pages break visually.
 *
 * **What This Check Does:**
 * - Scans imported content for shortcode integrity
 * - Validates Gutenberg block markers remain intact
 * - Detects HTML formatting loss (paragraphs, lists, embeds)
 * - Flags content where shortcodes are removed or escaped
 *
 * **Why This Matters:**
 * Many sites rely on builder shortcodes for entire page layouts. Losing them
 * means broken pages, missing CTAs, and lost conversions. Restoring formatting
 * manually after a failed import can take days.
 *
 * **Real-World Failure Scenario:**
 * - Site uses page builder shortcodes for landing pages
 * - Import process strips bracketed shortcodes
 * - Pages render as plain text and broken markup
 * - Campaign launch fails due to missing layouts
 *
 * Result: Lost revenue and emergency manual rebuilds.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Preserves site structure during migration
 * - #9 Show Value: Prevents expensive manual rework
 * - Helpful Neighbor: Highlights high‑risk content before launch
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/import-shortcodes
 * or https://wpshadow.com/training/migrating-page-builder-sites
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
 * Import Lost Shortcodes Formatting Diagnostic Class
 *
 * Inspects post content for shortcode and block markers after import.
 *
 * **Implementation Pattern:**
 * 1. Scan content for shortcode patterns
 * 2. Validate Gutenberg block comment syntax
 * 3. Identify escaped or stripped shortcode brackets
 * 4. Return findings with remediation guidance
 *
 * **Related Diagnostics:**
 * - Import Custom Field Mapping Failures
 * - Import Taxonomy Mismatches
 * - Import Character Encoding Corruption
 *
 * @since 1.6030.2148
 */
class Diagnostic_Import_Lost_Shortcodes_Formatting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-lost-shortcodes-formatting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Lost Shortcodes and Formatting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether page builder shortcodes and blocks survive import';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'import-export';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb, $shortcode_tags;
		
		$issues = array();

		// Get posts with shortcodes.
		$posts_with_shortcodes = $wpdb->get_results(
			"SELECT ID, post_title, post_content 
			FROM {$wpdb->posts} 
			WHERE post_type IN ('post', 'page') 
			AND post_status = 'publish' 
			AND post_content REGEXP '\\[([a-zA-Z0-9_-]+)' 
			LIMIT 50",
			ARRAY_A
		);

		$unregistered_shortcodes = array();
		
		foreach ( $posts_with_shortcodes as $post ) {
			// Find all shortcodes in content.
			preg_match_all( '/\[([a-zA-Z0-9_-]+)/', $post['post_content'], $matches );
			
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $shortcode ) {
					if ( ! isset( $shortcode_tags[ $shortcode ] ) && ! in_array( $shortcode, $unregistered_shortcodes, true ) ) {
						$unregistered_shortcodes[] = $shortcode;
					}
				}
			}
		}

		if ( ! empty( $unregistered_shortcodes ) ) {
			$issues[] = sprintf(
				/* translators: 1: count, 2: shortcode list */
				__( '%1$d unregistered shortcodes found: %2$s (may indicate incomplete import)', 'wpshadow' ),
				count( $unregistered_shortcodes ),
				implode( ', ', array_slice( $unregistered_shortcodes, 0, 5 ) )
			);
		}

		// Check for Gutenberg blocks.
		$posts_with_blocks = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type IN ('post', 'page') 
			AND post_status = 'publish' 
			AND post_content LIKE '%<!-- wp:%'"
		);

		if ( $posts_with_blocks > 0 ) {
			// Check for corrupted block markup.
			$corrupted_blocks = $wpdb->get_var(
				"SELECT COUNT(*) 
				FROM {$wpdb->posts} 
				WHERE post_type IN ('post', 'page') 
				AND post_status = 'publish' 
				AND (
					post_content LIKE '%<!-- wp:% -->' 
					OR post_content LIKE '%&lt;!-- wp:%'
					OR post_content LIKE '%<!--wp:%'
				)"
			);

			if ( $corrupted_blocks > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts */
					__( '%d posts have malformed Gutenberg blocks (import corruption)', 'wpshadow' ),
					$corrupted_blocks
				);
			}
		}

		// Check for page builder patterns (Elementor, Divi, etc.).
		$page_builders = array(
			'elementor' => array(
				'pattern' => '\[elementor-template',
				'meta_key' => '_elementor_data',
				'name' => 'Elementor',
			),
			'divi' => array(
				'pattern' => '\[et_pb_',
				'meta_key' => '_et_pb_use_builder',
				'name' => 'Divi',
			),
			'beaver' => array(
				'pattern' => '\[fl_builder_insert_layout',
				'meta_key' => '_fl_builder_enabled',
				'name' => 'Beaver Builder',
			),
		);

		foreach ( $page_builders as $builder ) {
			$posts_with_builder = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) 
					FROM {$wpdb->posts} 
					WHERE post_type IN ('post', 'page') 
					AND post_status = 'publish' 
					AND post_content REGEXP %s",
					$builder['pattern']
				)
			);

			if ( $posts_with_builder > 0 ) {
				// Check if builder plugin is active.
				$builder_meta_exists = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) 
						FROM {$wpdb->postmeta} 
						WHERE meta_key = %s",
						$builder['meta_key']
					)
				);

				if ( $builder_meta_exists === 0 || $builder_meta_exists < $posts_with_builder ) {
					$issues[] = sprintf(
						/* translators: 1: builder name, 2: post count */
						__( '%2$d posts use %1$s but meta is missing (incomplete import)', 'wpshadow' ),
						$builder['name'],
						$posts_with_builder
					);
				}
			}
		}

		// Check for serialized data corruption.
		$posts_with_serialized = $wpdb->get_results(
			"SELECT p.ID, pm.meta_value 
			FROM {$wpdb->posts} p 
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
			WHERE p.post_type IN ('post', 'page') 
			AND pm.meta_value LIKE 'a:%' 
			OR pm.meta_value LIKE 'O:%' 
			LIMIT 20",
			ARRAY_A
		);

		$corrupted_serialized = 0;
		foreach ( $posts_with_serialized as $post_meta ) {
			$unserialized = @unserialize( $post_meta['meta_value'] );
			
			if ( false === $unserialized && 'b:0;' !== $post_meta['meta_value'] ) {
				++$corrupted_serialized;
			}
		}

		if ( $corrupted_serialized > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of corrupted entries */
				__( '%d posts have corrupted serialized data (import issue)', 'wpshadow' ),
				$corrupted_serialized
			);
		}

		// Check for HTML entity encoding issues.
		$posts_with_entities = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type IN ('post', 'page') 
			AND post_status = 'publish' 
			AND (
				post_content LIKE '%&lt;%' 
				OR post_content LIKE '%&gt;%'
				OR post_content LIKE '%&amp;nbsp;%'
			)"
		);

		if ( $posts_with_entities > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have HTML entities in content (likely import issue)', 'wpshadow' ),
				$posts_with_entities
			);
		}

		// Check for wpautop filter issues.
		$wpautop_filter = $GLOBALS['wp_filter']['the_content'] ?? null;
		
		if ( ! $wpautop_filter ) {
			$issues[] = __( 'the_content filter chain missing (formatting will not work)', 'wpshadow' );
		} else {
			$wpautop_exists = false;
			
			foreach ( $wpautop_filter->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( 'wpautop' === $callback['function'] ) {
						$wpautop_exists = true;
						break 2;
					}
				}
			}

			if ( ! $wpautop_exists ) {
				$issues[] = __( 'wpautop filter not registered (paragraph formatting disabled)', 'wpshadow' );
			}
		}

		// Check for do_shortcode on the_content.
		$do_shortcode_exists = false;
		
		if ( $wpautop_filter ) {
			foreach ( $wpautop_filter->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( 'do_shortcode' === $callback['function'] ) {
						$do_shortcode_exists = true;
						break 2;
					}
				}
			}
		}

		if ( ! $do_shortcode_exists && ! empty( $posts_with_shortcodes ) ) {
			$issues[] = __( 'do_shortcode not on the_content filter (shortcodes will not render)', 'wpshadow' );
		}

		// Check for inline styles being stripped.
		$posts_with_inline_styles = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type IN ('post', 'page') 
			AND post_status = 'publish' 
			AND post_content LIKE '%style=%'"
		);

		if ( $posts_with_inline_styles > 0 ) {
			// Check if KSES is stripping styles.
			$test_content = '<div style="color: red;">Test</div>';
			$filtered_content = wp_kses_post( $test_content );
			
			if ( strpos( $filtered_content, 'style=' ) === false ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts */
					__( '%d posts have inline styles but KSES may strip them', 'wpshadow' ),
					$posts_with_inline_styles
				);
			}
		}

		// Check for Classic Editor vs Block Editor mismatch.
		$classic_posts = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = 'classic-editor-remember' 
			AND meta_value = 'classic-editor'"
		);

		$block_posts = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type IN ('post', 'page') 
			AND post_content LIKE '%<!-- wp:%'"
		);

		if ( $classic_posts > 0 && $block_posts > 0 ) {
			$issues[] = __( 'Mix of Classic and Block Editor content (may affect import consistency)', 'wpshadow' );
		}

		// Check for reusable blocks.
		$reusable_blocks = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'wp_block'"
		);

		$block_references = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_content LIKE '%<!-- wp:block {\"ref\":%'"
		);

		if ( $block_references > 0 && $reusable_blocks === 0 ) {
			$issues[] = __( 'Posts reference reusable blocks but none exist (incomplete import)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/import-lost-shortcodes-formatting',
			);
		}

		return null;
	}
}
