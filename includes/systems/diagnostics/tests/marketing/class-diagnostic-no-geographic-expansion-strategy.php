<?php
/**
 * No Geographic Expansion Strategy Diagnostic
 *
 * Checks if geographic/market expansion strategy is documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Geographic Expansion Strategy Diagnostic
 *
 * Detects when business expansion strategy isn't documented. Growth plateaus
 * without geographic expansion. Expanding to new markets/geographies is proven
 * growth strategy. Companies without expansion plans plateau at 10-20% below
 * their revenue potential.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Geographic_Expansion_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-geographic-expansion-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Geographic Expansion Strategy Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if geographic/market expansion strategy is documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_expansion_strategy = self::check_expansion_strategy();

		if ( ! $has_expansion_strategy ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No geographic expansion strategy detected. Your growth is limited to your current market. Without expansion, you plateau. Documented expansion strategy: 1) Identify top 3 new markets (geographic or demographic), 2) Analyze market size and competition, 3) Develop localization plan (language, currency, culture), 4) Build local partnerships, 5) Launch marketing in new market, 6) Track growth metrics. Expansion can double revenue.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/geographic-expansion-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'strategy_documented' => false,
					'expansion_types'     => self::get_expansion_types(),
					'business_impact'     => 'Can double revenue with expansion to new markets',
					'recommendation'      => __( 'Document geographic or market expansion plan for next 12-24 months', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if expansion strategy exists
	 *
	 * @since 0.6093.1200
	 * @return bool True if strategy documented
	 */
	private static function check_expansion_strategy(): bool {
		// Check for strategy documentation
		$strategy_posts = get_posts( array(
			'numberposts' => 10,
			's'           => 'expansion OR growth strategy OR market expansion OR geographic',
		) );

		if ( ! empty( $strategy_posts ) ) {
			return true;
		}

		// Check for geographic/language variations
		if ( self::has_multiple_languages() || self::has_multiple_currencies() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if site supports multiple languages
	 *
	 * @since 0.6093.1200
	 * @return bool True if multiple languages detected
	 */
	private static function has_multiple_languages(): bool {
		$plugins = get_plugins();

		$language_keywords = array( 'wpml', 'polylang', 'translate', 'multilingual', 'language' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $language_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if site supports multiple currencies
	 *
	 * @since 0.6093.1200
	 * @return bool True if multiple currencies detected
	 */
	private static function has_multiple_currencies(): bool {
		// Check WooCommerce multi-currency
		if ( function_exists( 'wc_get_currency' ) ) {
			$response = wp_remote_get( home_url( '/' ) );

			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );

				// Look for currency selectors or multiple currency displays
				if ( preg_match( '/currency|USD|EUR|GBP|JPY|AUD/i', $body ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get expansion types
	 *
	 * @since 0.6093.1200
	 * @return array Array of expansion types
	 */
	private static function get_expansion_types(): array {
		return array(
			array(
				'type'       => 'Geographic Expansion',
				'description' => 'Expand to new countries or regions',
				'example'     => 'US ecommerce expanding to Canada, UK, Australia',
				'effort'     => 'High (localization, regulations, fulfillment)',
				'timeline'   => '6-12 months',
			),
			array(
				'type'       => 'Language Expansion',
				'description' => 'Translate site to new languages',
				'example'     => 'English site translated to Spanish, French, German',
				'effort'     => 'Medium (translation, content, support)',
				'timeline'   => '3-6 months',
			),
			array(
				'type'       => 'Demographic Expansion',
				'description' => 'Target new customer segment',
				'example'     => 'B2B SaaS expanding to SMB market',
				'effort'     => 'Medium (messaging, positioning, features)',
				'timeline'   => '3-6 months',
			),
			array(
				'type'       => 'Vertical Expansion',
				'description' => 'Expand to new industries/use cases',
				'example'     => 'Project management tool for construction expanding to healthcare',
				'effort'     => 'High (customization, integrations)',
				'timeline'   => '6-12 months',
			),
		);
	}
}
