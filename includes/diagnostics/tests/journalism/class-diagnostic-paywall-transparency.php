<?php
/**
 * Paywall and Subscription Metering Transparency Diagnostic
 *
 * Checks if news sites with paywalls properly disclose metering limits,
 * subscription terms, and provide accessible content policies.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Journalism
 * @since      1.6031.1447
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Journalism;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paywall Transparency Diagnostic Class
 *
 * Verifies news paywalls have transparent metering and subscription disclosures.
 *
 * @since 1.6031.1447
 */
class Diagnostic_Paywall_Transparency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'paywall-transparency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Paywall and Subscription Metering Transparency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies news paywalls have clear metering limits and subscription disclosures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'journalism';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - Paywall/subscription plugins
	 * - Metering disclosure
	 * - Subscription terms page
	 * - Free article limits clearly stated
	 *
	 * @since  1.6031.1447
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for paywall/subscription plugins.
		$paywall_plugins = array(
			'paywall',
			'subscription',
			'metered',
			'paid-memberships-pro',
			'memberpress',
			'restrict-content',
		);

		$has_paywall = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $paywall_plugins as $pw_plugin ) {
				if ( stripos( $plugin, $pw_plugin ) !== false ) {
					$has_paywall = true;
					break 2;
				}
			}
		}

		if ( ! $has_paywall ) {
			return null; // No paywall, no check needed.
		}

		$issues = array();

		// Check for subscription terms page.
		$pages = get_pages();
		$has_subscription_terms = false;

		foreach ( $pages as $page ) {
			if ( stripos( $page->post_title, 'subscription' ) !== false ||
				stripos( $page->post_title, 'membership' ) !== false ||
				stripos( $page->post_content, 'subscription terms' ) !== false ) {
				$has_subscription_terms = true;
				break;
			}
		}

		if ( ! $has_subscription_terms ) {
			$issues[] = __( 'No subscription terms/disclosure page found', 'wpshadow' );
		}

		// Check for pricing page.
		$has_pricing_page = false;
		foreach ( $pages as $page ) {
			if ( stripos( $page->post_title, 'pricing' ) !== false ||
				stripos( $page->post_title, 'subscribe' ) !== false ||
				stripos( $page->post_title, 'plans' ) !== false ) {
				$has_pricing_page = true;
				break;
			}
		}

		if ( ! $has_pricing_page ) {
			$issues[] = __( 'No clear pricing/subscription plans page', 'wpshadow' );
		}

		// Check for FAQ or help section about paywall.
		$has_paywall_faq = false;
		foreach ( $pages as $page ) {
			if ( stripos( $page->post_content, 'article limit' ) !== false ||
				stripos( $page->post_content, 'free articles' ) !== false ||
				stripos( $page->post_content, 'metered' ) !== false ) {
				$has_paywall_faq = true;
				break;
			}
		}

		if ( ! $has_paywall_faq ) {
			$issues[] = __( 'No documentation explaining article limits/metering', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Paywall transparency concerns: %s. Sites with paywalls should clearly disclose subscription terms, pricing, and article limits.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/paywall-transparency',
		);
	}
}
