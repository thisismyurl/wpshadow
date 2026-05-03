<?php
/**
 * Autoloaded Options Diagnostic
 *
 * Checks whether the total size of autoloaded WordPress options exceeds a
 * healthy threshold, as excessive autoloads slow every page request.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Diagnostics;

use ThisIsMyURL\Shadow\Core\Diagnostic_Base;
use ThisIsMyURL\Shadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Autoloaded_Options Class
 *
 * @since 0.6095
 */
class Diagnostic_Autoloaded_Options extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'autoloaded-options';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Autoloaded Options';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the total autoloaded options payload exceeds 800 KB, as WordPress loads all autoloaded options on every page request before any content renders.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Uses the Server_Env helper to measure the total autoloaded options size
	 * in KB. Flags at medium severity above 800 KB and high above 2,000 KB.
	 *
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$kb = Server_Env::get_autoloaded_options_size_kb();

		// Under 800 KB is considered healthy.
		if ( $kb < 800 ) {
			return null;
		}

		$severity     = $kb > 2000 ? 'high' : 'medium';
		$threat_level = $kb > 2000 ? 65 : 40;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: autoloaded size in KB */
				__( 'Your site\'s autoloaded options currently use %s KB of memory on every single page load. Autoloaded options are fetched from the database and loaded into memory before any page renders. A large autoload set slows down every request.', 'thisismyurl-shadow' ),
				number_format( $kb, 1 )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'details'      => array(
				'autoloaded_kb'   => $kb,
				'threshold_kb'    => 800,
				'high_concern_kb' => 2000,
			),
		);
	}
}
