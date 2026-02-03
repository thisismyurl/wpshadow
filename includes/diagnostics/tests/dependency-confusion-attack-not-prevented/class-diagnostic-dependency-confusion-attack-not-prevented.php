<?php
/**
 * Dependency Confusion Attack Not Prevented Diagnostic
 *
 * Checks dependency confusion.
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
 * Diagnostic_Dependency_Confusion_Attack_Not_Prevented Class
 *
 * Performs diagnostic check for Dependency Confusion Attack Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Dependency_Confusion_Attack_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dependency-confusion-attack-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Dependency Confusion Attack Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks dependency confusion';

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
						'prevent_dependency_confusion' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Dependency confusion attack not prevented. Use composer repository config and verify package integrity.',
						'severity'   =>   'high',
						'threat_level'   =>   70,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/dependency-confusion-attack-not-prevented'
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
