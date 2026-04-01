<?php
/**
 * Knowledge Base Integration Not Configured Diagnostic
 *
 * Checks KB integration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Knowledge_Base_Integration_Not_Configured Class
 *
 * Performs diagnostic check for Knowledge Base Integration Not Configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Knowledge_Base_Integration_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'knowledge-base-integration-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Knowledge Base Integration Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks KB integration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'knowledge_base_integrated' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Knowledge base integration is not configured yet. Connecting helpful guides can make support easier for your users.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/knowledge-base-integration-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
