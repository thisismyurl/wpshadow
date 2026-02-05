<?php
/**
 * Post Format Support Treatment
 *
 * Checks if post formats (aside, gallery, video, etc.) are properly
 * supported by the theme.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1340
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Format Support Treatment Class
 *
 * Verifies that post formats are properly supported and configured
 * in the active theme.
 *
 * @since 1.6033.1340
 */
class Treatment_Post_Format_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-format-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Post Format Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post formats are properly supported by the theme';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Get theme's post format support.
		$theme_supports_formats = current_theme_supports( 'post-formats' );
		$supported_formats      = get_theme_support( 'post-formats' );

		// Check if theme supports post formats at all.
		if ( ! $theme_supports_formats ) {
			// Check if there are posts using formats.
			$posts_with_formats = $wpdb->get_var(
				"SELECT COUNT(DISTINCT tr.object_id)
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				WHERE tt.taxonomy = 'post_format'
				AND t.slug != 'post-format-standard'"
			);

			if ( $posts_with_formats > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts */
					__( 'Theme does not support post formats, but %d posts use them', 'wpshadow' ),
					$posts_with_formats
				);
			}
		} else {
			// Theme supports formats - check which ones.
			$supported_formats_array = is_array( $supported_formats ) && isset( $supported_formats[0] ) ? $supported_formats[0] : array();

			// Check for posts using unsupported formats.
			if ( ! empty( $supported_formats_array ) ) {
				$unsupported_format_posts = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(DISTINCT tr.object_id)
						FROM {$wpdb->term_relationships} tr
						INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
						INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
						WHERE tt.taxonomy = 'post_format'
						AND t.slug NOT IN ('" . implode( "','", array_map( 'esc_sql', array_merge( array( 'post-format-standard' ), array_map( function( $format ) {
							return 'post-format-' . $format;
						}, $supported_formats_array ) ) ) ) . "')"
					)
				);

				if ( $unsupported_format_posts > 0 ) {
					$issues[] = sprintf(
						/* translators: %d: number of posts */
						__( '%d posts use formats not supported by current theme', 'wpshadow' ),
						$unsupported_format_posts
					);
				}
			}

			// Check if theme has template files for supported formats.
			$theme          = wp_get_theme();
			$template_dir   = $theme->get_template_directory();
			$missing_templates = array();

			foreach ( $supported_formats_array as $format ) {
				$template_file = $template_dir . '/content-' . $format . '.php';
				$format_file   = $template_dir . '/format-' . $format . '.php';

				// Check if either naming convention exists.
				if ( ! file_exists( $template_file ) && ! file_exists( $format_file ) ) {
					$missing_templates[] = $format;
				}
			}

			if ( ! empty( $missing_templates ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated list of formats */
					__( 'Theme missing template files for formats: %s', 'wpshadow' ),
					implode( ', ', $missing_templates )
				);
			}
		}

		// Check for orphaned post format terms (formats with no posts).
		$orphaned_formats = $wpdb->get_var(
			"SELECT COUNT(t.term_id)
			FROM {$wpdb->terms} t
			INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			LEFT JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
			WHERE tt.taxonomy = 'post_format'
			AND tr.object_id IS NULL"
		);

		if ( $orphaned_formats > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of formats */
				__( '%d post format terms have no posts assigned', 'wpshadow' ),
				$orphaned_formats
			);
		}

		// Check for posts with multiple format assignments (invalid).
		$multiple_formats = $wpdb->get_var(
			"SELECT COUNT(object_id)
			FROM (
				SELECT tr.object_id, COUNT(*) as format_count
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE tt.taxonomy = 'post_format'
				GROUP BY tr.object_id
				HAVING format_count > 1
			) as multi_format_subquery"
		);

		if ( $multiple_formats > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have multiple formats assigned (invalid)', 'wpshadow' ),
				$multiple_formats
			);
		}

		// Check if post format UI is showing but theme doesn't support.
		if ( ! $theme_supports_formats ) {
			// Check if there's a recent post with format metadata (indicates UI was used).
			$recent_format_use = $wpdb->get_var(
				"SELECT COUNT(DISTINCT p.ID)
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE tt.taxonomy = 'post_format'
				AND p.post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
			);

			if ( $recent_format_use > 0 ) {
				$issues[] = __( 'Post formats being used despite no theme support', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-format-support',
			);
		}

		return null;
	}
}
