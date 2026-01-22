<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Redirect_Chain_Optimization {
    public static function check() {
        return ['id' => 'seo-redirect-chains', 'title' => __('Redirect Chain Optimization', 'wpshadow'), 'description' => __('Finds redirect chains (page A → B → C) that waste crawl budget. Direct redirects (A → C) preserve crawl efficiency and link equity better than chains.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/redirects/', 'training_link' => 'https://wpshadow.com/training/redirect-strategy/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
