<?php
/**
 * Post Content Encoding Issues Diagnostic
 *
 * Checks for character encoding corruption in post content.
 *
 * @since   1.26033.0800
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Post_Content_Encoding_Issues Class
 *
 * Detects character encoding problems in post content.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Post_Content_Encoding_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-content-encoding-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Content Encoding Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for character encoding corruption in post content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check database charset
		$db_charset = $wpdb->get_charset_collate();

		if ( ! strpos( $db_charset, 'utf8' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: current database charset */
					__( 'Database charset is %s, but UTF-8 is recommended. This can cause encoding issues with special characters and international content.', 'wpshadow' ),
					esc_attr( $db_charset )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-content-encoding-issues',
			);
		}

		// Sample check for mojibake (encoding corruption artifacts)
		$corrupted_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_content REGEXP '\\xC3\\xA9|\\xC3\\xAC|\\xC3\\xB3|\\xC3\\xB6'
			AND post_type IN ('post', 'page')
			LIMIT 50"
		);

		if ( intval( $corrupted_count ) > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Detected encoding corruption patterns in post content (mojibake). This indicates past character encoding issues. Consider running a database encoding conversion tool.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-content-encoding-issues',
			);
		}

		return null; // Post content encoding is healthy
	}
}
