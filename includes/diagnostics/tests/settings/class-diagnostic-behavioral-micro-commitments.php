<?php
/**
 * Diagnostic: Micro-Commitments Strategy
 *
 * Tests whether the site uses a stepped conversion strategy with small
 * commitments before big asks (increases conversion by 20-35%).
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4536
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Micro-Commitments Diagnostic
 *
 * Checks for psychological foot-in-the-door technique: small commitments
 * (email signup, quiz, free trial) before major purchase requests.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_Micro_Commitments extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-micro-commitments';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Micro-Commitments Strategy';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses stepped conversion with small commitments first';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for micro-commitment implementation.
	 *
	 * Looks for lead magnets, quizzes, calculators, free trials before
	 * main conversion asks.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		$commitment_steps = 0;

		// Check for email list building plugins.
		$email_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'newsletter/plugin.php',
			'mailpoet/mailpoet.php',
			'optinmonster/optin-monster-wp-api.php',
		);

		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				++$commitment_steps;
				break;
			}
		}

		// Check for quiz/survey plugins.
		$quiz_plugins = array(
			'quiz-master-next/mlw_quizmaster2.php',
			'wp-quiz/wp-quiz.php',
			'quiz-maker/quiz-maker.php',
		);

		foreach ( $quiz_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				++$commitment_steps;
				break;
			}
		}

		// Check for calculator/tool plugins.
		$tool_plugins = array(
			'calculated-fields-form/cp_calculatedfieldsf.php',
			'cost-calculator-builder/cost-calculator-builder.php',
		);

		foreach ( $tool_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				++$commitment_steps;
				break;
			}
		}

		// Check for free trial/freemium setup.
		if ( class_exists( 'WooCommerce' ) ) {
			// Check for subscription products.
			$subscription_plugins = array(
				'woocommerce-subscriptions/woocommerce-subscriptions.php',
			);

			foreach ( $subscription_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					++$commitment_steps;
					break;
				}
			}
		}

		// Check for lead magnet downloads.
		$download_plugins = array(
			'download-monitor/download-monitor.php',
			'easy-digital-downloads/easy-digital-downloads.php',
		);

		foreach ( $download_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				++$commitment_steps;
				break;
			}
		}

		// Need at least 1 micro-commitment step.
		if ( $commitment_steps >= 1 ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No micro-commitment strategy detected. Using small commitments (email signup, quiz, free content) before asking for purchases increases conversion by 20-35%. Implement lead magnets, calculators, or free trials to build trust progressively.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 42,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/micro-commitments',
		);
	}
}
