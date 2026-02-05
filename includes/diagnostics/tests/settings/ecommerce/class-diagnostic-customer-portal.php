<?php
/**
 * Customer Portal Diagnostic
 *
 * Checks whether customers have a self-service account portal.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Ecommerce
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Portal Diagnostic Class
 *
 * Verifies that account dashboard or order history access exists.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Customer_Portal extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'customer-portal';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Customer Portal or Account Dashboard';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customers have a self-service account area';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$portal_plugins = array(
			'woocommerce/woocommerce.php' => 'WooCommerce',
			'ultimate-member/ultimate-member.php' => 'Ultimate Member',
			'memberpress/memberpress.php' => 'MemberPress',
			'restrict-content-pro/restrict-content-pro.php' => 'Restrict Content Pro',
		);

		$active_portal = array();
		foreach ( $portal_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_portal[] = $plugin_name;
			}
		}

		$stats['portal_tools'] = ! empty( $active_portal ) ? implode( ', ', $active_portal ) : 'none';

		$account_page_id = (int) get_option( 'woocommerce_myaccount_page_id', 0 );
		$stats['woocommerce_account_page'] = $account_page_id > 0 ? 'set' : 'not set';

		if ( empty( $active_portal ) && 0 === $account_page_id ) {
			$issues[] = __( 'No customer portal or account page detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A customer portal lets people view orders, manage subscriptions, and help themselves. This reduces support requests and builds trust.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/customer-portal',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
