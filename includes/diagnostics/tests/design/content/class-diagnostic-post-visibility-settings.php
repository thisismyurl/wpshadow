<?php
/**
 * Post Visibility Settings Diagnostic
 *
 * Checks if post visibility (public/private/password) works correctly.
 * Tests access control and permission enforcement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Visibility Settings Diagnostic Class
 *
 * Verifies that post visibility settings are properly enforced
 * and access control works as expected.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Post_Visibility_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-visibility-settings';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Visibility Settings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post visibility and access control work correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for private posts visible to public.
		$private_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'private'
			AND post_type IN ('post', 'page')"
		);

		if ( $private_posts > 0 ) {
			// Check if read_private_posts capability is overly broad.
			$roles = wp_roles();
			$risky_roles = array();
			foreach ( $roles->roles as $role_name => $role_info ) {
				if ( isset( $role_info['capabilities']['read_private_posts'] ) &&
				     $role_info['capabilities']['read_private_posts'] &&
				     in_array( $role_name, array( 'subscriber', 'contributor' ), true ) ) {
					$risky_roles[] = $role_name;
				}
			}

			if ( ! empty( $risky_roles ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated role names */
					__( 'Roles with overly broad private post access: %s', 'wpshadow' ),
					implode( ', ', $risky_roles )
				);
			}
		}

		// Check for password-protected posts with weak/common passwords.
		$password_protected = $wpdb->get_results(
			"SELECT ID, post_password
			FROM {$wpdb->posts}
			WHERE post_password != ''
			AND post_status = 'publish'
			LIMIT 100"
		);

		$weak_passwords = 0;
		$common_passwords = array( 'password', '123456', 'admin', 'test', 'demo', '12345678' );
		foreach ( $password_protected as $post ) {
			if ( in_array( strtolower( $post->post_password ), $common_passwords, true ) ||
			     strlen( $post->post_password ) < 6 ) {
				++$weak_passwords;
			}
		}

		if ( $weak_passwords > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d password-protected posts have weak passwords', 'wpshadow' ),
				$weak_passwords
			);
		}

		// Check for posts with visibility metadata mismatch.
		$visibility_mismatch = $wpdb->get_var(
			"SELECT COUNT(p.ID)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_visibility'
			WHERE p.post_status = 'private'
			AND (pm.meta_value IS NULL OR pm.meta_value != 'private')"
		);

		if ( $visibility_mismatch > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d private posts have mismatched visibility metadata', 'wpshadow' ),
				$visibility_mismatch
			);
		}

		// Check if post_password cookie is secure.
		if ( ! is_ssl() ) {
			$protected_count = $wpdb->get_var(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_password != ''
				AND post_status = 'publish'"
			);

			if ( $protected_count > 0 ) {
				$issues[] = __( 'Site not using SSL - password-protected posts vulnerable', 'wpshadow' );
			}
		}

		// Check for posts with invalid post_status that bypass visibility.
		$custom_statuses = get_post_stati( array( '_builtin' => false ) );
		if ( ! empty( $custom_statuses ) ) {
			$posts_custom_status = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->posts}
					WHERE post_status IN ('" . implode( "','", array_map( 'esc_sql', $custom_statuses ) ) . "')
					AND post_password != ''"
				)
			);

			if ( $posts_custom_status > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts */
					__( '%d posts with custom status and passwords (visibility unclear)', 'wpshadow' ),
					$posts_custom_status
				);
			}
		}

		// Check for sticky private posts (should not be possible).
		$sticky_private = $wpdb->get_var(
			"SELECT COUNT(p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_status = 'private'
			AND pm.meta_key = '_sticky'
			AND pm.meta_value = '1'"
		);

		// Alternative check using options.
		$sticky_posts = get_option( 'sticky_posts', array() );
		if ( ! empty( $sticky_posts ) ) {
			$sticky_post_statuses = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT post_status, COUNT(*) as count
					FROM {$wpdb->posts}
					WHERE ID IN (" . implode( ',', array_map( 'absint', $sticky_posts ) ) . ")
					GROUP BY post_status"
				)
			);

			foreach ( $sticky_post_statuses as $status ) {
				if ( $status->post_status === 'private' ) {
					$issues[] = __( 'Private posts incorrectly marked as sticky', 'wpshadow' );
				}
			}
		}

		// Check for posts with both private status and password (redundant).
		$double_protected = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'private'
			AND post_password != ''"
		);

		if ( $double_protected > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts are both private and password-protected (redundant)', 'wpshadow' ),
				$double_protected
			);
		}

		// Check if caching might expose private content.
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			$issues[] = __( 'Page caching enabled - ensure private posts are excluded from cache', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-visibility-settings?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
