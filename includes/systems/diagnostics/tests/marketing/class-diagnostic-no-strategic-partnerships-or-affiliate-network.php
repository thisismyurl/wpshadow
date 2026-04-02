<?php
/**
 * No Strategic Partnerships or Affiliate Network Diagnostic
 *
 * Checks if strategic partnerships or affiliate programs are established.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Strategic Partnerships Diagnostic
 *
 * Detects when businesses aren't leveraging strategic partnerships or affiliate networks.
 * Partnerships distribute customer acquisition costs and access new customer bases.
 * Affiliate programs let partners sell for you on commission (zero upfront cost).
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Strategic_Partnerships_Or_Affiliate_Network extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-strategic-partnerships-affiliate-network';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Strategic Partnerships & Affiliate Programs Active';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if strategic partnerships or affiliate programs are established';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_partnerships = self::check_partnerships();

		if ( ! $has_partnerships ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No strategic partnerships or affiliate programs detected. You\'re acquiring customers 100% yourself. Partnerships give you leverage: access to their audience, shared costs, complementary products. Affiliate programs let partners sell for you on commission (zero upfront cost). Start: 1) List 10 complementary businesses, 2) Reach out with partnership proposal, 3) Build affiliate program, 4) Recruit 5+ affiliates, 5) Track referral revenue.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/strategic-partnerships-affiliates',
				'details'     => array(
					'partnerships_active' => false,
					'partnership_types'   => self::get_partnership_types(),
					'business_impact'     => 'Lower CAC, scalable growth, new markets',
					'recommendation'      => __( 'Start with 3 strategic partnerships and launch affiliate program', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if partnerships exist
	 *
	 * @since 1.6093.1200
	 * @return bool True if partnerships detected
	 */
	private static function check_partnerships(): bool {
		// Check for affiliate/partnership plugins
		$plugins = get_plugins();

		$partnership_keywords = array( 'affiliate', 'referral', 'partner', 'commission' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $partnership_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		// Check for partners/affiliates page
		$response = wp_remote_get( home_url( '/' ) );

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			if ( preg_match( '/partner|affiliate|referral/i', $body ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get partnership types
	 *
	 * @since 1.6093.1200
	 * @return array Array of partnership types
	 */
	private static function get_partnership_types(): array {
		return array(
			array(
				'type'        => 'Co-Marketing Partnerships',
				'description' => 'Partner with complementary business, share audience',
				'example'     => 'Ecommerce + Shipping company cross-promotion',
				'benefit'     => 'Low-cost customer acquisition, market expansion',
			),
			array(
				'type'        => 'Channel Partnerships',
				'description' => 'Partner resells your product to their audience',
				'example'     => 'Agency selling software to their clients',
				'benefit'     => 'Scalable sales force, existing customer trust',
			),
			array(
				'type'        => 'Technology Integration Partners',
				'description' => 'Integrate with complementary tools',
				'example'     => 'CRM integrated with email platform',
				'benefit'     => 'New feature without development, shared customers',
			),
			array(
				'type'        => 'Affiliate Programs',
				'description' => 'Anyone can promote, earn commission',
				'example'     => '10% commission for each sale referred',
				'benefit'     => 'Scale without guaranteed cost, attract ambassadors',
			),
			array(
				'type'        => 'Referral Programs',
				'description' => 'Customers refer friends, get rewards',
				'example'     => '$50 credit for each customer referred',
				'benefit'     => 'Cheapest customer acquisition, loyalty builder',
			),
		);
	}
}
