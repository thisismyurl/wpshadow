<?php
/**
 * Archive Template Functionality Diagnostic
 *
 * Validates that archive templates (category, tag, date) are properly
 * implemented with appropriate content display and navigation.
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
 * Archive Template Functionality Diagnostic Class
 *
 * Checks archive template implementation.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Archive_Template_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'archive-template-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Archive Template Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates archive template implementation';

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
		$issues       = array();
		$template_dir = get_template_directory();

		// Check for archive templates.
		$archive_templates = array(
			'archive.php'  => __( 'General archive template', 'wpshadow' ),
			'category.php' => __( 'Category archive template', 'wpshadow' ),
			'tag.php'      => __( 'Tag archive template', 'wpshadow' ),
			'author.php'   => __( 'Author archive template', 'wpshadow' ),
			'date.php'     => __( 'Date archive template', 'wpshadow' ),
		);

		$missing_templates = array();
		foreach ( $archive_templates as $template => $description ) {
			if ( ! file_exists( $template_dir . '/' . $template ) ) {
				$missing_templates[ $template ] = $description;
			}
		}

		// Only the general archive.php is required; others are optional.
		if ( isset( $missing_templates['archive.php'] ) ) {
			$issues[] = __( 'Missing archive.php template (uses index.php)', 'wpshadow' );
		}

		// Check archive.php implementation if it exists.
		$archive_file = $template_dir . '/archive.php';
		if ( file_exists( $archive_file ) ) {
			$content = file_get_contents( $archive_file );

			// Check for archive title display.
			if ( false === stripos( $content, 'the_archive_title' ) && false === stripos( $content, 'get_the_archive_title' ) ) {
				$issues[] = __( 'Archive template does not display archive title', 'wpshadow' );
			}

			// Check for archive description.
			if ( false === stripos( $content, 'the_archive_description' ) && false === stripos( $content, 'category_description' ) ) {
				$issues[] = __( 'Archive template does not display archive description (SEO opportunity)', 'wpshadow' );
			}

			// Check for pagination.
			$pagination_functions = array( 'the_posts_pagination', 'paginate_links', 'posts_nav_link' );
			$has_pagination       = false;

			foreach ( $pagination_functions as $func ) {
				if ( false !== stripos( $content, $func ) ) {
					$has_pagination = true;
					break;
				}
			}

			if ( ! $has_pagination ) {
				$issues[] = __( 'Archive template lacks pagination', 'wpshadow' );
			}

			// Check for post loop.
			if ( false === stripos( $content, 'have_posts' ) ) {
				$issues[] = __( 'Archive template lacks proper WordPress loop', 'wpshadow' );
			}
		}

		// Check if archives are disabled.
		$feeds_enabled = get_option( 'blog_public', 1 );
		// Note: blog_public doesn't directly control archives, but it's related.

		// Check for archive content depth.
		$posts_per_page = get_option( 'posts_per_page', 10 );
		if ( $posts_per_page > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: posts per page */
				__( 'Archive shows %d posts per page (may cause slow loading)', 'wpshadow' ),
				$posts_per_page
			);
		}

		// Check for category/tag descriptions.
		$categories_with_desc = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'meta_query' => array(
					array(
						'key'     => 'description',
						'compare' => '!=',
						'value'   => '',
					),
				),
			)
		);

		$total_categories = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'fields'     => 'count',
			)
		);

		if ( $total_categories > 5 && count( $categories_with_desc ) < $total_categories * 0.5 ) {
			$issues[] = sprintf(
				/* translators: 1: categories with descriptions, 2: total categories */
				__( 'Only %1$d of %2$d categories have descriptions (SEO opportunity)', 'wpshadow' ),
				count( $categories_with_desc ),
				$total_categories
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of archive template issues */
					__( 'Found %d archive template implementation issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'details'      => array(
					'issues'             => $issues,
					'missing_templates'  => $missing_templates,
					'recommendation'     => __( 'Ensure archive.php includes title, description, pagination, and proper loop.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
