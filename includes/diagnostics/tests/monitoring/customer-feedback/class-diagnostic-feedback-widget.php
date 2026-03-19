<?php
/**
 * Feedback Widget
 *
 * Checks if a feedback widget is present for easy feedback submission.
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
 * Feedback Widget Diagnostic
 */
class Diagnostic_Feedback_Widget extends Diagnostic_Base {

	protected static $slug = 'feedback-widget';
	protected static $title = 'Feedback Widget';
	protected static $description = 'Checks if a feedback widget is visible on the site';
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

		$widget_plugins = array(
			'hively-widget/hively.php'            => 'Hively Widget',
			'uservoice/uservoice.php'             => 'UserVoice',
			'zendesk-widget/zendesk.php'          => 'Zendesk Widget',
			'feedback-button/feedback.php'        => 'Feedback Button',
			'elfsight-feedback/elfsight.php'      => 'Elfsight Feedback',
		);

		$active_plugins = array();
		foreach ( $widget_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugins[] = $name;
			}
		}

		$stats['widget_plugins_found'] = count( $active_plugins );

		if ( empty( $active_plugins ) ) {
			$issues[] = __( 'No feedback widget found on your site', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'A feedback widget makes it easy for visitors to provide feedback without leaving the page', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/feedback-widget',
				'context'       => array( 'stats' => $stats, 'issues' => $issues ),
			);
		}

		return null;
	}
}
