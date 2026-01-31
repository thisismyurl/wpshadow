<?php
/**
 * Multisite Disk Space Usage Diagnostic
 *
 * Monitors disk space consumption across multisite network, detecting
 * sites consuming excessive storage and preventing disk full scenarios.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Multisite_Disk_Usage Class
 *
 * Monitors disk space usage on multisite networks.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Multisite_Disk_Usage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-disk-usage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Disk Space Usage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors disk space across multisite network';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if disk issues found, null otherwise.
	 */
	public static function check() {
		// Only run on multisite
		if ( ! is_multisite() ) {
			return null;
		}

		$disk_check = self::analyze_disk_usage();

		if ( ! $disk_check['is_concerning'] ) {
			return null; // Disk usage acceptable
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %.1f: disk usage percentage */
				__( 'Disk usage %.1f%% of available space. Multisite networks grow 5x faster than single sites. Full disk = ALL sites down.', 'wpshadow' ),
				$disk_check['usage_percent']
			),
			'severity'     => $disk_check['usage_percent'] > 90 ? 'critical' : 'high',
			'threat_level' => (int) $disk_check['usage_percent'],
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-disk-space',
			'family'       => self::$family,
			'meta'         => array(
				'disk_usage_percent' => $disk_check['usage_percent'],
				'total_sites'        => $disk_check['total_sites'],
				'uploads_size'       => $disk_check['uploads_size'],
			),
			'details'      => array(
				'why_multisite_uses_more_space' => array(
					__( 'Each site: /wp-content/uploads/sites/X/ directory' ),
					__( '10 sites = 10x database tables (wp_X_posts, etc.)' ),
					__( 'Media uploads multiply quickly' ),
					__( 'Plugin/theme caches per site' ),
				),
				'monitoring_disk_usage'     => array(
					'Via cPanel' => array(
						'cPanel → Disk Usage',
						'Shows breakdown by directory',
						'Identify largest consumers',
					),
					'Via SSH' => array(
						'Command: df -h (overall disk)',
						'Command: du -sh /var/www/html/wp-content/uploads/sites/*',
						'Shows per-site upload sizes',
					),
					'Via Plugin' => array(
						'Disk Usage Sunburst (free)',
						'Visual breakdown of disk usage',
						'Network admin view',
					),
				),
				'reducing_disk_usage'       => array(
					'Delete Media Library Items' => array(
						'Media → Library',
						'Sort by size',
						'Delete unused large files',
						'Per site: Can save 50-500MB',
					),
					'Clean Database' => array(
						'Revisions: wp post delete $(wp post list --post_type=revision --format=ids)',
						'Transients: wp transient delete --all',
						'Spam comments: wp comment delete $(wp comment list --status=spam --format=ids)',
					),
					'Offload Media to S3' => array(
						'Plugin: WP Offload Media Lite',
						'Move uploads to Amazon S3',
						'Frees local disk space',
						'Cost: ~$0.023/GB/month',
					),
					'Enable CDN' => array(
						'Serve images from CDN',
						'Optionally delete local copies',
						'BunnyCDN: $1/month 500GB',
					),
				),
				'per_site_upload_limits'    => array(
					'Setting Limits' => array(
						'Network Admin → Settings',
						'Site Upload Space: 100 MB (default)',
						'Adjust based on site purpose',
					),
					'Monitoring Compliance' => array(
						'Network Admin → Sites',
						'Shows disk usage per site',
						'Identify quota violations',
					),
				),
				'preventing_disk_full'      => array(
					__( 'Set per-site upload limits (50-200MB typical)' ),
					__( 'Monthly cleanup: Delete revisions, spam' ),
					__( 'Offload media to S3 or CDN' ),
					__( 'Monitor disk usage weekly' ),
					__( 'Provision 2x expected growth room' ),
				),
			),
		);
	}

	/**
	 * Analyze disk usage.
	 *
	 * @since  1.2601.2148
	 * @return array Disk usage analysis.
	 */
	private static function analyze_disk_usage() {
		if ( ! is_multisite() ) {
			return array( 'is_concerning' => false );
		}

		$total_sites = get_blog_count();

		// Estimate uploads directory size (can't easily calculate without filesystem access)
		$upload_dir = wp_upload_dir();
		$uploads_path = $upload_dir['basedir'];

		// Check if we can get disk stats
		$disk_free = @disk_free_space( $uploads_path );
		$disk_total = @disk_total_space( $uploads_path );

		if ( $disk_free && $disk_total ) {
			$usage_percent = ( ( $disk_total - $disk_free ) / $disk_total ) * 100;
		} else {
			// Estimate based on site count (conservative)
			$usage_percent = min( 70, $total_sites * 2 );
		}

		return array(
			'usage_percent'  => round( $usage_percent, 1 ),
			'total_sites'    => $total_sites,
			'uploads_size'   => $disk_free ? size_format( $disk_total - $disk_free ) : __( 'Unknown', 'wpshadow' ),
			'is_concerning'  => $usage_percent > 80,
		);
	}
}
