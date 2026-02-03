<?php
/**
 * XML Bomb Attack Not Prevented Diagnostic
 *
 * Checks XML bomb prevention.
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
 * Diagnostic_XML_Bomb_Attack_Not_Prevented Class
 *
 * Performs diagnostic check for Xml Bomb Attack Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_XML_Bomb_Attack_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-bomb-attack-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML Bomb Attack Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks XML bomb prevention';

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
						'prevent_xml_bombs' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('XML bomb attack not prevented. Disable XML external entities (XXE) and limit entity expansion in XML parsing.',
						'severity'   =>   'high',
						'threat_level'   =>   75,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/xml-bomb-attack-not-prevented'
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
