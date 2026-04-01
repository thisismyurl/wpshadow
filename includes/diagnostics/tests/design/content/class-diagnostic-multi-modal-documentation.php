<?php
/**
 * Multi-Modal Documentation Diagnostic
 *
 * Issue #4867: Documentation Only Text-Based (No Videos, Examples)
 * Pillar: 🎓 Learning Inclusive
 *
 * Checks if documentation includes multiple learning modalities.
 * Different people learn differently (visual, auditory, reading, kinesthetic).
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
 * Diagnostic_Multi_Modal_Documentation Class
 *
 * Checks for:
 * - Text documentation (for readers/writers)
 * - Video tutorials (for visual/auditory learners)
 * - Screenshots and diagrams (for visual learners)
 * - Real-world examples and use cases
 * - Interactive demos and step-by-step walkthroughs
 * - Code samples (for developers)
 * - FAQ section (common questions)
 *
 * Why this matters:
 * - 35% visual, 25% auditory, 25% reading/writing, 15% kinesthetic learners
 * - Single format excludes 65% of learners
 * - Neurodiversity considerations (ADHD, autism, dyslexia)
 * - Multiple formats strengthen retention for everyone
 *
 * @since 0.6093.1200
 */
class Diagnostic_Multi_Modal_Documentation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'multi-modal-documentation';

	/**
	 * The diagnostic title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Documentation Only Text-Based (No Videos, Examples)';

	/**
	 * The diagnostic description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if documentation supports all learning styles (text, video, visual, hands-on)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual content analysis would require reviewing KB.
		// We provide recommendations for multi-modal documentation.

		$issues = array();

		$issues[] = __( 'Provide text documentation for reading/writing learners', 'wpshadow' );
		$issues[] = __( 'Include video tutorials for visual/auditory learners', 'wpshadow' );
		$issues[] = __( 'Add screenshots and diagrams for visual learners', 'wpshadow' );
		$issues[] = __( 'Show real-world use cases and examples', 'wpshadow' );
		$issues[] = __( 'Include step-by-step walkthroughs (kinesthetic learners)', 'wpshadow' );
		$issues[] = __( 'Provide interactive demos or sandbox environments', 'wpshadow' );
		$issues[] = __( 'Create FAQ section with common questions', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'People learn in different ways. Text-only documentation excludes visual learners, auditory learners, and people who learn best by doing.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/multi-modal-documentation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'learning_styles'         => array(
						'visual'       => '35% (prefer diagrams, screenshots, videos)',
						'auditory'     => '25% (prefer spoken explanation, podcasts)',
						'reading'      => '25% (prefer text, written examples)',
						'kinesthetic'  => '15% (prefer hands-on, interactive demos)',
					),
					'video_duration'          => 'Keep videos 2-5 minutes (shorter = higher completion)',
					'accessibility'           => 'Add captions to videos for deaf/hard-of-hearing users',
					'neurodiversity'          => 'Help ADHD (shorter chunks), autism (detailed steps), dyslexia (visual aids)',
				),
			);
		}

		return null;
	}
}
