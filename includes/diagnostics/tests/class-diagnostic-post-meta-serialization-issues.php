<?php
/**
 * Post Meta Serialization Issues Diagnostic
 *
 * Checks for improperly serialized post meta data.
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
 * Diagnostic_Post_Meta_Serialization_Issues Class
 *
 * Detects serialization errors in post meta data.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Post_Meta_Serialization_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-meta-serialization-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Meta Serialization Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for improperly serialized post meta data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'meta';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for corrupted serialized data by sampling
		$sample_meta = $wpdb->get_results(
			"SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE 'a:%' OR meta_value LIKE 'o:%' LIMIT 100"
		);

		$corrupted_count = 0;
		foreach ( $sample_meta as $item ) {
			// Attempt to unserialize - if it fails, it's likely corrupted
			if ( is_serialized( $item->meta_value ) ) {
				$unserialized = @unserialize( $item->meta_value ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				if ( false === $unserialized && 'b:0;' !== $item->meta_value ) {
					$corrupted_count++;
				}
			}
		}

		if ( $corrupted_count > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Detected corrupted serialized data in post meta. This can cause plugin conflicts and data loss. Consider using a database repair tool.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-meta-serialization-issues',
			);
		}

		return null; // Post meta serialization is healthy
	}
}
