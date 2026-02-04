<?php
/**
 * Custom Field Visibility Diagnostic
 *
 * Checks if custom fields display in post editor correctly. Tests custom field UI
 * availability and configuration issues that hide custom fields from users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Field Visibility Diagnostic Class
 *
 * Checks for custom field visibility issues in the post editor.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Custom_Field_Visibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-field-visibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Field Visibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates custom fields display correctly in post editor';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if 'custom-fields' is supported by posts/pages.
		$posts_support = post_type_supports( 'post', 'custom-fields' );
		$pages_support = post_type_supports( 'page', 'custom-fields' );

		if ( ! $posts_support || ! $pages_support ) {
			$unsupported = array();
			if ( ! $posts_support ) {
				$unsupported[] = 'posts';
			}
			if ( ! $pages_support ) {
				$unsupported[] = 'pages';
			}

			// Check if there are custom fields stored.
			$post_types = array();
			if ( ! $posts_support ) {
				$post_types[] = 'post';
			}
			if ( ! $pages_support ) {
				$post_types[] = 'page';
			}

			$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
			$meta_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT pm.post_id)
					FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
					WHERE p.post_type IN ($placeholders)
					AND pm.meta_key NOT LIKE '\\_%%'", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					...$post_types
				)
			);

			if ( $meta_count > 0 ) {
				$issues[] = sprintf(
					/* translators: 1: post types, 2: number of posts */
					__( 'Custom fields disabled for %1$s but %2$d posts have custom field data (users can\'t edit)', 'wpshadow' ),
					implode( ', ', $unsupported ),
					$meta_count
				);
			}
		}

		// Check if postcustom meta box is removed.
		global $wp_meta_boxes;
		$postcustom_visible = false;
		foreach ( array( 'post', 'page' ) as $post_type ) {
			if ( isset( $wp_meta_boxes[ $post_type ] ) ) {
				foreach ( $wp_meta_boxes[ $post_type ] as $context => $priority_boxes ) {
					foreach ( $priority_boxes as $priority => $boxes ) {
						if ( isset( $boxes['postcustom'] ) ) {
							$postcustom_visible = true;
							break 3;
						}
					}
				}
			}
		}

		if ( ! $postcustom_visible && ( $posts_support || $pages_support ) ) {
			$issues[] = __( 'Custom fields meta box removed but custom-fields support enabled (users can\'t access UI)', 'wpshadow' );
		}

		// Check if using Gutenberg without custom fields panel enabled.
		if ( function_exists( 'use_block_editor_for_post_type' ) ) {
			$using_block_editor_posts = use_block_editor_for_post_type( 'post' );
			$using_block_editor_pages = use_block_editor_for_post_type( 'page' );

			if ( ( $using_block_editor_posts || $using_block_editor_pages ) && ( $posts_support || $pages_support ) ) {
				// In Gutenberg, custom fields are hidden by default.
				$issues[] = __( 'Gutenberg enabled with custom-fields support (users must enable Custom Fields panel in preferences)', 'wpshadow' );
			}
		}

		// Check for custom fields that might be hidden by plugins.
		$hidden_meta_keys = $wpdb->get_col(
			"SELECT DISTINCT meta_key
			FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '\\_%%'
			LIMIT 50"
		);

		$public_looking_hidden = 0;
		foreach ( $hidden_meta_keys as $key ) {
			// Check if key looks like it should be public (no common private patterns).
			if ( ! preg_match( '/^_(edit|wp|thumbnail|menu|enclosure|pingme|trackbackme)/', $key ) ) {
				++$public_looking_hidden;
			}
		}

		if ( $public_looking_hidden > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of keys */
				__( '%d hidden meta keys look like they should be visible (prefixed with underscore)', 'wpshadow' ),
				$public_looking_hidden
			);
		}

		// Check if there are public custom fields but no meta boxes.
		$public_meta_count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key)
			FROM {$wpdb->postmeta}
			WHERE meta_key NOT LIKE '\\_%%'"
		);

		if ( $public_meta_count > 20 && ! $postcustom_visible ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom field types */
				__( '%d public custom field types exist but no custom fields meta box (data exists but hidden)', 'wpshadow' ),
				$public_meta_count
			);
		}

		// Check user capabilities.
		$current_user = wp_get_current_user();
		if ( $current_user && $current_user->ID > 0 ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				$issues[] = __( 'Current user cannot edit posts (custom fields will not be editable)', 'wpshadow' );
			}
		}

		// Check for screen options that might hide custom fields.
		$user_id = get_current_user_id();
		if ( $user_id > 0 ) {
			$hidden_meta_boxes = get_user_meta( $user_id, 'metaboxhidden_post', true );
			if ( is_array( $hidden_meta_boxes ) && in_array( 'postcustom', $hidden_meta_boxes, true ) ) {
				$issues[] = __( 'Current user has custom fields meta box hidden via Screen Options', 'wpshadow' );
			}
		}

		// Check if custom fields are completely disabled via filter.
		$custom_fields_disabled = apply_filters( 'disable_captions', false );
		if ( true === $custom_fields_disabled ) {
			$issues[] = __( 'Custom fields disabled via disable_captions filter', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/custom-field-visibility',
			);
		}

		return null;
	}
}
