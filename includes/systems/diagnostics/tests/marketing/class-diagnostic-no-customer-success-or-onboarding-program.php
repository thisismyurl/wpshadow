<?php
/**
 * No Customer Success or Onboarding Program Diagnostic
 *
 * Checks if structured customer onboarding and success program exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Onboarding Program Diagnostic
 *
 * Detects when customers don't get a structured onboarding or success program.
 * Good onboarding reduces churn by 50% because customers succeed faster and see
 * ROI immediately. Without onboarding, customers get lost and disappear.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Customer_Success_Or_Onboarding_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-onboarding-program';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Onboarding Program Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if structured customer onboarding and success program exists';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_onboarding = self::check_onboarding_program();

		if ( ! $has_onboarding ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No customer onboarding or success program detected. This is leaving money on the table. Good onboarding reduces churn by 50% and increases customer lifetime value by 40%. Customers who get onboarded see ROI faster and stick around longer. Implement: 1) Welcome email sequence, 2) Guided setup/tutorial, 3) First milestone celebration, 4) Success metrics tracking, 5) Check-in at 30/60/90 days.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-onboarding-program?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'program_exists'     => false,
					'onboarding_elements' => self::get_onboarding_elements(),
					'business_impact'    => array(
						'churn_reduction'   => '50%',
						'ltv_increase'      => '40%',
						'nps_improvement'   => '25-35 points',
					),
					'recommendation'     => __( 'Create structured onboarding sequence starting immediately after sign-up', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Check if onboarding program exists
	 *
	 * @since 0.6093.1200
	 * @return bool True if onboarding program detected
	 */
	private static function check_onboarding_program(): bool {
		// Check for onboarding-related plugins
		$plugins = get_plugins();

		$onboarding_keywords = array( 'onboard', 'tutorial', 'tour', 'guide', 'setup wizard', 'welcome' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $onboarding_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		// Check for email automation
		if ( class_exists( 'WooCommerce' ) || class_exists( 'AutomateWoo' ) ) {
			// Has e-commerce, likely has some automation
			return true;
		}

		// Check if welcome email is documented or exists
		$welcome_pages = get_pages( array(
			's'           => 'welcome OR onboard OR tutorial',
			'numberposts' => 10,
		) );

		if ( ! empty( $welcome_pages ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get recommended onboarding elements
	 *
	 * @since 0.6093.1200
	 * @return array Array of onboarding elements
	 */
	private static function get_onboarding_elements(): array {
		return array(
			array(
				'element'     => 'Welcome Email (Sent Immediately)',
				'purpose'     => 'Sets expectations, introduces next steps',
				'best_practice' => 'Personalized, warm tone, clear CTA',
			),
			array(
				'element'     => 'Educational Content (Week 1)',
				'purpose'     => 'Builds confidence, shows value quickly',
				'best_practice' => 'Video tutorial, getting started guide, FAQ',
			),
			array(
				'element'     => 'First Win Celebration (Week 2)',
				'purpose'     => 'Motivates customer, shows quick value',
				'best_practice' => 'Celebrate first action completed',
			),
			array(
				'element'     => 'Success Metrics Check (Month 1)',
				'purpose'     => 'Verifies customer is achieving goals',
				'best_practice' => 'Dashboard, reporting, KPI tracking',
			),
			array(
				'element'     => 'Proactive Support (Ongoing)',
				'purpose'     => 'Prevents getting stuck, identifies issues early',
				'best_practice' => 'Check-in emails at 30/60/90 days, support available',
			),
			array(
				'element'     => 'Advanced Features Unlock (Month 3)',
				'purpose'     => 'Keeps engagement high, drives feature adoption',
				'best_practice' => 'Guide to power features after basics mastered',
			),
		);
	}
}
