<?php
/**
 * Content Duplication Across Pages Not Audited Diagnostic
 *
 * Checks if duplicate content is audited.
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
 * Content Duplication Across Pages Not Audited Diagnostic Class
 *
 * Detects unaudited duplicate content.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_Duplication_Across_Pages_Not_Audited extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-duplication-across-pages-not-audited';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Duplication Across Pages Not Audited';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if duplicate content is audited';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if duplicate content audit is scheduled
		if ( ! get_option( 'duplicate_content_audit_date' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Duplicate content audit is not performed. Audit your site for duplicated content and consolidate or canonicalize similar pages.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-duplication-across-pages-not-audited',
			);
		}

		return null;
	}
}
