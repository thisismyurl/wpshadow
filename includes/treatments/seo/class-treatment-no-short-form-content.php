<?php
/**
 * No Short-Form Content Treatment
 *
 * Detects lack of quick-read content, missing opportunities for
 * social sharing and quick engagement.
 *
 * @package    WPShadow
 * @subpackage Treatments\Content
 * @since      1.6034.2208
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Short-Form Content Treatment Class
 *
 * Analyzes content mix to ensure inclusion of short-form posts
 * that serve quick-consumption audiences.
 *
 * **Why This Matters:**
 * - 55% of visitors spend < 15 seconds on pages
 * - Short content drives social sharing
 * - Better for mobile consumption
 * - Increases publishing frequency
 * - Complements long-form pillar content
 *
 * **Short-Form Content Types:**
 * - Quick tips (300-500 words)
 * - News updates
 * - List posts
 * - Infographic summaries
 * - Product announcements
 *
 * **Ideal Content Mix:**
 * - 30% short-form (< 600 words)
 * - 50% medium (600-1500 words)
 * - 20% long-form (1500+ words)
 *
 * @since 1.6034.2208
 */
class Treatment_No_Short_Form_Content extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-short-form-content';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Short-Form Content';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'All content is long-form; missing quick-read posts for social sharing';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2208
	 * @return array|null Finding array if no short-form content, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_Short_Form_Content' );
	}
}
