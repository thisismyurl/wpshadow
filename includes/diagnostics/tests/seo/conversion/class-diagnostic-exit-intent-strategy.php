<?php
/**
 * Exit Intent Strategy Diagnostic
 *
 * Issue #4774: No Exit-Intent Strategy
 * Family: business-performance
 *
 * Checks if site captures abandoning visitors.
 * Exit-intent popups can recover 10-15% of abandoning visitors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6036.1510
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Exit_Intent_Strategy Class
 *
 * Checks for exit-intent capture mechanisms.
 *
 * @since 1.6036.1510
 */
class Diagnostic_Exit_Intent_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'exit-intent-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Exit-Intent Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if site captures abandoning visitors with exit-intent offers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6036.1510
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for exit-intent plugins.
		$exit_intent_plugins = array(
			'popup-maker/popup-maker.php'               => 'Popup Maker',
			'optinmonster/optin-monster-wp-api.php'     => 'OptinMonster',
			'thrive-leads/thrive-leads.php'             => 'Thrive Leads',
			'convertpro/convertpro.php'                 => 'Convert Pro',
		);

		$active_exit_plugins = array();
		foreach ( $exit_intent_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_exit_plugins[] = $plugin_name;
			}
		}

		$issues[] = __( 'Implement exit-intent popup for email capture', 'wpshadow' );
		$issues[] = __( 'Offer discount/incentive to abandoning visitors', 'wpshadow' );
		$issues[] = __( 'Show "Wait! Before you go..." message', 'wpshadow' );
		$issues[] = __( 'Collect feedback: "What were you looking for?"', 'wpshadow' );
		$issues[] = __( 'Mobile: use scroll or time trigger (not mouse exit)', 'wpshadow' );
		$issues[] = __( 'Don\'t show more than once per session (avoid annoyance)', 'wpshadow' );

		if ( ! empty( $issues ) || empty( $active_exit_plugins ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site isn\'t capturing visitors who are about to leave. Imagine a retail store where customers walk toward the exit and staff just watches them go—no "Can I help you find something?" or "Here\'s a 10%% discount for today!" Exit-intent technology detects when users move their mouse toward the browser close button (desktop) or have been inactive for X seconds (mobile) and shows a last-chance offer. This can recover 10-15%% of abandoning visitors. Best uses: Email capture ("Get 10%% off your first purchase"), Cart abandonment ("Wait! Your cart will expire in 10 minutes"), Feedback ("Quick: what were you looking for?"), Content upgrade ("Download the PDF version"). Don\'t be annoying—show once per session, make the offer valuable, and provide easy close.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/exit-intent-strategy',
				'details'      => array(
					'recommendations'       => $issues,
					'conversion_rate'       => 'Can recover 10-15% of abandoning visitors',
					'best_offer'            => 'Email signup with immediate value (discount, free download)',
					'timing'                => 'Desktop: mouse toward close button. Mobile: 30-60 seconds inactive',
					'frequency'             => 'Show once per session (use cookies)',
					'mobile_challenge'      => 'No "mouse exit" on mobile—use scroll depth or time triggers',
					'avoid_annoying'        => 'Fast close button, don\'t show if already signed up',
					'active_plugins'        => $active_exit_plugins,
				),
			);
		}

		return null;
	}
}
