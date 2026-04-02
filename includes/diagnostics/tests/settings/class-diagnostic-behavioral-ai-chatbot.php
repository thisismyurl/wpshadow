<?php
/**
 * Diagnostic: AI Chatbot Implemented
 *
 * Tests whether the site implements AI-powered chat assistance that handles
 * >50% of customer inquiries automatically.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4551
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
 * AI Chatbot Implemented Diagnostic
 *
 * Checks for AI-powered chatbot systems. AI chat handles 50-70% of inquiries
 * automatically, providing 24/7 support and freeing human agents for complex issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_AI_Chatbot extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-ai-chatbot';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'AI Chatbot Implemented';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site implements AI-powered chat assistance';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for AI chatbot implementation.
	 *
	 * Looks for AI-powered chat services and plugins.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for AI chatbot plugins/services.
		$ai_chat_plugins = array(
			'tidio-live-chat/tidio-live-chat.php'            => 'Tidio',
			'chatra-live-chat/chatra.php'                    => 'Chatra',
			'intercom/intercom.php'                          => 'Intercom',
			'drift/drift.php'                                => 'Drift',
		);

		foreach ( $ai_chat_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has AI-capable chat.
			}
		}

		// Check for AI-related JavaScript in scripts.
		global $wp_scripts;
		if ( isset( $wp_scripts->registered ) ) {
			$ai_keywords = array( 'ai', 'bot', 'watson', 'dialogflow', 'openai' );
			
			foreach ( $wp_scripts->registered as $handle => $script ) {
				foreach ( $ai_keywords as $keyword ) {
					if ( stripos( $handle, $keyword ) !== false ) {
						return null;
					}
				}
			}
		}

		// Only recommend for sites with support needs.
		$needs_support = false;
		
		if ( class_exists( 'WooCommerce' ) ) {
			$needs_support = true;
		}

		// Check for membership/services.
		$membership_indicators = array(
			class_exists( 'MeprUser' ),
			function_exists( 'pmpro_hasMembershipLevel' ),
		);

		foreach ( $membership_indicators as $indicator ) {
			if ( $indicator ) {
				$needs_support = true;
				break;
			}
		}

		// Check for service pages.
		$service_keywords = array( 'support', 'help', 'faq', 'contact' );
		$pages            = get_pages( array( 'number' => 30 ) );
		
		foreach ( $pages as $page ) {
			foreach ( $service_keywords as $keyword ) {
				if ( stripos( $page->post_title, $keyword ) !== false ) {
					$needs_support = true;
					break 2;
				}
			}
		}

		if ( ! $needs_support ) {
			return null; // Simple site, less critical.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No AI chatbot detected. AI-powered chat handles 50-70% of common questions automatically - instant answers 24/7. Frees human agents for complex issues. Modern AI chat (Tidio, Intercom, Drift) learns from conversations and improves over time. Provides instant support without staffing costs. Consider implementing AI chat for e-commerce/service sites.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ai-chatbot',
		);
	}
}
