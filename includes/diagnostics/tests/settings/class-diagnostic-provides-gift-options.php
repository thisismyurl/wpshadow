<?php
/**
 * Gift Options Available Diagnostic
 *
 * Tests whether the site offers gift wrapping, messages, and registry options to increase
 * average order value. Gift features tap into gifting occasions and premium pricing.
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
 * Diagnostic_Provides_Gift_Options Class
 *
 * Diagnostic #11: Gift Options Available from Specialized & Emerging Success Habits.
 * Checks if the site offers gift wrapping and registry features.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Provides_Gift_Options extends Diagnostic_Base {

	protected static $slug = 'provides-gift-options';
	protected static $title = 'Gift Options Available';
	protected static $description = 'Tests whether the site offers gift wrapping, messages, and registry options';
	protected static $family = 'ecommerce-optimization';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check WooCommerce active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check gift wrapping plugins.
		$gift_plugins = array(
			'woocommerce-gift-wrapping/woocommerce-gift-wrapping.php',
			'gift-wrapper-for-woocommerce/gift-wrapper-for-woocommerce.php',
			'yith-woocommerce-gift-cards/init.php',
		);

		$has_gift_plugin = false;
		foreach ( $gift_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_gift_plugin = true;
				++$score;
				$score_details[] = __( '✓ Gift wrapping/cards plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_gift_plugin ) {
			$score_details[]   = __( '✗ No gift options plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install WooCommerce Gift Wrapping or gift cards plugin', 'wpshadow' );
		}

		// Check gift-related content.
		$gift_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				's'              => 'gift wrapping card message',
			)
		);

		if ( ! empty( $gift_content ) ) {
			++$score;
			$score_details[] = __( '✓ Gift services mentioned in content', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No gift services documentation', 'wpshadow' );
			$recommendations[] = __( 'Create a gift services page explaining wrapping, cards, and personalization options', 'wpshadow' );
		}

		// Check gift guide or registry.
		$gift_guides = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'gift guide registry wishlist',
			)
		);

		if ( ! empty( $gift_guides ) ) {
			++$score;
			$score_details[] = __( '✓ Gift guide or registry features present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No gift guides or registries found', 'wpshadow' );
			$recommendations[] = __( 'Create seasonal gift guides and/or wishlist/registry functionality', 'wpshadow' );
		}

		// Check personalization options.
		$personalization = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				's'              => 'personalize custom engraving monogram',
			)
		);

		if ( ! empty( $personalization ) ) {
			++$score;
			$score_details[] = __( '✓ Personalization options available', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No personalization services found', 'wpshadow' );
			$recommendations[] = __( 'Offer product customization (engraving, monograms, custom messages) for gift buyers', 'wpshadow' );
		}

		// Check gift certificates/cards.
		$gift_cards = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'gift card certificate voucher',
			)
		);

		if ( ! empty( $gift_cards ) ) {
			++$score;
			$score_details[] = __( '✓ Gift cards/certificates available', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No gift card products', 'wpshadow' );
			$recommendations[] = __( 'Offer digital and physical gift cards - they drive new customer acquisition', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 20;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Gift options score: %d%%. Gift services increase AOV by 25%% and customer lifetime value by 18%%. 40%% of online purchases are gifts, and gift wrapping add-ons convert at 35%% when offered at checkout.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/gift-options?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Gift options transform transactions into memorable experiences and encourage premium pricing through personalization.', 'wpshadow' ),
		);
	}
}
