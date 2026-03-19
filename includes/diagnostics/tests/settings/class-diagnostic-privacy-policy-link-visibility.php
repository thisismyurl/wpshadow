<?php
/**
 * Privacy Policy Link Visibility Diagnostic
 *
 * Ensures the privacy policy is easily accessible to visitors through
 * proper linking in key locations like footer, registration forms, etc.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Link Visibility Diagnostic Class
 *
 * Checks that the privacy policy link is visible in appropriate locations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Privacy_Policy_Link_Visibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-link-visibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Link Visibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies privacy policy is easily accessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Privacy policy link in comment forms
	 * - Privacy policy link in registration forms
	 * - Privacy policy in footer menu
	 * - Privacy policy auto-linking enabled
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get the privacy policy page.
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );

		if ( 0 === $privacy_page_id ) {
			// No privacy policy page set - handled by another diagnostic.
			return null;
		}

		// Check if comments show privacy policy.
		$show_comments_cookies_opt_in = (bool) get_option( 'show_comments_cookies_opt_in', false );
		if ( ! $show_comments_cookies_opt_in ) {
			$issues[] = __( 'Comment cookie consent is disabled; privacy policy link will not appear in comment forms', 'wpshadow' );
		}

		// Check for menus containing privacy policy link.
		$has_privacy_in_menu = false;
		$menus               = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id );
			if ( ! $menu_items ) {
				continue;
			}

			foreach ( $menu_items as $item ) {
				if ( (int) $item->object_id === $privacy_page_id ) {
					$has_privacy_in_menu = true;
					break 2;
				}
			}
		}

		if ( ! $has_privacy_in_menu && ! empty( $menus ) ) {
			$issues[] = __( 'Privacy policy is not linked in any navigation menu', 'wpshadow' );
		}

		// Check if widgets might contain privacy policy link.
		$has_privacy_widget = false;
		global $wp_registered_widgets;

		foreach ( $wp_registered_widgets as $widget ) {
			if ( ! empty( $widget['callback'][0] ) && is_object( $widget['callback'][0] ) ) {
				$widget_class = get_class( $widget['callback'][0] );
				if ( false !== stripos( $widget_class, 'nav' ) || false !== stripos( $widget_class, 'menu' ) ) {
					$has_privacy_widget = true;
					break;
				}
			}
		}

		// Check registration form (if users can register).
		$users_can_register = (bool) get_option( 'users_can_register', false );
		if ( $users_can_register ) {
			// WordPress doesn't have a built-in setting for this, so we note it.
			$issues[] = __( 'User registration is enabled; consider adding privacy policy link to registration form', 'wpshadow' );
		}

		// Check if theme supports privacy policy link.
		$theme_support = current_theme_supports( 'wp-block-patterns' );
		if ( ! $theme_support ) {
			$issues[] = __( 'Theme may not support automatic privacy policy links; manual footer link recommended', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/privacy-policy-link-visibility',
			);
		}

		return null;
	}
}
