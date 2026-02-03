<?php
/**
 * AI Chatbot Diagnostic
 *
 * Tests whether the site implements an AI-powered chatbot for customer support automation.
 *
 * @since   1.26034.0200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Chatbot Diagnostic Class
 *
 * AI chatbots provide 24/7 automated customer support, lead qualification,
 * and instant responses, improving satisfaction and reducing support costs.
 *
 * @since 1.26034.0200
 */
class Diagnostic_Ai_Chatbot extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ai-chatbot';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'AI Chatbot Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site implements an AI-powered chatbot for customer support automation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'automation';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$chatbot_score = 0;
		$max_score = 7;

		// Check for chatbot plugins.
		$chatbot_plugins = array(
			'tidio-live-chat/tidio-live-chat.php' => 'Tidio',
			'chatra/chatra.php' => 'Chatra',
			'tawk-to-live-chat/tawk-to-live-chat.php' => 'Tawk.to',
			'wp-chatbot/wp-chatbot.php' => 'WP Chatbot',
			'collect-chat/collect-chat.php' => 'Collect.chat',
			'chatbot/chatbot.php' => 'Chatbot',
		);

		$has_chatbot = false;
		$active_chatbot = '';
		foreach ( $chatbot_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_chatbot = true;
				$active_chatbot = $plugin_name;
				$chatbot_score++;
				break;
			}
		}

		if ( ! $has_chatbot ) {
			$issues[] = __( 'No chatbot plugin detected', 'wpshadow' );
		}

		// Check for AI-powered features (not just basic chat).
		$ai_features = self::check_ai_features();
		if ( $ai_features ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'No AI-powered natural language processing detected', 'wpshadow' );
		}

		// Check for 24/7 availability indicators.
		$always_available = self::check_availability();
		if ( $always_available ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'Chatbot not configured for 24/7 availability', 'wpshadow' );
		}

		// Check for knowledge base integration.
		$kb_integration = self::check_knowledge_base_integration();
		if ( $kb_integration ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'No knowledge base integration for automated answers', 'wpshadow' );
		}

		// Check for lead capture functionality.
		$lead_capture = self::check_lead_capture();
		if ( $lead_capture ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'No lead capture or contact form in chatbot', 'wpshadow' );
		}

		// Check for multilingual support.
		$multilingual = self::check_multilingual_support();
		if ( $multilingual ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'No multilingual support in chatbot', 'wpshadow' );
		}

		// Check for analytics and insights.
		$analytics = self::check_chatbot_analytics();
		if ( $analytics ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'No chatbot analytics or conversation insights', 'wpshadow' );
		}

		// Determine severity based on chatbot implementation.
		$chatbot_percentage = ( $chatbot_score / $max_score ) * 100;

		if ( $chatbot_percentage < 30 ) {
			// Minimal or no chatbot implementation.
			$severity = 'medium';
			$threat_level = 45;
		} elseif ( $chatbot_percentage < 60 ) {
			// Basic chatbot implementation.
			$severity = 'low';
			$threat_level = 30;
		} else {
			// Good chatbot implementation - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Chatbot implementation percentage */
				__( 'Chatbot implementation at %d%%. ', 'wpshadow' ),
				(int) $chatbot_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'AI chatbots can handle 80% of routine inquiries, reducing support costs', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ai-chatbot',
			);
		}

		return null;
	}

	/**
	 * Check for AI-powered features.
	 *
	 * @since  1.26034.0200
	 * @return bool True if AI features exist, false otherwise.
	 */
	private static function check_ai_features() {
		// Check for advanced chatbot plugins with AI.
		$ai_chatbot_plugins = array(
			'ibm-watson-assistant/ibm-watson-assistant.php',
			'dialogflow-chatbot/dialogflow-chatbot.php',
			'chatbot-with-ibm-watson/chatbot-with-ibm-watson.php',
		);

		foreach ( $ai_chatbot_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for third-party AI chatbot services.
		$has_ai_service = apply_filters( 'wpshadow_chatbot_has_ai', false );

		return $has_ai_service;
	}

	/**
	 * Check for 24/7 availability.
	 *
	 * @since  1.26034.0200
	 * @return bool True if always available, false otherwise.
	 */
	private static function check_availability() {
		// If chatbot plugin is active, assume it's configured for 24/7.
		$chatbot_plugins = array(
			'tidio-live-chat/tidio-live-chat.php',
			'tawk-to-live-chat/tawk-to-live-chat.php',
			'wp-chatbot/wp-chatbot.php',
		);

		foreach ( $chatbot_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_chatbot_always_available', false );
	}

	/**
	 * Check for knowledge base integration.
	 *
	 * @since  1.26034.0200
	 * @return bool True if KB integration exists, false otherwise.
	 */
	private static function check_knowledge_base_integration() {
		// Check if knowledge base plugin is active.
		$kb_plugins = array(
			'knowledge-base/knowledge-base.php',
			'echo-knowledge-base/echo-knowledge-base.php',
			'wp-knowledge-base/wp-knowledge-base.php',
		);

		$has_kb = false;
		foreach ( $kb_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_kb = true;
				break;
			}
		}

		// Check for custom post types related to KB.
		if ( ! $has_kb ) {
			$post_types = get_post_types( array( 'public' => true ), 'names' );
			foreach ( $post_types as $post_type ) {
				if ( strpos( strtolower( $post_type ), 'kb' ) !== false || 
					 strpos( strtolower( $post_type ), 'knowledge' ) !== false ||
					 strpos( strtolower( $post_type ), 'faq' ) !== false ) {
					$has_kb = true;
					break;
				}
			}
		}

		return apply_filters( 'wpshadow_chatbot_has_kb_integration', $has_kb );
	}

	/**
	 * Check for lead capture functionality.
	 *
	 * @since  1.26034.0200
	 * @return bool True if lead capture exists, false otherwise.
	 */
	private static function check_lead_capture() {
		// Check for form plugins that might integrate with chatbot.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'wpforms-lite/wpforms.php',
			'ninja-forms/ninja-forms.php',
			'gravityforms/gravityforms.php',
		);

		foreach ( $form_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_chatbot_has_lead_capture', false );
	}

	/**
	 * Check for multilingual support.
	 *
	 * @since  1.26034.0200
	 * @return bool True if multilingual support exists, false otherwise.
	 */
	private static function check_multilingual_support() {
		// Check for translation plugins.
		$translation_plugins = array(
			'polylang/polylang.php',
			'sitepress-multilingual-cms/sitepress.php', // WPML.
			'translatepress-multilingual/index.php',
			'weglot/weglot.php',
		);

		foreach ( $translation_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_chatbot_has_multilingual', false );
	}

	/**
	 * Check for chatbot analytics.
	 *
	 * @since  1.26034.0200
	 * @return bool True if analytics exists, false otherwise.
	 */
	private static function check_chatbot_analytics() {
		// Most chatbot platforms include analytics by default.
		$chatbot_with_analytics = array(
			'tidio-live-chat/tidio-live-chat.php',
			'tawk-to-live-chat/tawk-to-live-chat.php',
		);

		foreach ( $chatbot_with_analytics as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_chatbot_has_analytics', false );
	}
}
