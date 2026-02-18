<?php
/**
 * Log File Age Diagnostic
 *
 * Checks debug log age and size to prevent excessive log growth.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1505
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Log_File_Age Class
 *
 * Evaluates debug.log age and size.
 *
 * @since 1.6035.1505
 */
class Diagnostic_Log_File_Age extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'log-file-age';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Log File Age';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether log files are growing too large or too old';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1505
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$log_path = WP_CONTENT_DIR . '/debug.log';
		if ( ! file_exists( $log_path ) ) {
			return null;
		}

		$size = (int) filesize( $log_path );
		$mtime = (int) filemtime( $log_path );
		$age_days = $mtime ? (int) floor( ( time() - $mtime ) / DAY_IN_SECONDS ) : 0;
		$size_mb = round( $size / 1024 / 1024, 2 );

		if ( $size_mb >= 50 || $age_days >= 90 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'debug.log is large or very old. Consider rotating or clearing logs.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/log-file-age',
				'meta'         => array(
					'size_mb'  => $size_mb,
					'age_days' => $age_days,
				),
			);
		}

		return null;
	}
}