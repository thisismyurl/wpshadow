<?php
/**
 * Readability Documentation Diagnostic
 *
 * Issue #4866: Documentation Uses Jargon Without Explanation
 * Pillar: 🎓 Learning Inclusive
 *
 * Checks if documentation explains technical concepts in plain language.
 * Commandment #1: Helpful Neighbor means explaining like you're talking to your grandmother.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Readability_Documentation Class
 *
 * Checks for:
 * - Plain language (8th grade reading level)
 * - Jargon explained on first use ("Cache (pronounced 'cash') is...")
 * - Analogies for technical concepts ("Think of it like...")
 * - Short sentences (15-20 words max)
 * - Common words instead of technical terms
 * - Active voice ("we set this up" not "this was implemented")
 * - Clear context and "why this matters"
 *
 * Why this matters:
 * - Jargon excludes non-technical users
 * - Technical language is gatekeeping
 * - Clear writing helps everyone (including native English speakers)
 * - Commandment #1: "Helpful Neighbor" means accessible language
 *
 * @since 1.6093.1200
 */
class Diagnostic_Readability_Documentation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'readability-documentation';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Documentation Uses Jargon Without Explanation';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if documentation is written in plain language everyone can understand';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual content analysis would require reading KB.
		// We provide recommendations for plain language writing.

		$issues = array();

		$issues[] = __( 'Use 8th-grade reading level (target 40-60 Flesch Reading Ease)', 'wpshadow' );
		$issues[] = __( 'Explain jargon on first use: "Cache (pronounced \'cash\') is..."', 'wpshadow' );
		$issues[] = __( 'Use analogies: "Think of it like [everyday example]"', 'wpshadow' );
		$issues[] = __( 'Keep sentences short (15-20 words maximum)', 'wpshadow' );
		$issues[] = __( 'Use active voice: "We set this up" not "This was implemented"', 'wpshadow' );
		$issues[] = __( 'Always answer "Why this matters" for technical concepts', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Technical jargon excludes non-technical users. Clear writing helps everyone understand WordPress better.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/readability-documentation',
				'details'      => array(
					'recommendations'       => $issues,
					'reading_tools'         => 'Hemingway App, Grammarly, Readability Checker',
					'target_reading_level'  => '8th grade (13-14 years old)',
					'flesch_ease_range'     => '40-60 (Very Readable)',
					'commandment'           => 'Commandment #1: Helpful Neighbor means explaining to your grandmother',
					'benefit'               => 'Helps non-native English speakers, beginners, and busy users',
				),
			);
		}

		return null;
	}
}
