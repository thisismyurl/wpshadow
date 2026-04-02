<?php
/**
 * Live Chat Support Diagnostic
 *
 * Checks if live chat support is implemented for real-time customer assistance.
 *
 * @package WPShadow\Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Live Chat Support Implementation
 *
 * Detects whether the site offers live chat for customer support.
 */
class Diagnostic_Live_Chat_Support extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'live-chat-support';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Live Chat Support';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for real-time live chat support';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-support';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'tidio-live-chat-and-chatbots/tidio.php'           => 'Tidio Live Chat',
			'tawkto/tawk.php'                                  => 'Tawk.to',
			'intercom/intercom.php'                            => 'Intercom',
			'drift/drift.php'                                  => 'Drift',
			'wp-live-chat-support/wp-live-chat-support.php'    => 'WP Live Chat Support',
			'zendesk-chat/zendesk-chat.php'                    => 'Zendesk Chat',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_chat_tools']  = count( $active );
		$stats['chat_plugins_found'] = $active;

		if ( empty( $active ) ) {
			$issues[] = __( 'No live chat support system detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Live chat support provides immediate assistance to website visitors, reducing bounce rates and increasing customer satisfaction. Real-time communication can significantly improve conversion rates and customer retention.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/live-chat-support',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
