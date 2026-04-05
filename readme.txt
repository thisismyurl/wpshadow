=== WP Shadow ===
Contributors: thisismyurl
Tags: diagnostics, site-health, security, performance, accessibility
Requires at least: 6.4
Requires PHP: 8.1
Tested up to: 6.6
Stable tag: 0.6093.1200
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Local-first WordPress diagnostics, safer remediation workflows, file review, and recovery tooling with accessibility-first guidance.

== Description ==

WP Shadow is a WordPress plugin for understanding site health, reviewing problems clearly, and making safer changes.

This first public release is a beta focused on the core plugin experience:

* 230 display-ready diagnostics across 11 categories
* 93 automated treatments with apply and undo support
* 8 guidance-only treatments for changes that should stay manual
* dashboard views for findings, trends, and status
* file-write review for risky changes
* local backup and recovery workflows
* WordPress Site Health integration
* WP-CLI commands for listing diagnostics, running scans, applying treatments, and exporting readiness data
* accessibility-first, plain-English guidance

WP Shadow Core runs locally and does not require registration or a cloud account.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wpshadow/` directory, or install the plugin through WordPress.
2. Activate the plugin through the Plugins screen in WordPress.
3. Open the WP Shadow dashboard from the WordPress admin menu.
4. Review findings and apply safe fixes where appropriate.

== Frequently Asked Questions ==

= Is this a beta release? =

Yes. This is the first public beta release of WP Shadow. The beta is intended for real-world use and feedback while the team continues to polish workflows, copy, and recovery paths.

= Does WP Shadow require an account or cloud service? =

No. WP Shadow Core runs locally. The current public beta does not require registration, a paid plan, or a cloud connection.

= What kinds of issues does it check? =

WP Shadow includes diagnostics across accessibility, code quality, database health, design, monitoring, performance, security, SEO, settings, WordPress health, and workflows.

= Does it make changes automatically? =

Some fixes can be applied through the treatment system. Lower-risk changes can be automated with apply and undo support. Higher-risk changes are designed to be reviewed more carefully, and some actions are guidance-only by design.

= Does it support multisite? =

WP Shadow includes multisite-aware admin behavior and capability handling. As with any beta, multisite administrators should test changes carefully before wide rollout.

= Is accessibility taken seriously? =

Yes. WP Shadow is built around clearer language, keyboard-friendly workflows, screen-reader-aware structure, and lower-stress recovery paths. Accessibility issues should be treated as product bugs, not polish.

= Does it send my data to third parties? =

Not by default. The core plugin is local-first. Optional future services, if introduced, must remain opt-in and clearly explained.

== Screenshots ==

1. WP Shadow dashboard overview
2. Diagnostics inventory and findings views
3. Treatment and file review workflows
4. Backup and recovery interface

== Changelog ==

= 0.6093.1200 =
* First public beta release of WP Shadow.
* Aligned public documentation with the current plugin scope and philosophy.
* Refined diagnostics, treatment, file-review, and recovery messaging for public release.
* Continued hardening of core safety boundaries and admin workflows.

= 0.6035.0948 =
* Expanded core diagnostics and release-readiness work.

= 0.6030.0750 =
* Initial development release.

== Support ==

See the project documentation and support policy in the repository support materials.

== License ==

This plugin is licensed under GPL v2 or later.
