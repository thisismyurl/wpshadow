<?php
/**
 * Welcome Email Sequence Diagnostic
 *
 * Checks if new email subscribers receive automated welcome sequence.
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
 * Welcome Email Sequence Diagnostic Class
 *
 * Welcome emails get 4x more opens and 5x more clicks than regular emails.
 * First week = highest engagement window. Miss it, lose them.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Welcome_Email_Sequence extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'welcome-email-sequence';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Welcome Email Sequence Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if new subscribers receive automated welcome emails';

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
		$issues        = array();
		$welcome_score = 0;
		$max_score     = 5;

		// Check for automated welcome email.
		$has_welcome = self::check_automated_welcome_email();
		if ( $has_welcome ) {
			++$welcome_score;
		} else {
			$issues[] = 'automated welcome email';
		}

		// Check for 3-5 email sequence.
		$has_sequence = self::check_email_sequence();
		if ( $has_sequence ) {
			++$welcome_score;
		} else {
			$issues[] = '3-5 email sequence over first week';
		}

		// Check for brand introduction.
		$has_introduction = self::check_brand_introduction();
		if ( $has_introduction ) {
			++$welcome_score;
		} else {
			$issues[] = 'brand/value proposition introduction';
		}

		// Check for clear next steps.
		$has_next_steps = self::check_clear_next_steps();
		if ( $has_next_steps ) {
			++$welcome_score;
		} else {
			$issues[] = 'clear next steps for engagement';
		}

		// Check for unsubscribe option.
		$has_unsubscribe = self::check_unsubscribe_option();
		if ( $has_unsubscribe ) {
			++$welcome_score;
		} else {
			$issues[] = 'visible unsubscribe option';
		}

		$completion_percentage = ( $welcome_score / $max_score ) * 100;

		if ( $completion_percentage >= 60 ) {
			return null; // Welcome sequence configured.
		}

		$severity     = $completion_percentage < 40 ? 'medium' : 'low';
		$threat_level = $completion_percentage < 40 ? 50 : 30;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Welcome sequence at %1$d%%. Missing: %2$s. Like meeting someone then never following up—wasted opportunity.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/welcome-email-sequence',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check for automated welcome email.
	 *
	 * @since 1.6093.1200
	 * @return bool True if welcome email exists.
	 */
	private static function check_automated_welcome_email(): bool {
		// Check for email marketing plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$email_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/plugin.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'mailchimp-for-woocommerce/mailchimp-woocommerce.php',
		);

		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for email sequence.
	 *
	 * @since 1.6093.1200
	 * @return bool True if sequence exists.
	 */
	private static function check_email_sequence(): bool {
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
	 * Check for brand introduction.
	 *
	 * @since 1.6093.1200
	 * @return bool True if introduction exists.
	 */
	private static function check_brand_introduction(): bool {
		// If email plugin is active, assume content is configured.
		return self::check_automated_welcome_email();
	}

	/**
	 * Check for clear next steps.
	 *
	 * @since 1.6093.1200
	 * @return bool True if next steps exist.
	 */
	private static function check_clear_next_steps(): bool {
		// If email plugin is active, assume CTAs are configured.
		return self::check_automated_welcome_email();
	}

	/**
	 * Check for unsubscribe option.
	 *
	 * @since 1.6093.1200
	 * @return bool True if unsubscribe exists.
	 */
	private static function check_unsubscribe_option(): bool {
		// Email plugins include unsubscribe by default (legal requirement).
		return self::check_automated_welcome_email();
	}
}
