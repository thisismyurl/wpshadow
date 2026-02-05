<?php
/**
 * Per-Site Media Storage Limits Treatment
 *
 * Tests media quota enforcement for individual sites.
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
 * Per-Site Media Storage Limits Treatment Class
 *
 * Verifies media quota enforcement for individual network sites,
 * including storage limit warnings and enforcement.
 *
 * @since 1.6033.0000
 */
class Treatment_Per_Site_Media_Storage_Limits extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'per-site-media-storage-limits';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Per-Site Media Storage Limits';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media quota enforcement for individual sites';

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
		// Only relevant for multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check if upload space checking is enabled.
		$upload_space_check_disabled = get_site_option( 'upload_space_check_disabled' );

		if ( ! empty( $upload_space_check_disabled ) ) {
			$issues[] = __( 'Upload space checking is disabled network-wide', 'wpshadow' );
		} else {
			// Space check is enabled - verify configuration.
			$blog_upload_space = get_site_option( 'blog_upload_space' );

			if ( empty( $blog_upload_space ) ) {
				$issues[] = __( 'Network upload space limit is not configured', 'wpshadow' );
			} elseif ( $blog_upload_space > 10000 ) {
				// Very high limit (>10GB).
				$issues[] = sprintf(
					/* translators: %d: upload space limit in MB */
					__( 'Network upload space limit is very high (%d MB), which may allow excessive storage use', 'wpshadow' ),
					$blog_upload_space
				);
			}

			// Check current site's usage.
			$space_used = get_space_used();
			if ( $space_used >= $blog_upload_space ) {
				$issues[] = sprintf(
					/* translators: 1: space used, 2: space allowed */
					__( 'Site has exceeded upload quota (%1$d MB used of %2$d MB allowed)', 'wpshadow' ),
					round( $space_used ),
					$blog_upload_space
				);
			} elseif ( $space_used >= ( $blog_upload_space * 0.9 ) ) {
				// Within 10% of limit.
				$issues[] = sprintf(
					/* translators: 1: space used, 2: space allowed */
					__( 'Site is approaching upload quota (%1$d MB used of %2$d MB allowed)', 'wpshadow' ),
					round( $space_used ),
					$blog_upload_space
				);
			}
		}

		// Check if upload quota warning is shown to users.
		$has_quota_filter = has_filter( 'pre_upload_error' );
		if ( ! $has_quota_filter ) {
			$issues[] = __( 'No upload quota warning filter detected', 'wpshadow' );
		}

		// Check if quota display function exists.
		if ( ! function_exists( 'get_space_allowed' ) ) {
			$issues[] = __( 'Space quota functions are not available', 'wpshadow' );
		}

		// Check for per-site quota overrides.
		$site_specific_quota = get_option( 'blog_upload_space' );
		if ( ! empty( $site_specific_quota ) && $site_specific_quota !== $blog_upload_space ) {
			// Site has custom quota - verify it's reasonable.
			if ( $site_specific_quota > $blog_upload_space ) {
				// Site quota exceeds network default.
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/per-site-media-storage-limits',
			);
		}

		return null;
	}
}
