<?php
/**
 * No Voice of Customer (VOC) Program Diagnostic
 *
 * Checks if customer feedback is systematically collected and analyzed.
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
 * Voice of Customer Program Diagnostic
 *
 * Detects when customer feedback isn't systematically collected or analyzed.
 * VOC programs reduce churn by 40%, improve product-market fit, and identify
 * high-value customers. Without VOC, you're guessing what customers want.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Voice_Of_Customer_Voc_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-voice-of-customer-program';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Voice of Customer (VOC) Program Active';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer feedback is systematically collected and analyzed';

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
		$has_voc = self::check_voc_program();

		if ( ! $has_voc ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No Voice of Customer (VOC) program detected. You\'re making decisions without customer input. VOC programs reduce churn by 40%, improve product-market fit, and identify high-value customers. Implement: 1) NPS surveys (quarterly), 2) Customer interviews (5+ per month), 3) Feedback forms (website), 4) Support ticket analysis, 5) Social media monitoring, 6) Customer advisory board.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/voice-of-customer-program?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'program_active'      => false,
					'voc_methods'         => self::get_voc_methods(),
					'business_impact'     => array(
						'churn_reduction' => '40%',
						'product_fit'     => 'Significantly improves',
						'retention'       => '50%+ increase possible',
					),
					'recommendation'      => __( 'Start with NPS surveys and customer interviews to understand pain points', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if VOC program exists
	 *
	 * @since 0.6093.1200
	 * @return bool True if VOC program detected
	 */
	private static function check_voc_program(): bool {
		// Check for survey plugins
		$plugins = get_plugins();

		$voc_keywords = array( 'survey', 'feedback', 'nps', 'review', 'testimonial', 'customer voice' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $voc_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		// Check for testimonials
		$testimonials = get_posts( array(
			'post_type'      => array( 'page', 'post' ),
			'numberposts'    => 5,
			's'              => 'testimonial OR review OR feedback',
		) );

		if ( ! empty( $testimonials ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get VOC collection methods
	 *
	 * @since 0.6093.1200
	 * @return array Array of VOC methods with descriptions
	 */
	private static function get_voc_methods(): array {
		return array(
			array(
				'method'       => 'NPS Surveys (Quarterly)',
				'effort'       => 'Low',
				'insights'     => 'Overall satisfaction and loyalty',
				'frequency'    => 'Every 3 months',
			),
			array(
				'method'       => 'Customer Interviews (5+/month)',
				'effort'       => 'High',
				'insights'     => 'Deep understanding of pain points and needs',
				'frequency'    => 'Ongoing',
			),
			array(
				'method'       => 'Feedback Forms (Website)',
				'effort'       => 'Low',
				'insights'     => 'Specific feature requests and usability issues',
				'frequency'    => 'Continuous',
			),
			array(
				'method'       => 'Support Ticket Analysis',
				'effort'       => 'Low',
				'insights'     => 'Common problems and missing features',
				'frequency'    => 'Monthly review',
			),
			array(
				'method'       => 'Social Media Listening',
				'effort'       => 'Medium',
				'insights'     => 'Brand perception and competitive gaps',
				'frequency'    => 'Weekly monitoring',
			),
			array(
				'method'       => 'Customer Advisory Board',
				'effort'       => 'High',
				'insights'     => 'Strategic feedback from power users',
				'frequency'    => 'Quarterly meetings',
			),
		);
	}
}
