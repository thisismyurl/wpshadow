<?php
/**
 * Media Private Media Access Control Diagnostic
 *
 * Tests access control for private/restricted media files.
 * Validates that permission checks are properly enforced.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.26033.2103
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Private_Media_Access_Control Class
 *
 * Checks if private and restricted media files have proper access controls.
 * Tests for:
 * - Private attachment permission checks
 * - Protected media access restrictions
 * - User role-based access control
 * - Media metadata privacy handling
 *
 * @since 1.26033.2103
 */
class Diagnostic_Media_Private_Media_Access_Control extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-private-media-access-control';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Private Media Access Control';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests access control for private/restricted media files';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2103
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if WordPress supports private posts/attachments
		$supports_private = post_type_supports( 'attachment', 'private' );
		if ( ! $supports_private ) {
			$issues[] = __( 'Attachment post type does not support private status', 'wpshadow' );
		}

		// Check for attachment access validation
		$access_issue = self::check_attachment_access_validation();
		if ( ! empty( $access_issue ) ) {
			$issues[] = $access_issue;
		}

		// Check for permission checks on attachment page
		$permission_issue = self::check_attachment_permission_filters();
		if ( ! empty( $permission_issue ) ) {
			$issues[] = $permission_issue;
		}

		// Check if private attachments are properly restricted
		$private_issue = self::check_private_attachment_handling();
		if ( ! empty( $private_issue ) ) {
			$issues[] = $private_issue;
		}

		// Check for media privacy plugins/filters
		$privacy_issue = self::check_media_privacy_enforcement();
		if ( ! empty( $privacy_issue ) ) {
			$issues[] = $privacy_issue;
		}

		// Check media library access control
		$library_issue = self::check_media_library_access_control();
		if ( ! empty( $library_issue ) ) {
			$issues[] = $library_issue;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( '. ', $issues ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-private-media-access-control',
			);
		}

		return null;
	}

	/**
	 * Check attachment access validation
	 *
	 * @return string|null Issue description if validation inadequate.
	 */
	private static function check_attachment_access_validation() {
		// Check if there's a filter for attachment access
		$has_attachment_filter = has_filter( 'attachment_template' );
		
		if ( ! $has_attachment_filter ) {
			// WordPress should have default attachment handling, check if function exists
			if ( ! function_exists( 'wp_attachment_is' ) ) {
				return __( 'WordPress attachment validation functions not available', 'wpshadow' );
			}
		}

		// Check if attachment access is properly gated
		if ( ! has_filter( 'attachment_template' ) && ! has_filter( 'template_redirect' ) ) {
			return __( 'No custom attachment access control filters detected - private attachments may not be properly restricted', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check for permission checking filters
	 *
	 * @return string|null Issue description if permission checks inadequate.
	 */
	private static function check_attachment_permission_filters() {
		// Check if there are permission verification hooks
		$permission_filters = array(
			'pre_get_posts',
			'posts_request',
			'posts_where',
		);

		$has_permission_check = false;
		foreach ( $permission_filters as $filter ) {
			if ( has_filter( $filter ) ) {
				$has_permission_check = true;
				break;
			}
		}

		if ( ! $has_permission_check ) {
			return __( 'No permission verification filters detected - attachment access may not be properly restricted', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check private attachment handling
	 *
	 * @return string|null Issue description if handling inadequate.
	 */
	private static function check_private_attachment_handling() {
		// Get a sample attachment to test
		$args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'private',
			'posts_per_page' => 1,
		);
		
		$private_attachments = get_posts( $args );

		if ( ! empty( $private_attachments ) ) {
			$attachment = $private_attachments[0];
			
			// Verify the attachment is properly marked as private
			if ( 'private' !== $attachment->post_status ) {
				return __( 'Private attachment status not properly enforced', 'wpshadow' );
			}

			// Check if post password protection is available as alternative
			if ( ! post_type_supports( 'attachment', 'post-passwords' ) ) {
				return __( 'Post password protection not available for attachments as fallback access control', 'wpshadow' );
			}
		}

		return null;
	}

	/**
	 * Check media privacy enforcement
	 *
	 * @return string|null Issue description if privacy enforcement inadequate.
	 */
	private static function check_media_privacy_enforcement() {
		// Check if all necessary access control hooks are in place
		$media_privacy_hooks = array(
			'wp_insert_attachment_data',  // Before attachment is inserted
			'edit_attachment',            // Before editing
			'delete_attachment',          // Before deleting
		);

		// At least some hooks should be present
		$has_hooks = false;
		foreach ( $media_privacy_hooks as $hook ) {
			if ( has_action( $hook ) || has_filter( $hook ) ) {
				$has_hooks = true;
				break;
			}
		}

		if ( ! $has_hooks ) {
			return __( 'No media privacy enforcement hooks detected - access control may not be enforced', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check media library access control
	 *
	 * @return string|null Issue description if library access inadequate.
	 */
	private static function check_media_library_access_control() {
		// Check if capability checks are performed
		$current_user = wp_get_current_user();
		
		// Check if edit_post capability is properly checked
		if ( ! function_exists( 'current_user_can' ) ) {
			return __( 'User capability checking function not available', 'wpshadow' );
		}

		// Verify the capability function works
		$test_cap = current_user_can( 'upload_files' );
		if ( ! is_bool( $test_cap ) ) {
			return __( 'User capability checking not working properly', 'wpshadow' );
		}

		// Check if there's a way to restrict authors to their own media
		if ( ! has_filter( 'pre_get_posts' ) ) {
			return __( 'No query filter for media library access control - authors may see other authors media', 'wpshadow' );
		}

		return null;
	}
}
