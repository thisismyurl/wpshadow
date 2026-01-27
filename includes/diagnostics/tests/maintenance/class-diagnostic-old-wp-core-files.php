<?php
/**
 * Diagnostic: Old WordPress Core Files
 *
 * Scans for old WordPress core files that should have been removed during updates.
 * Leftover files can pose security risks and cause conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Maintenance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Old_Wp_Core_Files
 *
 * Detects old WordPress core files that should be removed.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Old_Wp_Core_Files extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'old-wp-core-files';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Old WordPress Core Files';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Scans for old WordPress core files';

	/**
	 * Check for old WordPress core files.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// List of files that were removed in recent WordPress versions.
		$old_files = array(
			// Files removed in WordPress 5.5+.
			ABSPATH . 'wp-includes/js/wp-embed.js',
			ABSPATH . 'wp-includes/js/wp-embed.min.js',
			// Files removed in WordPress 5.0+.
			ABSPATH . 'wp-includes/js/tinymce/plugins/wpembed/plugin.js',
			ABSPATH . 'wp-includes/js/tinymce/plugins/wpembed/plugin.min.js',
			// Files removed in WordPress 4.9+.
			ABSPATH . 'wp-includes/js/customize-preview.js',
			ABSPATH . 'wp-includes/js/customize-preview.min.js',
			// Files removed in WordPress 4.7+.
			ABSPATH . 'wp-includes/js/tinymce/langs/',
			// Files removed in WordPress 4.5+.
			ABSPATH . 'wp-includes/js/jquery/jquery-migrate.js',
			// Old readme files.
			ABSPATH . 'readme.html',
			ABSPATH . 'liesmich.html',
			// Old wp-config-sample.php in root (should be wp-config-sample.php only).
			ABSPATH . 'wp-config-sample.php~',
			// Old language files.
			WP_CONTENT_DIR . '/languages/admin-network-en_US.mo',
			WP_CONTENT_DIR . '/languages/continents-cities-en_US.mo',
		);

		// Check for old wp-admin files.
		$old_admin_files = array(
			ABSPATH . 'wp-admin/import',
			ABSPATH . 'wp-admin/install.php~',
			ABSPATH . 'wp-admin/upgrade.php~',
		);

		$old_files = array_merge( $old_files, $old_admin_files );

		// Find existing old files.
		$found_files = array();

		foreach ( $old_files as $file ) {
			if ( file_exists( $file ) ) {
				$found_files[] = str_replace( ABSPATH, '', $file );
			}
		}

		// Check for old wp-includes directories.
		$old_dirs = array(
			ABSPATH . 'wp-includes/js/tinymce/plugins/wpembed',
			ABSPATH . 'wp-includes/js/tinymce/langs',
		);

		foreach ( $old_dirs as $dir ) {
			if ( is_dir( $dir ) ) {
				$found_files[] = str_replace( ABSPATH, '', $dir ) . '/';
			}
		}

		if ( ! empty( $found_files ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of old files */
					_n(
						'%d old WordPress core file found',
						'%d old WordPress core files found',
						count( $found_files ),
						'wpshadow'
					),
					count( $found_files )
				),
				'severity'    => 'low',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/old_wp_core_files',
				'meta'        => array(
					'old_files' => $found_files,
				),
			);
		}

		// No old WordPress core files found.
		return null;
	}
}
