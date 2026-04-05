<?php
/**
 * Treatment: Update default "Sample Page" slug and/or title
 *
 * When a site has repurposed the starter page with custom content but never
 * updated its original slug (sample-page) or title ("Sample Page"), the
 * placeholder identifiers appear in navigation menus, browser tabs, and
 * social share cards. This treatment renames only the stale identifiers:
 *   - Slug  sample-page  → about
 *   - Title "Sample Page" → "About"
 *
 * It only updates fields that still hold the WordPress default values so it
 * never overwrites intentional customisation.
 *
 * Undo: restores the previous slug and title from stored values.
 *
 * @package WPShadow
 * @since   0.6093.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renames stale slug/title identifiers on the repurposed Sample Page.
 */
class Treatment_Default_Page_Slug_Updated extends Treatment_Base {

	/** @var string */
	protected static $slug = 'default-page-slug-updated';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Locate the page and update any stale default identifiers.
	 *
	 * @return array
	 */
	public static function apply(): array {
		// Primary lookup: canonical slug from a fresh WordPress install.
		$page = get_page_by_path( 'sample-page', OBJECT, 'page' );

		// Fallback: slug was changed but title may still be the default.
		if ( null === $page ) {
			$query = new \WP_Query(
				array(
					'post_type'      => 'page',
					'post_status'    => array( 'publish', 'draft', 'private', 'future' ),
					'title'          => 'Sample Page',
					'posts_per_page' => 1,
					'no_found_rows'  => true,
				)
			);
			$page = $query->have_posts() ? $query->posts[0] : null;
		}

		if ( null === $page ) {
			return array(
				'success' => true,
				'message' => __( 'Default sample page not found — slug and title may have already been updated.', 'wpshadow' ),
			);
		}

		// If default body text is still present, the "remove" diagnostic covers this — skip.
		if ( str_contains( (string) $page->post_content, 'This is an example page' ) ) {
			return array(
				'success' => false,
				'message' => __( 'This page still contains the original sample content. Use the "Delete default Sample Page" treatment instead.', 'wpshadow' ),
			);
		}

		$has_default_slug  = ( 'sample-page' === $page->post_name );
		$has_default_title = ( 'Sample Page' === $page->post_title );

		if ( ! $has_default_slug && ! $has_default_title ) {
			return array(
				'success' => true,
				'message' => __( 'The page\'s slug and title have already been customised. No changes made.', 'wpshadow' ),
			);
		}

		// Store originals for undo().
		static::save_backup_value(
			'wpshadow_default_page_slug_prev',
			array(
				'id'    => $page->ID,
				'slug'  => $page->post_name,
				'title' => $page->post_title,
			)
		);

		$update_args = array( 'ID' => $page->ID );
		$changed     = array();

		if ( $has_default_slug ) {
			$update_args['post_name'] = 'about';
			$changed[]                = __( 'slug updated to "about"', 'wpshadow' );
		}

		if ( $has_default_title ) {
			$update_args['post_title'] = 'About';
			$changed[]                 = __( 'title updated to "About"', 'wpshadow' );
		}

		$result = wp_update_post( $update_args, true );

		if ( is_wp_error( $result ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: WP_Error message */
					__( 'Could not update the page: %s', 'wpshadow' ),
					$result->get_error_message()
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: comma-separated list of changes made */
				__( 'Page identifiers updated: %s. Edit the page to set a more specific title and permalink if needed.', 'wpshadow' ),
				implode( ', ', $changed )
			),
		);
	}

	/**
	 * Restore the previous page slug and title.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$loaded = static::load_backup_array( 'wpshadow_default_page_slug_prev', array( 'id', 'slug', 'title' ), true );
		$prev   = $loaded['value'];

		if ( ! $loaded['found'] || ! is_array( $prev ) ) {
			return array(
				'success' => false,
				'message' => __( 'No stored page data to restore.', 'wpshadow' ),
			);
		}

		$result = wp_update_post(
			array(
				'ID'         => (int) $prev['id'],
				'post_name'  => $prev['slug'],
				'post_title' => $prev['title'],
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: WP_Error message */
					__( 'Could not restore the page: %s', 'wpshadow' ),
					$result->get_error_message()
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: 1: slug, 2: title */
				__( 'Page restored to slug "%1$s" and title "%2$s".', 'wpshadow' ),
				esc_html( $prev['slug'] ),
				esc_html( $prev['title'] )
			),
		);
	}
}
