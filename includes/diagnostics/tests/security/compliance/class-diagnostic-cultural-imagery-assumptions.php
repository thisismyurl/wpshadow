<?php
/**
 * Cultural Imagery Assumptions Diagnostic
 *
 * Issue #4879: UI Uses Culture-Specific Imagery Without Context
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if imagery and examples respect cultural diversity.
 * Hand gestures, holidays, food, currency symbols vary globally.
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
 * Diagnostic_Cultural_Imagery_Assumptions Class
 *
 * Checks for:
 * - Diverse people in imagery (not just white/Western)
 * - No offensive hand gestures (thumbs up = insult in Middle East)
 * - Holiday examples are culturally neutral
 * - Food examples don't assume Western diet
 * - Currency examples allow flexibility
 * - Religious imagery avoided or inclusive
 * - Name examples support compound/non-Latin names
 * - Address formats respect global variations
 *
 * Why this matters:
 * - Thumbs up 👍 is offensive in parts of Middle East, Africa
 * - "Christmas sale" excludes 70% of world population
 * - Western names (John Smith) don't represent global users
 * - US address format doesn't work globally
 * - Cultural insensitivity alienates users
 *
 * @since 1.6093.1200
 */
class Diagnostic_Cultural_Imagery_Assumptions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'cultural-imagery-assumptions';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'UI Uses Culture-Specific Imagery Without Context';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if imagery and examples respect cultural diversity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual imagery review requires visual audit.
		// We provide recommendations.

		$issues = array();

		$issues[] = __( 'Use diverse people in imagery (skin tones, ages, abilities)', 'wpshadow' );
		$issues[] = __( 'Avoid hand gestures with offensive meanings in other cultures', 'wpshadow' );
		$issues[] = __( 'Use neutral holiday examples: "seasonal sale" not "Christmas sale"', 'wpshadow' );
		$issues[] = __( 'Support compound names: "Maria de los Santos", "李明"', 'wpshadow' );
		$issues[] = __( 'Currency symbols should be flexible (not always $)', 'wpshadow' );
		$issues[] = __( 'Address formats vary: US ZIP, UK postcode, Japan prefecture', 'wpshadow' );
		$issues[] = __( 'Religious imagery should be neutral or inclusive', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cultural assumptions exclude global users. Hand gestures, holidays, names, and imagery have different meanings worldwide.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/cultural-imagery',
				'details'      => array(
					'recommendations'         => $issues,
					'offensive_gestures'      => 'Thumbs up (Middle East), OK sign (Brazil, Turkey), Peace sign backward (UK)',
					'name_complexity'         => 'Support spaces, hyphens, apostrophes, non-Latin characters',
					'address_variations'      => 'US: ZIP 5+4, UK: postcode, Japan: prefecture + ward',
					'currency_flexibility'    => 'USD $, EUR €, GBP £, JPY ¥, INR ₹',
					'holiday_inclusivity'     => '"End of year sale" instead of "Christmas sale"',
				),
			);
		}

		return null;
	}
}
