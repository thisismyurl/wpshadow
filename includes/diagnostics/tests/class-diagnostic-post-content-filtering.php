<?php
/**
 * Post Content Filtering Diagnostic
 *
 * Checks if content filtering and sanitization is working properly.
 *
 * @since   1.26033.0901
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Post_Content_Filtering Class
 *
 * Validates post content filtering and sanitization.
 *
 * @since 1.26033.0901
 */
class Diagnostic_Post_Content_Filtering extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-content-filtering';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Content Filtering';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies post content filtering and sanitization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0901
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for potentially dangerous scripts in published content
		$dangerous_patterns = array(
			'<script' => 'JavaScript tags',
			'onclick=' => 'Inline event handlers',
			'onerror=' => 'Error event handlers',
		);

		$dangerous_count = 0;
		foreach ( $dangerous_patterns as $pattern => $name ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts}
					WHERE post_content LIKE %s
					AND post_status = 'publish'",
					'%' . $pattern . '%'
				)
			);
			$dangerous_count += intval( $count );
		}

		if ( $dangerous_count > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of posts with potentially dangerous content */
					__( 'Found %d published posts containing potentially dangerous script tags or event handlers. This could be a security risk or indicate compromised content.', 'wpshadow' ),
					$dangerous_count
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-content-filtering',
			);
		}

		return null; // Post content filtering is healthy
	}
}
