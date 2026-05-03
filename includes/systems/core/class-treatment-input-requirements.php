<?php
/**
 * Treatment Input Requirements
 *
 * Defines optional user-supplied inputs required before specific
 * treatment/diagnostic fixes can be safely applied.
 *
 * @package ThisIsMyURL\Shadow
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry and sanitizer for fix input requirements.
 */
final class Treatment_Input_Requirements {
	/**
	 * Option key storing persisted input values.
	 */
	private const STORAGE_OPTION = 'thisismyurl_shadow_treatment_input_values';

	/**
	 * Get all curated requirements keyed by finding ID.
	 *
	 * @return array<string, array<string,mixed>>
	 */
	public static function get_all(): array {
		return array(
			'about-page-published' => array(
				'title'  => __( 'Set Up an About Page', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'         => 'target_page_id',
						'type'        => 'select',
						'label'       => __( 'Use an existing page or create a new one', 'thisismyurl-shadow' ),
						'description' => __( 'Choose a page to repurpose as your About page, or create a new published page.', 'thisismyurl-shadow' ),
						'why'         => __( 'This diagnostic only needs a real published page with an About-style title or slug. Asking first avoids guessing which page should represent your brand story.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: create or edit a page so its title or slug clearly identifies it as your About page.', 'thisismyurl-shadow' ),
						'required'    => true,
						'options'     => self::get_existing_or_create_page_options( __( 'Create a new About page', 'thisismyurl-shadow' ) ),
					),
					array(
						'key'         => 'page_title',
						'type'        => 'text',
						'label'       => __( 'Page title', 'thisismyurl-shadow' ),
						'placeholder' => __( 'About', 'thisismyurl-shadow' ),
						'description' => __( 'Use a clear visitor-facing title such as About, About Us, or Our Story.', 'thisismyurl-shadow' ),
						'why'         => __( 'The diagnostic checks page titles and slugs for About-style naming patterns.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: edit the page title in the page editor and publish the page.', 'thisismyurl-shadow' ),
						'required'    => true,
					),
					array(
						'key'         => 'page_slug',
						'type'        => 'text',
						'label'       => __( 'Page slug', 'thisismyurl-shadow' ),
						'placeholder' => 'about',
						'description' => __( 'Use a URL slug that clearly identifies the About page, such as about or our-story.', 'thisismyurl-shadow' ),
						'why'         => __( 'A clear slug helps both this diagnostic and your visitors understand the page purpose.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: update the page permalink so the slug clearly identifies the About page.', 'thisismyurl-shadow' ),
						'required'    => true,
					),
				),
			),
			'custom-logo-set' => array(
				'title'  => __( 'Set Your Custom Logo', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'         => 'custom_logo_source',
						'type'        => 'text',
						'label'       => __( 'Logo image URL or attachment ID', 'thisismyurl-shadow' ),
						'placeholder' => __( 'Paste a Media Library image URL or numeric attachment ID', 'thisismyurl-shadow' ),
						'description' => __( 'Choose an existing Media Library image to use as the theme custom logo.', 'thisismyurl-shadow' ),
						'why'         => __( 'A custom logo fills the active theme’s branding slot and gives the header a more deliberate, professional identity.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: open Appearance -> Customize -> Site Identity and choose a Custom Logo.', 'thisismyurl-shadow' ),
						'required'    => true,
					),
				),
			),
			'contact-page-published' => array(
				'title'  => __( 'Set Up a Contact Page', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'         => 'target_page_id',
						'type'        => 'select',
						'label'       => __( 'Use an existing page or create a new one', 'thisismyurl-shadow' ),
						'description' => __( 'Choose a page to repurpose as your Contact page, or create a new published page.', 'thisismyurl-shadow' ),
						'why'         => __( 'This diagnostic only needs a real published page with Contact-style naming. Asking first avoids guessing where enquiries should land.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: create or edit a page so its title or slug clearly identifies it as your Contact page.', 'thisismyurl-shadow' ),
						'required'    => true,
						'options'     => self::get_existing_or_create_page_options( __( 'Create a new Contact page', 'thisismyurl-shadow' ) ),
					),
					array(
						'key'         => 'page_title',
						'type'        => 'text',
						'label'       => __( 'Page title', 'thisismyurl-shadow' ),
						'placeholder' => __( 'Contact', 'thisismyurl-shadow' ),
						'description' => __( 'Use a clear title such as Contact, Contact Us, or Get in Touch.', 'thisismyurl-shadow' ),
						'why'         => __( 'The diagnostic looks for Contact-style page naming to confirm visitors have an obvious route to reach you.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: edit the page title in the page editor and publish the page.', 'thisismyurl-shadow' ),
						'required'    => true,
					),
					array(
						'key'         => 'page_slug',
						'type'        => 'text',
						'label'       => __( 'Page slug', 'thisismyurl-shadow' ),
						'placeholder' => 'contact',
						'description' => __( 'Use a URL slug such as contact or get-in-touch.', 'thisismyurl-shadow' ),
						'why'         => __( 'A clear slug makes the page easier to recognize in navigation and satisfies the diagnostic’s naming checks.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: update the page permalink so the slug clearly identifies the Contact page.', 'thisismyurl-shadow' ),
						'required'    => true,
					),
				),
			),
			'front-page' => array(
				'title'  => __( 'Choose Your Homepage Page', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'         => 'homepage_page_id',
						'type'        => 'select',
						'label'       => __( 'Homepage page', 'thisismyurl-shadow' ),
						'description' => __( 'Select the published page that should act as the site homepage.', 'thisismyurl-shadow' ),
						'why'         => __( 'A static homepage gives visitors a stable landing page instead of a broken or missing front-page assignment.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: go to Settings -> Reading and choose a published page for Homepage.', 'thisismyurl-shadow' ),
						'required'    => true,
						'options'     => self::get_published_page_options(),
					),
					array(
						'key'         => 'posts_page_id',
						'type'        => 'select',
						'label'       => __( 'Posts page (optional)', 'thisismyurl-shadow' ),
						'description' => __( 'Optionally choose a published Blog page for posts index routing at the same time.', 'thisismyurl-shadow' ),
						'why'         => __( 'If your site uses a static homepage, assigning a separate posts page keeps blog archives intentional and easy to manage.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: in Settings -> Reading, set Posts page after choosing a static homepage.', 'thisismyurl-shadow' ),
						'required'    => false,
						'options'     => self::get_optional_published_page_options(),
					),
				),
			),
			'homepage-displays-intentional' => array(
				'title'  => __( 'Configure an Intentional Homepage', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'         => 'homepage_page_id',
						'type'        => 'select',
						'label'       => __( 'Homepage page', 'thisismyurl-shadow' ),
						'description' => __( 'Select the published page that should be used as the site homepage.', 'thisismyurl-shadow' ),
						'why'         => __( 'This diagnostic is about moving away from the default posts-feed homepage and choosing a deliberate landing page.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: go to Settings -> Reading, switch Homepage displays to a static page, then choose a published Homepage page.', 'thisismyurl-shadow' ),
						'required'    => true,
						'options'     => self::get_published_page_options(),
					),
					array(
						'key'         => 'posts_page_id',
						'type'        => 'select',
						'label'       => __( 'Blog page (optional)', 'thisismyurl-shadow' ),
						'description' => __( 'If you want posts on a separate archive page, choose it here.', 'thisismyurl-shadow' ),
						'why'         => __( 'Assigning a separate blog page makes the homepage and the posts archive intentional instead of inheriting WordPress defaults.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: in Settings -> Reading, set Posts page after choosing a static homepage.', 'thisismyurl-shadow' ),
						'required'    => false,
						'options'     => self::get_optional_published_page_options(),
					),
				),
			),
			'primary-navigation-assigned' => array(
				'title'  => __( 'Assign a Primary Navigation Menu', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'         => 'primary_menu_id',
						'type'        => 'select',
						'label'       => __( 'Menu to assign', 'thisismyurl-shadow' ),
						'description' => __( 'Choose an existing WordPress menu to assign to the current theme’s primary navigation location.', 'thisismyurl-shadow' ),
						'why'         => __( 'The diagnostic looks for the active theme’s primary location. Assigning a real menu there restores basic site navigation immediately.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: go to Appearance -> Menus, open Manage Locations, and assign a menu to the primary or main location.', 'thisismyurl-shadow' ),
						'required'    => true,
						'options'     => self::get_nav_menu_options(),
					),
				),
			),
			'footer-menu' => array(
				'title'  => __( 'Assign a Footer Navigation Menu', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'         => 'footer_menu_id',
						'type'        => 'select',
						'label'       => __( 'Menu to assign', 'thisismyurl-shadow' ),
						'description' => __( 'Choose an existing menu that should appear in the footer.', 'thisismyurl-shadow' ),
						'why'         => __( 'Footer navigation is usually where visitors expect legal, support, and utility links to live.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: go to Appearance -> Menus, create or choose a footer menu, then assign it to a footer location.', 'thisismyurl-shadow' ),
						'required'    => true,
						'options'     => self::get_nav_menu_options(),
					),
					array(
						'key'         => 'footer_location_key',
						'type'        => 'select',
						'label'       => __( 'Footer location', 'thisismyurl-shadow' ),
						'description' => __( 'Choose which registered footer-style menu location should receive the selected menu.', 'thisismyurl-shadow' ),
						'why'         => __( 'Some themes expose more than one footer or utility menu slot, so this avoids guessing the wrong one.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: in Appearance -> Menus -> Manage Locations, assign the chosen menu to the footer-related slot you actually use.', 'thisismyurl-shadow' ),
						'required'    => true,
						'options'     => self::get_footer_location_options(),
					),
				),
			),
			'login-url-hardening' => array(
				'title'  => __( 'Before You Enable This Fix', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'         => 'confirmed_login_url_storage',
						'type'        => 'toggle',
						'label'       => __( 'I can safely store the protected login URL before enabling this fix', 'thisismyurl-shadow' ),
						'description' => __( 'This fix changes how wp-login.php is accessed by requiring a secret token in the URL.', 'thisismyurl-shadow' ),
						'why'         => __( 'If you do not save the new tokenized URL, you can be locked out until the token option is removed directly in the database.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual fallback: open wp_options and delete the thisismyurl_shadow_login_url_token option, then wp-login.php will be accessible normally again.', 'thisismyurl-shadow' ),
						'required'    => true,
					),
				),
			),
			'database-prefix-intentional' => array(
				'title'  => __( 'Before You Start This Manual Fix', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'         => 'new_prefix',
						'type'        => 'text',
						'label'       => __( 'New database table prefix', 'thisismyurl-shadow' ),
						'placeholder' => 'mywp7_',
						'description' => __( 'Choose the new prefix you plan to use when renaming WordPress tables.', 'thisismyurl-shadow' ),
						'why'         => __( 'Prefix changes touch every core table and several option/meta keys. Planning and validating the exact prefix up front reduces outage risk.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: rename each WordPress table, update option_name/meta_key values that reference the old prefix, then update $table_prefix in wp-config.php.', 'thisismyurl-shadow' ),
						'required'    => true,
					),
				),
			),
			'site-title-tagline-intentional' => array(
				'title'  => __( 'Set Your Site Identity', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'          => 'site_title',
						'type'         => 'text',
						'label'        => __( 'Site Title', 'thisismyurl-shadow' ),
						'placeholder'  => __( 'Your Brand Name', 'thisismyurl-shadow' ),
						'description'  => __( 'This is shown in browser tabs and often used in search snippets.', 'thisismyurl-shadow' ),
						'why'          => __( 'An intentional title helps users recognize your brand and improves click confidence.', 'thisismyurl-shadow' ),
						'manual'       => __( 'Manual method: go to Settings -> General or Appearance -> Customize -> Site Identity and update Site Title.', 'thisismyurl-shadow' ),
						'required'     => true,
						'apply_option' => 'blogname',
					),
					array(
						'key'          => 'site_tagline',
						'type'         => 'text',
						'label'        => __( 'Site Tagline', 'thisismyurl-shadow' ),
						'placeholder'  => __( 'What your site is about', 'thisismyurl-shadow' ),
						'description'  => __( 'A short tagline communicates purpose and can appear in theme metadata and previews.', 'thisismyurl-shadow' ),
						'why'          => __( 'Leaving a default or empty tagline can look unfinished and reduce trust.', 'thisismyurl-shadow' ),
						'manual'       => __( 'Manual method: in Settings -> General, update Tagline and save changes.', 'thisismyurl-shadow' ),
						'required'     => true,
						'apply_option' => 'blogdescription',
					),
				),
			),
			'site-icon' => array(
				'title'  => __( 'Set Your Site Icon', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'         => 'site_icon_source',
						'type'        => 'text',
						'label'       => __( 'Site icon image URL or attachment ID', 'thisismyurl-shadow' ),
						'placeholder' => __( 'Paste a Media Library image URL or numeric attachment ID', 'thisismyurl-shadow' ),
						'description' => __( 'Use an image that already exists in the Media Library so WordPress can assign it as the site icon.', 'thisismyurl-shadow' ),
						'why'         => __( 'The favicon appears in browser tabs, bookmarks, app shortcuts, and some search results. Leaving it empty makes the site feel unfinished.', 'thisismyurl-shadow' ),
						'manual'      => __( 'Manual method: open Appearance -> Customize -> Site Identity and choose a Site Icon from the Media Library.', 'thisismyurl-shadow' ),
						'required'    => true,
					),
				),
			),
			'timezone' => array(
				'title'  => __( 'Set Your Site Timezone', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'          => 'timezone_string',
						'type'         => 'datalist',
						'label'        => __( 'Named timezone', 'thisismyurl-shadow' ),
						'placeholder'  => 'America/New_York',
						'description'  => __( 'Enter a PHP/WordPress timezone identifier for the site.', 'thisismyurl-shadow' ),
						'why'          => __( 'Using a named timezone keeps scheduled posts, timestamps, and event plugins aligned with the site’s real location.', 'thisismyurl-shadow' ),
						'manual'       => __( 'Manual method: go to Settings -> General and select a city-based timezone from the Timezone dropdown.', 'thisismyurl-shadow' ),
						'required'     => true,
						'options'      => self::get_timezone_options(),
						'apply_option' => 'timezone_string',
					),
				),
			),
			'site-language-intentional' => array(
				'title'  => __( 'Set Your Site Language', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'          => 'site_language',
						'type'         => 'datalist',
						'label'        => __( 'Locale code', 'thisismyurl-shadow' ),
						'placeholder'  => 'en_CA',
						'description'  => __( 'Enter the locale code that matches your audience, such as en_CA, fr_CA, or en_GB.', 'thisismyurl-shadow' ),
						'why'          => __( 'The site language affects translations, date formatting, and admin labels shown throughout WordPress.', 'thisismyurl-shadow' ),
						'manual'       => __( 'Manual method: go to Settings -> General and change Site Language.', 'thisismyurl-shadow' ),
						'required'     => true,
						'options'      => self::get_locale_options(),
						'apply_option' => 'WPLANG',
					),
				),
			),
			'date-time-format-intentional' => array(
				'title'  => __( 'Set Your Date Format', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'          => 'date_format',
						'type'         => 'select',
						'label'        => __( 'Date format', 'thisismyurl-shadow' ),
						'description'  => __( 'Choose the date format that best matches how your local audience expects to read dates.', 'thisismyurl-shadow' ),
						'why'          => __( 'Date formatting mismatches make a site feel foreign or unfinished to local visitors.', 'thisismyurl-shadow' ),
						'manual'       => __( 'Manual method: go to Settings -> General and choose a Date Format.', 'thisismyurl-shadow' ),
						'required'     => true,
						'options'      => self::get_date_format_options(),
						'apply_option' => 'date_format',
					),
					array(
						'key'          => 'time_format',
						'type'         => 'select',
						'label'        => __( 'Time format', 'thisismyurl-shadow' ),
						'description'  => __( 'Optional: choose a matching time format while you are already adjusting the site locale settings.', 'thisismyurl-shadow' ),
						'why'          => __( 'Using a compatible time format helps schedules, appointments, and timestamps feel consistent.', 'thisismyurl-shadow' ),
						'manual'       => __( 'Manual method: in Settings -> General, choose a Time Format.', 'thisismyurl-shadow' ),
						'required'     => false,
						'options'      => self::get_time_format_options(),
						'apply_option' => 'time_format',
					),
				),
			),
			'admin-email-domain-match' => array(
				'title'  => __( 'Set an Admin Email on Your Own Domain', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'          => 'admin_email',
						'type'         => 'text',
						'label'        => __( 'Admin email address', 'thisismyurl-shadow' ),
						'placeholder'  => 'hello@example.com',
						'description'  => __( 'Use a valid mailbox on your business domain for site notifications and security alerts.', 'thisismyurl-shadow' ),
						'why'          => __( 'A business-domain admin email looks more trustworthy and reduces the chances that important mail feels misconfigured.', 'thisismyurl-shadow' ),
						'manual'       => __( 'Manual method: go to Settings -> General and update the Administration Email Address.', 'thisismyurl-shadow' ),
						'required'     => true,
						'apply_option' => 'admin_email',
					),
				),
			),
			'admin-email-deliverable' => array(
				'title'  => __( 'Set a Deliverable Admin Email', 'thisismyurl-shadow' ),
				'fields' => array(
					array(
						'key'          => 'admin_email',
						'type'         => 'text',
						'label'        => __( 'Admin email address', 'thisismyurl-shadow' ),
						'placeholder'  => 'owner@example.com',
						'description'  => __( 'Use a real inbox that is actively monitored for site alerts and moderation notifications.', 'thisismyurl-shadow' ),
						'why'          => __( 'WordPress sends security alerts, moderation notices, and system emails here. Placeholder or shared inboxes are easy to miss.', 'thisismyurl-shadow' ),
						'manual'       => __( 'Manual method: go to Settings -> General and update the Administration Email Address.', 'thisismyurl-shadow' ),
						'required'     => true,
						'apply_option' => 'admin_email',
					),
				),
			),
		);
	}

	/**
	 * Build suggested timezone options for datalist rendering.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function get_timezone_options(): array {
		$preferred = array(
			'America/New_York',
			'America/Chicago',
			'America/Denver',
			'America/Los_Angeles',
			'America/Toronto',
			'America/Vancouver',
			'Europe/London',
			'Europe/Paris',
			'Europe/Berlin',
			'Australia/Sydney',
			'Pacific/Auckland',
			'UTC',
		);

		$options = array();
		foreach ( $preferred as $tz ) {
			$options[] = array(
				'value' => $tz,
				'label' => $tz,
			);
		}

		$current = (string) get_option( 'timezone_string', '' );
		if ( '' !== $current && ! in_array( $current, $preferred, true ) ) {
			array_unshift(
				$options,
				array(
					'value' => $current,
					'label' => $current,
				)
			);
		}

		return $options;
	}

	/**
	 * Build suggested locale options for datalist rendering.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function get_locale_options(): array {
		$preferred = array(
			'en_US' => 'English (United States)',
			'en_CA' => 'English (Canada)',
			'en_GB' => 'English (United Kingdom)',
			'fr_CA' => 'French (Canada)',
			'fr_FR' => 'French (France)',
			'es_ES' => 'Spanish (Spain)',
			'es_MX' => 'Spanish (Mexico)',
			'de_DE' => 'German (Germany)',
			'it_IT' => 'Italian (Italy)',
			'nl_NL' => 'Dutch (Netherlands)',
			'pt_BR' => 'Portuguese (Brazil)',
			'pt_PT' => 'Portuguese (Portugal)',
		);

		$options = array();
		foreach ( $preferred as $locale => $label ) {
			$options[] = array(
				'value' => $locale,
				'label' => $label,
			);
		}

		$current = (string) get_option( 'WPLANG', '' );
		if ( '' !== $current && ! isset( $preferred[ $current ] ) ) {
			array_unshift(
				$options,
				array(
					'value' => $current,
					'label' => $current,
				)
			);
		}

		return $options;
	}

	/**
	 * Build date format select options.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function get_date_format_options(): array {
		return array(
			array( 'value' => 'F j, Y', 'label' => __( 'January 5, 2025', 'thisismyurl-shadow' ) ),
			array( 'value' => 'j F Y', 'label' => __( '5 January 2025', 'thisismyurl-shadow' ) ),
			array( 'value' => 'd/m/Y', 'label' => __( '05/01/2025', 'thisismyurl-shadow' ) ),
			array( 'value' => 'm/d/Y', 'label' => __( '01/05/2025', 'thisismyurl-shadow' ) ),
			array( 'value' => 'Y-m-d', 'label' => __( '2025-01-05', 'thisismyurl-shadow' ) ),
		);
	}

	/**
	 * Build time format select options.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function get_time_format_options(): array {
		return array(
			array( 'value' => 'g:i a', 'label' => __( '2:30 pm', 'thisismyurl-shadow' ) ),
			array( 'value' => 'g:i A', 'label' => __( '2:30 PM', 'thisismyurl-shadow' ) ),
			array( 'value' => 'H:i', 'label' => __( '14:30', 'thisismyurl-shadow' ) ),
		);
	}

	/**
	 * Build select options for published pages.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function get_published_page_options(): array {
		$options = array();
		$pages   = get_pages(
			array(
				'sort_column' => 'menu_order,post_title',
				'post_status' => 'publish',
			)
		);

		foreach ( $pages as $page ) {
			if ( ! $page instanceof \WP_Post ) {
				continue;
			}

			$options[] = array(
				'value' => (string) $page->ID,
						'label' => $page->post_title ? $page->post_title : sprintf(
							/* translators: %d: page ID. */
							__( 'Page #%d', 'thisismyurl-shadow' ),
							$page->ID
						),
			);
		}

		return $options;
	}

	/**
	 * Build select options for an optional published page field.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function get_optional_published_page_options(): array {
		return array_merge(
			array(
				array(
					'value' => '',
					'label' => __( 'No separate posts page', 'thisismyurl-shadow' ),
				),
			),
			self::get_published_page_options()
		);
	}

	/**
	 * Build select options for choosing an existing page or creating a new one.
	 *
	 * @param string $create_label Label for the create-new option.
	 * @return array<int,array<string,string>>
	 */
	private static function get_existing_or_create_page_options( string $create_label ): array {
		return array_merge(
			array(
				array(
					'value' => 'create_new',
					'label' => $create_label,
				),
			),
			self::get_all_page_options()
		);
	}

	/**
	 * Build select options for all pages regardless of status.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function get_all_page_options(): array {
		$options = array();
		$pages   = get_pages(
			array(
				'sort_column' => 'menu_order,post_title',
				'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			)
		);

		foreach ( $pages as $page ) {
			if ( ! $page instanceof \WP_Post ) {
				continue;
			}

			$options[] = array(
				'value' => (string) $page->ID,
				'label' => sprintf(
					/* translators: 1: page title, 2: page status */
					__( '%1$s (%2$s)', 'thisismyurl-shadow' ),
							$page->post_title ? $page->post_title : sprintf(
								/* translators: %d: page ID. */
								__( 'Page #%d', 'thisismyurl-shadow' ),
								$page->ID
							),
					$page->post_status
				),
			);
		}

		return $options;
	}

	/**
	 * Build select options for existing nav menus.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function get_nav_menu_options(): array {
		$options = array();
		$menus   = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			if ( ! $menu instanceof \WP_Term ) {
				continue;
			}

			$options[] = array(
				'value' => (string) $menu->term_id,
				'label' => $menu->name,
			);
		}

		return $options;
	}

	/**
	 * Build select options for footer-style nav locations.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function get_footer_location_options(): array {
		$options    = array();
		$locations  = get_registered_nav_menus();
		$footer_map = self::get_footer_location_map();

		foreach ( array_keys( $footer_map ) as $location_key ) {
			$options[] = array(
				'value' => $location_key,
				'label' => isset( $locations[ $location_key ] ) ? (string) $locations[ $location_key ] : $location_key,
			);
		}

		return $options;
	}

	/**
	 * Get the primary nav location key used by the current theme.
	 *
	 * @return string
	 */
	private static function get_primary_nav_location_key(): string {
		$locations    = get_registered_nav_menus();
		$primary_keys = array( 'primary', 'main', 'header', 'top', 'main-menu', 'header-menu', 'primary-menu' );

		foreach ( $primary_keys as $key ) {
			if ( isset( $locations[ $key ] ) ) {
				return $key;
			}
		}

		$first_key = array_key_first( $locations );
		return is_string( $first_key ) ? $first_key : '';
	}

	/**
	 * Get footer-style nav locations for the current theme.
	 *
	 * @return array<string,string>
	 */
	private static function get_footer_location_map(): array {
		$locations = get_registered_nav_menus();
		$keywords  = array( 'footer', 'bottom', 'secondary', 'utility' );
		$matches   = array();

		foreach ( $locations as $location_key => $description ) {
			$needle = strtolower( $location_key . ' ' . $description );
			foreach ( $keywords as $keyword ) {
				if ( str_contains( $needle, $keyword ) ) {
					$matches[ $location_key ] = (string) $description;
					break;
				}
			}
		}

		return $matches;
	}

	/**
	 * Get requirement config for a finding.
	 *
	 * @param string $finding_id Finding/diagnostic ID.
	 * @return array<string,mixed>
	 */
	public static function get_for_finding( string $finding_id ): array {
		$all = self::get_all();
		return isset( $all[ $finding_id ] ) && is_array( $all[ $finding_id ] ) ? $all[ $finding_id ] : array();
	}

	/**
	 * Retrieve saved values for one finding.
	 *
	 * @param string $finding_id Finding/diagnostic ID.
	 * @return array<string,string>
	 */
	public static function get_saved_values( string $finding_id ): array {
		$stored = get_option( self::STORAGE_OPTION, array() );
		if ( ! is_array( $stored ) || ! isset( $stored[ $finding_id ] ) || ! is_array( $stored[ $finding_id ] ) ) {
			return array();
		}

		$values = array();
		foreach ( $stored[ $finding_id ] as $key => $value ) {
			$values[ sanitize_key( (string) $key ) ] = is_scalar( $value ) ? (string) $value : '';
		}

		return $values;
	}

	/**
	 * Persist normalized values for one finding.
	 *
	 * @param string              $finding_id Finding/diagnostic ID.
	 * @param array<string,mixed> $values     Normalized values.
	 * @return void
	 */
	public static function save_values( string $finding_id, array $values ): void {
		$stored = get_option( self::STORAGE_OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$stored[ $finding_id ] = $values;
		update_option( self::STORAGE_OPTION, $stored, false );
	}

	/**
	 * Validate and sanitize submitted values for a finding.
	 *
	 * @param string              $finding_id Finding/diagnostic ID.
	 * @param array<string,mixed> $submitted  Raw submitted values.
	 * @return array{success:bool, values:array<string,string>, message:string}
	 */
	public static function sanitize_values( string $finding_id, array $submitted ): array {
		$config = self::get_for_finding( $finding_id );
		$fields = isset( $config['fields'] ) && is_array( $config['fields'] ) ? $config['fields'] : array();

		if ( empty( $fields ) ) {
			return array(
				'success' => false,
				'values'  => array(),
				'message' => __( 'No input requirements are configured for this diagnostic.', 'thisismyurl-shadow' ),
			);
		}

		$values = array();
		foreach ( $fields as $field ) {
			$key      = isset( $field['key'] ) ? sanitize_key( (string) $field['key'] ) : '';
			$type     = isset( $field['type'] ) ? (string) $field['type'] : 'text';
			$required = ! empty( $field['required'] );
			$options  = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();

			if ( '' === $key ) {
				continue;
			}

			$raw = isset( $submitted[ $key ] ) ? $submitted[ $key ] : '';

			if ( 'toggle' === $type ) {
				$clean = rest_sanitize_boolean( $raw ) ? '1' : '0';
				if ( $required && '1' !== $clean ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please confirm all required toggle inputs before saving.', 'thisismyurl-shadow' ),
					);
				}
				$values[ $key ] = $clean;
				continue;
			}

			$clean = sanitize_text_field( (string) $raw );
			if ( $required && '' === $clean ) {
				return array(
					'success' => false,
					'values'  => array(),
					'message' => __( 'Please complete all required text inputs before saving.', 'thisismyurl-shadow' ),
				);
			}

			if ( ! empty( $options ) && in_array( $type, array( 'select', 'datalist' ), true ) ) {
				$allowed_values = array();
				foreach ( $options as $option ) {
					if ( is_array( $option ) && isset( $option['value'] ) ) {
						$allowed_values[] = (string) $option['value'];
					}
				}

				if ( '' !== $clean && ! empty( $allowed_values ) && ! in_array( $clean, $allowed_values, true ) ) {
					if ( 'datalist' !== $type ) {
						return array(
							'success' => false,
							'values'  => array(),
							'message' => __( 'Please choose one of the suggested values.', 'thisismyurl-shadow' ),
						);
					}
				}
			}

			if ( 'database-prefix-intentional' === $finding_id && 'new_prefix' === $key && '' !== $clean ) {
				if ( 1 !== preg_match( '/^[A-Za-z0-9_]+_$/', $clean ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Database prefix must contain only letters, numbers, underscores, and end with an underscore.', 'thisismyurl-shadow' ),
					);
				}
			}

			if ( 'timezone' === $finding_id && 'timezone_string' === $key && '' !== $clean ) {
				if ( ! in_array( $clean, timezone_identifiers_list(), true ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please enter a valid named timezone such as America/New_York or Europe/London.', 'thisismyurl-shadow' ),
					);
				}
			}

			if ( 'site-language-intentional' === $finding_id && 'site_language' === $key && '' !== $clean ) {
				if ( 1 !== preg_match( '/^[a-z]{2,3}(?:_[A-Z]{2})?$/', $clean ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please enter a valid locale code such as en_CA, fr_CA, or en_GB.', 'thisismyurl-shadow' ),
					);
				}
			}

			if ( in_array( $finding_id, array( 'front-page', 'homepage-displays-intentional' ), true ) && in_array( $key, array( 'homepage_page_id', 'posts_page_id' ), true ) ) {
				if ( '' !== $clean && ! ctype_digit( $clean ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please choose a valid published page from the list.', 'thisismyurl-shadow' ),
					);
				}

				if ( '' !== $clean ) {
					$page = get_post( (int) $clean );
					if ( ! $page instanceof \WP_Post || 'page' !== $page->post_type || 'publish' !== $page->post_status ) {
						return array(
							'success' => false,
							'values'  => array(),
							'message' => __( 'Please choose a published page for homepage-related settings.', 'thisismyurl-shadow' ),
						);
					}
				}
			}

			if ( in_array( $finding_id, array( 'about-page-published', 'contact-page-published' ), true ) && 'target_page_id' === $key && '' !== $clean && 'create_new' !== $clean ) {
				if ( ! ctype_digit( $clean ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please choose an existing page or create a new page.', 'thisismyurl-shadow' ),
					);
				}

				$page = get_post( (int) $clean );
				if ( ! $page instanceof \WP_Post || 'page' !== $page->post_type ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please choose a valid WordPress page.', 'thisismyurl-shadow' ),
					);
				}
			}

			if ( in_array( $finding_id, array( 'admin-email-domain-match', 'admin-email-deliverable' ), true ) && 'admin_email' === $key && '' !== $clean ) {
				if ( ! is_email( $clean ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please enter a valid email address.', 'thisismyurl-shadow' ),
					);
				}

				$domain = strtolower( (string) substr( strrchr( $clean, '@' ), 1 ) );
				$local  = strtolower( (string) strtok( $clean, '@' ) );

				if ( 'admin-email-domain-match' === $finding_id && self::is_free_email_domain( $domain ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please use an email address on your own domain rather than a free provider like Gmail or Outlook.', 'thisismyurl-shadow' ),
					);
				}

				if ( 'admin-email-deliverable' === $finding_id ) {
					if ( self::is_placeholder_email_domain( $domain ) ) {
						return array(
							'success' => false,
							'values'  => array(),
							'message' => __( 'Please use a real email domain rather than a placeholder like example.com or localhost.', 'thisismyurl-shadow' ),
						);
					}

					if ( self::is_generic_email_prefix( $local ) ) {
						return array(
							'success' => false,
							'values'  => array(),
							'message' => __( 'Please use a personally monitored mailbox rather than a generic alias like info@ or noreply@.', 'thisismyurl-shadow' ),
						);
					}
				}
			}

			if ( in_array( $finding_id, array( 'primary-navigation-assigned', 'footer-menu' ), true ) && in_array( $key, array( 'primary_menu_id', 'footer_menu_id' ), true ) && '' !== $clean ) {
				if ( ! ctype_digit( $clean ) || ! self::nav_menu_exists( (int) $clean ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please choose an existing WordPress menu from the list.', 'thisismyurl-shadow' ),
					);
				}
			}

			if ( 'footer-menu' === $finding_id && 'footer_location_key' === $key && '' !== $clean ) {
				$footer_locations = self::get_footer_location_map();
				if ( ! isset( $footer_locations[ $clean ] ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please choose a valid footer menu location from the list.', 'thisismyurl-shadow' ),
					);
				}
			}

			if ( in_array( $finding_id, array( 'site-icon', 'custom-logo-set' ), true ) && in_array( $key, array( 'site_icon_source', 'custom_logo_source' ), true ) && '' !== $clean ) {
				$attachment_id = self::resolve_image_attachment_id( $clean );

				if ( $attachment_id <= 0 ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please provide a Media Library image URL or numeric attachment ID for an existing image.', 'thisismyurl-shadow' ),
					);
				}

				$clean = (string) $attachment_id;
			}

			$values[ $key ] = $clean;
		}

		if ( in_array( $finding_id, array( 'front-page', 'homepage-displays-intentional' ), true ) ) {
			$homepage_page_id = isset( $values['homepage_page_id'] ) ? (string) $values['homepage_page_id'] : '';
			$posts_page_id    = isset( $values['posts_page_id'] ) ? (string) $values['posts_page_id'] : '';

			if ( '' === $homepage_page_id ) {
				return array(
					'success' => false,
					'values'  => array(),
					'message' => __( 'Please choose a published homepage page before saving.', 'thisismyurl-shadow' ),
				);
			}

			if ( '' !== $posts_page_id && $posts_page_id === $homepage_page_id ) {
				return array(
					'success' => false,
					'values'  => array(),
					'message' => __( 'Homepage and posts page must be different published pages.', 'thisismyurl-shadow' ),
				);
			}
		}

		if ( in_array( $finding_id, array( 'about-page-published', 'contact-page-published' ), true ) ) {
			$page_title = isset( $values['page_title'] ) ? (string) $values['page_title'] : '';
			$page_slug  = isset( $values['page_slug'] ) ? sanitize_title( (string) $values['page_slug'] ) : '';

			if ( '' === $page_slug ) {
				return array(
					'success' => false,
					'values'  => array(),
					'message' => __( 'Please provide a valid page slug using letters, numbers, and hyphens.', 'thisismyurl-shadow' ),
				);
			}

			$values['page_slug'] = $page_slug;

			if ( ! self::page_identity_matches_finding( $finding_id, $page_title, $page_slug ) ) {
				return array(
					'success' => false,
					'values'  => array(),
					'message' => 'about-page-published' === $finding_id
						? __( 'Use an About-style page title or slug such as About, About Us, or Our Story.', 'thisismyurl-shadow' )
						: __( 'Use a Contact-style page title or slug such as Contact, Contact Us, or Get in Touch.', 'thisismyurl-shadow' ),
				);
			}
		}

		if ( 'primary-navigation-assigned' === $finding_id && '' === self::get_primary_nav_location_key() ) {
			return array(
				'success' => false,
				'values'  => array(),
				'message' => __( 'The current theme does not expose a usable primary navigation location.', 'thisismyurl-shadow' ),
			);
		}

		if ( 'footer-menu' === $finding_id && empty( self::get_footer_location_map() ) ) {
			return array(
				'success' => false,
				'values'  => array(),
				'message' => __( 'The current theme does not expose a footer navigation location to assign.', 'thisismyurl-shadow' ),
			);
		}

		return array(
			'success' => true,
			'values'  => $values,
			'message' => __( 'Input requirements saved.', 'thisismyurl-shadow' ),
		);
	}

	/**
	 * Apply immediate option updates for fields that map to WP options.
	 *
	 * @param string              $finding_id Finding/diagnostic ID.
	 * @param array<string,mixed> $values     Sanitized values.
	 * @return array<int,string> Updated option names.
	 */
	public static function apply_immediate_updates( string $finding_id, array $values ): array {
		$config = self::get_for_finding( $finding_id );
		$fields = isset( $config['fields'] ) && is_array( $config['fields'] ) ? $config['fields'] : array();

		$updated = array();
		if ( in_array( $finding_id, array( 'about-page-published', 'contact-page-published' ), true ) ) {
			$page_id = self::create_or_update_page_from_inputs( $values );
			if ( $page_id > 0 ) {
				$updated[] = 'page_' . $page_id;
			}

			return $updated;
		}

		if ( 'custom-logo-set' === $finding_id ) {
			$attachment_id = isset( $values['custom_logo_source'] ) ? (int) $values['custom_logo_source'] : 0;
			if ( $attachment_id > 0 ) {
				set_theme_mod( 'custom_logo', $attachment_id );
				$updated[] = 'custom_logo';
			}

			return $updated;
		}

		if ( in_array( $finding_id, array( 'front-page', 'homepage-displays-intentional' ), true ) ) {
			$homepage_page_id = isset( $values['homepage_page_id'] ) ? (int) $values['homepage_page_id'] : 0;
			$posts_page_id    = isset( $values['posts_page_id'] ) ? (int) $values['posts_page_id'] : 0;

			if ( $homepage_page_id > 0 ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $homepage_page_id );
				$updated[] = 'show_on_front';
				$updated[] = 'page_on_front';
			}

			update_option( 'page_for_posts', $posts_page_id );
			$updated[] = 'page_for_posts';

			return $updated;
		}

		if ( 'primary-navigation-assigned' === $finding_id ) {
			$menu_id      = isset( $values['primary_menu_id'] ) ? (int) $values['primary_menu_id'] : 0;
			$location_key = self::get_primary_nav_location_key();

			if ( $menu_id > 0 && '' !== $location_key ) {
				self::assign_nav_menu_to_location( $location_key, $menu_id );
				$updated[] = 'nav_menu_locations';
			}

			return $updated;
		}

		if ( 'footer-menu' === $finding_id ) {
			$menu_id      = isset( $values['footer_menu_id'] ) ? (int) $values['footer_menu_id'] : 0;
			$location_key = isset( $values['footer_location_key'] ) ? sanitize_key( (string) $values['footer_location_key'] ) : '';

			if ( $menu_id > 0 && '' !== $location_key ) {
				self::assign_nav_menu_to_location( $location_key, $menu_id );
				$updated[] = 'nav_menu_locations';
			}

			return $updated;
		}

		foreach ( $fields as $field ) {
			$key         = isset( $field['key'] ) ? sanitize_key( (string) $field['key'] ) : '';
			$apply_option = isset( $field['apply_option'] ) ? sanitize_key( (string) $field['apply_option'] ) : '';

			if ( '' === $key || '' === $apply_option || ! isset( $values[ $key ] ) ) {
				if ( 'site-icon' !== $finding_id || 'site_icon_source' !== $key || ! isset( $values[ $key ] ) ) {
					continue;
				}
			}

			if ( 'site-icon' === $finding_id && 'site_icon_source' === $key ) {
				$attachment_id = (int) $values[ $key ];
				if ( $attachment_id > 0 ) {
					update_option( 'site_icon', $attachment_id );
					$updated[] = 'site_icon';
				}
				continue;
			}

			$option_value = sanitize_text_field( (string) $values[ $key ] );
			if ( 'site-language-intentional' === $finding_id && 'site_language' === $key ) {
				update_option( 'WPLANG', $option_value );
				$updated[] = 'WPLANG';
				continue;
			}
			update_option( $apply_option, $option_value );
			if ( 'timezone' === $finding_id && 'timezone_string' === $key ) {
				delete_option( 'gmt_offset' );
				$updated[] = 'gmt_offset';
			}
			$updated[] = $apply_option;
		}

		return $updated;
	}

	/**
	 * Determine whether a domain is a common free-mail provider.
	 *
	 * @param string $domain Email domain.
	 * @return bool
	 */
	private static function is_free_email_domain( string $domain ): bool {
		$free_providers = array(
			'gmail.com',
			'googlemail.com',
			'yahoo.com',
			'yahoo.co.uk',
			'ymail.com',
			'hotmail.com',
			'hotmail.co.uk',
			'outlook.com',
			'live.com',
			'msn.com',
			'icloud.com',
			'me.com',
			'mac.com',
			'aol.com',
			'protonmail.com',
			'proton.me',
			'zohomail.com',
			'zoho.com',
			'mail.com',
			'inbox.com',
			'gmx.com',
		);

		return in_array( strtolower( $domain ), $free_providers, true );
	}

	/**
	 * Determine whether a domain is a placeholder domain.
	 *
	 * @param string $domain Email domain.
	 * @return bool
	 */
	private static function is_placeholder_email_domain( string $domain ): bool {
		return in_array(
			strtolower( $domain ),
			array( 'example.com', 'example.org', 'example.net', 'test.com', 'localhost' ),
			true
		);
	}

	/**
	 * Determine whether an email local-part is a generic alias.
	 *
	 * @param string $local_part Email local-part before @.
	 * @return bool
	 */
	private static function is_generic_email_prefix( string $local_part ): bool {
		return in_array(
			strtolower( $local_part ),
			array( 'info', 'admin', 'webmaster', 'no-reply', 'noreply', 'postmaster', 'mail', 'contact' ),
			true
		);
	}

	/**
	 * Resolve a media-library image input into an attachment ID.
	 *
	 * @param string $value Attachment ID or image URL.
	 * @return int
	 */
	private static function resolve_image_attachment_id( string $value ): int {
		$attachment_id = 0;
		if ( ctype_digit( $value ) ) {
			$attachment_id = (int) $value;
		} elseif ( function_exists( 'attachment_url_to_postid' ) ) {
			$attachment_id = (int) attachment_url_to_postid( $value );
		}

		if ( $attachment_id <= 0 || ! wp_attachment_is_image( $attachment_id ) ) {
			return 0;
		}

		return $attachment_id;
	}

	/**
	 * Determine whether a nav menu exists.
	 *
	 * @param int $menu_id Menu term ID.
	 * @return bool
	 */
	private static function nav_menu_exists( int $menu_id ): bool {
		$menu = wp_get_nav_menu_object( $menu_id );
		return $menu instanceof \WP_Term;
	}

	/**
	 * Assign a menu to a registered theme location.
	 *
	 * @param string $location_key Theme location key.
	 * @param int    $menu_id      Menu term ID.
	 * @return void
	 */
	private static function assign_nav_menu_to_location( string $location_key, int $menu_id ): void {
		$locations                  = get_nav_menu_locations();
		$locations[ $location_key ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Check whether a page title or slug matches the expected finding keywords.
	 *
	 * @param string $finding_id Finding slug.
	 * @param string $page_title Proposed page title.
	 * @param string $page_slug  Proposed page slug.
	 * @return bool
	 */
	private static function page_identity_matches_finding( string $finding_id, string $page_title, string $page_slug ): bool {
		$keywords = array();
		if ( 'about-page-published' === $finding_id ) {
			$keywords = array( 'about', 'about-us', 'our-story', 'who-we-are', 'meet-the-team', 'our-team', 'company', 'about-me' );
		} elseif ( 'contact-page-published' === $finding_id ) {
			$keywords = array( 'contact', 'contact-us', 'get-in-touch', 'reach-us', 'work-with-us', 'hire-us', 'enquiry', 'inquiry', 'support', 'help' );
		}

		$title = strtolower( $page_title );
		$slug  = strtolower( $page_slug );

		foreach ( $keywords as $keyword ) {
			if ( str_contains( $slug, $keyword ) || str_contains( $title, str_replace( '-', ' ', $keyword ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Create or update a page from sanitized page-input values.
	 *
	 * @param array<string,mixed> $values Sanitized values.
	 * @return int
	 */
	private static function create_or_update_page_from_inputs( array $values ): int {
		$page_id_raw = isset( $values['target_page_id'] ) ? (string) $values['target_page_id'] : 'create_new';
		$postarr     = array(
			'post_title'     => isset( $values['page_title'] ) ? sanitize_text_field( (string) $values['page_title'] ) : '',
			'post_name'      => isset( $values['page_slug'] ) ? sanitize_title( (string) $values['page_slug'] ) : '',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => get_current_user_id() ?: 1,
			'comment_status' => 'closed',
		);

		if ( ctype_digit( $page_id_raw ) && (int) $page_id_raw > 0 ) {
			$postarr['ID'] = (int) $page_id_raw;
			return (int) wp_update_post( $postarr );
		}

		return (int) wp_insert_post( $postarr );
	}
}
