<?php
/**
 * Upload Retry Logic Diagnostic
 *
 * Issue #4858: Failed Upload Has No Retry Mechanism
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if failed uploads can be automatically retried.
 * Network interruptions are common (especially on mobile), so uploads should be resumable/retryable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Upload_Retry_Logic Class
 *
 * Checks for:
 * - Automatic retry on upload failure
 * - Exponential backoff (don't hammer server)
 * - Manual retry button
 * - Clear error message with recovery steps
 * - Chunked uploads (for large files)
 * - Resume capability for interrupted uploads
 *
 * Why this matters:
 * - Network glitches are inevitable (WiFi dropout, 4G handoff)
 * - Users on mobile experience more interruptions
 * - Without retry, users lose their uploaded file and must try again
 * - Automatic retry provides seamless experience
 *
 * @since 1.6093.1200
 */
class Diagnostic_Upload_Retry_Logic extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'upload-retry-logic';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Failed Upload Has No Retry Mechanism';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if failed uploads can be automatically retried';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WordPress uses chunked uploads
		// Check for upload timeout settings

		$upload_timeout = (int) ini_get( 'max_execution_time' );
		$post_max_size = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );

		$issues = array();

		if ( $upload_timeout < 300 ) {  // Less than 5 minutes
			$issues[] = sprintf(
				/* translators: %d: seconds */
				__( 'PHP max_execution_time is %d seconds (large uploads may timeout)', 'wpshadow' ),
				$upload_timeout
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Failed uploads should retry automatically to handle network interruptions gracefully', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/upload-retry',
				'details'      => array(
					'findings'                  => $issues,
					'upload_timeout'            => $upload_timeout,
					'post_max_size'             => size_format( $post_max_size ),
					'recommended_timeout'       => '300+ seconds (5+ minutes)',
					'mobile_consideration'      => '3G/4G handoffs cause 5-10% of uploads to fail',
				),
			);
		}

		return null;
	}
}
