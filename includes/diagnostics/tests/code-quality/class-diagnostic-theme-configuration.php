<?php
/**
 * Theme Configuration and Security
 *
 * Validates theme configuration and security implementation.
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
 * Diagnostic_Theme_Configuration Class
 *
 * Checks theme configuration and security.
 *
 * @since 1.2034.1615
 */
class Diagnostic_Theme_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme configuration and security implementation';

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
		// Pattern 1: Theme missing required template tags
		$current_theme = wp_get_theme();
		$missing_tags  = array();

		$required_tags = array(
			'wp_head'   => array(
				'location'     => 'header.php',
				'purpose'      => 'Critical metadata, stylesheets, scripts',
				'consequences' => 'Broken styles, missing meta tags',
			),
			'wp_footer' => array(
				'location'     => 'footer.php',
				'purpose'      => 'JavaScript, closing tags',
				'consequences' => 'Scripts not loaded, broken layout',
			),
			'wp_title'  => array(
				'location'     => 'header.php or via wp_head',
				'purpose'      => 'Page title in browser and search',
				'consequences' => 'No page titles, SEO impact',
			),
		);

		foreach ( $required_tags as $tag => $info ) {
			$template_files = array(
				get_template_directory() . '/header.php',
				get_template_directory() . '/footer.php',
				get_template_directory() . '/index.php',
			);

			$found = false;
			foreach ( $template_files as $file ) {
				if ( file_exists( $file ) ) {
					$content = file_get_contents( $file );

					if ( strpos( $content, $tag . '()' ) !== false || strpos( $content, $tag . '(' ) !== false ) {
						$found = true;
						break;
					}
				}
			}

			if ( ! $found ) {
				$missing_tags[] = $tag;
			}
		}

		if ( ! empty( $missing_tags ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme missing critical template tags', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-configuration',
				'details'      => array(
					'issue'                => 'missing_template_tags',
					'missing_tags'         => $missing_tags,
					'message'              => sprintf(
						/* translators: %s: tag names */
						__( 'Theme missing critical tags: %s', 'wpshadow' ),
						implode( ', ', $missing_tags )
					),
					'wp_head_purpose'      => __( 'Loads all stylesheets, meta tags, scripts for admin bar', 'wpshadow' ),
					'wp_footer_purpose'    => __( 'Closes all script/style tags, loads footer scripts', 'wpshadow' ),
					'wp_body_open_purpose' => __( 'Required after HTML body tag opening', 'wpshadow' ),
					'consequences'         => array(
						'CSS/JS not loaded'   => 'Broken styling',
						'Admin bar missing'   => 'Can\'t see admin link',
						'Page titles missing' => 'SEO issues',
						'Meta tags missing'   => 'Social sharing broken',
					),
					'header_php_example'   => "<?php
/**
 * The header template
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset=\"<?php bloginfo( 'charset' ); ?>\">
	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
	<?php wp_head(); // Required to load CSS, JS, and meta tags ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); // Added in WordPress 5.2 ?>",
					'footer_php_example'   => '<?php
/**
 * The footer template
 */
?>
	</div><!-- #content -->
	<footer>
		<!-- Footer content -->
	</footer>
	<?php wp_footer(); // Required to load footer scripts and clean up ?>
</body>
</html>',
					'adding_tags'          => array(
						'1. Open header.php in theme editor',
						'2. Find <head> section',
						'3. Add <?php wp_head(); ?> before </head>',
						'4. Open footer.php',
						'5. Add <?php wp_footer(); ?> before </body>',
						'6. Save and test',
					),
					'plugin_notification'  => __( 'WordPress will warn if these tags are missing', 'wpshadow' ),
					'recommendation'       => __( 'Add all required WordPress template tags to theme', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Theme not escaping output
		$theme_dir = get_template_directory();
		$php_files = glob( $theme_dir . '/*.php' );

		$unescaped_output = 0;
		foreach ( $php_files as $file ) {
			$content = file_get_contents( $file );

			// Look for unescaped echo statements
			if ( preg_match( '/echo\s+\$[a-zA-Z_]/', $content ) ) {
				++$unescaped_output;
			}
		}

		if ( $unescaped_output > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme outputting unescaped content', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-configuration',
				'details'      => array(
					'issue'               => 'unescaped_output',
					'unescaped_files'     => $unescaped_output,
					'message'             => sprintf(
						/* translators: %d: count */
						__( '%d theme template files have unescaped output', 'wpshadow' ),
						$unescaped_output
					),
					'xss_risk'            => __( 'Unescaped output allows XSS attacks', 'wpshadow' ),
					'attack_example'      => array(
						'Attacker injects: <script>alert(1)</script>',
						'Unescaped echo renders: <script>alert(1)</script>',
						'Script executes in browser',
						'User data stolen, redirected',
					),
					'escaping_functions'  => array(
						'esc_html()'     => 'HTML content, <?php echo esc_html($text); ?>',
						'esc_attr()'     => 'HTML attributes, <?php echo esc_attr($attr); ?>',
						'esc_url()'      => 'URLs, <?php echo esc_url($url); ?>',
						'esc_js()'       => 'JavaScript, <?php echo esc_js($var); ?>',
						'wp_kses_post()' => 'Allow safe HTML tags',
					),
					'context_matters'     => array(
						'HTML context'       => 'esc_html()',
						'Attribute context'  => 'esc_attr()',
						'URL context'        => 'esc_url()',
						'JavaScript context' => 'esc_js()',
					),
					'before_and_after'    => "// WRONG - XSS vulnerability
echo \$title;
echo '<a href=\"' . \$url . '\">Link</a>';

// RIGHT - Escaped
echo esc_html(\$title);
echo '<a href=\"' . esc_url(\$url) . '\">Link</a>';",
					'wordpress_standards' => __( 'WordPress Coding Standards require escaping at output', 'wpshadow' ),
					'automated_checking'  => __( 'Use phpcs with WPCS ruleset to find unescaped output', 'wpshadow' ),
					'recommendation'      => __( 'Escape all user and dynamic content in theme templates', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Theme has file permissions issues
		$theme_dir        = get_template_directory();
		$files_with_write = array();

		foreach ( glob( $theme_dir . '/*.php' ) as $file ) {
			if ( is_writable( $file ) && fileperms( $file ) & 0x0080 ) {
				$files_with_write[] = basename( $file );
			}
		}

		if ( count( $files_with_write ) > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme files have overly permissive permissions', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-configuration',
				'details'      => array(
					'issue'                      => 'permissive_file_permissions',
					'writable_count'             => count( $files_with_write ),
					'sample_files'               => array_slice( $files_with_write, 0, 10 ),
					'message'                    => sprintf(
						/* translators: %d: count */
						__( '%d theme files are world-writable', 'wpshadow' ),
						count( $files_with_write )
					),
					'security_risk'              => __( 'World-writable files can be modified by anyone on server', 'wpshadow' ),
					'permission_levels'          => array(
						'755' => 'Owner read/write/execute, others read/execute',
						'644' => 'Owner read/write, others read',
						'777' => 'Everyone read/write/execute (BAD)',
						'666' => 'Everyone read/write (BAD)',
					),
					'setting_proper_permissions' => array(
						'Directories: 755',
						'PHP files: 644',
						'Config files: 600',
						'Avoid 777 or 666',
					),
					'fixing_via_ftp'             => array(
						'1. Connect to server via FTP',
						'2. Right-click on theme directory',
						'3. Set permissions to 755',
						'4. Apply recursively',
						'5. Set PHP files to 644',
					),
					'fixing_via_ssh'             => '# Fix directory permissions
chmod 755 /path/to/theme

# Fix file permissions
find /path/to/theme -type f -exec chmod 644 {} \\;

# Fix specific file
chmod 644 /path/to/theme/style.css',
					'checking_permissions'       => "// Check file permissions
\$file = get_template_directory() . '/functions.php';
\$perms = substr(sprintf('%o', fileperms(\$file)), -4);
echo 'Permissions: ' . \$perms;",
					'web_server_account'         => __( 'Ensure web server user (www-data, apache) can read but not write', 'wpshadow' ),
					'recommendation'             => __( 'Fix file permissions to 644 for PHP files, 755 for directories', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Theme not declaring support for required features
		if ( ! current_theme_supports( 'post-thumbnails' ) || ! current_theme_supports( 'title-tag' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme missing support declarations', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-configuration',
				'details'      => array(
					'issue'               => 'missing_feature_support',
					'message'             => __( 'Theme not declaring support for WordPress features', 'wpshadow' ),
					'essential_features'  => array(
						'title-tag'       => 'WordPress manages page titles',
						'post-thumbnails' => 'Featured images for posts',
						'html5'           => 'HTML5 markup support',
						'custom-logo'     => 'Logo in customizer',
						'menus'           => 'Custom navigation menus',
					),
					'feature_declaration' => "// In functions.php - declare what theme supports
add_theme_support('title-tag');
add_theme_support('post-thumbnails');
add_theme_support('html5', array(
	'search-form',
	'comment-form',
	'comment-list',
	'gallery',
	'caption',
	'script',
	'style',
));
add_theme_support('custom-logo', array(
	'height' => 100,
	'width' => 400,
));
add_theme_support('menus');",
					'why_important'       => array(
						'Plugins rely on feature support',
						'Core WordPress functionality depends on it',
						'Ensures compatibility',
						'Improves customizer options',
					),
					'checking_support'    => "// Check if feature is supported
if (current_theme_supports('post-thumbnails')) {
	echo 'Theme supports featured images';
} else {
	echo 'Theme needs post-thumbnails support';
}",
					'conditional_support' => "// Add support with conditions
if (is_user_logged_in()) {
	add_theme_support('post-thumbnails');
}

// Or check plugin active
if (class_exists('WooCommerce')) {
	add_theme_support('wc-product-gallery-lightbox');
}",
					'recommendation'      => __( 'Add theme feature support declarations', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
