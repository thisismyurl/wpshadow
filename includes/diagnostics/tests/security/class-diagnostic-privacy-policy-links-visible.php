<?php
/**
 * Privacy Policy Links Visible Diagnostic
 *
 * Checks that a privacy policy page is assigned, published, and linked
 * in a navigation menu so visitors can easily access it as required by
 * GDPR, CCPA, and other privacy regulations.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Privacy_Policy_Links_Visible Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Privacy_Policy_Links_Visible extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-links-visible';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Links Visible';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that a privacy policy page is assigned in WordPress settings, published, and linked in a navigation menu to meet GDPR, CCPA, and other privacy regulation requirements.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Three-step validation: (1) privacy page set in wp_page_for_privacy_policy,
	 * (2) page is published, (3) page appears in at least one nav menu.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );

		if ( 0 === $privacy_page_id ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No privacy policy page has been set in WordPress. A visible, accessible privacy policy is required by GDPR, CCPA, and most other privacy regulations. Create a privacy policy page and assign it under Settings → Privacy.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'details'      => array( 'privacy_page_id' => 0, 'linked_in_menu' => false ),
			);
		}

		$page = get_post( $privacy_page_id );
		if ( ! $page || 'publish' !== $page->post_status ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A privacy policy page is assigned but it is not published. Visitors and regulators cannot access an unpublished privacy policy. Publish the page or replace it with an active page under Settings → Privacy.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'details'      => array( 'privacy_page_id' => $privacy_page_id, 'page_status' => $page ? $page->post_status : 'missing' ),
			);
		}

		// Check if the privacy policy page is linked in any registered nav menu.
		$menus = wp_get_nav_menus();
		$linked = false;
		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			if ( is_array( $items ) ) {
				foreach ( $items as $item ) {
					if ( 'post_type' === $item->type && (int) $item->object_id === $privacy_page_id ) {
						$linked = true;
						break 2;
					}
				}
			}
		}

		if ( ! $linked ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A privacy policy page exists but is not linked in any navigation menu. Privacy regulations require the policy to be easily accessible from any page, typically via a footer menu. Add the privacy policy page to a navigation menu.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'details'      => array( 'privacy_page_id' => $privacy_page_id, 'linked_in_menu' => false ),
			);
		}

		return null;
	}
}
