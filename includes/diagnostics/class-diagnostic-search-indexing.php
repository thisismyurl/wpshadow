<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Search_Indexing extends Diagnostic_Base {

	protected function get_id(): string {
		return 'search-indexing';
	}

	protected function get_title(): string {
		return __( 'Search Engine Indexing', 'wpshadow' );
	}

	protected function get_description(): string {
		return __( 'Checks if search engines are blocked from indexing the site.', 'wpshadow' );
	}

	protected function get_category(): string {
		return 'seo';
	}

	protected function get_severity(): string {
		return 'critical';
	}

	protected function is_auto_fixable(): bool {
		return true;
	}

	public function check(): ?array {
		$blog_public = get_option( 'blog_public' );

		if ( '1' === $blog_public || 1 === $blog_public ) {
			return null;
		}

		return array(
			'finding_id'   => $this->get_id(),
			'title'        => $this->get_title(),
			'description'  => __( 'Search engines are blocked from indexing this site! The "Discourage search engines" setting is enabled. This is often accidentally left on after development and prevents the site from appearing in Google. Your site is invisible to search engines.', 'wpshadow' ),
			'category'     => $this->get_category(),
			'severity'     => $this->get_severity(),
			'threat_level' => 98,
			'auto_fixable' => $this->is_auto_fixable(),
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
