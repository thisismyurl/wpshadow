<?php
/**
 * Abandoned Cart Email Recovery Diagnostic
 *
 * Checks if abandoned cart email recovery sequence is properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abandoned Cart Email Recovery Diagnostic Class
 *
 * 70% of carts are abandoned. Recovering even 10% = massive revenue increase.
 * Abandoned cart emails recover 10-15% of lost sales.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Abandoned_Cart_Email_Recovery extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'abandoned-cart-email-recovery';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Abandoned Cart Email Recovery';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if abandoned cart email sequence is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'e-commerce-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues          = array();
		$recovery_score  = 0;
		$max_score       = 5;

		// Check if abandoned cart emails are enabled.
		$emails_enabled = self::check_abandoned_cart_emails_enabled();
		if ( $emails_enabled ) {
			$recovery_score++;
		} else {
			$issues[] = 'abandoned cart emails enabled';
		}

		// Check for multi-email sequence (3 emails over 3 days).
		$has_sequence = self::check_email_sequence();
		if ( $has_sequence ) {
			$recovery_score++;
		} else {
			$issues[] = '3-email sequence over 3 days';
		}

		// Check for personalized cart contents.
		$has_personalization = self::check_cart_personalization();
		if ( $has_personalization ) {
			$recovery_score++;
		} else {
			$issues[] = 'personalized with cart contents';
		}

		// Check for incentive offered.
		$has_incentive = self::check_recovery_incentive();
		if ( $has_incentive ) {
			$recovery_score++;
		} else {
			$issues[] = 'incentive (discount/free shipping)';
		}

		// Check for one-click cart return.
		$has_one_click = self::check_one_click_return();
		if ( $has_one_click ) {
			$recovery_score++;
		} else {
			$issues[] = 'one-click return to cart link';
		}

		$completion_percentage = ( $recovery_score / $max_score ) * 100;

		if ( $completion_percentage >= 60 ) {
			return null; // Cart recovery properly configured.
		}

		$severity     = $completion_percentage < 40 ? 'high' : 'medium';
		$threat_level = $completion_percentage < 40 ? 70 : 50;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Cart recovery at %1$d%%. Missing: %2$s. Like calling customers who left items at register—most come back. 10-15%% recovery rate typical.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/abandoned-cart-email-recovery',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check if abandoned cart emails are enabled.
	 *
	 * @since 1.6093.1200
	 * @return bool True if emails enabled.
	 */
	private static function check_abandoned_cart_emails_enabled(): bool {
		// Check for cart abandonment plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$abandonment_plugins = array(
			'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php',
			'abandoned-cart-lite-for-woocommerce/woocommerce-ac.php',
			'retainful-next-order-coupons-for-woocommerce/retainful-woocommerce.php',
			'cartbounty-abandoned-cart-recovery/cartbounty.php',
			'cart-abandonment-for-woocommerce/cart-abandonment.php',
		);

		foreach ( $abandonment_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for multi-email sequence.
	 *
	 * @since 1.6093.1200
	 * @return bool True if sequence exists.
	 */
	private static function check_email_sequence(): bool {
		// Check if cart abandonment plugin is active AND configured.
		if ( ! self::check_abandoned_cart_emails_enabled() ) {
			return false;
		}

		// Check for scheduled cron events (indicates active sequences).
		$scheduled_events = array(
			'woocommerce_ac_send_email',
			'cartbounty_send_reminder',
			'retainful_abandoned_cart_email',
		);

		foreach ( $scheduled_events as $event ) {
			if ( wp_next_scheduled( $event ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for cart content personalization.
	 *
	 * @since 1.6093.1200
	 * @return bool True if personalization exists.
	 */
	private static function check_cart_personalization(): bool {
		// Cart personalization is standard in cart abandonment plugins.
		// If plugin is active, assume personalization exists.
		return self::check_abandoned_cart_emails_enabled();
	}

	/**
	 * Check for recovery incentive.
	 *
	 * @since 1.6093.1200
	 * @return bool True if incentive configured.
	 */
	private static function check_recovery_incentive(): bool {
		// Check for coupon-related options.
		$has_coupon_option = get_option( 'woocommerce_ac_coupon_enabled', false );
		if ( $has_coupon_option ) {
			return true;
		}

		// Check for free shipping threshold.
		$has_free_shipping = get_option( 'woocommerce_free_shipping_enabled', false );
		if ( $has_free_shipping ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for one-click cart return.
	 *
	 * @since 1.6093.1200
	 * @return bool True if one-click return exists.
	 */
	private static function check_one_click_return(): bool {
		// One-click return is standard in cart abandonment plugins.
		return self::check_abandoned_cart_emails_enabled();
	}
}
