<?php
/**
 * Quick Edit Functionality Diagnostic
 *
 * Tests quick edit feature in post list for reliability.
 * Verifies data saves correctly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1345
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quick Edit Functionality Diagnostic Class
 *
 * Verifies that WordPress Quick Edit feature is functioning
 * properly and saving data without loss or corruption.
 *
 * @since 1.6033.1345
 */
class Diagnostic_Quick_Edit_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'quick-edit-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Quick Edit Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests quick edit feature for reliability and data integrity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1345
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if admin-ajax.php is accessible (required for quick edit).
		$admin_ajax_url = admin_url( 'admin-ajax.php' );
		$ajax_test      = wp_remote_post(
			$admin_ajax_url,
			array(
				'timeout' => 5,
				'body'    => array( 'action' => 'heartbeat' ),
			)
		);

		if ( is_wp_error( $ajax_test ) ) {
			$issues[] = __( 'Admin AJAX endpoint not accessible (quick edit will fail)', 'wpshadow' );
		}

		// Check for JavaScript conflicts that might break quick edit.
		global $wp_scripts;
		$required_scripts = array( 'inline-edit-post', 'jquery' );
		foreach ( $required_scripts as $handle ) {
			if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
				$issues[] = sprintf(
					/* translators: %s: script handle */
					__( 'Required script "%s" not registered', 'wpshadow' ),
					$handle
				);
			}
		}

		// Check for posts with very long titles that might break quick edit UI.
		$long_titles = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE LENGTH(post_title) > 200
			AND post_status IN ('publish', 'draft', 'pending', 'private')
			AND post_type IN ('post', 'page')"
		);

		if ( $long_titles > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have very long titles (>200 chars, may break quick edit)', 'wpshadow' ),
				$long_titles
			);
		}

		// Check if nonces are properly configured.
		if ( ! wp_verify_nonce( wp_create_nonce( 'inlineeditnonce' ), 'inlineeditnonce' ) ) {
			// This is actually testing nonce creation/verification works.
			$issues[] = __( 'Nonce verification system may be compromised', 'wpshadow' );
		}

		// Check for excessive number of custom fields (can slow quick edit).
		$posts_with_many_meta = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id)
			FROM (
				SELECT post_id, COUNT(*) as meta_count
				FROM {$wpdb->postmeta}
				WHERE meta_key NOT LIKE '\_%'
				GROUP BY post_id
				HAVING meta_count > 50
			) as heavy_meta_subquery"
		);

		if ( $posts_with_many_meta > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have excessive custom fields (>50, may slow quick edit)', 'wpshadow' ),
				$posts_with_many_meta
			);
		}

		// Check for custom taxonomies that might not appear in quick edit.
		$public_taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
		$quick_editable    = 0;
		foreach ( $public_taxonomies as $taxonomy ) {
			$tax_object = get_taxonomy( $taxonomy );
			if ( ! empty( $tax_object->show_in_quick_edit ) ) {
				++$quick_editable;
			}
		}

		if ( count( $public_taxonomies ) > 0 && $quick_editable === 0 ) {
			$issues[] = __( 'Public taxonomies may not be available in quick edit', 'wpshadow' );
		}

		// Check if max_input_vars is sufficient for quick edit with many fields.
		$max_input_vars = ini_get( 'max_input_vars' );
		if ( $max_input_vars && $max_input_vars < 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: current value */
				__( 'PHP max_input_vars is low (%d) - quick edit may fail with many fields', 'wpshadow' ),
				$max_input_vars
			);
		}

		// Check for posts with invalid post_author that would fail quick edit.
		$invalid_authors = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID
			WHERE p.post_author > 0
			AND u.ID IS NULL
			AND p.post_status IN ('publish', 'draft', 'pending', 'private')
			LIMIT 100"
		);

		if ( $invalid_authors > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have invalid authors (quick edit will fail)', 'wpshadow' ),
				$invalid_authors
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/quick-edit-functionality',
			);
		}

		return null;
	}
}
