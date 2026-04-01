<?php
/**
 * Voice Commerce Enabled Diagnostic
 *
 * Tests whether the site enables voice ordering capabilities for convenient purchasing.
 * Voice commerce integration allows customers to make purchases through voice commands
 * via devices like Amazon Alexa, Google Assistant, or Siri, streamlining the checkout
 * process for hands-free, on-the-go shopping experiences.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Enables_Voice_Commerce Class
 *
 * Diagnostic #40: Voice Commerce Enabled from Specialized & Emerging Success Habits.
 * Checks if the website has implemented voice commerce capabilities for voice-activated
 * purchasing through virtual assistants.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Enables_Voice_Commerce extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'enables-voice-commerce';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Voice Commerce Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site enables voice ordering capabilities for convenient purchasing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the diagnostic check.
	 *
	 * Voice commerce is an emerging channel for e-commerce businesses. This diagnostic
	 * checks for evidence of voice shopping integration through plugins, APIs, content
	 * references, and structured data that supports voice assistants.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Voice commerce plugins.
		$voice_commerce_plugins = array(
			'woocommerce-voice-commerce/woocommerce-voice-commerce.php',
			'voice-search/voice-search.php',
			'alexa-shopping/alexa-shopping.php',
			'voice-order/voice-order.php',
			'google-assistant-shopping/google-assistant-shopping.php',
		);

		$has_voice_plugin = false;
		foreach ( $voice_commerce_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_voice_plugin = true;
				break;
			}
		}

		if ( $has_voice_plugin ) {
			++$score;
			$score_details[] = __( '✓ Voice commerce plugin active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No voice commerce plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a voice commerce plugin to enable voice ordering capabilities', 'wpshadow' );
		}

		// Check 2: Voice assistant action pages or documentation.
		$voice_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'voice shop',
			)
		);

		if ( empty( $voice_pages ) ) {
			$voice_pages = get_posts(
				array(
					'post_type'      => 'page',
					'posts_per_page' => 5,
					'post_status'    => 'publish',
					's'              => 'alexa skill',
				)
			);
		}

		if ( ! empty( $voice_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Voice shopping documentation page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No voice shopping documentation found', 'wpshadow' );
			$recommendations[] = __( 'Create a page explaining how to shop via voice assistants', 'wpshadow' );
		}

		// Check 3: Product schema with voice-friendly attributes.
		$recent_products = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$has_voice_schema = false;
		foreach ( $recent_products as $product ) {
			$content = $product->post_content;
			if ( stripos( $content, 'schema.org/Product' ) !== false || stripos( $content, '"@type":"Product"' ) !== false ) {
				$has_voice_schema = true;
				break;
			}
		}

		if ( $has_voice_schema ) {
			++$score;
			$score_details[] = __( '✓ Product schema markup detected (voice-assistant friendly)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No product schema markup found', 'wpshadow' );
			$recommendations[] = __( 'Add structured data (schema.org/Product) to products for better voice search results', 'wpshadow' );
		}

		// Check 4: Voice ordering references in content.
		$voice_order_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'voice order',
			)
		);

		if ( empty( $voice_order_posts ) ) {
			$voice_order_posts = get_posts(
				array(
					'post_type'      => 'any',
					'posts_per_page' => 3,
					'post_status'    => 'publish',
					's'              => 'ask alexa',
				)
			);
		}

		if ( ! empty( $voice_order_posts ) ) {
			++$score;
			$score_details[] = __( '✓ Voice ordering referenced in content', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No voice ordering references in content', 'wpshadow' );
			$recommendations[] = __( 'Promote voice shopping capabilities in blog posts and product descriptions', 'wpshadow' );
		}

		// Check 5: WooCommerce with API enabled (prerequisite for voice commerce).
		if ( class_exists( 'WooCommerce' ) ) {
			$wc_api_enabled = get_option( 'woocommerce_api_enabled', 'no' );
			if ( 'yes' === $wc_api_enabled || function_exists( 'wc_rest_check_post_permissions' ) ) {
				++$score;
				$score_details[] = __( '✓ WooCommerce REST API enabled (supports voice integrations)', 'wpshadow' );
			} else {
				$score_details[]   = __( '✗ WooCommerce REST API not enabled', 'wpshadow' );
				$recommendations[] = __( 'Enable WooCommerce REST API to allow voice assistant integrations', 'wpshadow' );
			}
		} else {
			$score_details[]   = __( '✗ WooCommerce not installed', 'wpshadow' );
			$recommendations[] = __( 'Install WooCommerce to enable e-commerce and voice ordering', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			// Voice commerce capabilities are adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Voice commerce capabilities score: %d%%. Voice shopping is a rapidly growing channel that increases convenience and accessibility. Customers can reorder products, add items to cart, and complete purchases using voice commands through devices like Amazon Alexa, Google Assistant, or Siri.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/voice-commerce?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Voice commerce is projected to reach $40 billion in revenue by 2024. Early adopters gain competitive advantage in this emerging channel.', 'wpshadow' ),
		);
	}
}
