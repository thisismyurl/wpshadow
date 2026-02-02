<?php
/**
 * CPT Archive Page Support Diagnostic
 *
 * Validates that custom post types with archives have proper configuration.
 * Checks for archive URL generation, template availability, and rewrite rules.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Archive Page Support Diagnostic Class
 *
 * Checks for archive page configuration issues with custom post types.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CPT_Archive_Page_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-archive-page-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Archive Page Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates CPT archive pages are properly configured and accessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cpt';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Get all registered post types.
		$post_types = get_post_types( array(), 'objects' );

		// Filter to only custom post types.
		$built_in = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation' );
		$custom_post_types = array_filter(
			$post_types,
			function ( $pt ) use ( $built_in ) {
				return ! in_array( $pt->name, $built_in, true );
			}
		);

		if ( empty( $custom_post_types ) ) {
			return null;
		}

		// Get all page slugs for conflict detection.
		$page_slugs = $wpdb->get_col(
			"SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status IN ('publish', 'private')"
		);

		foreach ( $custom_post_types as $cpt ) {
			// Skip if not public.
			if ( ! $cpt->public ) {
				continue;
			}

			// Count published posts for this CPT.
			$post_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
					$cpt->name
				)
			);

			// Check if CPT has posts but no archive enabled.
			if ( $post_count > 5 && ! $cpt->has_archive ) {
				$issues[] = sprintf(
					/* translators: 1: post type slug, 2: number of posts */
					__( 'CPT "%1$s" has %2$d published posts but no archive page enabled', 'wpshadow' ),
					esc_html( $cpt->name ),
					$post_count
				);
			}

			// If archive is enabled, check configuration.
			if ( $cpt->has_archive ) {
				// Verify publicly_queryable is true.
				if ( ! $cpt->publicly_queryable ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" has archive enabled but not publicly queryable (archive inaccessible)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}

				// Get archive slug.
				$archive_slug = is_string( $cpt->has_archive ) ? $cpt->has_archive : $cpt->name;

				// Check if archive slug conflicts with pages.
				if ( in_array( $archive_slug, $page_slugs, true ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: archive slug */
						__( 'CPT "%1$s" archive slug "%2$s" conflicts with existing page', 'wpshadow' ),
						esc_html( $cpt->name ),
						esc_html( $archive_slug )
					);
				}

				// Check if rewrite rules exist for archive.
				$rewrite_rules = get_option( 'rewrite_rules', array() );
				$has_archive_rule = false;

				if ( is_array( $rewrite_rules ) ) {
					foreach ( $rewrite_rules as $pattern => $replacement ) {
						if ( false !== strpos( $replacement, 'post_type=' . $cpt->name ) && false === strpos( $replacement, '&name=' ) ) {
							$has_archive_rule = true;
							break;
						}
					}
				}

				if ( ! $has_archive_rule ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" archive missing rewrite rules (permalinks may need flushing)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}

				// Check if theme has archive template.
				$template_file = 'archive-' . $cpt->name . '.php';
				$template_path = get_stylesheet_directory() . '/' . $template_file;

				if ( ! file_exists( $template_path ) ) {
					// Check parent theme if using child theme.
					if ( get_template_directory() !== get_stylesheet_directory() ) {
						$template_path = get_template_directory() . '/' . $template_file;
					}

					if ( ! file_exists( $template_path ) ) {
						// Also check for generic archive.php.
						$generic_archive = get_stylesheet_directory() . '/archive.php';
						if ( ! file_exists( $generic_archive ) ) {
							$issues[] = sprintf(
								/* translators: 1: post type slug, 2: template filename */
								__( 'CPT "%1$s" archive enabled but no template file ("%2$s" or "archive.php")', 'wpshadow' ),
								esc_html( $cpt->name ),
								esc_html( $template_file )
							);
						}
					}
				}

				// Check if archive URL is accessible (basic validation).
				$archive_link = get_post_type_archive_link( $cpt->name );
				if ( empty( $archive_link ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" archive enabled but get_post_type_archive_link() returns empty', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}

				// Check if rewrite is disabled (conflicts with has_archive).
				if ( false === $cpt->rewrite ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" has archive enabled but rewrite disabled (archive inaccessible)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cpt-archive-page-support',
			);
		}

		return null;
	}
}
