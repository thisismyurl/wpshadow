<?php
/**
 * Max Execution Time Diagnostic
 *
 * Issue #4940: PHP Execution Time Too Short
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if max_execution_time is adequate.
 * Short execution times cause timeouts on imports and backups.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Max_Execution_Time Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Max_Execution_Time extends Diagnostic_Base {

	protected static $slug = 'max-execution-time';
	protected static $title = 'PHP Execution Time Too Short';
	protected static $description = 'Checks if max_execution_time allows long operations';
	protected static $family = 'reliability';

	public static function check() {
		$max_execution_time = ini_get( 'max_execution_time' );
		$recommended = 300; // 5 minutes

		if ( $max_execution_time > 0 && $max_execution_time < $recommended ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: current execution time, 2: recommended time */
					__( 'Current execution time is %1$d seconds. Long operations need at least %2$d seconds (5 minutes).', 'wpshadow' ),
					$max_execution_time,
					$recommended
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/execution-time',
				'details'      => array(
					'current_time'            => $max_execution_time . ' seconds',
					'recommended_time'        => $recommended . ' seconds',
					'affected_operations'     => 'Backups, imports, batch processing, large uploads',
					'increase_method'         => 'set_time_limit(300); or php.ini: max_execution_time = 300',
				),
			);
		}

		return null;
	}
}
