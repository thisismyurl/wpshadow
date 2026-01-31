<?php
/**
 * Multisite Domain Mapping Not Configured Diagnostic
 *
 * Checks if multisite domain mapping is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2345
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Domain Mapping Not Configured Diagnostic Class
 *
 * Detects missing domain mapping.
 *
 * @since 1.2601.2345
 */
class Diagnostic_Multisite_Domain_Mapping_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-domain-mapping-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Domain Mapping Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if domain mapping is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2345
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		$sites = get_sites();
		if ( count( $sites ) > 1 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Multisite has multiple sites but domain mapping is not configured. Use domain mapping plugins for custom domains.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/multisite-domain-mapping-not-configured',
			);
		}

		return null;
	}
}
