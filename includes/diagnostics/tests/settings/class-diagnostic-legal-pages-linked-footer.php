<?php
/**
 * Legal Pages Linked in Footer Diagnostic
 *
 * Checks whether the published privacy policy and other legal pages are linked
 * somewhere in the site footer menus for compliance and visitor trust.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Legal_Pages_Linked_Footer Class
 *
 * Verifies that the privacy policy page is linked in at least one registered
 * navigation menu, returning a medium-severity finding when it is not.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Legal_Pages_Linked_Footer extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'legal-pages-linked-footer';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Legal Pages Linked in Footer';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the published privacy policy and other legal pages are linked somewhere in the site footer for compliance and visitor trust.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Uses WP_Settings::has_published_privacy_policy_page() to confirm a valid
	 * page exists. If no menus are registered, returns null (unverifiable).
	 * Otherwise iterates all registered nav menus and their items, returning null
	 * when the privacy policy page ID is found in any menu. Returns a
	 * medium-severity finding when the page exists but is not linked anywhere.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when privacy policy is not in any menu, null when healthy.
	 */
	public static function check() {
		// Verify there is a published privacy policy page to look for.
		if ( ! WP_Settings::has_published_privacy_policy_page() ) {
			// No privacy policy page exists at all — covered by privacy-policy-page-set diagnostic.
			return null;
		}

		$privacy_page_id = WP_Settings::get_privacy_policy_page_id();

		// Check every registered nav menu for a link to the privacy policy page.
		$menus = wp_get_nav_menus();
		if ( empty( $menus ) || ! is_array( $menus ) ) {
			// No menus registered — can't verify.
			return null;
		}

		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
			if ( empty( $items ) || ! is_array( $items ) ) {
				continue;
			}
			foreach ( $items as $item ) {
				if ( 'post_type' === $item->type && (int) $item->object_id === $privacy_page_id ) {
					return null;
				}
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'A privacy policy page exists but is not linked in any of your registered navigation menus. Legal pages (Privacy Policy, Terms of Service) should be accessible from the footer of every page to meet GDPR, CCPA, and general compliance requirements.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => 'https://wpshadow.com/kb/legal-pages-linked-footer?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'privacy_page_id'     => $privacy_page_id,
				'menus_checked'       => count( $menus ),
				'found_in_nav_menu'   => false,
			),
		);
	}
}
