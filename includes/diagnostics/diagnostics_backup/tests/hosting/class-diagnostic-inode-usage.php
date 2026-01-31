<?php
/**
 * Inode Usage Diagnostic (Shared Hosting)
 *
 * Monitors file count on shared hosting where inode limits cause site failures.
 * Many shared hosts limit inodes (files) to 250k-500k, causing silent failures.
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
 * Diagnostic_Inode_Usage Class
 *
 * Counts files recursively to estimate inode usage and compare against typical hosting limits.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Inode_Usage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inode-usage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inode Usage (Shared Hosting)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors file count to prevent inode exhaustion on shared hosting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$usage_data = self::count_inodes();

		if ( ! $usage_data ) {
			return null;
		}

		$total_files        = $usage_data['total_files'];
		$cache_files        = $usage_data['cache_files'];
		$upload_files       = $usage_data['upload_files'];
		$estimated_limit    = $usage_data['estimated_limit'];
		$usage_percentage   = $usage_data['usage_percentage'];

		// Thresholds: <60% good, 60-80% warning, >80% critical.
		if ( $usage_percentage < 60 ) {
			return null; // Good inode usage.
		}

		$severity     = 'medium';
		$threat_level = 60;

		if ( $usage_percentage > 80 ) {
			$severity     = 'critical';
			$threat_level = 90;
		} elseif ( $usage_percentage > 70 ) {
			$severity     = 'high';
			$threat_level = 75;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: File count, 2: Usage percentage, 3: Estimated limit */
				__( 'Site uses %1$s files (%2$d%% of estimated %3$s inode limit). Shared hosting typically limits inodes, causing silent failures when exceeded.', 'wpshadow' ),
				number_format_i18n( $total_files ),
				$usage_percentage,
				number_format_i18n( $estimated_limit )
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/inode-usage',
			'details'     => self::get_details( $usage_data ),
		);
	}

	/**
	 * Count inodes (files) in WordPress installation.
	 *
	 * Counts files recursively, focusing on cache and uploads directories.
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Inode usage data.
	 *
	 *     @type int   $total_files       Total file count.
	 *     @type int   $cache_files       Files in cache directories.
	 *     @type int   $upload_files      Files in uploads directory.
	 *     @type int   $estimated_limit   Estimated hosting inode limit.
	 *     @type int   $usage_percentage  Percentage of limit used.
	 * }
	 */
	private static function count_inodes() {
		$total_files  = 0;
		$cache_files  = 0;
		$upload_files = 0;

		// Count WordPress root files.
		$total_files += self::count_files_in_directory( ABSPATH );

		// Count cache files separately (common culprit).
		$cache_dir = WP_CONTENT_DIR . '/cache';
		if ( is_dir( $cache_dir ) ) {
			$cache_files = self::count_files_in_directory( $cache_dir );
		}

		// Count uploads separately.
		$upload_dir = wp_upload_dir();
		if ( isset( $upload_dir['basedir'] ) && is_dir( $upload_dir['basedir'] ) ) {
			$upload_files = self::count_files_in_directory( $upload_dir['basedir'] );
		}

		// Estimate hosting limit based on file count.
		// Common limits: 250k (budget), 500k (standard), 1M (premium).
		$estimated_limit = 250000; // Conservative estimate.

		if ( $total_files > 100000 ) {
			$estimated_limit = 500000; // Likely standard hosting.
		}

		$usage_percentage = 0;
		if ( $estimated_limit > 0 ) {
			$usage_percentage = round( ( $total_files / $estimated_limit ) * 100 );
		}

		return array(
			'total_files'      => $total_files,
			'cache_files'      => $cache_files,
			'upload_files'     => $upload_files,
			'estimated_limit'  => $estimated_limit,
			'usage_percentage' => $usage_percentage,
		);
	}

	/**
	 * Count files in a directory recursively.
	 *
	 * Uses iterator for memory efficiency.
	 *
	 * @since  1.2601.2148
	 * @param  string $directory Directory path.
	 * @return int Number of files.
	 */
	private static function count_files_in_directory( $directory ) {
		$count = 0;

		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS ),
				\RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ( $iterator as $file ) {
				if ( $file->isFile() ) {
					++$count;
				}
			}
		} catch ( \Exception $e ) {
			// Directory not accessible, return 0.
			return 0;
		}

		return $count;
	}

	/**
	 * Get detailed information about the finding.
	 *
	 * @since  1.2601.2148
	 * @param  array $usage_data Inode usage data.
	 * @return array Details array with explanation and solutions.
	 */
	private static function get_details( $usage_data ) {
		$total_files      = $usage_data['total_files'];
		$cache_files      = $usage_data['cache_files'];
		$upload_files     = $usage_data['upload_files'];
		$estimated_limit  = $usage_data['estimated_limit'];
		$usage_percentage = $usage_data['usage_percentage'];

		$explanation = sprintf(
			/* translators: 1: Total files, 2: Cache files, 3: Upload files, 4: Usage percent */
			__( 'Your site contains %1$s files (%2$d%% of estimated hosting limit). Cache directories contain %3$s files, uploads contain %4$s files. Shared hosting typically limits inodes (file count), and exceeding this limit causes silent failures: upload errors, plugin installation failures, and site crashes without clear error messages.', 'wpshadow' ),
			number_format_i18n( $total_files ),
			$usage_percentage,
			number_format_i18n( $cache_files ),
			number_format_i18n( $upload_files )
		);

		$solutions = array(
			'free' => array(
				__( 'Clear cache directories: Delete files in wp-content/cache/', 'wpshadow' ),
				__( 'Remove old plugin files: Delete unused plugin directories', 'wpshadow' ),
				__( 'Clean uploads: Remove duplicate or unused media files', 'wpshadow' ),
				__( 'Disable file-heavy plugins: Caching plugins that create thousands of files', 'wpshadow' ),
			),
			'premium' => array(
				__( 'Use object caching: Redis/Memcached instead of file-based caching', 'wpshadow' ),
				__( 'Offload media to CDN: Move images to external storage (S3, Cloudflare)', 'wpshadow' ),
				__( 'Upgrade hosting plan: Higher inode limits on premium plans', 'wpshadow' ),
			),
			'advanced' => array(
				__( 'Move to VPS/dedicated: No inode limits on managed hosting', 'wpshadow' ),
				__( 'Implement aggressive cleanup: Scheduled jobs to remove old cache files', 'wpshadow' ),
				__( 'Use external services: Offload backups, logs, and static files', 'wpshadow' ),
			),
		);

		$additional_info = sprintf(
			/* translators: 1: Files per category percentage */
			__( 'Cache files: %1$d%% of total. Uploads: %2$d%% of total. Common hosting limits: Budget (250k), Standard (500k), Premium (1M). Contact your host to confirm your actual limit.', 'wpshadow' ),
			$total_files > 0 ? round( ( $cache_files / $total_files ) * 100 ) : 0,
			$total_files > 0 ? round( ( $upload_files / $total_files ) * 100 ) : 0
		);

		return array(
			'explanation'     => $explanation,
			'solutions'       => $solutions,
			'additional_info' => $additional_info,
			'technical_data'  => array(
				'total_files'      => number_format_i18n( $total_files ),
				'cache_files'      => number_format_i18n( $cache_files ),
				'upload_files'     => number_format_i18n( $upload_files ),
				'other_files'      => number_format_i18n( $total_files - $cache_files - $upload_files ),
				'estimated_limit'  => number_format_i18n( $estimated_limit ),
				'usage_percentage' => $usage_percentage . '%',
				'files_remaining'  => number_format_i18n( $estimated_limit - $total_files ),
			),
			'resources'       => array(
				array(
					'label' => __( 'Understanding Inodes', 'wpshadow' ),
					'url'   => 'https://www.inmotionhosting.com/support/server/linux/what-is-an-inode/',
				),
				array(
					'label' => __( 'Shared Hosting Limits', 'wpshadow' ),
					'url'   => 'https://www.siteground.com/kb/what-is-inode/',
				),
			),
		);
	}
}
