<?php
/**
 * Retention Campaigns Diagnostic
 *
 * Tests whether the site runs proactive campaigns to reduce member churn.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retention Campaigns Diagnostic Class
 *
 * Proactive retention campaigns can reduce churn by 10-20%. This includes
 * re-engagement emails, win-back campaigns, and targeted retention offers.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Retention_Campaigns extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'retention-campaigns';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Retention Campaigns';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site runs proactive campaigns to reduce member churn';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'membership';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for membership sites.
		if ( ! self::is_membership_site() ) {
			return null;
		}

		$issues = array();
		$retention_score = 0;
		$max_score = 7;

		// Check for email marketing platform.
		$email_platform = self::check_email_platform();
		if ( $email_platform ) {
			$retention_score++;
		} else {
			$issues[] = __( 'No email marketing platform for retention campaigns', 'wpshadow' );
		}

		// Check for re-engagement campaigns.
		$reengagement = self::check_reengagement_campaigns();
		if ( $reengagement ) {
			$retention_score++;
		} else {
			$issues[] = __( 'No re-engagement campaigns for inactive members', 'wpshadow' );
		}

		// Check for win-back campaigns.
		$winback = self::check_winback_campaigns();
		if ( $winback ) {
			$retention_score++;
		} else {
			$issues[] = __( 'No win-back campaigns for churned members', 'wpshadow' );
		}

		// Check for segmentation.
		$segmentation = self::check_segmentation();
		if ( $segmentation ) {
			$retention_score++;
		} else {
			$issues[] = __( 'No member segmentation for targeted campaigns', 'wpshadow' );
		}

		// Check for automation.
		$automation = self::check_automation();
		if ( $automation ) {
			$retention_score++;
		} else {
			$issues[] = __( 'No marketing automation for retention triggers', 'wpshadow' );
		}

		// Check for retention offers.
		$retention_offers = self::check_retention_offers();
		if ( $retention_offers ) {
			$retention_score++;
		} else {
			$issues[] = __( 'No special offers or discounts for at-risk members', 'wpshadow' );
		}

		// Check for campaign analytics.
		$analytics = self::check_campaign_analytics();
		if ( $analytics ) {
			$retention_score++;
		} else {
			$issues[] = __( 'No tracking of retention campaign effectiveness', 'wpshadow' );
		}

		// Determine severity based on retention campaign implementation.
		$retention_percentage = ( $retention_score / $max_score ) * 100;

		if ( $retention_percentage < 40 ) {
			$severity = 'medium';
			$threat_level = 50;
		} elseif ( $retention_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 35;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Retention campaign percentage */
				__( 'Retention campaign infrastructure at %d%%. ', 'wpshadow' ),
				(int) $retention_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Proactive campaigns can reduce churn by 10-20%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/retention-campaigns?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check if this is a membership site.
	 *
	 * @since 0.6093.1200
	 * @return bool True if membership features detected, false otherwise.
	 */
	private static function is_membership_site() {
		$membership_plugins = array(
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
			'memberpress/memberpress.php',
			'woocommerce-memberships/woocommerce-memberships.php',
		);

		foreach ( $membership_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for email platform.
	 *
	 * @since 0.6093.1200
	 * @return bool True if email platform exists, false otherwise.
	 */
	private static function check_email_platform() {
		$email_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'email-subscribers/email-subscribers.php',
			'sendinblue-mailin/sendinblue.php',
		);

		foreach ( $email_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_email_platform', false );
	}

	/**
	 * Check for re-engagement campaigns.
	 *
	 * @since 0.6093.1200
	 * @return bool True if campaigns exist, false otherwise.
	 */
	private static function check_reengagement_campaigns() {
		$keywords = array( 're-engage', 'we miss you', 'come back', 'haven\'t seen you' );

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

		return apply_filters( 'wpshadow_has_reengagement_campaigns', false );
	}

	/**
	 * Check for win-back campaigns.
	 *
	 * @since 0.6093.1200
	 * @return bool True if win-back campaigns exist, false otherwise.
	 */
	private static function check_winback_campaigns() {
		$keywords = array( 'win back', 'we want you back', 'special offer for you', 'rejoin' );

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

		return apply_filters( 'wpshadow_has_winback_campaigns', false );
	}

	/**
	 * Check for segmentation.
	 *
	 * @since 0.6093.1200
	 * @return bool True if segmentation exists, false otherwise.
	 */
	private static function check_segmentation() {
		// MailPoet has segmentation.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) && class_exists( 'MailPoet\Models\Segment' ) ) {
			return true;
		}

		// Check for segment-related content.
		$query = new \WP_Query(
			array(
				's'              => 'member segment active inactive engaged',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for automation.
	 *
	 * @since 0.6093.1200
	 * @return bool True if automation exists, false otherwise.
	 */
	private static function check_automation() {
		$automation_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
			'automated-emails/automated-emails.php',
			'fluentcrm/fluentcrm.php',
		);

		foreach ( $automation_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_marketing_automation', false );
	}

	/**
	 * Check for retention offers.
	 *
	 * @since 0.6093.1200
	 * @return bool True if retention offers exist, false otherwise.
	 */
	private static function check_retention_offers() {
		$keywords = array( 'retention discount', 'stay with us', 'special price', 'exclusive offer' );

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

		// Check for coupons.
		if ( class_exists( 'WooCommerce' ) ) {
			$coupons = get_posts(
				array(
					'post_type'      => 'shop_coupon',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( ! empty( $coupons ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_retention_offers', false );
	}

	/**
	 * Check for campaign analytics.
	 *
	 * @since 0.6093.1200
	 * @return bool True if analytics exist, false otherwise.
	 */
	private static function check_campaign_analytics() {
		// Email platforms typically have analytics.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ||
			 is_plugin_active( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		// Check for general analytics.
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

		return apply_filters( 'wpshadow_has_campaign_analytics', false );
	}
}
