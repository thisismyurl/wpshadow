<?php
/**
 * Treatment: Make the privacy policy visible in navigation
 *
 * A privacy policy should be published and reachable from site navigation.
 * This treatment ensures a privacy policy page exists, then adds it to an
 * existing footer menu when possible, falling back to another menu or a newly
 * created footer-style menu.
 *
 * Undo: removes the added menu item, restores prior menu-location assignment
 * when one was changed, deletes any menu created by WPShadow, and reverts the
 * privacy page setup when this treatment had to create or assign one.
 *
 * @package WPShadow
 * @since   0.7056.0200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensures the privacy policy page is linked in site navigation.
 */
class Treatment_Privacy_Policy_Links_Visible extends Treatment_Base {

	/** @var string */
	protected static $slug = 'privacy-policy-links-visible';

	/**
	 * Footer-ish menu location keywords.
	 *
	 * @var array<int,string>
	 */
	private const FOOTER_KEYWORDS = array( 'footer', 'bottom', 'secondary', 'utility' );

	/** @return string */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Ensure a privacy policy exists and is linked in navigation.
	 *
	 * @return array
	 */
	public static function apply(): array {
		$page_was_prepared = false;
		$page_id           = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		$page              = $page_id > 0 ? get_post( $page_id ) : null;

		if ( 0 === $page_id || ! ( $page instanceof \WP_Post ) || 'publish' !== $page->post_status ) {
			$prepared = Treatment_Privacy_Policy_Page_Set::apply();
			if ( empty( $prepared['success'] ) ) {
				return $prepared;
			}
			$page_was_prepared = true;
			$page_id           = (int) get_option( 'wp_page_for_privacy_policy', 0 );
			$page              = $page_id > 0 ? get_post( $page_id ) : null;
		}

		if ( $page_id <= 0 || ! ( $page instanceof \WP_Post ) ) {
			return array(
				'success' => false,
				'message' => __( 'A published privacy policy page could not be prepared automatically.', 'wpshadow' ),
			);
		}

		if ( self::page_is_linked_in_any_menu( $page_id ) ) {
			static::save_backup_value( 'wpshadow_privacy_links_visible_prepared_page', $page_was_prepared ? 1 : 0 );
			return array(
				'success' => true,
				'message' => __( 'The privacy policy page is already linked in navigation.', 'wpshadow' ),
			);
		}

		$menu_context = self::ensure_target_menu();
		if ( empty( $menu_context['menu_id'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Could not find or create a navigation menu for the privacy policy link.', 'wpshadow' ),
			);
		}

		$item_id = wp_update_nav_menu_item(
			(int) $menu_context['menu_id'],
			0,
			array(
				'menu-item-title'     => get_the_title( $page_id ),
				'menu-item-object-id' => $page_id,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);

		if ( is_wp_error( $item_id ) || ! $item_id ) {
			return array(
				'success' => false,
				'message' => __( 'The privacy policy page could not be added to a navigation menu.', 'wpshadow' ),
			);
		}

		static::save_backup_value(
			'wpshadow_privacy_links_visible_menu',
			array(
				'item_id'             => (int) $item_id,
				'created_menu_id'     => (int) $menu_context['created_menu_id'],
				'previous_locations'  => $menu_context['previous_locations'],
			)
		);
		static::save_backup_value( 'wpshadow_privacy_links_visible_prepared_page', $page_was_prepared ? 1 : 0 );

		return array(
			'success' => true,
			'message' => __( 'The privacy policy page was added to site navigation.', 'wpshadow' ),
		);
	}

	/**
	 * Undo the menu visibility changes.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$menu_loaded     = static::load_backup_array( 'wpshadow_privacy_links_visible_menu', array( 'item_id', 'created_menu_id', 'previous_locations' ), true );
		$prepared_loaded = static::load_backup_value( 'wpshadow_privacy_links_visible_prepared_page', true );

		if ( $menu_loaded['found'] && is_array( $menu_loaded['value'] ) ) {
			$data = $menu_loaded['value'];

			if ( ! empty( $data['item_id'] ) ) {
				wp_delete_post( (int) $data['item_id'], true );
			}

			if ( is_array( $data['previous_locations'] ) ) {
				set_theme_mod( 'nav_menu_locations', $data['previous_locations'] );
			}

			if ( ! empty( $data['created_menu_id'] ) && function_exists( 'wp_delete_nav_menu' ) ) {
				wp_delete_nav_menu( (int) $data['created_menu_id'] );
			}
		}

		if ( $prepared_loaded['found'] && (int) $prepared_loaded['value'] === 1 ) {
			Treatment_Privacy_Policy_Page_Set::undo();
		}

		return array(
			'success' => true,
			'message' => __( 'Privacy policy navigation changes restored.', 'wpshadow' ),
		);
	}

	/**
	 * Determine whether a page is linked in any nav menu.
	 *
	 * @param int $page_id Page ID.
	 * @return bool
	 */
	private static function page_is_linked_in_any_menu( int $page_id ): bool {
		$menus = wp_get_nav_menus();

		foreach ( (array) $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			foreach ( (array) $items as $item ) {
				if ( 'post_type' === $item->type && (int) $item->object_id === $page_id ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Ensure there is a suitable menu to add legal links to.
	 *
	 * @return array{menu_id:int,created_menu_id:int,previous_locations:array<string,int>}
	 */
	private static function ensure_target_menu(): array {
		$registered = get_registered_nav_menus();
		$locations  = get_nav_menu_locations();
		$previous   = is_array( $locations ) ? $locations : array();
		$menus      = wp_get_nav_menus();

		foreach ( (array) $registered as $location_key => $label ) {
			$needle = strtolower( $location_key . ' ' . $label );
			foreach ( self::FOOTER_KEYWORDS as $keyword ) {
				if ( str_contains( $needle, $keyword ) ) {
					if ( ! empty( $locations[ $location_key ] ) ) {
						return array(
							'menu_id'            => (int) $locations[ $location_key ],
							'created_menu_id'    => 0,
							'previous_locations' => $previous,
						);
					}

					$menu_id = wp_create_nav_menu( __( 'Footer Navigation', 'wpshadow' ) );
					if ( ! is_wp_error( $menu_id ) && $menu_id > 0 ) {
						$locations[ $location_key ] = (int) $menu_id;
						set_theme_mod( 'nav_menu_locations', $locations );
						return array(
							'menu_id'            => (int) $menu_id,
							'created_menu_id'    => (int) $menu_id,
							'previous_locations' => $previous,
						);
					}
				}
			}
		}

		if ( ! empty( $menus ) ) {
			return array(
				'menu_id'            => (int) $menus[0]->term_id,
				'created_menu_id'    => 0,
				'previous_locations' => $previous,
			);
		}

		$menu_id = wp_create_nav_menu( __( 'Legal Links', 'wpshadow' ) );
		if ( is_wp_error( $menu_id ) || $menu_id <= 0 ) {
			return array(
				'menu_id'            => 0,
				'created_menu_id'    => 0,
				'previous_locations' => $previous,
			);
		}

		return array(
			'menu_id'            => (int) $menu_id,
			'created_menu_id'    => (int) $menu_id,
			'previous_locations' => $previous,
		);
	}
}