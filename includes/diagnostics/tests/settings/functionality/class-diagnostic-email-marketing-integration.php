<?php
/**
 * Email Marketing Integration Diagnostic
 *
 * Checks if comprehensive email marketing system is set up.
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
 * Email Marketing Integration Diagnostic Class
 *
 * Email marketing returns $42 for every $1 spent. Without it, you're leaving
 * massive revenue on the table. It's the most profitable channel.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Email_Marketing_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-marketing-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Email Marketing Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if email marketing system is fully integrated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing-automation';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues            = array();
		$integration_score = 0;
		$max_score         = 5;

		// Check for ESP connection.
		$has_esp = self::check_esp_connection();
		if ( $has_esp ) {
			++$integration_score;
		} else {
			$issues[] = 'email service provider connected';
		}

		// Check for opt-in forms.
		$has_forms = self::check_optin_forms();
		if ( $has_forms ) {
			++$integration_score;
		} else {
			$issues[] = 'opt-in forms on site';
		}

		// Check for welcome sequence.
		$has_welcome = self::check_welcome_sequence();
		if ( $has_welcome ) {
			++$integration_score;
		} else {
			$issues[] = 'welcome email sequence';
		}

		// Check for list segmentation.
		$has_segmentation = self::check_list_segmentation();
		if ( $has_segmentation ) {
			++$integration_score;
		} else {
			$issues[] = 'list segmentation';
		}

		// Check for email analytics.
		$has_analytics = self::check_email_analytics();
		if ( $has_analytics ) {
			++$integration_score;
		} else {
			$issues[] = 'email analytics tracking';
		}

		$completion_percentage = ( $integration_score / $max_score ) * 100;

		if ( $completion_percentage >= 60 ) {
			return null; // Email marketing properly integrated.
		}

		$severity     = $completion_percentage < 40 ? 'high' : 'medium';
		$threat_level = $completion_percentage < 40 ? 70 : 50;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Email marketing at %1$d%%. Missing: %2$s. Like owning customer mailing list but never sending offers—free money left uncollected.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/email-marketing-integration',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check for ESP connection.
	 *
	 * @since 1.6093.1200
	 * @return bool True if ESP connected.
	 */
	private static function check_esp_connection(): bool {
		// Check for email marketing plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$esp_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/plugin.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'mailchimp-for-woocommerce/mailchimp-woocommerce.php',
			'email-subscribers/email-subscribers.php',
		);

		foreach ( $esp_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for opt-in forms.
	 *
	 * @since 1.6093.1200
	 * @return bool True if forms exist.
	 */
	private static function check_optin_forms(): bool {
		// Check for form plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$form_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'mailpoet/mailpoet.php',
			'newsletter/plugin.php',
		);

		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for welcome sequence.
	 *
	 * @since 1.6093.1200
	 * @return bool True if sequence exists.
	 */
	private static function check_welcome_sequence(): bool {
		// Check for scheduled welcome emails.
		$scheduled_events = array(
			'mailpoet_sending_queue',
			'newsletter_scheduled',
			'mailchimp_automation',
		);

		foreach ( $scheduled_events as $event ) {
			if ( wp_next_scheduled( $event ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for list segmentation.
	 *
	 * @since 1.6093.1200
	 * @return bool True if segmentation exists.
	 */
	private static function check_list_segmentation(): bool {
		// If ESP is active, assume segmentation is available.
		return self::check_esp_connection();
	}

	/**
	 * Check for email analytics.
	 *
	 * @since 1.6093.1200
	 * @return bool True if analytics exist.
	 */
	private static function check_email_analytics(): bool {
		// Check for analytics plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php',
			'wp-mail-logging/wp-mail-logging.php',
		);

		foreach ( $analytics_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// ESP platforms include analytics.
		return self::check_esp_connection();
	}
}
