<?php
/**
 * Custom Post Type Archive Pages Configuration
 *
 * Validates archive page configuration for custom post types.
 *
 * @since   1.2034.1145
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CPT_Archive_Pages Class
 *
 * Checks custom post type archive page configuration issues.
 *
 * @since 1.2034.1145
 */
class Diagnostic_CPT_Archive_Pages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-archive-pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Post Type Archive Pages';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates archive page configuration for custom post types';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'custom-post-types';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1145
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_post_types;

		// Get all custom post types (exclude built-in)
		$built_in_types = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation' );
		$custom_types = array();

		foreach ( $wp_post_types as $type => $type_obj ) {
			if ( ! in_array( $type, $built_in_types, true ) && $type_obj->public ) {
				$custom_types[ $type ] = $type_obj;
			}
		}

		// Pattern 1: Public custom post types without archives
		$no_archives = array();
		foreach ( $custom_types as $type => $type_obj ) {
			if ( false === $type_obj->has_archive ) {
				$no_archives[] = $type;
			}
		}

		if ( ! empty( $no_archives ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Public custom post types without archive pages', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-archive-pages',
				'details'      => array(
					'issue' => 'no_archives',
					'affected_post_types' => $no_archives,
					'message' => sprintf(
						/* translators: %d: number of post types */
						__( '%d public custom post types have no archive pages', 'wpshadow' ),
						count( $no_archives )
					),
					'why_archives_matter' => array(
						'List all posts of that type',
						'Improve content discoverability',
						'Better SEO (indexed archive pages)',
						'User navigation',
					),
					'when_archives_needed' => array(
						'Public post types with multiple posts',
						'Content meant to be browsed',
						'Portfolio items, products, events',
						'Any content type users should see as a collection',
					),
					'when_archives_not_needed' => array(
						'Single instance post types',
						'Admin-only content',
						'Content shown via shortcodes/blocks only',
					),
					'how_to_enable' => "register_post_type('portfolio', array(
	'public' => true,
	'has_archive' => true, // Creates /portfolio/ archive
	// Or custom archive slug:
	'has_archive' => 'our-work', // Creates /our-work/ archive
));",
					'archive_template_hierarchy' => array(
						'archive-{post_type}.php' => 'Specific post type archive',
						'archive.php' => 'Generic archive template',
						'index.php' => 'Fallback',
					),
					'creating_archive_template' => __( 'Create archive-{post_type}.php in your theme for custom archive design', 'wpshadow' ),
					'seo_benefits' => array(
						'Archive pages indexed by search engines',
						'Additional entry points for content',
						'Better internal linking structure',
					),
					'after_enabling' => __( 'Flush rewrite rules (Settings > Permalinks > Save)', 'wpshadow' ),
					'recommendation' => __( 'Enable archives for public custom post types with multiple posts', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Archive pages with no posts per page configuration
		$default_posts_per_page = get_option( 'posts_per_page', 10 );
		$archive_settings_missing = array();

		foreach ( $custom_types as $type => $type_obj ) {
			if ( $type_obj->has_archive ) {
				// Check if custom posts_per_page is set
				$option_name = "posts_per_page_{$type}";
				$custom_ppp = get_option( $option_name );

				if ( false === $custom_ppp ) {
					$archive_settings_missing[] = $type;
				}
			}
		}

		if ( ! empty( $archive_settings_missing ) && count( $archive_settings_missing ) >= 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post type archives using default posts per page', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-archive-pages',
				'details'      => array(
					'issue' => 'default_posts_per_page',
					'affected_post_types' => $archive_settings_missing,
					'current_default' => $default_posts_per_page,
					'message' => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post type archives using default posts per page setting', 'wpshadow' ),
						count( $archive_settings_missing )
					),
					'why_custom_settings_matter' => array(
						'Different content types need different display counts',
						'Portfolio items: show 12-24 per page (grid)',
						'Blog posts: show 10 per page (list)',
						'Products: show 16-48 per page (grid)',
						'Events: show 5-10 per page (detailed list)',
					),
					'how_to_customize' => "add_action('pre_get_posts', function(\$query) {
	if (!is_admin() && \$query->is_main_query() && is_post_type_archive('portfolio')) {
		\$query->set('posts_per_page', 24);
	}
});",
					'considerations' => array(
						'Grid layouts' => 'Use multiples of columns (12, 16, 24)',
						'Image-heavy content' => 'Fewer items (6-12) to reduce page load',
						'Text-heavy content' => 'More items (20-30) acceptable',
						'Mobile experience' => 'Consider responsive grid collapsing',
					),
					'performance_impact' => __( 'Too many items can slow page load; too few frustrates users', 'wpshadow' ),
					'user_experience' => __( 'Match posts per page to content type and display style', 'wpshadow' ),
					'recommendation' => __( 'Set custom posts_per_page for each archive based on content type', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Archive pages without dedicated templates
		$theme_path = get_template_directory();
		$missing_templates = array();

		foreach ( $custom_types as $type => $type_obj ) {
			if ( $type_obj->has_archive ) {
				$template_file = $theme_path . '/archive-' . $type . '.php';
				if ( ! file_exists( $template_file ) ) {
					$missing_templates[] = $type;
				}
			}
		}

		if ( ! empty( $missing_templates ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post type archives without dedicated templates', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-archive-pages',
				'details'      => array(
					'issue' => 'missing_templates',
					'affected_post_types' => $missing_templates,
					'theme_path' => $theme_path,
					'message' => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post type archives using generic template', 'wpshadow' ),
						count( $missing_templates )
					),
					'why_dedicated_templates_matter' => array(
						'Custom layout for specific content',
						'Optimized design for post type',
						'Custom fields display',
						'Better user experience',
					),
					'template_hierarchy' => array(
						'1. archive-{post_type}.php' => 'Most specific',
						'2. archive.php' => 'Generic archives',
						'3. index.php' => 'Fallback',
					),
					'creating_template' => array(
						'1. Copy archive.php or index.php',
						'2. Rename to archive-{post_type}.php',
						'3. Customize markup and styling',
						'4. Add custom field displays',
						'5. Optimize for post type',
					),
					'block_theme_alternative' => __( 'Block themes use template parts and patterns instead of PHP templates', 'wpshadow' ),
					'common_customizations' => array(
						'Grid layout for portfolios',
						'List layout with thumbnails for products',
						'Calendar view for events',
						'Card layout with custom fields',
					),
					'example_template_code' => "<?php
/**
 * Archive Template for Portfolio
 */
get_header(); ?>

<div class=\"portfolio-archive\">
	<h1><?php post_type_archive_title(); ?></h1>
	
	<?php if (have_posts()) : ?>
		<div class=\"portfolio-grid\">
			<?php while (have_posts()) : the_post(); ?>
				<article id=\"post-<?php the_ID(); ?>\">
					<?php the_post_thumbnail('medium'); ?>
					<h2><?php the_title(); ?></h2>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php endif; ?>
</div>

<?php get_footer();",
					'recommendation' => __( 'Create dedicated archive templates for better content presentation', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Rewrite rules need flushing
		$rewrite_rules = get_option( 'rewrite_rules' );
		$missing_rules = array();

		foreach ( $custom_types as $type => $type_obj ) {
			if ( $type_obj->has_archive && ! empty( $type_obj->rewrite ) ) {
				$archive_slug = is_array( $type_obj->rewrite ) && isset( $type_obj->rewrite['slug'] ) 
					? $type_obj->rewrite['slug'] 
					: $type;

				// Check if rewrite rule exists
				$found = false;
				if ( is_array( $rewrite_rules ) ) {
					foreach ( $rewrite_rules as $pattern => $substitution ) {
						if ( false !== strpos( $pattern, $archive_slug ) ) {
							$found = true;
							break;
						}
					}
				}

				if ( ! $found ) {
					$missing_rules[] = $type;
				}
			}
		}

		if ( ! empty( $missing_rules ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post type archive rewrite rules need flushing', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-archive-pages',
				'details'      => array(
					'issue' => 'missing_rewrite_rules',
					'affected_post_types' => $missing_rules,
					'message' => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post type archives have missing rewrite rules', 'wpshadow' ),
						count( $missing_rules )
					),
					'symptoms' => array(
						'404 errors on archive pages',
						'Archive URLs not working',
						'Posts display but archive doesn\'t',
						'Recent plugin/theme activation',
					),
					'what_are_rewrite_rules' => __( 'Rules that convert clean URLs (/portfolio/) to query strings (?post_type=portfolio)', 'wpshadow' ),
					'why_this_happens' => array(
						'Post type registration changed',
						'Plugin/theme activation',
						'Permalink structure changed',
						'Manual database changes',
					),
					'how_to_fix' => array(
						'Manual: Go to Settings > Permalinks > Save Changes',
						'Code: flush_rewrite_rules() (not on every page load!)',
						'Plugin activation hook: register_activation_hook()',
					),
					'code_example_safe' => "// On plugin activation only
register_activation_hook(__FILE__, function() {
	// Register post types first
	my_plugin_register_post_types();
	
	// Then flush rules
	flush_rewrite_rules();
});

// On plugin deactivation
register_deactivation_hook(__FILE__, function() {
	flush_rewrite_rules();
});",
					'do_not_do_this' => "// NEVER call flush_rewrite_rules() on every page load!
add_action('init', function() {
	register_post_type('portfolio', array(...));
	flush_rewrite_rules(); // ❌ Performance killer!
});",
					'performance_warning' => __( 'flush_rewrite_rules() is expensive; only use on activation/deactivation', 'wpshadow' ),
					'when_to_manually_flush' => array(
						'After plugin activation',
						'After theme change',
						'After permalink structure change',
						'After post type registration changes',
					),
					'recommendation' => __( 'Visit Settings > Permalinks and click Save Changes to flush rewrite rules', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Archive pages with no SEO optimization
		$no_seo_optimization = array();

		foreach ( $custom_types as $type => $type_obj ) {
			if ( $type_obj->has_archive ) {
				// Check if common SEO plugins have settings for this post type
				$has_yoast = defined( 'WPSEO_VERSION' );
				$has_rank_math = defined( 'RANK_MATH_VERSION' );
				$has_aioseo = defined( 'AIOSEO_VERSION' );

				// If no SEO plugin, add to list
				if ( ! $has_yoast && ! $has_rank_math && ! $has_aioseo ) {
					$no_seo_optimization[] = $type;
				}
			}
		}

		if ( ! empty( $no_seo_optimization ) && count( $no_seo_optimization ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post type archives without SEO optimization', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-archive-pages',
				'details'      => array(
					'issue' => 'no_seo_optimization',
					'affected_post_types' => $no_seo_optimization,
					'message' => __( 'Custom post type archives missing SEO metadata', 'wpshadow' ),
					'why_seo_matters' => array(
						'Archive pages can rank in search',
						'Meta descriptions improve CTR',
						'Title tags control search appearance',
						'Social sharing optimization',
					),
					'seo_elements_to_optimize' => array(
						'Page title' => 'Descriptive, keyword-rich titles',
						'Meta description' => 'Compelling summaries',
						'Open Graph tags' => 'Social sharing images/text',
						'Canonical URL' => 'Prevent duplicate content',
						'Breadcrumbs' => 'Navigation and schema',
					),
					'without_seo_plugin' => array(
						'Manual title/description' => 'Add via wp_head action',
						'og:title, og:description' => 'Open Graph tags',
						'Twitter Card tags' => 'Twitter sharing',
						'Canonical link' => 'Link rel=canonical',
					),
					'code_example_manual' => "add_action('wp_head', function() {
	if (is_post_type_archive('portfolio')) {
		\$title = 'Our Portfolio | ' . get_bloginfo('name');
		\$description = 'Browse our portfolio of recent projects and case studies.';
		
		echo '<meta name=\"description\" content=\"' . esc_attr(\$description) . '\">';
		echo '<meta property=\"og:title\" content=\"' . esc_attr(\$title) . '\">';
		echo '<meta property=\"og:description\" content=\"' . esc_attr(\$description) . '\">';
		echo '<link rel=\"canonical\" href=\"' . esc_url(get_post_type_archive_link('portfolio')) . '\">';
	}
});",
					'seo_plugin_recommendations' => array(
						'Yoast SEO' => 'Most popular, comprehensive',
						'Rank Math' => 'Modern, feature-rich',
						'All in One SEO' => 'Long-established, reliable',
					),
					'what_seo_plugins_do' => array(
						'Auto-generate meta descriptions',
						'Customize archive titles',
						'Add structured data',
						'Social sharing optimization',
						'XML sitemap inclusion',
					),
					'recommendation' => __( 'Install SEO plugin or manually add meta tags to archive pages', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: Archive query performance issues
		global $wpdb;
		$slow_archives = array();

		foreach ( $custom_types as $type => $type_obj ) {
			if ( $type_obj->has_archive ) {
				// Count posts in post type
				$count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
						$type
					)
				);

				if ( $count > 1000 ) {
					$slow_archives[ $type ] = $count;
				}
			}
		}

		if ( ! empty( $slow_archives ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post type archives may have performance issues', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-archive-pages',
				'details'      => array(
					'issue' => 'archive_performance',
					'affected_post_types' => $slow_archives,
					'message' => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post type archives with large post counts', 'wpshadow' ),
						count( $slow_archives )
					),
					'performance_concerns' => array(
						'Large post counts slow queries',
						'Pagination overhead increases',
						'Memory usage grows',
						'Database load increases',
					),
					'optimization_strategies' => array(
						'Object caching' => 'Cache query results (Redis, Memcached)',
						'Query optimization' => 'Limit fields, add indexes',
						'Lazy loading' => 'Load images only when visible',
						'Pagination' => 'Keep posts per page reasonable',
						'Transient caching' => 'Cache expensive queries',
					),
					'query_optimization_example' => "add_action('pre_get_posts', function(\$query) {
	if (!is_admin() && \$query->is_main_query() && is_post_type_archive('portfolio')) {
		// Only query needed fields
		\$query->set('fields', 'ids'); // If you only need IDs
		
		// Reduce posts per page
		\$query->set('posts_per_page', 12);
		
		// Disable unnecessary queries
		\$query->set('no_found_rows', true); // If pagination not needed
	}
});",
					'caching_example' => "function get_portfolio_archive_posts() {
	\$cache_key = 'portfolio_archive_' . get_query_var('paged', 1);
	\$posts = get_transient(\$cache_key);
	
	if (false === \$posts) {
		\$posts = new WP_Query(array(
			'post_type' => 'portfolio',
			'posts_per_page' => 12,
			'paged' => get_query_var('paged', 1),
		));
		
		set_transient(\$cache_key, \$posts, HOUR_IN_SECONDS);
	}
	
	return \$posts;
}",
					'when_to_use_caching' => __( 'Archives with >100 posts benefit from caching', 'wpshadow' ),
					'pagination_best_practices' => array(
						'Limit posts per page (10-24)',
						'Use prev/next pagination',
						'Consider infinite scroll',
						'Add load more button',
					),
					'recommendation' => __( 'Implement caching and query optimization for large archives', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
