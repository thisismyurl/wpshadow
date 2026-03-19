<?php
/**
 * No Exit Intent Popup Strategy Diagnostic
 *
 * Detects when exit intent is not being captured,
 * missing opportunity to recover abandoning visitors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Exit Intent Popup Strategy
 *
 * Checks whether exit intent popups are used
 * to capture abandoning visitors.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Exit_Intent_Popup_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-exit-intent-popup-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Exit Intent Popup Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether exit intent popups are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for exit intent plugins
		$has_exit_intent = is_plugin_active( 'optinmonster/optinmonster-app.php' ) ||
			is_plugin_active( 'popup-maker/popup-maker.php' ) ||
			is_plugin_active( 'mailchimp-for-wp/mailchimp-for-wp.php' );

		if ( ! $has_exit_intent ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not using exit intent popups, which means you\'re not trying to recover visitors who are about to leave. Exit intent detects when someone moves their mouse toward closing the tab, then shows a final offer: discount code, free resource, newsletter signup. When done right (not annoying), exit popups convert 2-5% of abandoning visitors. That\'s 2-5% of traffic that would have been lost forever.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Visitor Recovery',
					'potential_gain' => 'Recover 2-5% of abandoning visitors',
					'roi_explanation' => 'Exit intent popups capture abandoning visitors with final offers, recovering 2-5% who would have left forever.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/exit-intent-popup-strategy',
			);
		}

		return null;
	}
}
