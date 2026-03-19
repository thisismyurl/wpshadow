<?php
/**
 * Diagnostic: Chatbot for Conversion
 *
 * Tests whether the site uses chatbot assistance to guide purchase decisions
 * and drive conversions (increases conversions by 10-30%).
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4538
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Chatbot for Conversion Diagnostic
 *
 * Checks for chatbot/live chat implementation. Proactive chat assistance
 * increases conversions by 10-30% by answering questions in real-time.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_Chatbot_Conversion extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-chatbot-for-conversion';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Chatbot for Conversion';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses chatbot to guide purchase decisions';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for chatbot implementation.
	 *
	 * Detects live chat, chatbot, and messaging plugins.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for live chat/chatbot plugins.
		$chat_plugins = array(
			'livechat-inc-for-wordpress/livechat.php'        => 'LiveChat',
			'tawk-to-live-chat/tawk-to.php'                  => 'Tawk.to',
			'tidio-live-chat/tidio-live-chat.php'            => 'Tidio',
			'chatra-live-chat/chatra.php'                    => 'Chatra',
			'crisp-live-chat/crisp.php'                      => 'Crisp',
			'intercom/intercom.php'                          => 'Intercom',
			'drift/drift.php'                                => 'Drift',
			'messenger-customer-chat/facebook-messenger-customer-chat.php' => 'Facebook Messenger',
		);

		foreach ( $chat_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has chat capability.
			}
		}

		// Check for chatbot JavaScript in header/footer.
		global $wp_scripts;
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				$keywords = array( 'chat', 'intercom', 'drift', 'crisp', 'tawk', 'tidio' );
				foreach ( $keywords as $keyword ) {
					if ( strpos( strtolower( $handle ), $keyword ) !== false ) {
						return null;
					}
				}
			}
		}

		// Only recommend if site has e-commerce or complex products.
		$needs_chat = false;
		
		if ( class_exists( 'WooCommerce' ) ) {
			$needs_chat = true;
		}

		// Check if site has services/consulting.
		$pages = get_pages();
		foreach ( $pages as $page ) {
			if ( preg_match( '/(service|consult|pricing|contact)/i', $page->post_title ) ) {
				$needs_chat = true;
				break;
			}
		}

		if ( ! $needs_chat ) {
			return null; // Chat less critical for simple sites.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No chatbot or live chat detected. Real-time chat assistance answers questions instantly, reduces abandonment, and increases conversions by 10-30%. Consider implementing live chat (Tawk.to, Tidio) or AI chatbot for 24/7 support.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 38,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/chatbot-conversion',
		);
	}
}
