<?php
/**
 * Comment Engagement and Community Health
 *
 * Validates comment section engagement metrics and community activity.
 *
 * @since   1.6030.2148
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Comment_Engagement Class
 *
 * Checks comment section engagement and community health indicators.
 *
 * @since 1.6030.2148
 */
class Treatment_Comment_Engagement extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-engagement';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Engagement and Community Health';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes comment engagement metrics and community activity patterns';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Engagement' );
	}

	/**
	 * Check for featured comments system.
	 *
	 * @since  1.6030.2148
	 * @return bool True if system exists.
	 */
	private static function has_featured_comments_system() {
		// Check for comment rating plugins
		$plugins = array(
			'wp-comment-form-customizer',
			'ultimate-comment-system',
		);

		foreach ( $plugins as $plugin ) {
			if ( is_plugin_active( $plugin . '/' . $plugin . '.php' ) ) {
				return true;
			}
		}

		// Check for featured comments option
		if ( get_option( 'featured_comments_enabled', false ) ) {
			return true;
		}

		return false;
	}
}
