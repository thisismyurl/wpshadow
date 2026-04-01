<?php
/**
 * Change Log Available Diagnostic
 *
 * Checks whether change logging is enabled for administrative actions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Change_Log_Available Class
 *
 * Detects whether change logging is enabled.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Change_Log_Available extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'change-log-available';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Change Log Available';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether change logging is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_logger = class_exists( '\\WPShadow\\Core\\Activity_Logger' )
			|| class_exists( 'SimpleHistory' );

		if ( ! $has_logger ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No change log system detected. Enable logging to track administrative actions.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/change-log-available?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}