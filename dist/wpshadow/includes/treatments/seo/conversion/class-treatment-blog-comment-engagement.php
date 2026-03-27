<?php
/**
 * Blog Comment Engagement Treatment
 *
 * Issue #4784: Blog Posts Don't Encourage Comments or Discussion
 * Family: business-performance
 *
 * Checks if blog posts actively encourage reader engagement.
 * Comments build community, provide user-generated content, and signal active site.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Blog_Comment_Engagement Class
 *
 * Checks for comment engagement strategies.
 *
 * @since 1.6093.1200
 */
class Treatment_Blog_Comment_Engagement extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'blog-comment-engagement';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Blog Posts Don\'t Encourage Comments or Discussion';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if blog posts actively invite reader comments and discussion';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Blog_Comment_Engagement' );
	}
}
