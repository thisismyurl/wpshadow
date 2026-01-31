<?php
/**
 * Schema Markup Not Implemented Diagnostic
 *
 * Checks if schema markup is implemented.
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
 * Schema Markup Not Implemented Diagnostic Class
 *
 * Detects missing schema markup.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Schema_Markup_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-markup-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Schema Markup Not Implemented';

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
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for schema plugins
		$schema_plugins = array(
			'wordpress-seo/wp-seo.php',
			'schema/schema.php',
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
				'description'   => __( 'Schema markup is not implemented. Search engines use schema data to enhance search results with rich snippets.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/schema-markup-not-implemented',
			);
		}

		return null;
	}
}
