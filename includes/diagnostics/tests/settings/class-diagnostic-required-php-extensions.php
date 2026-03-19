<?php
/**
 * Required PHP Extensions Diagnostic
 *
 * Checks for required and recommended PHP extensions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required PHP Extensions Diagnostic Class
 *
 * Verifies required PHP extensions are loaded.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Required_PHP_Extensions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'required-php-extensions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Required PHP Extensions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for required PHP extensions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting-environment';

	/**
	 * Required extensions
	 *
	 * @var array
	 */
	private const REQUIRED_EXTENSIONS = array( 'mysqli', 'gd', 'curl' );

	/**
	 * Recommended extensions
	 *
	 * @var array
	 */
	private const RECOMMENDED_EXTENSIONS = array( 'mbstring', 'zip', 'imagick', 'opcache', 'intl', 'exif' );

	/**
	 * Run the PHP extensions diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if extension issue detected, null otherwise.
	 */
	public static function check() {
		$missing_required = array();
		$missing_recommended = array();

		// Check required extensions.
		foreach ( self::REQUIRED_EXTENSIONS as $ext ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing_required[] = $ext;
			}
		}

		// Check recommended extensions.
		foreach ( self::RECOMMENDED_EXTENSIONS as $ext ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing_recommended[] = $ext;
			}
		}

		$result = null;

		if ( ! empty( $missing_required ) ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of extensions */
					__( 'Required PHP extensions are missing: %s. WordPress may not function properly.', 'wpshadow' ),
					implode( ', ', $missing_required )
				),
				'severity'    => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php-extensions-required',
				'meta'        => array(
					'missing_required'     => $missing_required,
					'missing_recommended'  => $missing_recommended,
				),
			);
		} elseif ( ! empty( $missing_recommended ) ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of extensions */
					__( 'Recommended PHP extensions are missing: %s. Some features may not work optimally.', 'wpshadow' ),
					implode( ', ', $missing_recommended )
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php-extensions-recommended',
				'meta'        => array(
					'missing_recommended' => $missing_recommended,
				),
			);
		}

		return $result;
	}
}
