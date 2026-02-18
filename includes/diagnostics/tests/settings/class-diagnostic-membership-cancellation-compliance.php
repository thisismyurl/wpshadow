<?php
/**
 * Membership Cancellation Compliance Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Membership_Cancellation_Compliance extends Diagnostic_Base {
	protected static $slug = 'membership-cancellation-compliance';
	protected static $title = 'Membership Cancellation Compliance';
	protected static $description = 'Verifies members can easily cancel with proper notice and refunds';
	protected static $family = 'privacy';

	public static function check() {
		// Check for membership plugins.
		$membership_plugins = array(
			'MemberPress'       => class_exists( 'MeprAppCtrl' ),
			'Paid Memberships Pro' => defined( 'PMPRO_VERSION' ),
			'Restrict Content Pro' => class_exists( 'RCP_Requirements_Check' ),
			'WooCommerce Memberships' => class_exists( 'WC_Memberships' ),
			'MemberMouse'       => class_exists( 'MemberMouse' ),
		);

		$active_memberships = array_filter( $membership_plugins );

		if ( empty( $active_memberships ) ) {
			return null; // No membership plugins active.
		}

		$issues = array();

		// Check if terms of service page exists.
		$pages = get_pages();
		$has_tos = false;
		$has_cancellation_policy = false;

		foreach ( $pages as $page ) {
			$title = strtolower( $page->post_title );
			$content = strtolower( $page->post_content );

			if ( strpos( $title, 'terms' ) !== false || strpos( $title, 'service' ) !== false ) {
				$has_tos = true;
			}

			if ( strpos( $content, 'cancel' ) !== false && strpos( $content, 'refund' ) !== false ) {
				$has_cancellation_policy = true;
			}
		}

		if ( ! $has_tos ) {
			$issues[] = array(
				'issue'       => 'no_terms_of_service',
				'description' => __( 'No Terms of Service page found for membership site', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		if ( ! $has_cancellation_policy ) {
			$issues[] = array(
				'issue'       => 'no_cancellation_policy',
				'description' => __( 'No clear cancellation/refund policy found in site content', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		// Check if there's a way to cancel online (look for account/subscription management).
		$has_account_page = get_option( 'woocommerce_myaccount_page_id' ) ||
		                    get_option( 'pmpro_account_page_id' ) ||
		                    get_option( 'mepr-account-page-id' );

		if ( ! $has_account_page ) {
			$issues[] = array(
				'issue'       => 'no_self_service_cancellation',
				'description' => __( 'No member account page detected - users may not be able to self-cancel', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d membership cancellation compliance issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/membership-cancellation-compliance',
		);
	}
}
