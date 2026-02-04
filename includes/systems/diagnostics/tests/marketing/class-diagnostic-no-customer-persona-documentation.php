<?php
/**
 * No Customer Persona Documentation Diagnostic
 *
 * Checks if customer personas are documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Personas Diagnostic
 *
 * You can't talk to everyone. Talk to someone specific.
 * Personas are customer avatars that guide all decisions.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Customer_Persona_Documentation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-persona-documentation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Persona Documentation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer personas are documented';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_customer_personas() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No customer personas detected. Personas are customer avatars that guide all decisions. Define 2-4 primary personas: Demographics (age, company size, industry), Psychographics (values, goals, pain points), Behaviors (how they buy, consume content), Quote (what they say). Example: "IT Manager Mike: 45-year-old CTO at enterprise company, overwhelmed, wants proven solutions, quotes \'we don\'t have time to experiment\'". Use personas for: Product roadmap (what would help Mike?), Marketing (which message resonates?), Sales (how does Mike buy?), Support (how do we help Mike?). Real personas come from talking to customers (not guessing).', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-personas',
				'details'     => array(
					'issue'          => __( 'No customer personas detected', 'wpshadow' ),
					'recommendation' => __( 'Document 2-4 primary customer personas', 'wpshadow' ),
					'business_impact' => __( 'Inability to align marketing, sales, and product on target customer', 'wpshadow' ),
					'persona_framework' => self::get_persona_framework(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if personas are documented.
	 *
	 * @since  1.6035.0000
	 * @return bool True if personas detected, false otherwise.
	 */
	private static function has_customer_personas() {
		$persona_posts = self::count_posts_by_keywords(
			array(
				'persona',
				'customer avatar',
				'target customer',
				'ideal customer',
				'buyer profile',
			)
		);

		return $persona_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since  1.6035.0000
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get persona framework.
	 *
	 * @since  1.6035.0000
	 * @return array Persona framework template.
	 */
	private static function get_persona_framework() {
		return array(
			'demographics' => array(
				'age'          => __( 'Age range (e.g., 35-45)', 'wpshadow' ),
				'role'         => __( 'Job title/role (e.g., VP of Marketing)', 'wpshadow' ),
				'company_size' => __( 'Company size (startup/SMB/enterprise)', 'wpshadow' ),
				'industry'     => __( 'Industry (e.g., SaaS, retail, healthcare)', 'wpshadow' ),
				'income'       => __( 'Income/budget range', 'wpshadow' ),
			),
			'psychographics' => array(
				'goals'       => __( 'Professional goals (what are they trying to achieve?)', 'wpshadow' ),
				'pain_points' => __( 'Pain points (what frustrates them?)', 'wpshadow' ),
				'values'      => __( 'Values (what\'s important to them?)', 'wpshadow' ),
				'priorities'  => __( 'Top 3 priorities (how do they measure success?)', 'wpshadow' ),
			),
			'behaviors'    => array(
				'tech_comfort' => __( 'Tech comfort level (novice/intermediate/advanced)', 'wpshadow' ),
				'buying_cycle' => __( 'Buying cycle (impulse/weeks/months/quarters)', 'wpshadow' ),
				'content'      => __( 'Content preferences (text/video/podcasts)', 'wpshadow' ),
				'decision'     => __( 'Decision makers involved (individual/committee)', 'wpshadow' ),
			),
			'quote'        => array(
				'statement' => __( 'A direct quote they might say (makes them real)', 'wpshadow' ),
				'example'  => __( '"We don\'t have time to learn new tools"', 'wpshadow' ),
			),
			'usage'        => array(
				'product'       => __( 'How they use your product (primary use case)', 'wpshadow' ),
				'frequency'     => __( 'Usage frequency (daily/weekly/monthly)', 'wpshadow' ),
				'success_metric' => __( 'How they measure success (what\'s a win?)', 'wpshadow' ),
			),
		);
	}
}
