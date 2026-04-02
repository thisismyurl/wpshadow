<?php
/**
 * Contact Information Visibility Diagnostic
 *
 * Checks whether visitors can easily find contact details or a contact page.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Information Visibility Diagnostic Class
 *
 * Verifies that a contact page exists and is discoverable in menus,
 * with at least one contact method available.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Contact_Information_Visibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'contact-information-visibility';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Contact Information Difficult to Find';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if visitors can easily find a contact page or contact method';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$contact_keywords = array(
			'contact',
			'contact us',
			'get in touch',
			'support',
			'help',
			'customer support',
			'book a call',
			'request a quote',
			'schedule',
		);

		$contact_pages = self::find_pages_by_keywords( $contact_keywords );
		$menu_links    = self::find_menu_contact_links( $contact_keywords );

		$stats['contact_pages'] = ! empty( $contact_pages ) ? implode( ', ', $contact_pages ) : 'none';
		$stats['menu_links']    = ! empty( $menu_links ) ? implode( ', ', $menu_links ) : 'none';

		if ( empty( $contact_pages ) ) {
			$issues[] = __( 'No contact page detected using common names (Contact, Support, Help)', 'wpshadow' );
		}

		if ( empty( $menu_links ) ) {
			$issues[] = __( 'No contact link found in site navigation menus', 'wpshadow' );
		}

		$contact_plugins = array(
			'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
			'wpforms-lite/wpforms.php'            => 'WPForms Lite',
			'gravityforms/gravityforms.php'       => 'Gravity Forms',
			'ninja-forms/ninja-forms.php'          => 'Ninja Forms',
			'formidable/formidable.php'            => 'Formidable Forms',
		);

		$active_contact_plugins = array();
		foreach ( $contact_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_contact_plugins[] = $plugin_name;
			}
		}

		$stats['contact_plugins'] = ! empty( $active_contact_plugins ) ? implode( ', ', $active_contact_plugins ) : 'none';

		if ( empty( $active_contact_plugins ) ) {
			$issues[] = __( 'No contact form plugin detected', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Visitors need a clear way to reach you, like a front door on a shop. When contact details are hard to find, people often leave instead of asking a question or booking a service.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/contact-information-visibility',
			'context'      => array(
				'stats'  => $stats,
				'issues' => $issues,
			),
		);
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page' ),
					'post_status'    => 'publish',
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}

	/**
	 * Find navigation menu items that look like contact links.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching menu item titles.
	 */
	private static function find_menu_contact_links( array $keywords ): array {
		if ( ! function_exists( 'wp_get_nav_menus' ) ) {
			return array();
		}

		$matches = array();
		$menus   = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			if ( empty( $items ) ) {
				continue;
			}

			foreach ( $items as $item ) {
				$haystack = strtolower( $item->title . ' ' . $item->url );
				foreach ( $keywords as $keyword ) {
					if ( false !== strpos( $haystack, strtolower( $keyword ) ) ) {
						$matches[ $item->ID ] = $item->title;
						break;
					}
				}
			}
		}

		return array_values( $matches );
	}
}
