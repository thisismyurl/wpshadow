<?php
/**
 * CPT Multi-Language Support
 *
 * Provides integration with WPML and Polylang for multilingual content.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Multi_Language Class
 *
 * Integrates custom post types with WPML and Polylang.
 *
 * @since 1.6093.1200
 */
class CPT_Multi_Language {

	/**
	 * Initialize multi-language support.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_translations' ), 100 );
		add_filter( 'wpml_custom_post_types', array( __CLASS__, 'register_with_wpml' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_with_polylang' ) );
	}

	/**
	 * Register custom post types for translation.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_translations() {
		$post_types = self::get_translatable_post_types();

		// WPML Integration.
		if ( function_exists( 'wpml_element_type' ) ) {
			foreach ( $post_types as $post_type ) {
				wpml_register_single_string( 'wpshadow', $post_type . '_name', get_post_type_object( $post_type )->labels->name );
			}
		}

		// Polylang Integration.
		if ( function_exists( 'pll_register_string' ) ) {
			foreach ( $post_types as $post_type ) {
				$obj = get_post_type_object( $post_type );
				if ( $obj ) {
					pll_register_string( $post_type . '_name', $obj->labels->name, 'wpshadow' );
				}
			}
		}
	}

	/**
	 * Register CPTs with WPML.
	 *
	 * @since 1.6093.1200
	 * @param  array $post_types Registered post types.
	 * @return array Modified post types array.
	 */
	public static function register_with_wpml( $post_types ) {
		$cpt_post_types = self::get_translatable_post_types();

		foreach ( $cpt_post_types as $post_type ) {
			$post_types[ $post_type ] = array(
				'translate'           => 1,
				'display_as_translated' => 1,
			);
		}

		return $post_types;
	}

	/**
	 * Register CPTs with Polylang.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_with_polylang() {
		if ( ! function_exists( 'pll_register_post_type' ) ) {
			return;
		}

		$post_types = self::get_translatable_post_types();

		foreach ( $post_types as $post_type ) {
			pll_register_post_type( $post_type );
		}

		// Register taxonomies.
		$taxonomies = self::get_translatable_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			if ( function_exists( 'pll_register_taxonomy' ) ) {
				pll_register_taxonomy( $taxonomy );
			}
		}
	}

	/**
	 * Get translatable post types.
	 *
	 * @since 1.6093.1200
	 * @return array Post type slugs (only if they exist).
	 */
	private static function get_translatable_post_types() {
		$post_types = array(
			'testimonial',
			'team_member',
			'portfolio_item',
			'wps_event',
			'resource',
			'case_study',
			'service',
			'location',
			'documentation',
			'wps_product',
		);

		// Filter to only registered post types.
		return array_filter( $post_types, 'post_type_exists' );
	}

	/**
	 * Get translatable taxonomies.
	 *
	 * @since 1.6093.1200
	 * @return array Taxonomy slugs (only if they exist).
	 */
	private static function get_translatable_taxonomies() {
		$taxonomies = array(
			'testimonial_category',
			'team_department',
			'portfolio_category',
			'portfolio_tag',
			'event_category',
			'event_tag',
			'resource_type',
			'resource_category',
			'case_study_industry',
			'case_study_service',
			'service_category',
			'location_type',
			'doc_category',
			'doc_tag',
			'product_category',
		);

		// Filter to only registered taxonomies.
		return array_filter( $taxonomies, 'taxonomy_exists' );
	}

	/**
	 * Get available languages.
	 *
	 * @since 1.6093.1200
	 * @return array Languages array.
	 */
	public static function get_available_languages() {
		$languages = array();

		// WPML.
		if ( function_exists( 'icl_get_languages' ) ) {
			$wpml_languages = icl_get_languages( 'skip_missing=0' );
			foreach ( $wpml_languages as $lang ) {
				$languages[ $lang['code'] ] = $lang['native_name'];
			}
		}

		// Polylang.
		if ( function_exists( 'pll_the_languages' ) ) {
			$pll_languages = pll_the_languages( array( 'raw' => 1 ) );
			foreach ( $pll_languages as $lang ) {
				$languages[ $lang['slug'] ] = $lang['name'];
			}
		}

		return $languages;
	}

	/**
	 * Get post translations.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return array Translations array.
	 */
	public static function get_post_translations( $post_id ) {
		$translations = array();

		// WPML.
		if ( function_exists( 'icl_object_id' ) ) {
			$trid = apply_filters( 'wpml_element_trid', null, $post_id, 'post_' . get_post_type( $post_id ) );
			if ( $trid ) {
				$translations = apply_filters( 'wpml_get_element_translations', array(), $trid );
			}
		}

		// Polylang.
		if ( function_exists( 'pll_get_post_translations' ) ) {
			$translations = pll_get_post_translations( $post_id );
		}

		return $translations;
	}
}
