<?php
/**
 * Live Chat Support Diagnostic
 *
 * Checks whether live chat or instant support is available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Conversion
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Live Chat Support Diagnostic Class
 *
 * Verifies that an instant support option is available.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Live_Chat_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'live-chat-support';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Live Chat or Instant Support Option';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if an instant support option is available on key pages';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$chat_plugins = array(
			'tawkto-live-chat/tawkto.php' => 'Tawk.to',
			'livechat/livechat.php'       => 'LiveChat',
			'crisp/crisp.php'             => 'Crisp',
			'chaty/chats.php'             => 'Chaty',
			'hubspot-all-in-one-marketing-forms-analytics/hubspot.php' => 'HubSpot',
			'intercom/intercom.php'       => 'Intercom',
			'drift/drift.php'             => 'Drift',
		);

		$active_chat = array();
		foreach ( $chat_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_chat[] = $plugin_name;
			}
		}

		$stats['chat_tools'] = ! empty( $active_chat ) ? implode( ', ', $active_chat ) : 'none';

		if ( empty( $active_chat ) ) {
			$issues[] = __( 'No live chat or instant support tool detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Instant support helps visitors get quick answers and feel confident about buying. A simple chat option can remove hesitation.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/live-chat-support',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
