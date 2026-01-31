<?php
/**
 * Autoload Options Size Diagnostic
 *
 * Checks the total size of autoloaded options.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1354
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload Options Size Diagnostic Class
 *
 * Flags large autoloaded option payloads that slow page loads.
 *
 * @since 1.5049.1354
 */
class Diagnostic_Autoload_Options_Size extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'autoload-options-size';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Autoload Options Size';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks the size of autoloaded options in the database';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1354
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$size_bytes = (int) $wpdb->get_var( "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'" );

		if ( $size_bytes <= 0 ) {
			return null;
		}

		$size_mb = round( $size_bytes / 1024 / 1024, 2 );

		if ( $size_mb >= 1.0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Autoloaded options are large and may slow down every page load.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'autoload_size_mb' => $size_mb,
				),
				'kb_link'      => 'https://wpshadow.com/kb/autoload-options-size',
			);
		}

		return null;
	}
}
