<?php
/**
 * Missing Alt Text Diagnostic
 *
 * Detects images without alt text, creating accessibility barriers
 * for screen reader users and SEO penalties.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_Alt_Text Class
 *
 * Detects images without alt attributes.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Missing_Alt_Text extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-alt-text';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Alt Text on Images';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects images without alt text';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$alt_text_analysis = self::analyze_alt_text();

		if ( $alt_text_analysis['missing_count'] === 0 ) {
			return null; // All images have alt text
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of images, 2: percentage */
				__( '%1$d images (%2$d%%) missing alt text. Screen reader users hear "image" with no context. Google can\'t understand images without alt text = SEO penalty.', 'wpshadow' ),
				$alt_text_analysis['missing_count'],
				$alt_text_analysis['missing_percent']
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/alt-text',
			'family'       => self::$family,
			'meta'         => array(
				'total_images'   => $alt_text_analysis['total'],
				'missing_alt'    => $alt_text_analysis['missing_count'],
				'missing_percent' => $alt_text_analysis['missing_percent'] . '%',
			),
			'details'      => array(
				'why_alt_text_matters'      => array(
					__( '15% of population has disabilities (WHO)' ),
					__( 'Screen readers speak alt text aloud' ),
					__( 'Google uses alt text for image search ranking' ),
					__( 'Legal requirement (ADA, AODA, Section 508)' ),
					__( 'Shows when images fail to load' ),
				),
				'writing_effective_alt_text' => array(
					'Describe Function, Not Appearance' => array(
						'Bad: "Red button"',
						'Good: "Download PDF report button"',
					),
					'Be Specific and Concise' => array(
						'Bad: "Image"',
						'Good: "Golden retriever playing fetch in park"',
						'Length: 125 characters maximum',
					),
					'Decorative Images' => array(
						'Use: alt="" (empty string)',
						'Example: Decorative borders, spacers',
						'Screen reader: Skips completely',
					),
					'Complex Images' => array(
						'Charts: Describe data trends',
						'Infographics: Summarize key points',
						'Long descriptions: Use aria-describedby',
					),
				),
				'adding_alt_text_wordpress'  => array(
					'Media Library' => array(
						'Media → Library → Select image',
						'Alt Text field on right sidebar',
						'Save changes',
					),
					'Block Editor' => array(
						'Click image block',
						'Right sidebar → Alt text field',
						'Or: Image settings → Alt text',
					),
					'Classic Editor' => array(
						'Click image',
						'Edit icon → Advanced Options',
						'Alternative Text field',
					),
					'Bulk Adding' => array(
						'SEO plugins (Yoast, Rank Math)',
						'Media Library Assistant plugin',
						'Export/import CSV with alt text',
					),
				),
				'finding_missing_alt_text'   => array(
					'Manual Check' => array(
						'Browser DevTools: Inspect images',
						'Look for: <img alt="">',
						'Screen reader: NVDA (free) or JAWS',
					),
					'Automated Tools' => array(
						'WAVE browser extension (free)',
						'axe DevTools extension',
						'Accessibility Checker plugin',
					),
					'WordPress Dashboard' => array(
						'Media → Library',
						'Sort by: Images without alt text',
						'(requires compatible theme/plugin)',
					),
				),
				'legal_compliance'          => array(
					'ADA (Americans with Disabilities Act)' => __( 'US businesses must provide equal access' ),
					'WCAG 2.1 Level AA' => __( 'International standard (1.1.1 Non-text Content)' ),
					'Section 508' => __( 'US federal websites requirement' ),
					'AODA (Ontario)' => __( 'Canadian provincial requirement' ),
					'Lawsuits' => __( '3,500+ ADA website lawsuits in 2020' ),
				),
			),
		);
	}

	/**
	 * Analyze alt text coverage.
	 *
	 * @since  1.2601.2148
	 * @return array Alt text analysis.
	 */
	private static function analyze_alt_text() {
		// Get all attachments (images)
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$images = get_posts( $args );
		$total = count( $images );

		if ( $total === 0 ) {
			return array(
				'total'           => 0,
				'missing_count'   => 0,
				'missing_percent' => 0,
			);
		}

		$missing_count = 0;

		foreach ( $images as $image_id ) {
			$alt_text = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			if ( empty( $alt_text ) ) {
				$missing_count++;
			}
		}

		$missing_percent = round( ( $missing_count / $total ) * 100 );

		return array(
			'total'           => $total,
			'missing_count'   => $missing_count,
			'missing_percent' => (int) $missing_percent,
		);
	}
}
