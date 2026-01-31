<?php
/**
 * PHP Version Compatibility Diagnostic
 *
 * Checks server PHP version against recommended minimums.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PHP Version Compatibility Diagnostic
 *
 * Validates PHP version meets WordPress and WPShadow requirements.
 *
 * @since 1.2601.2240
 */
class Diagnostic_PHP_Version_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-version-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks server PHP version against recommended minimums';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$current = PHP_VERSION;
		$minimum = '8.1.0';
		$recommended = '8.2.0';

		if ( version_compare( $current, $minimum, '>=' ) ) {
			if ( version_compare( $current, $recommended, '>=' ) ) {
				return null;
			}
		}

		$severity = version_compare( $current, $minimum, '>=' ) ? 'medium' : 'high';
		$threat = version_compare( $current, $minimum, '>=' ) ? 55 : 85;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'PHP version is below recommended compatibility levels', 'wpshadow' ),
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/php-version-compatibility',
			'details'      => array(
				'current_version'     => $current,
				'minimum_version'     => $minimum,
				'recommended_version' => $recommended,
			),
		);
	}
}
