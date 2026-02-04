<?php
/**
 * WordPress Version Compatibility Diagnostic
 *
 * Checks WordPress core version against recommended minimums.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Version Compatibility Diagnostic
 *
 * Validates WordPress core version is up to date.
 *
 * @since 1.6030.2240
 */
class Diagnostic_WordPress_Version_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-version-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Version Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks WordPress core version against recommended minimums';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_version;

		$minimum = '6.4.0';
		$recommended = '6.5.0';

		if ( version_compare( $wp_version, $minimum, '>=' ) && version_compare( $wp_version, $recommended, '>=' ) ) {
			return null;
		}

		$core_updates = get_core_updates();
		$update_available = ! empty( $core_updates ) && isset( $core_updates[0]->current );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress core version is below recommended levels', 'wpshadow' ),
			'severity'     => version_compare( $wp_version, $minimum, '>=' ) ? 'medium' : 'high',
			'threat_level' => version_compare( $wp_version, $minimum, '>=' ) ? 60 : 85,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/wordpress-version-compatibility',
			'details'      => array(
				'current_version'     => $wp_version,
				'minimum_version'     => $minimum,
				'recommended_version' => $recommended,
				'update_available'    => $update_available,
			),
		);
	}
}
