<?php
/**
 * Comment Attachment Issues Diagnostic
 *
 * Detects problems with comment attachments if enabled by plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Attachment Diagnostic Class
 *
 * @since 1.26031.1300
 */
class Diagnostic_Comment_Attachment_Issues extends Diagnostic_Base {

	protected static $slug = 'comment-attachment-issues';
	protected static $title = 'Comment Attachment Issues';
	protected static $description = 'Detects problems with comment attachments if enabled';
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26031.1300
	 * @return array|null
	 */
	public static function check() {
		// Check if comment attachments are enabled by any plugin.
		$attachment_plugins = array(
			'WP Comment Attachment' => defined( 'WP_COMMENT_ATTACHMENT_VERSION' ),
			'Comment Images'        => class_exists( 'Comment_Images' ),
		);

		$active_plugins = array_filter( $attachment_plugins );

		if ( empty( $active_plugins ) ) {
			return null; // No attachment plugins active.
		}

		$issues = array();

		// Check upload directory permissions.
		$upload_dir = wp_upload_dir();
		if ( ! is_writable( $upload_dir['basedir'] ) ) {
			$issues[] = array(
				'issue'       => 'upload_not_writable',
				'description' => __( 'Upload directory is not writable for comment attachments', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		// Check for file type restrictions.
		$allowed_types = get_option( 'comment_attachment_allowed_types', array() );
		if ( empty( $allowed_types ) ) {
			$issues[] = array(
				'issue'       => 'no_file_type_restriction',
				'description' => __( 'No file type restrictions configured for comment attachments', 'wpshadow' ),
				'severity'    => 'critical',
			);
		}

		// Check for file size limits.
		$max_size = get_option( 'comment_attachment_max_size', 0 );
		if ( 0 === $max_size || $max_size > 5242880 ) { // 5MB.
			$issues[] = array(
				'issue'       => 'no_size_limit',
				'max_size'    => $max_size,
				'description' => __( 'Comment attachment size limit not set or too high (>5MB)', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d comment attachment security issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 45,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/comment-attachment-issues',
		);
	}
}
