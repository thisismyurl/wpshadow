<?php
/**
 * Content Staging Environment Not Set Up Diagnostic
 *
 * Checks if content staging is configured.
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
 * Content Staging Environment Not Set Up Diagnostic Class
 *
 * Detects missing content staging.
 *
 * @since 1.2601.2345
 */
class Diagnostic_Content_Staging_Environment_Not_Set_Up extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-staging-environment-not-set-up';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Staging Environment Not Set Up';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if staging environment is configured';

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
		// Check if not on localhost/staging (advisory only)
		if ( getenv( 'WP_ENV' ) !== 'staging' && getenv( 'WP_ENV' ) !== 'development' ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No staging environment is configured. Set up a staging site for testing before publishing to production.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-staging-environment-not-set-up',
			);
		}

		return null;
	}
}
