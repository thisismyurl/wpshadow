<?php
/**
 * Pause Subscription Option Diagnostic
 *
 * Tests whether the site offers subscription pause option that reduces cancellations.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pause Subscription Option Diagnostic Class
 *
 * Offering subscription pause as an alternative to cancellation can reduce churn
 * by 20-30%, giving members flexibility during busy periods or financial constraints.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Pause_Subscription_Option extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pause-subscription-option';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pause Subscription Option';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site offers subscription pause option that reduces cancellations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'membership';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for subscription/membership sites.
		if ( ! self::has_subscriptions() ) {
			return null;
		}

		$issues = array();
		$pause_score = 0;
		$max_score = 5;

		// Check for subscription plugins with pause functionality.
		$pause_capable_plugins = self::check_pause_capable_plugins();
		if ( $pause_capable_plugins ) {
			$pause_score++;
		} else {
			$issues[] = __( 'Subscription plugin does not support pause functionality', 'wpshadow' );
		}

		// Check for pause option documentation.
		$pause_documentation = self::check_pause_documentation();
		if ( $pause_documentation ) {
			$pause_score++;
		} else {
			$issues[] = __( 'No documentation about pause/snooze subscription option', 'wpshadow' );
		}

		// Check for pause duration options.
		$duration_options = self::check_duration_options();
		if ( $duration_options ) {
			$pause_score++;
		} else {
			$issues[] = __( 'No flexible pause duration options (1, 2, 3 months)', 'wpshadow' );
		}

		// Check for retention messaging.
		$retention_messaging = self::check_retention_messaging();
		if ( $retention_messaging ) {
			$pause_score++;
		} else {
			$issues[] = __( 'No retention messaging offering pause before cancellation', 'wpshadow' );
		}

		// Check for cancellation flow with pause option.
		$cancellation_flow = self::check_cancellation_flow();
		if ( $cancellation_flow ) {
			$pause_score++;
		} else {
			$issues[] = __( 'Cancellation flow does not offer pause as alternative', 'wpshadow' );
		}

		// Determine severity based on pause implementation.
		$pause_percentage = ( $pause_score / $max_score ) * 100;

		if ( $pause_percentage < 30 ) {
			$severity = 'high';
			$threat_level = 65;
		} elseif ( $pause_percentage < 60 ) {
			$severity = 'medium';
			$threat_level = 45;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Pause option implementation percentage */
				__( 'Subscription pause implementation at %d%%. ', 'wpshadow' ),
				(int) $pause_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Pause options can recover 20-30% of cancellations', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pause-subscription-option',
			);
		}

		return null;
	}

	/**
	 * Check if site has subscriptions.
	 *
	 * @since 1.6093.1200
	 * @return bool True if subscriptions detected, false otherwise.
	 */
	private static function has_subscriptions() {
		// Check for subscription plugins.
		$subscription_plugins = array(
			'woocommerce-subscriptions/woocommerce-subscriptions.php',
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
			'memberpress/memberpress.php',
			'surecart/surecart.php',
		);

		foreach ( $subscription_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for pause-capable plugins.
	 *
	 * @since 1.6093.1200
	 * @return bool True if pause capability exists, false otherwise.
	 */
	private static function check_pause_capable_plugins() {
		// These plugins support pause/suspend functionality.
		$pause_plugins = array(
			'woocommerce-subscriptions/woocommerce-subscriptions.php' => 'WooCommerce Subscriptions',
			'memberpress/memberpress.php' => 'MemberPress',
			'restrict-content-pro/restrict-content-pro.php' => 'Restrict Content Pro',
		);

		foreach ( $pause_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_pause_capability', false );
	}

	/**
	 * Check for pause documentation.
	 *
	 * @since 1.6093.1200
	 * @return bool True if documentation exists, false otherwise.
	 */
	private static function check_pause_documentation() {
		$keywords = array( 'pause subscription', 'pause membership', 'suspend account', 'snooze subscription' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_pause_documentation', false );
	}

	/**
	 * Check for duration options.
	 *
	 * @since 1.6093.1200
	 * @return bool True if duration options exist, false otherwise.
	 */
	private static function check_duration_options() {
		// Check for content mentioning pause durations.
		$duration_keywords = array( '1 month pause', '2 month pause', 'pause for 3 months' );

		foreach ( $duration_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_pause_duration_options', false );
	}

	/**
	 * Check for retention messaging.
	 *
	 * @since 1.6093.1200
	 * @return bool True if retention messaging exists, false otherwise.
	 */
	private static function check_retention_messaging() {
		$retention_keywords = array( 'before you cancel', 'instead of canceling', 'take a break' );

		foreach ( $retention_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_retention_messaging', false );
	}

	/**
	 * Check for cancellation flow with pause option.
	 *
	 * @since 1.6093.1200
	 * @return bool True if cancellation flow offers pause, false otherwise.
	 */
	private static function check_cancellation_flow() {
		// Check for account pages or cancellation-related content.
		$pages = array( 'my-account', 'account', 'cancel-subscription', 'manage-subscription' );

		foreach ( $pages as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				// Check if page content mentions pause.
				if ( stripos( $page->post_content, 'pause' ) !== false ) {
					return true;
				}
			}
		}

		return apply_filters( 'wpshadow_cancellation_offers_pause', false );
	}
}
