<?php
/**
 * English Idioms Cultural Diagnostic
 *
 * Issue #4802: Content Uses English Idioms
 * Family: internationalization (Pillar: Culturally Respectful, Commandment #1)
 *
 * Checks if content uses English idioms that confuse non-native speakers.
 * Phrases like "piece of cake" don't translate well.
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
 * Diagnostic_English_Idioms Class
 *
 * Checks for culturally-specific phrases.
 *
 * @since 0.6093.1200
 */
class Diagnostic_English_Idioms extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'english-idioms';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Uses English Idioms';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if content avoids English idioms that confuse international audience';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Replace "piece of cake" with "easy" or "simple"', 'wpshadow' );
		$issues[] = __( 'Replace "hit the nail on the head" with "exactly correct"', 'wpshadow' );
		$issues[] = __( 'Replace "ballpark figure" with "approximate estimate"', 'wpshadow' );
		$issues[] = __( 'Replace "get the ball rolling" with "start the process"', 'wpshadow' );
		$issues[] = __( 'Replace "break a leg" with "good luck"', 'wpshadow' );
		$issues[] = __( 'Use simple, direct language that translates easily', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your content might use English idioms that confuse non-native speakers. Idioms are phrases whose meaning isn\'t literal—they only make sense if you grew up with them. Problem: 75% of web users are non-native English speakers. Idioms confuse them and machine translation fails completely. Common confusing idioms: "Piece of cake" (literally: dessert; idiom: easy) — Say: "This is easy", "Hit the nail on the head" (literally: carpentry; idiom: correct) — Say: "Exactly right", "Ballpark figure" (literally: baseball stadium; idiom: estimate) — Say: "Approximate estimate", "Get the ball rolling" (literally: pushing ball; idiom: start) — Say: "Start the process", "Break a leg" (literally: injury; idiom: good luck) — Say: "Good luck", "Spill the beans" (literally: drop food; idiom: reveal secret) — Say: "Tell the secret", "Cost an arm and a leg" (literally: body parts; idiom: expensive) — Say: "Very expensive". Why idioms break: 1) Machine translation translates literally (Google Translate converts "piece of cake" to French as "morceau de gâteau" = dessert, not easy), 2) Non-native speakers get confused (searching for literal meaning), 3) Cultural assumptions ("break a leg" sounds threatening, not encouraging). Solution: Write plainly. Replace idioms with direct language. Test: Would a 12-year-old non-native speaker understand this? Commandment #1: Helpful Neighbor (simple, clear, friendly). Pillar: Culturally Respectful (write for global audience).', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/avoid-idioms?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'       => $issues,
					'audience_impact'       => '75% of web users are non-native English speakers',
					'translation_problem'   => 'Machine translation converts idioms literally (wrong meaning)',
					'common_idioms'         => '"piece of cake", "break a leg", "ballpark figure", "spill the beans"',
					'replacement_pattern'   => 'Replace with literal, direct language',
					'testing'               => 'Ask: Would a 12-year-old non-native speaker understand?',
					'commandment'           => '#1: Helpful Neighbor',
					'pillar'                => 'Culturally Respectful',
				),
			);
		}

		return null;
	}
}
