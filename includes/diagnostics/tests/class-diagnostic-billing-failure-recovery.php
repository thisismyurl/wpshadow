<?php
/**
 * Billing Failure Recovery Diagnostic
 *
 * Tests whether the site has automated recovery for failed subscription payments.
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
 * Billing Failure Recovery Diagnostic Class
 *
 * Failed subscription payments can result in 15-25% customer loss without
 * proper recovery processes. This diagnostic checks for automated retry
 * and recovery systems.
 *
 * @since 1.26034.0230
 */
class Diagnostic_Billing_Failure_Recovery extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'billing-failure-recovery';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Billing Failure Recovery';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site has automated recovery for failed subscription payments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'e-commerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for subscription sites.
		if ( ! self::has_subscriptions() ) {
			return null;
		}

		$issues = array();
		$recovery_score = 0;
		$max_score = 7;

		// Check for automatic retry systems.
		$auto_retry = self::check_automatic_retry();
		if ( $auto_retry ) {
			$recovery_score++;
		} else {
			$issues[] = __( 'No automatic payment retry system configured', 'wpshadow' );
		}

		// Check for dunning emails.
		$dunning_emails = self::check_dunning_emails();
		if ( $dunning_emails ) {
			$recovery_score++;
		} else {
			$issues[] = __( 'No dunning email sequence for failed payments', 'wpshadow' );
		}

		// Check for payment method update process.
		$update_process = self::check_payment_update();
		if ( $update_process ) {
			$recovery_score++;
		} else {
			$issues[] = __( 'No easy payment method update process', 'wpshadow' );
		}

		// Check for grace periods.
		$grace_period = self::check_grace_period();
		if ( $grace_period ) {
			$recovery_score++;
		} else {
			$issues[] = __( 'No grace period before subscription cancellation', 'wpshadow' );
		}

		// Check for failure notifications.
		$notifications = self::check_failure_notifications();
		if ( $notifications ) {
			$recovery_score++;
		} else {
			$issues[] = __( 'No immediate notification system for payment failures', 'wpshadow' );
		}

		// Check for alternative payment methods.
		$alt_payment = self::check_alternative_payment();
		if ( $alt_payment ) {
			$recovery_score++;
		} else {
			$issues[] = __( 'No alternative payment methods offered', 'wpshadow' );
		}

		// Check for recovery analytics.
		$analytics = self::check_recovery_analytics();
		if ( $analytics ) {
			$recovery_score++;
		} else {
			$issues[] = __( 'No tracking of billing failure recovery rates', 'wpshadow' );
		}

		// Determine severity based on recovery implementation.
		$recovery_percentage = ( $recovery_score / $max_score ) * 100;

		if ( $recovery_percentage < 40 ) {
			$severity = 'high';
			$threat_level = 70;
		} elseif ( $recovery_percentage < 70 ) {
			$severity = 'medium';
			$threat_level = 50;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Recovery system percentage */
				__( 'Billing failure recovery at %d%%. ', 'wpshadow' ),
				(int) $recovery_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Proper recovery can reduce involuntary churn by 15-25%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/billing-failure-recovery',
			);
		}

		return null;
	}

	/**
	 * Check if site has subscriptions.
	 *
	 * @since  1.26034.0230
	 * @return bool True if subscriptions detected, false otherwise.
	 */
	private static function has_subscriptions() {
		$subscription_plugins = array(
			'woocommerce-subscriptions/woocommerce-subscriptions.php',
			'memberpress/memberpress.php',
			'restrict-content-pro/restrict-content-pro.php',
			'paid-memberships-pro/paid-memberships-pro.php',
		);

		foreach ( $subscription_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for automatic retry.
	 *
	 * @since  1.26034.0230
	 * @return bool True if auto-retry exists, false otherwise.
	 */
	private static function check_automatic_retry() {
		// WooCommerce Subscriptions has built-in retry.
		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			return true;
		}

		// MemberPress has retry settings.
		if ( is_plugin_active( 'memberpress/memberpress.php' ) && class_exists( 'MeprOptions' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_payment_retry', false );
	}

	/**
	 * Check for dunning emails.
	 *
	 * @since  1.26034.0230
	 * @return bool True if dunning emails exist, false otherwise.
	 */
	private static function check_dunning_emails() {
		$keywords = array( 'payment failed', 'billing issue', 'update payment method', 'subscription expiring' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page', 'email' ),
					'posts_per_page' => 1,
					'post_status'    => 'any',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_dunning_emails', false );
	}

	/**
	 * Check for payment update process.
	 *
	 * @since  1.26034.0230
	 * @return bool True if update process exists, false otherwise.
	 */
	private static function check_payment_update() {
		// Check for account/billing pages.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'numberposts' => -1,
			)
		);

		foreach ( $pages as $page ) {
			$content = strtolower( $page->post_content . ' ' . $page->post_title );
			if ( strpos( $content, 'payment method' ) !== false || strpos( $content, 'update billing' ) !== false ) {
				return true;
			}
		}

		// WooCommerce My Account has payment methods.
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_payment_update', false );
	}

	/**
	 * Check for grace period.
	 *
	 * @since  1.26034.0230
	 * @return bool True if grace period exists, false otherwise.
	 */
	private static function check_grace_period() {
		// Most subscription plugins have grace periods by default.
		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			return true;
		}

		if ( is_plugin_active( 'memberpress/memberpress.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_grace_period', false );
	}

	/**
	 * Check for failure notifications.
	 *
	 * @since  1.26034.0230
	 * @return bool True if notifications exist, false otherwise.
	 */
	private static function check_failure_notifications() {
		// Check for notification plugins.
		$notification_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
			'automated-emails/automated-emails.php',
		);

		foreach ( $notification_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Subscription plugins typically have email notifications.
		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_failure_notifications', false );
	}

	/**
	 * Check for alternative payment methods.
	 *
	 * @since  1.26034.0230
	 * @return bool True if multiple payment methods exist, false otherwise.
	 */
	private static function check_alternative_payment() {
		if ( ! class_exists( 'WC_Payment_Gateways' ) ) {
			return false;
		}

		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		return count( $gateways ) > 1;
	}

	/**
	 * Check for recovery analytics.
	 *
	 * @since  1.26034.0230
	 * @return bool True if analytics tracking exists, false otherwise.
	 */
	private static function check_recovery_analytics() {
		// Check for analytics plugins.
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php',
			'google-site-kit/google-site-kit.php',
			'matomo/matomo.php',
		);

		foreach ( $analytics_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// WooCommerce has built-in analytics.
		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_recovery_analytics', false );
	}
}
