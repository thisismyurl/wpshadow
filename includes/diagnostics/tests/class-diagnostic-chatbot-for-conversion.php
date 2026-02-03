<?php
/**
 * Chatbot for Conversion Diagnostic
 *
 * Tests whether the site uses chatbot assistance to guide purchase decisions and drive conversions.
 *
 * @since   1.26034.0230
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Chatbot for Conversion Diagnostic Class
 *
 * Conversion-optimized chatbots can increase sales by 20-40% by answering questions,
 * overcoming objections, and guiding buyers through the purchase process.
 *
 * @since 1.26034.0230
 */
class Diagnostic_Chatbot_For_Conversion extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'chatbot-for-conversion';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Chatbot for Conversion';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses chatbot assistance to guide purchase decisions and drive conversions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$chatbot_score = 0;
		$max_score = 7;

		// Check for chatbot plugins.
		$chatbot_installed = self::check_chatbot_installed();
		if ( $chatbot_installed ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'No chatbot or live chat functionality installed', 'wpshadow' );
		}

		// Check for proactive messaging.
		$proactive_messages = self::check_proactive_messaging();
		if ( $proactive_messages ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'No proactive chat messages to engage visitors', 'wpshadow' );
		}

		// Check for product recommendations.
		$recommendations = self::check_product_recommendations();
		if ( $recommendations ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'Chatbot not configured to recommend products', 'wpshadow' );
		}

		// Check for cart assistance.
		$cart_assistance = self::check_cart_assistance();
		if ( $cart_assistance ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'No chatbot assistance during checkout process', 'wpshadow' );
		}

		// Check for FAQ integration.
		$faq_integration = self::check_faq_integration();
		if ( $faq_integration ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'Chatbot not integrated with FAQ or knowledge base', 'wpshadow' );
		}

		// Check for lead capture.
		$lead_capture = self::check_lead_capture();
		if ( $lead_capture ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'Chatbot missing lead capture functionality', 'wpshadow' );
		}

		// Check for conversion tracking.
		$conversion_tracking = self::check_conversion_tracking();
		if ( $conversion_tracking ) {
			$chatbot_score++;
		} else {
			$issues[] = __( 'No tracking of chatbot conversion impact', 'wpshadow' );
		}

		// Determine severity based on chatbot implementation.
		$chatbot_percentage = ( $chatbot_score / $max_score ) * 100;

		if ( $chatbot_percentage < 30 ) {
			$severity = 'low';
			$threat_level = 35;
		} elseif ( $chatbot_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Chatbot conversion optimization percentage */
				__( 'Chatbot conversion strategy at %d%%. ', 'wpshadow' ),
				(int) $chatbot_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Conversion chatbots can increase sales by 20-40%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/chatbot-for-conversion',
			);
		}

		return null;
	}

	/**
	 * Check for chatbot installation.
	 *
	 * @since  1.26034.0230
	 * @return bool True if chatbot exists, false otherwise.
	 */
	private static function check_chatbot_installed() {
		$chatbot_plugins = array(
			'tidio-live-chat/tidio-live-chat.php',
			'tawk-to-live-chat/tawk-to.php',
			'wp-live-chat-support/wp-live-chat-support.php',
			'chatra-live-chat/chatra.php',
			'crisp/crisp.php',
			'messenger-customer-chat/facebook-messenger-customer-chat.php',
		);

		foreach ( $chatbot_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_chatbot', false );
	}

	/**
	 * Check for proactive messaging.
	 *
	 * @since  1.26034.0230
	 * @return bool True if proactive messages exist, false otherwise.
	 */
	private static function check_proactive_messaging() {
		// Tidio and Tawk.to support proactive messages.
		if ( is_plugin_active( 'tidio-live-chat/tidio-live-chat.php' ) ||
			 is_plugin_active( 'tawk-to-live-chat/tawk-to.php' ) ) {
			return true;
		}

		// Check for trigger-related content.
		$query = new \WP_Query(
			array(
				's'              => 'chat trigger proactive message',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for product recommendations.
	 *
	 * @since  1.26034.0230
	 * @return bool True if recommendations exist, false otherwise.
	 */
	private static function check_product_recommendations() {
		// WooCommerce with chatbot can offer recommendations.
		if ( class_exists( 'WooCommerce' ) &&
			 ( is_plugin_active( 'tidio-live-chat/tidio-live-chat.php' ) ||
			   is_plugin_active( 'tawk-to-live-chat/tawk-to.php' ) ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_chatbot_recommends_products', false );
	}

	/**
	 * Check for cart assistance.
	 *
	 * @since  1.26034.0230
	 * @return bool True if cart assistance exists, false otherwise.
	 */
	private static function check_cart_assistance() {
		// Check if chatbot is on checkout pages.
		if ( class_exists( 'WooCommerce' ) ) {
			$checkout_page = get_option( 'woocommerce_checkout_page_id' );
			if ( $checkout_page && ( is_plugin_active( 'tidio-live-chat/tidio-live-chat.php' ) ||
									is_plugin_active( 'tawk-to-live-chat/tawk-to.php' ) ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_chatbot_assists_cart', false );
	}

	/**
	 * Check for FAQ integration.
	 *
	 * @since  1.26034.0230
	 * @return bool True if FAQ integration exists, false otherwise.
	 */
	private static function check_faq_integration() {
		// Check for FAQ content.
		$has_faq = false;
		$faq_query = new \WP_Query(
			array(
				's'              => 'faq frequently asked questions',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		if ( $faq_query->have_posts() ) {
			$has_faq = true;
		}

		// If has FAQ and chatbot, likely integrated.
		if ( $has_faq && ( is_plugin_active( 'tidio-live-chat/tidio-live-chat.php' ) ||
						  is_plugin_active( 'tawk-to-live-chat/tawk-to.php' ) ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_chatbot_has_faq', false );
	}

	/**
	 * Check for lead capture.
	 *
	 * @since  1.26034.0230
	 * @return bool True if lead capture exists, false otherwise.
	 */
	private static function check_lead_capture() {
		// Most chatbot plugins have lead capture.
		if ( is_plugin_active( 'tidio-live-chat/tidio-live-chat.php' ) ||
			 is_plugin_active( 'tawk-to-live-chat/tawk-to.php' ) ||
			 is_plugin_active( 'chatra-live-chat/chatra.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_chatbot_captures_leads', false );
	}

	/**
	 * Check for conversion tracking.
	 *
	 * @since  1.26034.0230
	 * @return bool True if tracking exists, false otherwise.
	 */
	private static function check_conversion_tracking() {
		// Check for analytics.
		if ( is_plugin_active( 'google-site-kit/google-site-kit.php' ) ||
			 is_plugin_active( 'matomo/matomo.php' ) ) {
			return true;
		}

		// Chatbot platforms have their own analytics.
		if ( is_plugin_active( 'tidio-live-chat/tidio-live-chat.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_tracks_chatbot_conversions', false );
	}
}
