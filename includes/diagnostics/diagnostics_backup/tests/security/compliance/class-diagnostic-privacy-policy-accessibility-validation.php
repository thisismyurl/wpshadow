<?php
/**
 * Privacy Policy Accessibility Validation Diagnostic
 *
 * Ensures privacy policy is accessible per legal requirements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Accessibility Validation Class
 *
 * Tests whether privacy policy meets legal accessibility requirements.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Privacy_Policy_Accessibility_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-accessibility-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Accessibility Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures privacy policy is accessible per legal requirements';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if privacy policy page exists and is set.
		$privacy_policy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === $privacy_policy_page_id ) {
			$issues[] = __( 'No privacy policy page configured in Settings > Privacy', 'wpshadow' );
		} else {
			$privacy_page = get_post( $privacy_policy_page_id );
			if ( ! $privacy_page || 'publish' !== $privacy_page->post_status ) {
				$issues[] = __( 'Privacy policy page exists but is not published', 'wpshadow' );
			} elseif ( self::is_page_login_required( $privacy_policy_page_id ) ) {
				$issues[] = __( 'Privacy policy page requires login (must be publicly accessible)', 'wpshadow' );
			} elseif ( self::is_generic_template( $privacy_page->post_content ) ) {
				$issues[] = __( 'Privacy policy appears to be generic template (not customized)', 'wpshadow' );
			}
		}

		// Check for footer link.
		if ( ! self::has_footer_privacy_link() ) {
			$issues[] = __( 'Privacy policy link not found in footer', 'wpshadow' );
		}

		// Check for links in registration forms.
		if ( get_option( 'users_can_register' ) && ! self::has_registration_privacy_link() ) {
			$issues[] = __( 'Privacy policy not linked on registration form', 'wpshadow' );
		}

		// Check for links in checkout forms (WooCommerce).
		if ( class_exists( 'WooCommerce' ) && ! self::has_checkout_privacy_link() ) {
			$issues[] = __( 'Privacy policy not linked on WooCommerce checkout', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/privacy-policy-accessibility-validation',
				'meta'         => array(
					'issues_found' => count( $issues ),
					'issues'       => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Check if page requires login.
	 *
	 * @since  1.26028.1905
	 * @param  int $page_id Page ID to check.
	 * @return bool True if page requires login.
	 */
	private static function is_page_login_required( $page_id ) {
		// Check common membership plugins.
		$post_meta = get_post_meta( $page_id );

		// Check for common membership plugin meta keys.
		$restricted_keys = array(
			'_restricted_to',
			'_members_access_role',
			'_pmpro_membership_level',
			'_wc_memberships_access',
		);

		foreach ( $restricted_keys as $key ) {
			if ( isset( $post_meta[ $key ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if privacy policy content is generic template.
	 *
	 * @since  1.26028.1905
	 * @param  string $content Page content.
	 * @return bool True if content appears to be generic template.
	 */
	private static function is_generic_template( $content ) {
		// Check for common template phrases.
		$template_phrases = array(
			'[your company name]',
			'[company name]',
			'[your name]',
			'[website name]',
			'insert your',
			'replace this',
			'example.com',
			'yoursite.com',
		);

		$content_lower = strtolower( $content );

		foreach ( $template_phrases as $phrase ) {
			if ( false !== strpos( $content_lower, $phrase ) ) {
				return true;
			}
		}

		// Check if content is too short (less than 500 characters).
		if ( strlen( strip_tags( $content ) ) < 500 ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if footer has privacy policy link.
	 *
	 * @since  1.26028.1905
	 * @return bool True if footer has privacy link.
	 */
	private static function has_footer_privacy_link() {
		$privacy_policy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === $privacy_policy_page_id ) {
			return false;
		}

		// Check if theme supports privacy link (WordPress 4.9.6+).
		$nav_menus = get_registered_nav_menus();
		foreach ( $nav_menus as $location => $description ) {
			if ( false !== stripos( $location, 'footer' ) ) {
				$menu_items = wp_get_nav_menu_items( get_nav_menu_locations()[ $location ] ?? 0 );
				if ( $menu_items ) {
					foreach ( $menu_items as $item ) {
						if ( (int) $item->object_id === $privacy_policy_page_id ) {
							return true;
						}
					}
				}
			}
		}

		// Check if theme uses wp_footer hook for privacy link.
		return has_action( 'wp_footer', 'the_privacy_policy_link' );
	}

	/**
	 * Check if registration form has privacy policy link.
	 *
	 * @since  1.26028.1905
	 * @return bool True if registration form has privacy link.
	 */
	private static function has_registration_privacy_link() {
		// WordPress core adds privacy checkbox since 4.9.6.
		// Check if register_form hook is used.
		return has_action( 'register_form', 'wp_registration_privacy_policy_text' ) !== false;
	}

	/**
	 * Check if WooCommerce checkout has privacy policy link.
	 *
	 * @since  1.26028.1905
	 * @return bool True if checkout has privacy link.
	 */
	private static function has_checkout_privacy_link() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return true; // N/A if WooCommerce not active.
		}

		// WooCommerce adds privacy checkbox since 3.4.0.
		$privacy_policy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === $privacy_policy_page_id ) {
			return false;
		}

		// Check WooCommerce privacy settings.
		$wc_privacy_enabled = get_option( 'woocommerce_checkout_privacy_policy_text' );
		return ! empty( $wc_privacy_enabled );
	}
}
