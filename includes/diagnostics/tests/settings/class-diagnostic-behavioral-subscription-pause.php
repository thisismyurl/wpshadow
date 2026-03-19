<?php
/**
 * Diagnostic: Pause Subscription Option
 *
 * Tests whether the site offers subscription pause/vacation mode that reduces
 * cancellations by 30-40% through temporary suspension.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4549
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
 * Pause Subscription Option Diagnostic
 *
 * Checks for subscription pause capability. Members wanting to cancel temporarily
 * will fully cancel without pause option. Pause reduces cancellations 30-40%.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_Subscription_Pause extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'offers-subscription-pause';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pause Subscription Option';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site offers subscription pause to reduce cancellations';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for pause subscription implementation.
	 *
	 * Looks for pause features in subscription management.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// WooCommerce Subscriptions has built-in pause.
		if ( class_exists( 'WC_Subscriptions' ) ) {
			// Check if customer pause is enabled.
			$pause_enabled = get_option( 'wcs_allow_customers_to_pause', 'no' );
			
			if ( $pause_enabled === 'yes' ) {
				return null;
			}

			// Subscriptions active but pause not enabled.
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __(
					'WooCommerce Subscriptions detected but customer pause is disabled. Members cancel permanently when they need temporary breaks. Offering pause/vacation mode reduces cancellations by 30-40% - members return after travel, busy periods, or financial constraints. Enable "Allow customers to suspend" in WooCommerce > Settings > Subscriptions.',
					'wpshadow'
				),
				'severity'     => 'medium',
				'threat_level' => 49,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/subscription-pause',
			);
		}

		// Check for MemberPress (has pause).
		if ( class_exists( 'MeprUser' ) ) {
			return null; // MemberPress supports pause.
		}

		// Check other membership plugins.
		$membership_plugins = array(
			'paid-memberships-pro/paid-memberships-pro.php'  => 'Paid Memberships Pro',
			'restrict-content-pro/restrict-content-pro.php'  => 'Restrict Content Pro',
		);

		$has_membership = false;
		foreach ( $membership_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_membership = true;
			}
		}

		if ( ! $has_membership ) {
			return null; // No subscription system.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No subscription pause option detected. Members wanting temporary breaks will cancel permanently without pause capability. Pause/vacation mode reduces cancellations by 30-40% - members return after travel, busy seasons, or financial constraints. Implement pause functionality with clear resume dates.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 46,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/subscription-pause-implementation',
		);
	}
}
