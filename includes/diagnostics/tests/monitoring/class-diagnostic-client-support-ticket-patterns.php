<?php
/**
 * Client Support Ticket Patterns Diagnostic
 *
 * Checks whether support ticket pattern tracking is configured.
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
 * Diagnostic_Client_Support_Ticket_Patterns Class
 *
 * Ensures ticket pattern analytics are configured.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Client_Support_Ticket_Patterns extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'client-support-ticket-patterns';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Client Support Ticket Patterns';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether support ticket pattern tracking is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$patterns = get_option( 'wpshadow_support_ticket_patterns', array() );

		if ( empty( $patterns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Support ticket pattern tracking is not configured. Tracking recurring issues helps reduce support load.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/client-support-ticket-patterns',
			);
		}

		return null;
	}
}