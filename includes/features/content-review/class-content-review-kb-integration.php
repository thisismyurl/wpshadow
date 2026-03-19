<?php
/**
 * Content Review KB Article Integration
 *
 * Provides filter hooks for KB articles and training courses related to
 * diagnostics and families, used by the content review wizard.
 *
 * @package    WPShadow
 * @subpackage Features/ContentReview
 * @since 1.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter hook: Get KB articles for a diagnostic
 *
 * @since 1.6093.1200
 * @param  array  $articles Array of KB articles (initially empty).
 * @param  string $slug     Diagnostic slug.
 * @return array KB articles for the diagnostic.
 *
 * Example:
 * add_filter( 'wpshadow_kb_articles_for_diagnostic', function( $articles, $slug ) {
 *     if ( 'content-missing-alt-text' === $slug ) {
 *         $articles[] = array(
 *             'title' => 'How to Add Alt Text to Images',
 *             'url'   => 'https://wpshadow.com/kb/alt-text/',
 *             'excerpt' => 'Alt text improves accessibility and SEO...',
 *         );
 *     }
 *     return $articles;
 * }, 10, 2 );
 */
// Hook is used in Content_Review_Manager::get_related_kb_articles()

/**
 * Filter hook: Get training courses for a family
 *
 * @since 1.6093.1200
 * @param  array  $courses Array of training courses (initially empty).
 * @param  string $family  Diagnostic family name.
 * @return array Training courses for the family.
 *
 * Example:
 * add_filter( 'wpshadow_training_courses_for_family', function( $courses, $family ) {
 *     if ( 'seo' === $family ) {
 *         $courses[] = array(
 *             'title'       => 'WordPress SEO Fundamentals',
 *             'url'         => 'https://wpshadow.com/academy/courses/wordpress-seo/',
 *             'duration'    => '45 minutes',
 *             'description' => 'Learn on-page SEO optimization...',
 *         );
 *     }
 *     return $courses;
 * }, 10, 2 );
 */
// Hook is used in Content_Review_Manager::get_related_training()

/**
 * Filter hook: Content report generated
 *
 * Fires after a content report is generated in the wizard.
 *
 * @since 1.6093.1200
 * @param  int   $post_id Post ID being reported on.
 * @param  array $report  Full report data including diagnostics and summary.
 *
 * Example:
 * add_action( 'wpshadow_content_report_generated', function( $post_id, $report ) {
 *     // Log report to external service, email to user, etc.
 * }, 10, 2 );
 */
// Hook is used in Content_Review_Generate_Report_Handler::handle()

/**
 * Get KB article registry compatibility helper
 *
 * Connects to the existing KB article registry if available.
 *
 * @since 1.6093.1200
 * @return void
 */
function wpshadow_register_content_review_kb_hooks() {
	// Check if KB registry exists.
	if ( ! class_exists( 'WPShadow\Features\Academy\KB_Article_Registry' ) ) {
		return;
	}

	// Register filter to fetch KB articles for diagnostics.
	add_filter(
		'wpshadow_kb_articles_for_diagnostic',
		function( $articles, $slug ) {
			// Map diagnostics to KB articles.
			$kb_mapping = array(
				'content-missing-alt-text'     => 'alt-text-guide',
				'content-long-paragraphs'      => 'readable-content',
				'content-missing-h1'           => 'heading-structure',
				'content-missing-meta-descriptions' => 'meta-descriptions',
				'keyword-stuffing'             => 'keyword-optimization',
				'broken-internal-links'        => 'internal-linking',
				'missing-featured-image'       => 'featured-images',
			);

			$kb_id = $kb_mapping[ $slug ] ?? null;
			if ( $kb_id ) {
				$article = apply_filters( 'wpshadow_get_kb_article', null, $kb_id );
				if ( $article ) {
					$articles[] = $article;
				}
			}

			return $articles;
		},
		10,
		2
	);

	// Register filter to fetch training for families.
	add_filter(
		'wpshadow_training_courses_for_family',
		function( $courses, $family ) {
			$family_mapping = array(
				'seo'             => 'wordpress-seo',
				'accessibility'   => 'wcag-compliance',
				'content'         => 'content-strategy',
				'readability'     => 'readable-content',
				'code-quality'    => 'wordpress-best-practices',
			);

			$course_id = $family_mapping[ $family ] ?? null;
			if ( $course_id ) {
				$course = apply_filters( 'wpshadow_get_training_course', null, $course_id );
				if ( $course ) {
					$courses[] = $course;
				}
			}

			return $courses;
		},
		10,
		2
	);
}

add_action( 'plugins_loaded', 'wpshadow_register_content_review_kb_hooks' );
