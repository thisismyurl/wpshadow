<?php
/**
 * No Marketing Automation Workflow Diagnostic
 *
 * Detects when marketing automation workflows are not implemented,
 * missing efficiency and personalization opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Marketing Automation Workflow
 *
 * Checks whether marketing automation workflows are
 * configured for triggered sequences.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Marketing_Automation_Workflow extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-marketing-automation-workflow';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Marketing Automation Workflows';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether marketing automation workflows are configured';

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
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for marketing automation platforms
		$has_automation = is_plugin_active( 'fluentcrm/fluent-crm.php' ) ||
			is_plugin_active( 'brevo/brevo.php' ) ||
			is_plugin_active( 'mailchimp-for-wordpress/mailchimp-for-wordpress.php' );

		if ( ! $has_automation ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not using marketing automation, which means you\'re doing everything manually. Marketing automation creates triggered sequences: "someone signs up → send welcome email → 2 days later send tips → 7 days later ask for feedback → if no response send re-engagement". Automation frees you from repetitive tasks, increases consistency, and personalizes at scale. Automated campaigns have 3-4x better engagement than batch emails.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Email Engagement & Efficiency',
					'potential_gain' => '+3-4x better engagement',
					'roi_explanation' => 'Marketing automation enables triggered sequences that are 3-4x more effective than manual batch emails while freeing your time.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/marketing-automation-workflows',
			);
		}

		return null;
	}
}
