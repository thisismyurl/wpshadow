<?php
/**
 * Schema.org Markup Not Generated Diagnostic
 *
 * Checks if schema markup is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2335
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema.org Markup Not Generated Diagnostic Class
 *
 * Detects missing schema markup.
 *
 * @since 1.2601.2335
 */
class Diagnostic_Schema_org_Markup_Not_Generated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-org-markup-not-generated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Schema.org Markup Not Generated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if schema markup is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for schema plugins
		$schema_plugins = array(
			'yoast-seo/wp-seo.php',
			'rank-math-seo/rank-math.php',
			'all-in-one-schema-rich-snippets/all-in-one-schema-rich-snippets.php',
		);

		$schema_active = false;
		foreach ( $schema_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$schema_active = true;
				break;
			}
		}

		if ( ! $schema_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Schema.org markup is not implemented. Add schema markup to improve search visibility and rich snippets.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/schema-org-markup-not-generated',
			);
		}

		return null;
	}
}
