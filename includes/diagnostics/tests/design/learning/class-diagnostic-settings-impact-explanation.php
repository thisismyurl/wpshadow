<?php
/**
 * Settings Impact Explanation Diagnostic
 *
 * Issue #4781: Settings Don't Explain Impact of Changes
 * Family: learning (Commandment #8: Inspire Confidence)
 *
 * Checks if settings pages explain what each option does and its impact.
 * Clear explanations help users make informed decisions confidently.
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
 * Diagnostic_Settings_Impact_Explanation Class
 *
 * Checks for helpful setting descriptions.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Settings_Impact_Explanation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'settings-impact-explanation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Settings Don\'t Explain Impact of Changes';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if settings pages explain what each option does and its consequences';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Add help text below each setting explaining what it does', 'wpshadow' );
		$issues[] = __( 'Explain the impact: "This will slow your site" vs "This improves security"', 'wpshadow' );
		$issues[] = __( 'Provide examples: "Example: yoursite.com/blog instead of yoursite.com/?p=123"', 'wpshadow' );
		$issues[] = __( 'Show consequences: "Warning: This may break existing links"', 'wpshadow' );
		$issues[] = __( 'Link to documentation for complex settings', 'wpshadow' );
		$issues[] = __( 'Use plain language, avoid jargon', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your settings pages might not explain what changes will actually do. This makes users afraid to change anything (they might break something) or they make changes without understanding consequences. Good settings explanations: 1) What it does (in simple terms), 2) Why you\'d enable it (the benefit), 3) Any trade-offs ("May slow site slightly but improves security"), 4) Examples (show before/after), 5) Link to full documentation if complex. Compare bad vs good: Bad: "Enable caching" (What does that mean? What happens?), Good: "Enable caching - Stores copies of pages to load faster for visitors. May occasionally show outdated content until cache refreshes (every 24 hours). [Learn more]". Best practice: Assume user has never seen this setting before. Explain like you\'re answering "What will happen if I click this?" This aligns with Commandment #8: Inspire Confidence—users should feel empowered, not confused or afraid.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/settings-explanations?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'       => $issues,
					'commandment'           => '#8: Inspire Confidence (users should feel empowered)',
					'good_pattern'          => 'What + Why + Trade-offs + Example + Link',
					'bad_example'           => '"Enable feature X" (no context)',
					'good_example'          => '"Widget caching - Speeds up sidebar loading by storing widget HTML. Updates every 12 hours. [Learn more]"',
					'user_benefit'          => 'Users make informed decisions instead of guessing',
					'consequence_clarity'   => 'Always mention if change affects speed, security, or breaks things',
				),
			);
		}

		return null;
	}
}
