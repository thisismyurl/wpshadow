<?php
/**
 * CCPA Privacy Rights Diagnostic
 *
 * Analyzes CCPA compliance and privacy rights implementation.
 *
 * @since   1.6033.2135
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CCPA Privacy Rights Diagnostic
 *
 * Evaluates CCPA compliance for California privacy rights.
 *
 * @since 1.6033.2135
 */
class Diagnostic_CCPA_Privacy_Rights extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ccpa-privacy-rights';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CCPA Privacy Rights';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes CCPA compliance and privacy rights implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2135
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for "Do Not Sell" link in footer
		$footer_nav_menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
		$has_do_not_sell  = false;

		foreach ( $footer_nav_menus as $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id );
			if ( empty( $menu_items ) ) {
				continue;
			}

			foreach ( $menu_items as $item ) {
				if ( stripos( $item->title, 'do not sell' ) !== false ||
				     stripos( $item->url, 'do-not-sell' ) !== false ) {
					$has_do_not_sell = true;
					break 2;
				}
			}
		}

		// Check for privacy policy page
		$privacy_page = get_option( 'wp_page_for_privacy_policy' );

		// Check for data export/erasure tools
		$has_export_tool  = function_exists( 'wp_privacy_generate_personal_data_export_file' );
		$has_erasure_tool = function_exists( 'wp_privacy_anonymize_data' );

		// Check if site collects user data
		global $wpdb;
		$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		$collects_data = absint( $user_count ) > 1; // More than admin user

		// Check for third-party data sharing
		global $wp_scripts;
		$shares_data = false;

		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! isset( $script->src ) ) {
					continue;
				}

				// Check for advertising networks
				if ( strpos( $script->src, 'googleadservices' ) !== false ||
				     strpos( $script->src, 'facebook.net' ) !== false ||
				     strpos( $script->src, 'doubleclick' ) !== false ) {
					$shares_data = true;
					break;
				}
			}
		}

		// Generate findings if CCPA requirements not met
		if ( $collects_data && ! $has_do_not_sell && $shares_data ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site shares user data with third parties without "Do Not Sell My Personal Information" option. CCPA requires opt-out mechanism.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ccpa-privacy-rights',
				'meta'         => array(
					'has_do_not_sell'  => $has_do_not_sell,
					'has_privacy_page' => ! empty( $privacy_page ),
					'has_export_tool'  => $has_export_tool,
					'has_erasure_tool' => $has_erasure_tool,
					'collects_data'    => $collects_data,
					'shares_data'      => $shares_data,
					'user_count'       => absint( $user_count ),
					'recommendation'   => 'Add "Do Not Sell" link and implement opt-out mechanism',
					'ccpa_requirements' => array(
						'Notice at collection',
						'Do Not Sell opt-out',
						'Access to personal information',
						'Deletion rights',
						'Non-discrimination for exercising rights',
					),
					'fines'            => 'Up to $7,500 per intentional violation',
				),
			);
		}

		// Check if privacy policy exists
		if ( $collects_data && empty( $privacy_page ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site collects user data without designated privacy policy page. Both GDPR and CCPA require privacy policy.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ccpa-privacy-rights',
				'meta'         => array(
					'has_privacy_page' => false,
					'user_count'       => absint( $user_count ),
					'recommendation'   => 'Create and designate privacy policy page in Settings > Privacy',
				),
			);
		}

		return null;
	}
}
