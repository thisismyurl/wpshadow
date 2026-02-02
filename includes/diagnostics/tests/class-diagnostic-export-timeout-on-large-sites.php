<?php
/**
 * Export Timeout on Large Sites Diagnostic
 *
 * Tests whether export timeout may occur on large sites.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export Timeout on Large Sites Diagnostic Class
 *
 * Tests whether PHP timeout may interrupt large site exports.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Export_Timeout_On_Large_Sites extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-timeout-on-large-sites';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export Timeout on Large Sites';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether export timeout may occur on large sites';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check PHP execution time.
		$max_exec = ini_get( 'max_execution_time' );
		$max_exec_int = (int) $max_exec;

		if ( $max_exec_int > 0 && $max_exec_int < 300 ) {
			$issues[] = sprintf(
				/* translators: %d: execution time in seconds */
				__( 'PHP max_execution_time is low (%d seconds) - may timeout on large exports', 'wpshadow' ),
				$max_exec_int
			);
		}

		// Count posts to estimate export size.
		$post_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );

		if ( $post_count > 50000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( 'Large post count (%d) - export will take considerable time', 'wpshadow' ),
				$post_count
			);
		}

		// Count attachments and estimate file size.
		$attachment_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'" );

		if ( $attachment_count > 10000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				__( 'Large attachment count (%d) - export file will be very large', 'wpshadow' ),
				$attachment_count
			);
		}

		// Check for set_time_limit support.
		if ( ! function_exists( 'set_time_limit' ) ) {
			$issues[] = __( 'set_time_limit() disabled - cannot extend timeout during export', 'wpshadow' );
		}

		// Estimate export time.
		if ( $post_count > 1000 ) {
			// Rough estimate: 0.1 seconds per post.
			$estimated_time = $post_count * 0.1;
			if ( $estimated_time > $max_exec_int ) {
				$issues[] = sprintf(
					/* translators: %s: estimated time, %d: timeout */
					__( 'Estimated export time %.0f seconds exceeds timeout limit %d seconds', 'wpshadow' ),
					$estimated_time,
					$max_exec_int
				);
			}
		}

		// Check for chunked export support.
		if ( ! has_filter( 'wxr_export_query_args' ) ) {
			$issues[] = __( 'No chunked export support detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/export-timeout-on-large-sites',
			);
		}

		return null;
	}
}
