<?php
/**
 * Screen Reader Compatibility Diagnostic
 *
 * Tests if site is compatible with screen readers for blind users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1320
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Screen Reader Compatibility Diagnostic Class
 *
 * Validates that the site works properly with screen readers
 * including proper semantic HTML and ARIA labels.
 *
 * @since 1.7034.1320
 */
class Diagnostic_Screen_Reader_Compatibility extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'screen-reader-compatibility';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Screen Reader Compatibility';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site is compatible with screen readers for blind users';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests screen reader compatibility including alt text, ARIA labels,
	 * semantic HTML, and heading hierarchy.
	 *
	 * @since  1.7034.1320
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for images without alt text.
		global $wpdb;
		$total_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
		);

		$images_without_alt = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			 LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
			 WHERE p.post_type = 'attachment'
			 AND p.post_mime_type LIKE 'image/%'
			 AND (pm.meta_value IS NULL OR pm.meta_value = '')"
		);

		$alt_text_coverage = $total_images > 0 ? ( ( $total_images - $images_without_alt ) / $total_images ) * 100 : 100;

		// Check for proper heading hierarchy.
		$recent_posts = $wpdb->get_results(
			"SELECT post_content FROM {$wpdb->posts}
			 WHERE post_type = 'post' AND post_status = 'publish'
			 ORDER BY post_date DESC LIMIT 10",
			ARRAY_A
		);

		$heading_issues = 0;
		foreach ( $recent_posts as $post ) {
			$content = $post['post_content'];
			// Check if h3 appears before h2 (heading hierarchy violation).
			if ( strpos( $content, '<h3' ) !== false && strpos( $content, '<h2' ) === false ) {
				++$heading_issues;
			}
		}

		// Check template files for semantic HTML.
		$header_file        = get_template_directory() . '/header.php';
		$uses_semantic_html = false;

		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			$semantic_tags  = array( '<header', '<nav', '<main', '<article', '<section', '<aside', '<footer' );
			foreach ( $semantic_tags as $tag ) {
				if ( strpos( $header_content, $tag ) !== false ) {
					$uses_semantic_html = true;
					break;
				}
			}
		}

		// Check for ARIA labels on forms.
		$has_aria_labels = false;
		if ( file_exists( $header_file ) ) {
			$header_content  = file_get_contents( $header_file );
			$has_aria_labels = ( strpos( $header_content, 'aria-label' ) !== false ) ||
							( strpos( $header_content, 'aria-labelledby' ) !== false );
		}

		// Check for screen reader text class.
		$style_css         = get_stylesheet_directory() . '/style.css';
		$has_sr_text_class = false;

		if ( file_exists( $style_css ) ) {
			$style_content     = file_get_contents( $style_css );
			$has_sr_text_class = ( strpos( $style_content, 'screen-reader-text' ) !== false ) ||
								( strpos( $style_content, 'sr-only' ) !== false );
		}

		// Check for empty links.
		$empty_links = 0;
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			preg_match_all( '/<a[^>]*>\s*<\/a>/', $header_content, $matches );
			$empty_links = count( $matches[0] );
		}

		// Check for link text quality.
		$generic_link_text = false;
		foreach ( $recent_posts as $post ) {
			$content = $post['post_content'];
			if ( strpos( $content, '>click here<' ) !== false ||
				strpos( $content, '>read more<' ) !== false ||
				strpos( $content, '>here<' ) !== false ) {
				$generic_link_text = true;
				break;
			}
		}

		// Check for language attribute.
		$lang_attribute     = get_bloginfo( 'language' );
		$has_lang_attribute = ! empty( $lang_attribute );

		// Check for issues.
		$issues = array();

		// Issue 1: Many images without alt text.
		if ( $alt_text_coverage < 80 ) {
			$issues[] = array(
				'type'        => 'missing_alt_text',
				'description' => sprintf(
					/* translators: %s: percentage of images with alt text */
					__( 'Only %s%% of images have alt text; screen readers cannot describe images', 'wpshadow' ),
					round( $alt_text_coverage, 1 )
				),
			);
		}

		// Issue 2: Heading hierarchy violations.
		if ( $heading_issues > 2 ) {
			$issues[] = array(
				'type'        => 'heading_hierarchy',
				'description' => sprintf(
					/* translators: %d: number of posts with issues */
					__( '%d posts have heading hierarchy violations; confuses screen reader navigation', 'wpshadow' ),
					$heading_issues
				),
			);
		}

		// Issue 3: Not using semantic HTML5 elements.
		if ( ! $uses_semantic_html ) {
			$issues[] = array(
				'type'        => 'no_semantic_html',
				'description' => __( 'Theme does not use semantic HTML5 elements; screen readers cannot identify page structure', 'wpshadow' ),
			);
		}

		// Issue 4: No ARIA labels on interactive elements.
		if ( ! $has_aria_labels ) {
			$issues[] = array(
				'type'        => 'no_aria_labels',
				'description' => __( 'No ARIA labels found; interactive elements not properly announced to screen readers', 'wpshadow' ),
			);
		}

		// Issue 5: No screen reader text utility class.
		if ( ! $has_sr_text_class ) {
			$issues[] = array(
				'type'        => 'no_sr_text_class',
				'description' => __( 'No screen-reader-text CSS class; cannot hide visual content while keeping it accessible', 'wpshadow' ),
			);
		}

		// Issue 6: Generic link text detected.
		if ( $generic_link_text ) {
			$issues[] = array(
				'type'        => 'generic_link_text',
				'description' => __( 'Generic link text ("click here", "read more") detected; not descriptive for screen readers', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site is not optimized for screen readers, preventing blind and visually impaired users from accessing content', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/screen-reader-compatibility',
				'details'      => array(
					'total_images'           => absint( $total_images ),
					'images_without_alt'     => absint( $images_without_alt ),
					'alt_text_coverage'      => round( $alt_text_coverage, 1 ) . '%',
					'heading_issues'         => $heading_issues,
					'uses_semantic_html'     => $uses_semantic_html,
					'has_aria_labels'        => $has_aria_labels,
					'has_sr_text_class'      => $has_sr_text_class,
					'empty_links'            => $empty_links,
					'generic_link_text'      => $generic_link_text,
					'has_lang_attribute'     => $has_lang_attribute,
					'issues_detected'        => $issues,
					'recommendation'         => __( 'Add alt text to images, use semantic HTML, add ARIA labels, fix heading hierarchy', 'wpshadow' ),
					'wcag_requirements'      => array(
						'WCAG 1.1.1' => 'Non-text Content - All images need alt text',
						'WCAG 1.3.1' => 'Info and Relationships - Use semantic HTML',
						'WCAG 2.4.6' => 'Headings and Labels - Descriptive headings',
						'WCAG 2.4.4' => 'Link Purpose - Descriptive link text',
						'WCAG 4.1.2' => 'Name, Role, Value - ARIA labels for custom controls',
					),
					'alt_text_guidelines'    => array(
						'Decorative images'  => 'Use empty alt="" ',
						'Informative images' => 'Describe the content/function',
						'Complex images'     => 'Provide detailed description in text',
						'Links with images'  => 'Describe destination, not image',
					),
					'screen_reader_text_css' => '.screen-reader-text { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(1px,1px,1px,1px); }',
					'semantic_html_examples' => array(
						'<header>'  => 'Site header',
						'<nav>'     => 'Navigation menus',
						'<main>'    => 'Main content',
						'<article>' => 'Independent content',
						'<aside>'   => 'Sidebar content',
						'<footer>'  => 'Site footer',
					),
					'screen_reader_usage'    => '7.3% of internet users rely on screen readers',
				),
			);
		}

		return null;
	}
}
