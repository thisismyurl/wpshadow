<?php
/**
 * Incomplete Personal Data Collection Diagnostic
 *
 * Tests whether Personal Data Export includes all user data from WordPress core tables.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.2034.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Incomplete_Personal_Data_Collection Class
 *
 * Verifies that personal data exports include all required user information.
 *
 * @since 1.2034.1430
 */
class Diagnostic_Incomplete_Personal_Data_Collection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'incomplete-personal-data-collection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Personal Data Export Completeness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that GDPR data exports include all required user information';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check if core export functionality exists.
		if ( ! function_exists( 'wp_privacy_generate_personal_data_export_file' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress personal data export functionality is not available', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-personal-data-export',
			);
		}

		// 2. Check if data exporters are registered.
		$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );
		
		if ( empty( $exporters ) ) {
			$issues[] = __( 'No personal data exporters are registered', 'wpshadow' );
		} else {
			// 3. Verify core exporters exist.
			$core_exporters = array(
				'wordpress-user',
				'wordpress-comments',
			);

			$missing_core = array();
			foreach ( $core_exporters as $exporter_id ) {
				$found = false;
				foreach ( $exporters as $exporter ) {
					if ( isset( $exporter['exporter_friendly_name'] ) && 
					     false !== strpos( strtolower( $exporter['exporter_friendly_name'] ), str_replace( 'wordpress-', '', $exporter_id ) ) ) {
						$found = true;
						break;
					}
				}
				if ( ! $found ) {
					$missing_core[] = $exporter_id;
				}
			}

			if ( ! empty( $missing_core ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated list of missing exporters */
					__( 'Missing core data exporters: %s', 'wpshadow' ),
					implode( ', ', $missing_core )
				);
			}
		}

		// 4. Check if user meta is being exported.
		$test_user_id = get_current_user_id();
		if ( $test_user_id ) {
			$user_meta = get_user_meta( $test_user_id );
			if ( ! empty( $user_meta ) ) {
				// Check if any exporter handles user meta.
				$has_meta_exporter = false;
				foreach ( $exporters as $exporter ) {
					if ( isset( $exporter['callback'] ) && is_callable( $exporter['callback'] ) ) {
						// Core WordPress user exporter handles meta.
						if ( isset( $exporter['exporter_friendly_name'] ) && 
						     false !== strpos( strtolower( $exporter['exporter_friendly_name'] ), 'user' ) ) {
							$has_meta_exporter = true;
							break;
						}
					}
				}

				if ( ! $has_meta_exporter ) {
					$issues[] = __( 'User meta data may not be included in exports', 'wpshadow' );
				}
			}
		}

		// 5. Check for empty exporter callbacks.
		foreach ( $exporters as $exporter_id => $exporter ) {
			if ( ! isset( $exporter['callback'] ) || ! is_callable( $exporter['callback'] ) ) {
				$issues[] = sprintf(
					/* translators: %s: exporter ID */
					__( 'Exporter "%s" has invalid or missing callback', 'wpshadow' ),
					esc_html( $exporter_id )
				);
			}
		}

		// 6. Check if export directory is writable.
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'wp-personal-data-exports';
		
		if ( ! file_exists( $export_dir ) ) {
			if ( ! wp_mkdir_p( $export_dir ) ) {
				$issues[] = __( 'Unable to create personal data export directory', 'wpshadow' );
			}
		} elseif ( ! wp_is_writable( $export_dir ) ) {
			$issues[] = __( 'Personal data export directory is not writable', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Personal data export may be incomplete: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 95,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/gdpr-personal-data-export',
			'details'      => array(
				'issues'           => $issues,
				'registered_count' => count( $exporters ),
				'export_directory' => $export_dir,
			),
		);
	}
}
