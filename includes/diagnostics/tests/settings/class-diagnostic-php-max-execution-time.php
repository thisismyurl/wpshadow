<?php
/**
 * PHP Max Execution Time Diagnostic
 *
 * Checks PHP max execution time setting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1506
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PHP Max Execution Time Diagnostic Class
 *
 * Verifies PHP max execution time is adequate.
 *
 * @since 1.6035.1506
 */
class Diagnostic_PHP_Max_Execution_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-max-execution-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Max Execution Time';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks PHP max execution time setting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting-environment';

	/**
	 * Minimum execution time
	 *
	 * @var int
	 */
	private const MIN_EXECUTION_TIME = 30;

	/**
	 * Recommended execution time
	 *
	 * @var int
	 */
	private const RECOMMENDED_EXECUTION_TIME = 60;

	/**
	 * Run the execution time diagnostic check.
	 *
	 * @since  1.6035.1506
	 * @return array|null Finding array if time issue detected, null otherwise.
	 */
	public static function check() {
		$max_execution_time = (int) ini_get( 'max_execution_time' );

		if ( 0 === $max_execution_time ) {
			// Unlimited
			return null;
		}

		if ( $max_execution_time < self::MIN_EXECUTION_TIME ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: current time, 2: minimum required */
					__( 'Max execution time is %1$d seconds, below minimum %2$d seconds. Long operations may timeout.', 'wpshadow' ),
					$max_execution_time,
					self::MIN_EXECUTION_TIME
				),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/increase-php-execution-time',
				'meta'        => array(
					'max_execution_time' => $max_execution_time,
					'minimum_required'   => self::MIN_EXECUTION_TIME,
				),
			);
		}

		if ( $max_execution_time < self::RECOMMENDED_EXECUTION_TIME ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: current time, 2: recommended time */
					__( 'Max execution time is %1$d seconds. Recommended minimum is %2$d seconds for admin operations.', 'wpshadow' ),
					$max_execution_time,
					self::RECOMMENDED_EXECUTION_TIME
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/increase-php-execution-time',
				'meta'        => array(
					'max_execution_time' => $max_execution_time,
					'recommended'        => self::RECOMMENDED_EXECUTION_TIME,
				),
			);
		}

		return null;
	}
}
