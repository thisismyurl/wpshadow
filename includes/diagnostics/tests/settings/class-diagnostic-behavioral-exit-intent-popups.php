<?php
/**
 * Diagnostic: Exit Intent Popups Strategic
 *
 * Tests whether the site uses exit intent popups strategically to capture
 * leaving visitors with positive ROI.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4531
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since      1.6034.1440
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exit Intent Popups Diagnostic
 *
 * Checks if site uses exit intent technology to recover abandoning visitors.
 * Strategic use can increase conversions by 2-4%.
 *
 * @since 1.6034.1440
 */
class Diagnostic_Behavioral_Exit_Intent_Popups extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-exit-intent-strategically';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Exit Intent Popups Strategic';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses exit intent popups to capture leaving visitors';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for exit intent implementation.
	 *
	 * Detects exit intent plugins and JavaScript implementations.
	 *
	 * @since  1.6034.1440
	 * @return array|null Finding array if not implemented, null if present.
	 */
	public static function check() {
		// Check for popular exit intent plugins.
		$exit_intent_plugins = array(
			'optinmonster/optin-monster-wp-api.php'          => 'OptinMonster',
			'popup-maker/popup-maker.php'                    => 'Popup Maker',
			'hustle/opt-in.php'                              => 'Hustle',
			'thrive-leads/thrive-leads.php'                  => 'Thrive Leads',
			'convertpro/convertpro.php'                      => 'Convert Pro',
			'mailpoet/mailpoet.php'                          => 'MailPoet',
		);

		foreach ( $exit_intent_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has exit intent capability.
			}
		}

		// Check for exit intent JavaScript in theme.
		global $wp_scripts;
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( strpos( $handle, 'exit' ) !== false || strpos( $handle, 'popup' ) !== false ) {
					return null;
				}
			}
		}

		// Check if site has lead generation forms (makes exit intent more valuable).
		$has_forms = false;
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'wpforms-lite/wpforms.php',
			'gravityforms/gravityforms.php',
			'ninja-forms/ninja-forms.php',
		);

		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_forms = true;
				break;
			}
		}

		if ( ! $has_forms ) {
			// No forms = exit intent less critical.
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'Site lacks exit intent popup strategy. Exit intent technology can recover 2-4% of abandoning visitors by presenting targeted offers when users attempt to leave. Consider implementing exit intent popups for lead capture, special offers, or cart recovery.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/exit-intent-popups',
		);
	}
}
