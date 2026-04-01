<?php
/**
 * Customer Communication Automation Diagnostic
 *
 * Checks if automated email and communication sequences are implemented.
 *
 * @package WPShadow\Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Customer Communication Automation
 *
 * Detects whether the site has automated communication workflows.
 */
class Diagnostic_Customer_Communication_Automation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'customer-communication-automation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Communication Automation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for automated communication and email sequences';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-retention';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'automatewoo/automatewoo.php'                      => 'AutomateWoo',
			'fluentcrm-pro/fluentcrm-pro.php'                  => 'FluentCRM Pro',
			'convertkit-commerce/convertkit-commerce.php'      => 'ConvertKit Commerce',
			'email-subscribers/email-subscribers.php'          => 'Email Subscribers',
			'mailchimp-for-wordpress/mailchimp-for-wordpress.php' => 'Mailchimp for WordPress',
			'activemail-cloud/activemail-cloud.php'            => 'ActiveMail Cloud',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_automation_tools']   = count( $active );
		$stats['automation_plugins_found']  = $active;

		if ( empty( $active ) ) {
			$issues[] = __( 'No customer communication automation system detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Automated communication sequences (welcome emails, abandoned cart recovery, post-purchase follow-ups) nurture customer relationships at scale, increasing retention and lifetime value while reducing manual effort.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/email-automation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
