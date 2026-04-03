<?php
/**
 * Plugins Updated Diagnostic
 *
 * Checks whether any installed plugins have available updates, as outdated
 * plugins are a leading source of WordPress security vulnerabilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugins_Updated Class
 *
 * Retrieves the list of plugins that have pending updates and flags the
 * site when any outdated plugins are found.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugins_Updated extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'plugins-updated';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Plugins Updated';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether any installed plugins have available updates, as outdated plugins are a leading source of WordPress security vulnerabilities.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is part of the core trusted set.
	 *
	 * @var bool
	 */
	protected static $is_core = true;

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'high';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the WordPress update transient via the WP_Settings helper to
	 * collect plugins that have pending updates, returning a high-severity
	 * finding when any are found.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when outdated plugins exist, null when healthy.
	 */
	public static function check() {
		$outdated = WP_Settings::get_plugins_needing_updates();
		if ( empty( $outdated ) ) {
			return null;
		}

		$count = count( $outdated );
		$names = array_column( array_values( $outdated ), 'name' );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				_n(
					'%d plugin has an available update. Outdated plugins are a primary attack vector - install updates promptly.',
					'%d plugins have available updates. Outdated plugins are a primary attack vector - install updates promptly.',
					$count,
					'wpshadow'
				),
				$count
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'kb_link'      => 'https://wpshadow.com/kb/plugins-updated?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'count'   => $count,
				'plugins' => $names,
			),
		);
	}
}
