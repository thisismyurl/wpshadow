<?php
/**
 * Content Reading Level Too Low Treatment
 *
 * Detects when content is overly simple for target audience.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Reading Level Too Low Treatment Class
 *
 * For technical/professional audiences, overly simple content damages credibility.
 * Grade 6 content on developer blog reduces authority.
 *
 * @since 1.6033.1645
 */
class Treatment_Content_Reading_Level_Too_Low extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-reading-level-too-low';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Reading Level Too Low';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detect when content is overly simple for professional/technical audiences';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if site targets technical audience
		$technical_audience = apply_filters( 'wpshadow_site_targets_technical_audience', false );
		if ( ! $technical_audience ) {
			return null; // Not applicable for general audiences
		}

		// Check reading level
		$reading_level = apply_filters( 'wpshadow_average_reading_level_grade', 0 );
		if ( $reading_level < 10 && $reading_level > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: grade level */
				__( 'Average reading level is grade %d; technical audience expects grade 12+ complexity', 'wpshadow' ),
				$reading_level
			);
		}

		// Check for technical depth
		$technical_depth = apply_filters( 'wpshadow_content_has_technical_depth', false );
		if ( ! $technical_depth ) {
			$issues[] = __( 'Technical audience expects in-depth explanations and technical terminology', 'wpshadow' );
		}

		// Check for industry jargon
		$uses_jargon = apply_filters( 'wpshadow_content_uses_industry_terminology', false );
		if ( ! $uses_jargon ) {
			$issues[] = __( 'Developer/professional content should use appropriate industry terminology', 'wpshadow' );
		}

		// Check for advanced concepts
		$advanced_content = apply_filters( 'wpshadow_content_covers_advanced_concepts', false );
		if ( ! $advanced_content ) {
			$issues[] = __( 'Professional audiences expect content that goes beyond basics', 'wpshadow' );
		}

		// Check for credential damage
		$authority_impact = apply_filters( 'wpshadow_simple_content_damages_authority', false );
		if ( $authority_impact ) {
			$issues[] = __( 'Overly simple content on technical blog damages credibility with expert audience', 'wpshadow' );
		}

		// Check for target audience mismatch
		$audience_mismatch = apply_filters( 'wpshadow_content_audience_mismatch', false );
		if ( $audience_mismatch ) {
			$issues[] = __( 'Content complexity should match target audience expertise level', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-reading-level-too-low',
			);
		}

		return null;
	}
}
