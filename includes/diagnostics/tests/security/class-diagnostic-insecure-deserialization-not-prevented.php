<?php
/**
 * Insecure Deserialization Not Prevented Diagnostic
 *
 * Checks deserialization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Insecure_Deserialization_Not_Prevented Class
 *
 * Performs diagnostic check for Insecure Deserialization Not Prevented.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Insecure_Deserialization_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'insecure-deserialization-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Insecure Deserialization Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks deserialization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'validate_deserialization' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Insecure deserialization not prevented. Never unserialize() data from user input or untrusted sources.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/insecure-deserialization-not-prevented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
