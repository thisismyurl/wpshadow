<?php
/**
 * Theme Code Quality and Standards
 *
 * Validates theme code quality and WordPress standards compliance.
 *
 * @since   1.2034.1615
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Theme_Code_Quality Class
 *
 * Checks theme code quality and standards compliance.
 *
 * @since 1.2034.1615
 */
class Diagnostic_Theme_Code_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-code-quality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Code Quality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme code quality and WordPress standards compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'theme-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Pattern 1: Theme using direct database queries
		$theme_dir = get_template_directory();
		$php_files = glob( $theme_dir . '/**/*.php', GLOB_RECURSIVE );

		$files_with_direct_db = array();
		foreach ( $php_files as $file ) {
			$content = file_get_contents( $file );

			// Look for direct database access
			if ( preg_match( '/\$wpdb->get_|mysql_|mysqli_/', $content ) ) {
				// Check if using proper prepare()
				if ( ! preg_match( '/\$wpdb->prepare\(/', $content ) ) {
					$files_with_direct_db[] = basename( $file );
				}
			}
		}

		if ( count( $files_with_direct_db ) > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme using direct database queries without prepare', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-code-quality',
				'details'      => array(
					'issue' => 'unprepared_db_queries',
					'files_count' => count( $files_with_direct_db ),
					'sample_files' => array_slice( $files_with_direct_db, 0, 10 ),
					'message' => sprintf(
						/* translators: %d: count */
						__( '%d theme files use unsafe database queries', 'wpshadow' ),
						count( $files_with_direct_db )
					),
					'sql_injection_risk' => __( 'Unescaped queries vulnerable to SQL injection', 'wpshadow' ),
					'attack_example' => array(
						'URL: ?page=1" OR "1"="1',
						'Query becomes: SELECT * FROM posts WHERE page = 1" OR "1"="1',
						'Returns all posts regardless of condition',
						'Data exposed or modified',
					),
					'secure_database_access' => "// WRONG - SQL injection vulnerable
\$results = \$wpdb->get_results(\"SELECT * FROM {$wpdb->posts} WHERE ID = {$_GET['id']}\");

// RIGHT - Using prepare()
\$results = \$wpdb->get_results(\$wpdb->prepare(
	\"SELECT * FROM {$wpdb->posts} WHERE ID = %d\",
	\$_GET['id']
));

// RIGHT - Multiple values
\$results = \$wpdb->get_results(\$wpdb->prepare(
	\"SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_status = %s\",
	\$post_id,
	\$status
));",
					'placeholder_types' => array(
						'%d' => 'Integer values',
						'%s' => 'String/text values',
						'%i' => 'Identifier (table/column name)',
						'%f' => 'Float values',
					),
					'wordpress_functions' => array(
						'get_posts()' => 'Use instead of custom queries',
						'get_users()' => 'For user queries',
						'get_comments()' => 'For comments',
						'WP_Query' => 'For advanced post queries',
					),
					'replacing_direct_queries' => "// BEFORE - Direct query
\$theme_posts = \$wpdb->get_results(
	\"SELECT * FROM {$wpdb->posts} WHERE post_type = 'post' LIMIT 10\"
);

// AFTER - Using WordPress functions
\$theme_posts = get_posts(array(
	'post_type' => 'post',
	'numberposts' => 10,
));",
					'legacy_mysql_functions' => __( 'mysql_* functions removed in PHP 7.0 - never use', 'wpshadow' ),
					'recommendation' => __( 'Use WordPress functions or properly prepared queries in theme', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Theme hardcoding values
		$files_with_hardcoding = array();

		foreach ( $php_files as $file ) {
			$content = file_get_contents( $file );

			// Look for hardcoded site URLs or paths
			if ( preg_match( '/\'http[s]?:\/\//', $content ) || preg_match( '/get_home_url\(\).*\./', $content ) ) {
				if ( ! preg_match( '/admin_url|site_url|home_url|plugin_dir_url/', $content ) ) {
					$files_with_hardcoding[] = basename( $file );
				}
			}
		}

		if ( count( $files_with_hardcoding ) > 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme hardcoding values that should be dynamic', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-code-quality',
				'details'      => array(
					'issue' => 'hardcoded_values',
					'files_count' => count( $files_with_hardcoding ),
					'sample_files' => array_slice( $files_with_hardcoding, 0, 10 ),
					'message' => sprintf(
						/* translators: %d: count */
						__( '%d theme files hardcode URLs and paths', 'wpshadow' ),
						count( $files_with_hardcoding )
					),
					'problems' => array(
						'Domain changes break links',
						'Site migrations fail',
						'URLs invalid after move',
						'Maintenance difficult',
					),
					'dynamic_functions' => array(
						'home_url()' => 'Site home URL',
						'site_url()' => 'Site root URL',
						'admin_url()' => 'WordPress admin URL',
						'plugin_dir_url()' => 'Plugin directory URL',
						'get_template_directory_uri()' => 'Theme directory URL',
						'WP_CONTENT_URL' => 'Content directory URL',
					),
					'before_and_after' => "// WRONG - Hardcoded
<link rel=\"stylesheet\" href=\"http://mysite.com/wp-content/themes/mytheme/style.css\">
<img src=\"http://mysite.com/images/logo.png\" />

// RIGHT - Dynamic
<link rel=\"stylesheet\" href=\"<?php echo get_template_directory_uri(); ?>/style.css\">
<img src=\"<?php echo home_url('/images/logo.png'); ?>\" />",
					'example_urls' => "// All these should be dynamic
\$home = home_url();  // Instead of 'http://example.com'
\$admin = admin_url(); // Instead of 'http://example.com/wp-admin/'
\$theme = get_template_directory_uri(); // Theme CSS/JS",
					'multisite_compatibility' => __( 'Dynamic URLs required for multisite installations', 'wpshadow' ),
					'migration_ready' => __( 'Dynamic URLs make migration to new domain seamless', 'wpshadow' ),
					'recommendation' => __( 'Use WordPress dynamic URL functions instead of hardcoded values', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Theme not translatable
		$theme_dir = get_template_directory();
		$functions_file = $theme_dir . '/functions.php';
		$theme_translated = false;

		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			if ( preg_match( '/load_theme_textdomain|add_theme_support.*i18n/', $content ) ) {
				$theme_translated = true;
			}
		}

		if ( ! $theme_translated ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme not set up for translation', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-code-quality',
				'details'      => array(
					'issue' => 'not_translatable',
					'message' => __( 'Theme strings not set up for translation', 'wpshadow' ),
					'benefits' => array(
						'Support international users',
						'Increase audience reach',
						'Better SEO for multiple languages',
						'Professional appearance',
					),
					'setup_translation' => "// In functions.php
add_action('after_setup_theme', function() {
	load_theme_textdomain(
		'my-theme',
		get_template_directory() . '/languages'
	);
});",
					'string_functions' => array(
						'__()' => 'Simple strings',
						'_e()' => 'Echo strings',
						'_n()' => 'Pluralization',
						'_x()' => 'With context',
						'esc_html__()' => 'Escaped strings',
					),
					'using_strings' => "// WRONG - Hardcoded English
echo 'Hello World';
echo '<h1>Welcome</h1>';

// RIGHT - Translatable
echo __('Hello World', 'my-theme');
echo '<h1>' . __('Welcome', 'my-theme') . '</h1>';",
					'text_domain' => 'my-theme',
					'creating_pot_file' => array(
						'1. Use Poedit or WP-CLI',
						'2. Scan theme files',
						'3. Generate .pot file',
						'4. Create .po translations',
						'5. Compile to .mo files',
					),
					'pot_generation' => "# Using WP-CLI
wp i18n make-pot . languages/my-theme.pot",
					'directory_structure' => 'languages/
  my-theme.pot (template)
  de_DE.po (German)
  de_DE.mo (German compiled)
  es_ES.po (Spanish)
  es_ES.mo (Spanish compiled)',
					'wordpress_org_submission' => __( 'Translation-ready required for WordPress.org directory', 'wpshadow' ),
					'recommendation' => __( 'Set up theme for translation support', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
