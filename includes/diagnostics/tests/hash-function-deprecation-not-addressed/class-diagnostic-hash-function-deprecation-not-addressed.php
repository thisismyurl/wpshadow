<?php
/**
 * Hash Function Deprecation Not Addressed Diagnostic
 *
 * Checks hash function security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Hash_Function_Deprecation_Not_Addressed Class
 *
 * Performs diagnostic check for Hash Function Deprecation Not Addressed.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Hash_Function_Deprecation_Not_Addressed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hash-function-deprecation-not-addressed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hash Function Deprecation Not Addressed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks hash function security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'use_argon2_hashing' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Hash function deprecation not addressed. Use password_hash() with argon2 instead of MD5/SHA1 for secure password hashing.',
						'severity'   =>   'high',
						'threat_level'   =>   70,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/hash-function-deprecation-not-addressed'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
