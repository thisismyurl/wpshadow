<?php
/**
 * No Customer Persona Development or Research Diagnostic
 *
 * Checks if customer personas are developed and documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Persona Diagnostic
 *
 * Detects when customer personas aren't developed or documented.
 * Without personas, marketing is generic and ineffective. With personas,
 * messaging resonates, conversion improves by 30-50%, and marketing becomes
 * more efficient.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Customer_Persona_Development_Or_Research extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-persona-development';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Personas Developed & Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer personas are developed and documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$personas_documented = self::check_personas();

		if ( ! $personas_documented ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No customer personas detected. You\'re marketing to everyone, connecting with no one. Without personas, messaging is generic and ineffective. With personas, conversion improves 30-50%. Develop personas: 1) Interview best customers (10+ interviews), 2) Identify common patterns (goals, frustrations, demographics), 3) Create 3-5 detailed personas, 4) Document decision-making process, 5) Use in all marketing. Include: name, photo, job, income, goals, frustrations, how they find you.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-persona-development',
				'details'     => array(
					'personas_documented'   => false,
					'persona_elements'      => self::get_persona_elements(),
					'research_methods'      => self::get_research_methods(),
					'business_impact'       => '30-50% conversion improvement, better marketing alignment',
					'recommendation'        => __( 'Interview 10+ best customers and develop 3-5 detailed personas', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if personas exist
	 *
	 * @since 1.6093.1200
	 * @return bool True if personas documented
	 */
	private static function check_personas(): bool {
		// Check for persona-related pages
		$persona_posts = get_posts( array(
			'numberposts' => 10,
			's'           => 'persona OR customer profile OR buyer avatar',
		) );

		return ! empty( $persona_posts );
	}

	/**
	 * Get persona elements
	 *
	 * @since 1.6093.1200
	 * @return array Array of persona elements
	 */
	private static function get_persona_elements(): array {
		return array(
			'Demographics'   => 'Age, gender, income, location, education, job title',
			'Goals'          => 'What are they trying to achieve? What success looks like?',
			'Frustrations'   => 'What problems are they trying to solve? What keeps them up at night?',
			'Decision Drivers' => 'What influences their buying decision? Price? Quality? Speed?',
			'Objections'     => 'What are their initial concerns or hesitations?',
			'Information Sources' => 'Where do they find information? Blog? Podcasts? Social? Industry publications?',
			'Buying Behavior' => 'How do they typically buy? Research first? Impulse? Group decision?',
			'Loyalty Factors' => 'What makes them loyal or switch? What\'s important?',
		);
	}

	/**
	 * Get research methods
	 *
	 * @since 1.6093.1200
	 * @return array Array of research methods
	 */
	private static function get_research_methods(): array {
		return array(
			'Customer Interviews' => 'Direct 30-min conversations with 10+ best customers',
			'Surveys' => 'Online survey with 50+ responses capturing key questions',
			'Analytics Review' => 'Who visits your site? What pages do they view? How long?',
			'Support Tickets' => 'What questions do they ask? What problems do they have?',
			'Social Listening' => 'What are they saying about your product, competitors?',
			'Competitor Analysis' => 'Who are their customers? What are they saying?',
			'Industry Research' => 'Industry reports, trends, common customer profiles',
		);
	}
}
