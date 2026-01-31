<?php
/**
 * XML-RPC Protocol Not Disabled Diagnostic
 *
 * Checks if XML-RPC is disabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML-RPC Protocol Not Disabled Diagnostic Class
 *
 * Detects enabled XML-RPC.
 *
 * @since 1.2601.2310
 */
class Diagnostic_XML_RPC_Protocol_Not_Disabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-rpc-protocol-not-disabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML-RPC Protocol Not Disabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if XML-RPC is disabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if XML-RPC is disabled
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'XML-RPC is enabled. Disable it if not needed to prevent brute force attacks via XML-RPC pingback.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/xml-rpc-protocol-not-disabled',
			);
		}

		return null;
	}
}
