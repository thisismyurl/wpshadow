<?php
/**
 * Media Usage Analytics Treatment
 *
 * Tests tracking which posts/pages use specific media.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Usage Analytics Treatment Class
 *
 * Verifies ability to track and report on which posts/pages use specific media.
 *
 * @since 1.6033.0000
 */
class Treatment_Media_Usage_Analytics extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-usage-analytics';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Usage Analytics';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests tracking which posts/pages use specific media';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if attachment relationship functions exist.
		if ( ! function_exists( 'wp_get_attachment_image' ) ) {
			$issues[] = __( 'Attachment image functions not available', 'wpshadow' );
		}

		// Test get_posts with attachment relationship.
		$attachments = get_posts( array(
			'post_type' => 'attachment',
			'number'    => 1,
		) );

		if ( empty( $attachments ) ) {
			// No attachments to test with.
			return null;
		}

		$attachment_id = $attachments[0]->ID;

		// Check if we can find posts using this attachment.
		$posts_with_image = new \WP_Query( array(
			'post_type'  => 'post',
			'meta_query' => array(
				array(
					'key'     => '_thumbnail_id',
					'value'   => $attachment_id,
					'compare' => '=',
				),
			),
		) );

		// This query should work but may not find the attachment if it's not featured.
		if ( is_wp_error( $posts_with_image ) ) {
			$issues[] = __( 'Error querying posts with attachment relationships', 'wpshadow' );
		}

		// Check if attachment is in use in post content.
		$posts_with_attachment = new \WP_Query( array(
			'post_type'   => array( 'post', 'page' ),
			's'           => wp_get_attachment_url( $attachment_id ),
			'post_status' => 'any',
		) );

		if ( is_wp_error( $posts_with_attachment ) ) {
			$issues[] = __( 'Error searching for attachment usage in post content', 'wpshadow' );
		}

		// Check for media analytics plugins.
		$analytics_plugins = array(
			'media-manager/media-manager.php',
			'attachment-pages/attachment-pages.php',
		);

		$has_analytics = false;
		foreach ( $analytics_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_analytics = true;
				break;
			}
		}

		if ( ! $has_analytics ) {
			$issues[] = __( 'No media analytics plugin detected', 'wpshadow' );
		}

		// Check for attachment table indexing.
		global $wpdb;
		$indexes = $wpdb->get_results( "SHOW INDEXES FROM {$wpdb->posts} WHERE Key_name != 'PRIMARY'" );
		if ( empty( $indexes ) ) {
			$issues[] = __( 'No indexes found on posts table for efficient attachment queries', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-usage-analytics',
			);
		}

		return null;
	}
}
