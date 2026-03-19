<?php
/**
 * Help Text Quality Diagnostic
 *
 * Issue #4764: Help Text Explains What, Not Why
 * Pillar: 🎓 Learning Inclusive
 * Commandment: #1 (Helpful Neighbor)
 *
 * Checks if help text explains both what AND why.
 * Users need context and motivation, not just instructions.
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
 * Diagnostic_Help_Text_Quality Class
 *
 * Checks for explanatory help text that provides context.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Help_Text_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'help-text-quality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Help Text Explains What, Not Why';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if help text provides context and reasoning, not just instructions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Explain WHY a setting matters, not just WHAT it does', 'wpshadow' );
		$issues[] = __( 'Use analogies: "Like a lock on your front door"', 'wpshadow' );
		$issues[] = __( 'Show impact: "This could speed up your site by 30%"', 'wpshadow' );
		$issues[] = __( 'Add examples: "For a blog, use 10. For a store, use 20."', 'wpshadow' );
		$issues[] = __( 'Link to detailed documentation about the feature', 'wpshadow' );
		$issues[] = __( 'Avoid: "Set the value" → Better: "How many to show affects load time"', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your help text might say WHAT but not WHY. Compare: "Enter cache duration in seconds" (what) versus "Enter cache duration in seconds. Longer = faster site but stale content. We recommend 3600 (1 hour) for blogs, 300 (5 minutes) for news sites" (what + why + examples). Users need motivation and context to make informed decisions. They\'re asking: Why does this matter? What happens if I get it wrong? What do similar sites use? Help text should be like a knowledgeable friend explaining something over coffee, not a robot reading a technical manual. This follows Commandment #1 (Helpful Neighbor)—be friendly and educational, not just instructional.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/help-text-quality',
				'details'      => array(
					'recommendations'     => $issues,
					'commandment'         => 'Commandment #1: Helpful Neighbor Experience',
					'pattern'             => 'What it does + Why it matters + Example values + Learn more link',
					'bad_example'         => '"Maximum upload size (MB)" ← just what',
					'good_example'        => '"Maximum upload size affects what files users can upload. Larger = bigger files allowed but more server memory needed. We recommend 10MB for most sites." ← what + why + recommendation',
					'neurodiversity'      => 'Clear explanations help users with ADHD, autism, dyslexia',
				),
			);
		}

		return null;
	}
}
