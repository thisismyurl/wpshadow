<?php
/**
 * No Customer Feedback or Survey System Diagnostic
 *
 * Detects when customer feedback systems are not implemented,
 * missing insights for product and service improvement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Customer Feedback or Survey System
 *
 * Checks whether customer feedback and survey systems are
 * implemented for gathering insights and improvements.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Customer_Feedback_Or_Survey_System extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-feedback-survey-system';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Feedback & Survey System';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether customer feedback systems are in place';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for feedback/survey plugins
		$has_feedback_system = is_plugin_active( 'wpforms-lite/wpforms.php' ) ||
			is_plugin_active( 'formidable-forms/formidable.php' ) ||
			is_plugin_active( 'gravity-forms/gravity-forms.php' ) ||
			is_plugin_active( 'qualtrics/qualtrics.php' );

		// Check for custom feedback system
		$has_custom_feedback = get_option( 'wpshadow_customer_feedback_system' );

		if ( ! $has_feedback_system && ! $has_custom_feedback ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not systematically collecting customer feedback, which means you\'re operating blind. You don\'t know what customers like, dislike, want next, or why they left. Simple feedback systems (quick surveys, email feedback buttons, post-purchase surveys) reveal: what to build next, what\'s causing churn, what messaging resonates. Companies that collect feedback systematically improve products 3-5x faster and have 20-40% higher retention.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Product-Market Fit',
					'potential_gain' => '20-40% better retention',
					'roi_explanation' => 'Systematic feedback collection reveals product-market mismatches early, enabling faster iteration and improvement.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/customer-feedback-survey-system?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
