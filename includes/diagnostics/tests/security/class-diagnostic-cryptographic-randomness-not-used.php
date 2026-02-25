<?php
/**
 * Cryptographic Randomness Not Used Diagnostic
 *
 * Checks random generation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Cryptographic_Randomness_Not_Used Class
 *
 * Performs diagnostic check for Cryptographic Randomness Not Used.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Cryptographic_Randomness_Not_Used extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cryptographic-randomness-not-used';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cryptographic Randomness Not Used';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks random generation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'use_cryptographic_random' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cryptographic randomness protections are not configured yet. Using secure random sources helps protect tokens and sensitive workflows.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/cryptographic-randomness-not-used',
			);
		}

		return null;
	}
}
