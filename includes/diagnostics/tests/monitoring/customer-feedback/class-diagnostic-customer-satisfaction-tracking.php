<?php
/**
 * Customer Satisfaction Tracking
 *
 * Checks if customer satisfaction is being tracked.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Satisfaction Tracking Diagnostic
 */
class Diagnostic_Customer_Satisfaction_Tracking extends Diagnostic_Base {

	protected static $slug = 'customer-satisfaction-tracking';
	protected static $title = 'Customer Satisfaction Tracking';
	protected static $description = 'Checks if customer satisfaction metrics are tracked';
	protected static $family = 'customer-feedback';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$satisfaction_plugins = array(
			'happyforms/happyforms.php'         => 'Happy Forms',
			'wp-feedback-form/feedback.php'     => 'WP Feedback Form',
			'satisfaction-survey/survey.php'    => 'Satisfaction Survey',
			'site-feedback/site-feedback.php'   => 'Site Feedback',
		);

		$active_plugins = array();
		foreach ( $satisfaction_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugins[] = $name;
			}
		}

		$stats['satisfaction_tools_found'] = count( $active_plugins );

		if ( empty( $active_plugins ) ) {
			$issues[] = __( 'No customer satisfaction tracking tool found', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Tracking satisfaction helps you measure and improve customer experience', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/customer-satisfaction-tracking',
				'context'       => array( 'stats' => $stats, 'issues' => $issues ),
			);
		}

		return null;
	}
}
