<?php
/**
 * Diagnostic: Scarcity Tactics Ethical
 *
 * Tests whether the site uses real urgency without manipulation to increase
 * conversions ethically.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4532
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
 * Ethical Scarcity Tactics Diagnostic
 *
 * Checks if site uses genuine scarcity (limited stock, time-bound offers)
 * vs fake urgency that damages trust.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_Ethical_Scarcity extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-ethical-scarcity-tactics';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Scarcity Tactics Ethical';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses real urgency without manipulation';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for scarcity implementation.
	 *
	 * Looks for legitimate scarcity features vs manipulative fake urgency.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues detected, null if ethical.
	 */
	public static function check() {
		$issues = array();

		// Check WooCommerce stock management (good scarcity).
		$has_stock_management = false;
		if ( class_exists( 'WooCommerce' ) ) {
			$stock_enabled = get_option( 'woocommerce_manage_stock', 'yes' );
			if ( 'yes' === $stock_enabled ) {
				$has_stock_management = true;
			}
		}

		// Check for countdown timer plugins (can be good or bad).
		$countdown_plugins = array(
			'countdown-timer-ultimate/countdown-timer-ultimate.php',
			'evergreen-countdown-timer/evergreen-countdown-timer.php',
		);

		$has_countdown = false;
		foreach ( $countdown_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_countdown = true;
				break;
			}
		}

		// Check theme/content for manipulative language.
		$theme      = wp_get_theme();
		$theme_root = get_theme_root();
		$theme_path = $theme_root . '/' . $theme->get_stylesheet();

		if ( file_exists( $theme_path . '/woocommerce.php' ) || file_exists( $theme_path . '/woocommerce' ) ) {
			// WooCommerce theme - check for fake scarcity patterns.
			$files_to_check = array();
			
			if ( file_exists( $theme_path . '/woocommerce.php' ) ) {
				$files_to_check[] = $theme_path . '/woocommerce.php';
			}
			
			if ( is_dir( $theme_path . '/woocommerce' ) ) {
				$files_to_check = array_merge(
					$files_to_check,
					glob( $theme_path . '/woocommerce/*.php' )
				);
			}

			foreach ( $files_to_check as $file ) {
				$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				
				// Check for manipulative phrases.
				$manipulative_patterns = array(
					'Only \d+ spots? left',
					'Hurry.*almost sold out',
					'Limited time.*ends? (soon|today)',
					'\d+ people (viewing|looking)',
				);

				foreach ( $manipulative_patterns as $pattern ) {
					if ( preg_match( '/' . $pattern . '/i', $content ) ) {
						$issues[] = sprintf(
							/* translators: %s: pattern found */
							__( 'Potentially manipulative scarcity pattern: "%s"', 'wpshadow' ),
							$pattern
						);
					}
				}
			}
		}

		// If has legitimate scarcity features and no manipulative patterns.
		if ( $has_stock_management && empty( $issues ) ) {
			return null; // Ethical scarcity in place.
		}

		// If has countdown but no stock management.
		if ( $has_countdown && ! $has_stock_management ) {
			$issues[] = __( 'Countdown timers without real stock scarcity', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of issues */
				__(
					'Scarcity tactics may be manipulative: %s. Use real inventory limits, genuine time-bound offers, and honest language. Fake urgency damages trust and long-term conversion rates.',
					'wpshadow'
				),
				implode( '; ', array_slice( $issues, 0, 3 ) )
			),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ethical-scarcity-tactics',
		);
	}
}
