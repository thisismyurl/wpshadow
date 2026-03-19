<?php
/**
 * Journalism Paywall and Subscription Transparency Diagnostic
 *
 * Verifies news sites clearly communicate paywall policies and subscription terms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
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
 * Checks for clear subscription policies.
 *
 * @since 1.6093.1200
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
	protected static $title = 'Journalism Paywall and Subscription Transparency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies news sites clearly communicate subscription policies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'journalism';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for subscription/paywall plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$paywall_keywords = array( 'subscription', 'paywall', 'member', 'metered' );
		$has_paywall = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $paywall_keywords as $keyword ) {
				if ( stripos( $plugin, $keyword ) !== false ) {
					$has_paywall = true;
					break 2;
				}
			}
		}

		if ( ! $has_paywall ) {
			return null; // No paywall detected.
		}

		$issues = array();

		// Check for subscription disclosure page.
		$subscription_page = get_page_by_path( 'subscription' );
		if ( ! $subscription_page ) {
			$issues[] = __( 'No subscription terms page found', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Paywall concerns: %s. News sites should clearly communicate subscription policies.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/paywall-transparency',
		);
	}
}
