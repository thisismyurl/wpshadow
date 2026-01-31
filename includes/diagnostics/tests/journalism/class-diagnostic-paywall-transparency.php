<?php
/**
 * Journalism Paywall and Subscription Transparency Diagnostic
 *
 * Verifies news sites clearly communicate paywall policies
 *
 * @package    WPShadow
 * @subpackage Diagnostics\\Journalism
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Journalism;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_PaywallTransparency Class
 *
 * Checks for: subscription disclosure, pricing transparency, article metering
 *
 * @since 1.6031.1445
 */
class Diagnostic_PaywallTransparency extends Diagnostic_Base {

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
	protected static $description = 'Verifies news sites clearly communicate paywall policies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'journalism';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for subscription page.
		$page = get_page_by_path( 'subscription' );
		if ( ! $page ) {
			$issues[] = __( 'No subscription disclosure page', 'wpshadow' );
		}

		// Check for relevant plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_keywords = array( 'subscription', 'paywall', 'metered', 'memberpress' );
		$has_plugin = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $plugin_keywords as $keyword ) {
				if ( stripos( $plugin, $keyword ) !== false ) {
					$has_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_plugin ) {
			$issues[] = __( 'No relevant plugin detected', 'wpshadow' );
		}

		// Additional checks would go here for: No pricing page found

		// Additional checks would go here for: No metered article tracking

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
