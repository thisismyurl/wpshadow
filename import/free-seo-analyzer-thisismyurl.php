<?php
/**
 * Plugin Name: Free All-in-One SEO Analyzer by thisismyurl
 * Plugin URI: https://thisismyurl.com
 * Description: SEO Command Center v15.1: Real-time suite status updates during the forensic audit process.
 * Version: 15.1.0
 * Author: thisismyurl
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// [Admin Menu and Assets remain consistent with v15.0.0]
add_action( 'admin_menu', 'fisa_aio_menu_v15' );
function fisa_aio_menu_v15() {
    $page = add_management_page( 'SEO Analyzer', 'SEO Analyzer', 'manage_options', 'seo-analyzer', 'fisa_render_aio_page' );
    add_action( "admin_print_scripts-$page", function() { wp_enqueue_script('jquery'); });
}

function fisa_aio_assets() {
    $nonce = wp_create_nonce('fisa_scan_nonce');
    ?>
    <style>
        :root { --fisa-crit: #d63638; --fisa-high: #dba617; --fisa-med: #72aee6; --fisa-low: #00a32a; }
        .fisa-card { background: #fff; border: 1px solid #ccd0d4; padding: 25px; margin-top: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .fisa-status-pill { font-size: 10px; text-transform: uppercase; padding: 2px 8px; border-radius: 10px; background: #eee; color: #666; font-weight: bold; }
        .status-ready { background: #eff8ff; color: #2271b1; }
        .status-working { background: #fffcf5; color: #dba617; border: 1px solid #dba617; }
        .fisa-issue-tag { display: inline-block; margin: 1px; font-size: 10px; padding: 2px 6px; border-radius: 3px; border: 1px solid #ccc; font-weight: 600; }
        .tag-crit { color: var(--fisa-crit); border-color: var(--fisa-crit); background: #fcf0f1; }
        .tag-warn { color: var(--fisa-high); border-color: var(--fisa-high); background: #fffcf5; }
        .tag-success { color: var(--fisa-low); border-color: var(--fisa-low); background: #f0fff4; }
        .fisa-actions-cell { white-space: nowrap; width: 200px; text-align: right; }
        #fisa-inventory-loader { display:none; margin-left: 10px; vertical-align: middle; }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        const fisaNonce = '<?php echo $nonce; ?>';
        const suites = [
            { id: 'metadata', name: 'Metadata & Content' },
            { id: 'technical', name: 'Technical SEO' },
            { id: 'performance', name: 'Performance' },
            { id: 'images', name: 'Image Forensics' },
            { id: 'security', name: 'Security & Social' },
            { id: 'semantic', name: 'Semantic Quality' },
            { id: 'ux', name: 'Conversion & UX' },
            { id: 'authority', name: 'Authority & Links' },
            { id: 'hygiene', name: 'Code Hygiene' }
            ];
        // Load Cached Data on Page Load
        $.post(ajaxurl, { action: 'fisa_get_cached_inventory', security: fisaNonce }, function(r) {
            if(r.success && r.data.items.length > 0) {
                renderInventoryTable(r.data.items);
                $('.fisa-table-container').show();
                $('#fisa-fetch-inventory').text('Clear Cache & Rebuild');
            }
        });

        $('#fisa-fetch-inventory, #fisa-rebuild-inventory').on('click', function(e) {
            e.preventDefault();
            const btn = $(this);
            btn.prop('disabled', true).text('Rebuilding Cache...');
            $('#fisa-inventory-loader').show();
            
            $.post(ajaxurl, { action: 'fisa_build_priority_inventory', security: fisaNonce }, function(r) {
                if(r.success) {
                    renderInventoryTable(r.data.items);
                    $('.fisa-table-container').fadeIn();
                    btn.text('Clear Cache & Rebuild').prop('disabled', false);
                    $('#fisa-inventory-loader').hide();
                }
            });
        });

        function renderInventoryTable(items) {
            let html = '';
            items.forEach((item, index) => {
                let statusHtml = item.issues_html ? item.issues_html : '<span class="fisa-status-pill status-ready">Ready</span>';
                html += `
                <tr id="fisa-row-${index}" data-url="${item.url}">
                    <td><strong>${item.title}</strong><br><small><a href="${item.url}" target="_blank">${item.url}</a></small></td>
                    <td class="fisa-results-cell">${statusHtml}</td>
                    <td class="fisa-actions-cell">
                        <button class="button fisa-run-test" data-index="${index}">${item.issues_html ? 'Refetch' : 'Run Audit'}</button>
                    </td>
                </tr>`;
            });
            $('#fisa-results-body').html(html);
        }

        $(document).on('click', '.fisa-run-test', async function(e) {
            e.preventDefault();
            const btn = $(this);
            const index = btn.data('index');
            const row = $(`#fisa-row-${index}`);
            const url = row.data('url');
            const resultsCell = row.find('.fisa-results-cell');

            btn.prop('disabled', true).text('Analyzing...');
            
            let urlIssues = [];
            for (const suite of suites) {
                // Update Results cell with current suite name
                resultsCell.html(`<span class="fisa-status-pill status-working">Running ${suite.name}...</span>`);
                
                try {
                    const r = await $.post(ajaxurl, { 
                        action: 'fisa_run_suite_audit', 
                        security: fisaNonce, 
                        url: url, 
                        suite: suite.id 
                    });
                    if (r.success && r.data.issues) {
                        urlIssues = urlIssues.concat(r.data.issues);
                    }
                } catch (err) {
                    console.error("Suite failed: " + suite.name);
                }
            }

            let issuesHtml = (urlIssues.length === 0) ? 
                '<span class="fisa-issue-tag tag-success">✅ 100-Point Pass</span>' : 
                [...new Set(urlIssues)].join(' ');
            
            // Save final result to Persistent Cache
            $.post(ajaxurl, { 
                action: 'fisa_update_item_cache', 
                security: fisaNonce, 
                url: url, 
                issues_html: issuesHtml 
            });

            resultsCell.html(issuesHtml);
            btn.prop('disabled', false).text('Refetch');
        });
    });
    </script>
    <?php
}

/**
 * 2. AJAX & Caching Logic
 */
add_action( 'wp_ajax_fisa_get_cached_inventory', function() {
    check_ajax_referer('fisa_scan_nonce', 'security');
    $cache = get_transient('fisa_audit_cache');
    wp_send_json_success(['items' => $cache ? $cache : []]);
});

add_action( 'wp_ajax_fisa_update_item_cache', function() {
    check_ajax_referer('fisa_scan_nonce', 'security');
    $url = esc_url_raw($_POST['url']);
    $issues_html = wp_kses_post($_POST['issues_html']);
    $cache = get_transient('fisa_audit_cache');
    
    if($cache) {
        foreach($cache as &$item) {
            if($item['url'] === $url) $item['issues_html'] = $issues_html;
        }
        set_transient('fisa_audit_cache', $cache, 7 * DAY_IN_SECONDS);
    }
    wp_send_json_success();
});

add_action( 'wp_ajax_fisa_build_priority_inventory', function() {
    check_ajax_referer('fisa_scan_nonce', 'security');
    delete_transient('fisa_audit_cache');
    
    $inventory = [];
    $critical = [['title' => 'Homepage', 'url' => home_url('/')]];
    foreach($critical as $c) $inventory[$c['url']] = ['title' => $c['title'], 'url' => $c['url'], 'issues_html' => ''];

    $items = get_posts(['post_type' => ['page', 'post'], 'posts_per_page' => 24, 'post_status' => 'publish']);
    foreach($items as $i) {
        $url = get_permalink($i->ID);
        if(!isset($inventory[$url])) $inventory[$url] = ['title' => get_the_title($i->ID), 'url' => $url, 'issues_html' => ''];
        if(count($inventory) >= 25) break;
    }

    $final = array_values($inventory);
    set_transient('fisa_audit_cache', $final, 7 * DAY_IN_SECONDS);
    wp_send_json_success(['items' => $final]);
});

/**
 * 3. Suite Audit Logic
 */
add_action( 'wp_ajax_fisa_run_suite_audit', function() {
    check_ajax_referer('fisa_scan_nonce', 'security');
    $url = esc_url_raw($_POST['url']);
    $suite = sanitize_text_field($_POST['suite']);
    
    $res = wp_remote_get($url, ['timeout' => 15]);
    if(is_wp_error($res)) wp_send_json_error();

    $html = wp_remote_retrieve_body($res);
    $issues = [];

    switch($suite) {
        case 'metadata':
            // 1. TITLE FORENSICS & CLICK-THROUGH OPTIMIZATION
            if (preg_match('/<title>(.*?)<\/title>/is', $html, $title_m)) {
                $title = trim($title_m[1]);
                $title_len = strlen($title);
                $extra_data['title'] = $title;

                // Length & Branding
                if ($title_len < 30) $issues[] = '<span class="fisa-issue-tag tag-warn">Short Title ('.$title_len.')</span>';
                if ($title_len > 65) $issues[] = '<span class="fisa-issue-tag tag-warn">Long Title ('.$title_len.')</span>';
                
                // Brand Consistency: Check if the title ends with a brand separator like | or -
                if (!preg_match('/[\|\-\»\•]/', $title)) {
                    $issues[] = '<span class="fisa-issue-tag tag-info">Missing Brand Separator</span>';
                }

                // Stop Word Bloat: Titles with too many "and, the, of" waste space
                $stop_words = ['and', 'the', 'for', 'with', 'from', 'that', 'this'];
                $words = explode(' ', strtolower($title));
                $stop_count = count(array_intersect($words, $stop_words));
                if ($stop_count > 3) $issues[] = '<span class="fisa-issue-tag tag-info">Title Stop-word Bloat</span>';

                // Sentiment/Click-bait: Check for "Power Words" or Numbers (High CTR signals)
                if (!preg_match('/[0-9%]/', $title) && !preg_match('/(best|top|guide|how|tips|free)/i', $title)) {
                    $issues[] = '<span class="fisa-issue-tag tag-info">Low Engagement Title</span>';
                }
            } else {
                $issues[] = '<span class="fisa-issue-tag tag-crit">No Title Tag</span>';
            }

            // 2. SOCIAL MEDIA & RICH SNIPPET FORENSICS
            // Missing OG:Locale can affect localized search in Canada
            if (!strpos($html, 'og:locale')) $issues[] = '<span class="fisa-issue-tag tag-info">Missing OG:Locale</span>';
            
            // Check for OG:Site_Name (Important for Brand Identity)
            if (!strpos($html, 'og:site_name')) $issues[] = '<span class="fisa-issue-tag tag-info">Missing OG:Site_Name</span>';

            // 3. WORDPRESS HEAD CLEANLINESS
            // WP-JSON link exposure (often unnecessary for standard pages)
            if (strpos($html, 'rel="https://api.w.org/"')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">REST API Link Exposed</span>';
            }
            
            // Emoji Script Bloat: WordPress loads an extra JS file for emojis by default
            if (strpos($html, 'window._wpemojiSettings')) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Legacy Emoji Scripts</span>';
            }

            // 4. ADVANCED INDEXING & SEARCH DIRECTIVES
            // Checking for X-Robots-Tag (If headers are available)
            if (isset($headers['x-robots-tag']) && strpos($headers['x-robots-tag'], 'noindex') !== false) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Header-Level Noindex</span>';
            }

            // 5. THEME-LEVEL META ERRORS
            // Look for hardcoded "Just another WordPress site" tagline
            if (strpos($html, 'Just another WordPress site')) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Default Tagline Found</span>';
            }

            // 6. H1 Tags
            $h1_count = preg_match_all('/<h1/i', $html, $matches);
            if ($h1_count == 0) $issues[] = '<span class="fisa-issue-tag tag-crit">Missing H1 Heading</span>';
            if ($h1_count > 1) $issues[] = '<span class="fisa-issue-tag tag-warn">Multiple H1 Tags Found</span>';

            // 7. Canonical Tag
            if (!strpos($html, 'rel="canonical"')) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Missing Canonical Tag</span>';
            }

            // 8. Robots Meta
            if (preg_match('/<meta name="robots" content="(.*?)"/is', $html, $robo_m)) {
                if (strpos($robo_m[1], 'noindex') !== false) $issues[] = '<span class="fisa-issue-tag tag-crit">Hidden from Search (noindex)</span>';
            }

            // 9. Open Graph (Social)
            if (!strpos($html, 'og:title')) $issues[] = '<span class="fisa-issue-tag tag-info">Missing OG:Title</span>';
            if (!strpos($html, 'og:description')) $issues[] = '<span class="fisa-issue-tag tag-info">Missing OG:Description</span>';
            if (!strpos($html, 'og:image')) $issues[] = '<span class="fisa-issue-tag tag-high">No Social Share Image</span>';

            // 10. Twitter Cards
            if (!strpos($html, 'twitter:card')) $issues[] = '<span class="fisa-issue-tag tag-info">No Twitter Card Meta</span>';

            // 11. Favicon
            if (!strpos($html, 'rel="icon"') && !strpos($html, 'rel="shortcut icon"')) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Missing Favicon</span>';
            }

            // 12. Viewport (Mobile SEO)
            if (!strpos($html, 'name="viewport"')) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Not Mobile Responsive</span>';
            }

            // 13. Charset
            if (!strpos($html, 'charset="UTF-8"') && !strpos($html, 'charset="utf-8"')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Charset Not Defined</span>';
            }

            // 14. Language Attribute
            if (!strpos($html, 'lang=')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Missing HTML Lang Attr</span>';
            }

            // 15. Schema / Structured Data
            if (!strpos($html, 'application/ld+json')) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Missing Schema Markup</span>';
            }
            break;


        case 'technical':
            // --- 1. SERVER & COMPRESSION (CORE WEB VITALS) ---
            // Checking if the server is compressing data for faster delivery
            if (empty($headers['content-encoding'])) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">No GZIP/Brotli</span>';
            }
            // Check for modern HTTP/2 or HTTP/3 protocols
            $protocol = $headers['http_version'] ?? '';
            if ($protocol && $protocol < 2.0) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Legacy HTTP/1.1</span>';
            }

            // --- 2. WORDPRESS RESOURCE BLOAT ---
            // WordPress often loads styles for Gutenberg blocks even if they aren't used
            if (strpos($html, 'wp-block-library-css')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Gutenberg Style Bloat</span>';
            }
            // Check for excessive CSS files (Theme bloat)
            $css_count = preg_match_all('/<link rel=["\']stylesheet["\']/i', $html, $m);
            if ($css_count > 15) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Excessive CSS (' . $css_count . ')</span>';
            }
            // Check for excessive JS files (Plugin bloat)
            $js_count = preg_match_all('/<script src=/i', $html, $m);
            if ($js_count > 20) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Excessive JS (' . $js_count . ')</span>';
            }

            // --- 3. SECURITY & HEADERS ---
            // SSL Mixed Content Check (Already exists, but refined)
            if (is_ssl() && preg_match('/src=["\']http:\/\//i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">🔒 Mixed Content</span>';
            }
            // Checking for basic Security Headers that Google favors
            if (empty($headers['x-frame-options'])) {
                $issues[] = '<span class="fisa-issue-tag tag-info">No Clickjack Protection</span>';
            }
            if (empty($headers['strict-transport-security'])) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">HSTS Not Enabled</span>';
            }

            // --- 4. CRAWLABILITY & REDIRECTS ---
            // Check for inline CSS which prevents caching
            if (strpos($html, '<style') && strlen($html) > 50000) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Heavy Inline CSS</span>';
            }
            // Check for 'nofollow' links that might be bleeding authority
            if (preg_match_all('/rel=["\']nofollow["\']/i', $html, $m) > 10) {
                $issues[] = '<span class="fisa-issue-tag tag-info">High Nofollow Count</span>';
            }

            // --- 5. MOBILE & UX ASSETS ---
            // Touch targets/Scaling (Viewport check refined)
            if (!strpos($html, 'width=device-width')) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Viewport Scale Error</span>';
            }
            // Check for Apple Touch Icon (Mobile branding)
            if (!strpos($html, 'apple-touch-icon')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">No Apple Touch Icon</span>';
            }

            // --- 6. DOM COMPLEXITY ---
            // If the HTML is massive, it slows down mobile rendering
            $kb_size = strlen($html) / 1024;
            if ($kb_size > 150) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Large DOM (' . round($kb_size) . 'KB)</span>';
            }
            break;

            // --- 7. WORDPRESS CORE FORENSICS ---
            
            // A. Comment Link Bloat
            // WordPress adds 'rel=desc' or 'rel=next' to comment pagination which can leak crawl budget
            if (strpos($html, 'rel=\'prev\'') || strpos($html, 'rel=\'next\'')) {
                if (strpos($url, '/comment-page-')) {
                    $issues[] = '<span class="fisa-issue-tag tag-warn">Comment Pagination Leak</span>';
                }
            }

            // B. JQuery Migrate Detection
            // Most modern themes don't need jquery-migrate.min.js. It's a redundant request.
            if (strpos($html, 'jquery-migrate.min.js')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">JQuery Migrate Bloat</span>';
            }

            // C. XML-RPC Exposure
            // If the RSD link is present, XML-RPC is likely active. This is a primary target for DDoS/Brute Force.
            if (strpos($html, 'edituri')) {
                $issues[] = '<span class="fisa-issue-tag tag-high">XML-RPC (EditURI) Exposed</span>';
            }

            // D. Embed Script Bloat (wp-embed.min.js)
            // WordPress loads a script to allow others to "embed" your posts. 99% of local sites don't need this.
            if (strpos($html, 'wp-embed.min.js')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">WP Embed Script Bloat</span>';
            }

            // E. WooCommerce Fragmentation (If applicable)
            // WooCommerce often loads 'cart-fragments' on every page, which slows down non-shop pages.
            if (strpos($html, 'wc-cart-fragments')) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">WooCommerce Frag Bloat</span>';
            }

            // F. Theme/Plugin Path Exposure
            // Checking if the code leaks /wp-content/themes/ folder names (Security through Obscurity)
            if (preg_match('/\/wp-content\/themes\/([a-zA-Z0-9\-_]+)\//', $html, $theme_match)) {
                // If the theme name contains 'v1' or 'beta' or 'test', flag it.
                if (preg_match('/(v[0-9]|beta|test|temp)/i', $theme_match[1])) {
                    $issues[] = '<span class="fisa-issue-tag tag-info">Dev Theme Path Visible</span>';
                }
            }

            // G. Heartbeat API Frequency
            // If the script sees 'wp-auth-check', the Heartbeat API is firing, which can strain cheap hosting.
            if (strpos($html, 'wp-auth-check')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Heartbeat API Active</span>';
            }

// --- 8. GREEDY PLUGIN FORENSICS ---
            
            // A. Contact Form 7 (Commonly loads on every page)
            if (strpos($html, 'contact-form-7-css') || strpos($html, 'wpcf7-scripts')) {
                // If we are NOT on a contact page, flag it as bloat
                if (!preg_match('/(contact|get-a-quote|hire|estimate)/i', $url)) {
                    $issues[] = '<span class="fisa-issue-tag tag-high">CF7 Plugin Bloat</span>';
                }
            }

            // B. Elementor/Page Builder Bloat
            // Page builders often load massive icon libraries (FontAwesome) even for one icon
            if (strpos($html, 'elementor-icons-fa-solid') || strpos($html, 'fontawesome')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Heavy Icon Library Loaded</span>';
            }

            // C. Slider Revolution / LayerSlider
            // These are notorious for "Render Blocking" JS in the header
            if (strpos($html, 'revslider') || strpos($html, 'layerslider')) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Heavy Slider Script Found</span>';
            }

            // D. Social Sharing Plugins (AddThis, ShareThis, etc.)
            // These often load heavy external JS that tracks users and slows the DOM
            if (preg_match('/(addthis\.com|sharethis\.com|sumome)/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Third-party Tracker Bloat</span>';
            }

            // E. Excessive Stylesheet Detection
            // If the total number of <style> and <link rel='stylesheet'> tags is too high
            $total_styles = preg_match_all('/(<style|<link rel=["\']stylesheet)/i', $html, $m);
            if ($total_styles > 20) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Excessive CSS Requests (' . $total_styles . ')</span>';
            }

            // F. Non-Critical CSS in Head
            // If the <head> contains more than 50KB of inline CSS, it delays rendering
            preg_match('/<head>(.*?)<\/head>/is', $html, $head_match);
            if (isset($head_match[1])) {
                $head_css_size = strlen($head_match[1]);
                if ($head_css_size > 50000) { // 50KB
                    $issues[] = '<span class="fisa-issue-tag tag-warn">Heavy Head Bloat ('.round($head_css_size/1024).'KB)</span>';
                }
            }

            break;

        case 'images':
            
            case 'images':
            preg_match_all('/<img[^>]+>/i', $html, $img_tags);
            $total_images = count($img_tags[0]);
            
            if ($total_images === 0) {
                $issues[] = '<span class="fisa-issue-tag tag-info">No Images Found</span>';
                break;
            }

            $has_missing_alt = false;
            $has_missing_dimensions = false;
            $has_non_responsive = false;
            $has_legacy_format = false;
            $has_hero_lazy = false;
            $img_count = 0;

            foreach ($img_tags[0] as $tag) {
                $img_count++;
                
                // 1. ALT TEXT INTEGRITY (Accessibility & SEO)
                if (!preg_match('/alt=["\']([^"\']+)["\']/i', $tag)) {
                    $has_missing_alt = true;
                }

                // 2. CORE WEB VITALS: DIMENSION CHECK (CLS Prevention)
                if (!strpos($tag, 'width=') || !strpos($tag, 'height=')) {
                    $has_missing_dimensions = true;
                }

                // 3. RESPONSIVE INTEGRITY (srcset)
                // If a WP image lacks srcset, it's not serving scaled sizes to mobile
                if (!strpos($tag, 'srcset=')) {
                    $has_non_responsive = true;
                }

                // 4. NEXT-GEN FORMAT CHECK
                // Flagging JPG/PNG to encourage WebP/AVIF adoption
                if (preg_match('/\.(jpg|jpeg|png)/i', $tag) && !strpos($tag, '.webp') && !strpos($tag, '.avif')) {
                    $has_legacy_format = true;
                }

                // 5. HERO IMAGE LAZY-LOAD ERROR (LCP Optimization)
                // The first image (Hero) should NOT be lazy loaded
                if ($img_count === 1 && strpos($tag, 'loading="lazy"')) {
                    $has_hero_lazy = true;
                }

                // 6. DECORATIVE IMAGE CHECK
                // Images with empty alt="" should have role="presentation" or aria-hidden
                if (strpos($tag, 'alt=""') && !strpos($tag, 'aria-hidden') && !strpos($tag, 'role=')) {
                    $issues[] = '<span class="fisa-issue-tag tag-info">Decorative Img Accessibility</span>';
                }
            }

            // --- Aggregate Forensic Reports ---
            if ($has_missing_alt) $issues[] = '<span class="fisa-issue-tag tag-crit">Missing Alt Text</span>';
            if ($has_missing_dimensions) $issues[] = '<span class="fisa-issue-tag tag-high">CLS Risk (No Width/Height)</span>';
            if ($has_non_responsive) $issues[] = '<span class="fisa-issue-tag tag-warn">Non-Responsive (No srcset)</span>';
            if ($has_legacy_format) $issues[] = '<span class="fisa-issue-tag tag-info">Legacy Format (Use WebP)</span>';
            if ($has_hero_lazy) $issues[] = '<span class="fisa-issue-tag tag-crit">Hero Image Lazy-Loaded</span>';

            // --- 7. WORDPRESS MEDIA BLOAT ---
            // Detect if the theme is loading original full-sized images instead of WP thumbs
            if (strpos($html, '-scaled.jpg') || strpos($html, '-scaled.png')) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Serving "Scaled" Originals</span>';
            }

            // --- 8. GRAVATAR IMPACT ---
            // If many gravatars are loaded in comments, they create dozens of external requests
            $gravatar_count = substr_count($html, 'secure.gravatar.com');
            if ($gravatar_count > 10) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">High Gravatar Load (' . $gravatar_count . ')</span>';
            }
foreach ($img_tags[0] as $tag) {
                // Get the SRC for deeper analysis
                preg_match('/src=["\']([^"\']+)["\']/i', $tag, $src_m);
                $src = $src_m[1] ?? '';
                $filename = basename($src);

                // 9. FILENAME SEO (Keyword Relevance)
                // If a filename is "IMG_5829.jpg" or "DSC102.png", it's a wasted SEO opportunity.
                if (preg_match('/(dsc|img|image|scan|screenshot|photo|picture|untitled)[-_]?[0-9]*/i', $filename)) {
                    $issues[] = '<span class="fisa-issue-tag tag-high">Unoptimized Filename: '.$filename.'</span>';
                }

                // 10. HOTLINKING / CDN CHECK
                // If the image is loaded from a different domain, it might be a hotlink or an unconfigured CDN.
                $parsed_src = parse_url($src);
                if (isset($parsed_src['host']) && $parsed_src['host'] !== parse_url($url, PHP_URL_HOST)) {
                    // Ignore common CDNs like Jetpack or Cloudflare
                    if (!preg_match('/(wp.com|cloudinary|wp.com|akamai)/i', $parsed_src['host'])) {
                        $issues[] = '<span class="fisa-issue-tag tag-info">External Image Request (Hotlink?)</span>';
                    }
                }

                // 11. SVG SECURITY & BLOAT
                // SVGs are great for speed, but if they aren't "sanitized" they can contain malicious code or metadata bloat.
                if (strpos($src, '.svg')) {
                    $issues[] = '<span class="fisa-issue-tag tag-info">SVG Detected: Ensure Sanitization</span>';
                }

                // 12. IMAGE WRAPPING (Contextual SEO)
                // Google likes images wrapped in <figure> and <figcaption> for better context.
                if (!strpos($html, '<figure') && !strpos($html, '<figcaption')) {
                    $issues[] = '<span class="fisa-issue-tag tag-info">Missing Figure/Caption Tags</span>';
                }
            }

            // --- 13. DATA-URI (BASE64) BLOAT ---
            // Small icons are fine, but large images encoded as Base64 bloat the HTML and can't be cached.
            if (strpos($html, 'data:image/')) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Base64 Inline Images Detected</span>';
            }

            // --- 14. EXIF & GPS LOCAL SEO SIGNAL ---
            // We search for clues of EXIF data or Geo-coordinates in the HTML/Schema.
            if (!strpos($html, 'geo.position') && !strpos($html, 'ICBM') && !strpos($html, 'GeoCoordinates')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">No Geo-Tags in Image Metadata</span>';
            }

            // --- 15. BROKEN IMAGE (404) DETECTION ---
            // If the script finds a source with "placeholder" or "broken", flag it.
            if (preg_match('/(placeholder|broken|temp|test|example)\.(jpg|png|gif)/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Placeholder Image Left on Live Site</span>';
            }

            break;

         case 'performance':
            
            $kb_size = strlen($html) / 1024;
            
            // --- 1. SERVER LATENCY & TTFB ---
            // Checking for slow server response times
            $response_time = $headers['total_time'] ?? 0; // If available via CURL
            if ($response_time > 0.8) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Slow Server Response (TTFB)</span>';
            }

            // --- 2. RENDER-BLOCKING ASSETS ---
            // CSS/JS in the <head> that isn't deferred stops the page from showing content
            if (preg_match_all('/<script[^>]+(?!async|defer)[^>]*src=/i', $html, $m) > 5) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Render-Blocking JS</span>';
            }
            if (strpos($html, '<link rel="stylesheet"') < strpos($html, '<body')) {
                // Technically all CSS is render-blocking, but we flag if not using 'preload' or 'print' tricks
                if (!strpos($html, 'rel="preload"')) {
                    $issues[] = '<span class="fisa-issue-tag tag-warn">Critical CSS Not Prioritized</span>';
                }
            }

            // --- 3. CORE WEB VITALS: CLS (Layout Shift) ---
            // Besides image dimensions, we check for "Flash of Unstyled Text" (FOUT)
            if (strpos($html, '@font-face') && !strpos($html, 'font-display: swap')) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Font Swap Missing (CLS Risk)</span>';
            }

            // --- 4. DOM DEPTH & WEIGHT ---
            // Excessive nesting of DIVs (common in Elementor/Divi) makes the browser work harder
            if (substr_count($html, '<div') > 300) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Excessive DOM Depth</span>';
            }
            if ($kb_size > 200) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Heavy Page Weight ('.round($kb_size).'KB)</span>';
            }

            // --- 5. BROWSER CACHING ---
            // If the server doesn't tell the browser to "remember" files, return visits are slow
            if (empty($headers['cache-control']) || strpos($headers['cache-control'], 'no-cache') !== false) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Browser Caching Disabled</span>';
            }

            // --- 6. PREFETCH & PRECONNECT ---
            // Modern SEO uses hints to speed up external requests (Google Fonts, Maps)
            if (!strpos($html, 'rel="dns-prefetch"') && !strpos($html, 'rel="preconnect"')) {
                if (strpos($html, 'gstatic.com') || strpos($html, 'google-analytics.com')) {
                    $issues[] = '<span class="fisa-issue-tag tag-info">Missing Preconnect Hints</span>';
                }
            }

            // --- 7. BROKEN INTERNAL LINKS (404 Prevention) ---
            // We check for empty links or 'javascript:void(0)' which are UX dead-ends
            if (preg_match_all('/href=["\'](#|javascript:void\(0\))["\']/i', $html, $m) > 5) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">High Count of Dead Links</span>';
            }

            // --- 8. SECURITY AS PERFORMANCE ---
            // Insecure form actions on an HTTPS site trigger browser warnings
            if (is_ssl() && preg_match('/action=["\']http:\/\//i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Insecure Form Action</span>';
            }
            // Check for target="_blank" without rel="noopener" (Performance & Security risk)
            if (preg_match('/target=["\']_blank["\'](?!.*rel=["\']noopener)/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Insecure External Links</span>';
            }

            // --- 9. WORDPRESS HEARTBEAT & DASHBOARD BLOAT ---
            if (strpos($html, 'wp-auth-check') && strpos($html, 'heartbeat')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Heartbeat API Unoptimized</span>';
            }

            // --- 10. JQUERY-DEPENDENT RENDERING ---
            // If the site requires jQuery in the <head>, it forces a massive download before any text appears
            if (preg_match('/<head>.*jquery\.min\.js.*<\/head>/is', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Render-Blocking JQuery</span>';
            }

            // --- 11. CSS PRELOAD & FRAGMENTATION ---
            // Check if the theme is using 'all' media type for CSS instead of specific 'screen' or 'print'
            if (strpos($html, 'media=\'all\'') || strpos($html, 'media="all"')) {
                // Too many "all" media types prevent the browser from prioritizing stylesheets
                if (substr_count($html, 'media=\'all\'') > 10) {
                    $issues[] = '<span class="fisa-issue-tag tag-warn">Unprioritized CSS (Media All)</span>';
                }
            }

            // --- 12. DATABASE-DRIVEN DOM WEIGHT (THE "DIV SOUP" CHECK) ---
            // We check the ratio of HTML tags to actual content words.
            $tag_count = substr_count($html, '<');
            $word_count = str_word_count(strip_tags($html));
            if ($word_count > 0 && ($tag_count / $word_count) > 2) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Excessive Code-to-Content Ratio</span>';
            }

            // --- 13. EXTERNAL FONT LATENCY ---
            // If the site loads fonts from fonts.googleapis.com but doesn't use 'preconnect'
            if (strpos($html, 'fonts.googleapis.com') && !strpos($html, 'rel="preconnect"')) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Slow Google Font Handshake</span>';
            }

            // --- 14. EMOJI SCRIPT RESIDUE ---
            // WordPress still injects legacy emoji detection scripts that add 0.2s of parsing time
            if (strpos($html, 'window._wpemojiSettings')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Legacy WP-Emoji Bloat</span>';
            }

            // --- 15. ASYNC/DEFER COMPLIANCE ---
            // Check if scripts injected by plugins (like Yoast or WooCommerce) are actually deferred
            $total_scripts = preg_match_all('/<script/i', $html, $m);
            $async_scripts = preg_match_all('/<(script|async|defer)/i', $html, $m);
            if ($total_scripts > 10 && ($async_scripts < ($total_scripts / 2))) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Non-Async Script Overload</span>';
            }

            break;
        case 'security':
            // --- 1. SSL & PROTOCOL INTEGRITY ---
            if (!is_ssl()) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Non-HTTPS Protocol</span>';
            }
            if (is_ssl() && preg_match('/src=["\']http:\/\//i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">🔒 Mixed Content Alert</span>';
            }

            // --- 2. HTTP SECURITY HEADERS (E-E-A-T Signals) ---
            // These headers tell the browser how to behave securely
            if (empty($headers['strict-transport-security'])) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">HSTS Not Enabled</span>';
            }
            if (empty($headers['x-content-type-options'])) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Missing No-Sniff Header</span>';
            }
            if (empty($headers['x-frame-options'])) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Clickjack Protection Missing</span>';
            }

            // --- 3. WORDPRESS VULNERABILITY SURFACE ---
            // Exposing the WP version is a roadmap for hackers
            if (strpos($html, 'generator') && strpos($html, 'WordPress')) {
                $issues[] = '<span class="fisa-issue-tag tag-high">WP Version Leak</span>';
            }
            // Check for Directory Listing risks (Checking for common /wp-content/ paths)
            if (preg_match('/wp-content\/plugins\/(.*?)\//', $html, $plugin_m)) {
                if (strpos($plugin_m[0], 'revslider') || strpos($plugin_m[0], 'elementor')) {
                    // We don't flag the plugin itself, but remind to check for updates
                    $issues[] = '<span class="fisa-issue-tag tag-info">Update Audit: ' . ucfirst($plugin_m[1]) . '</span>';
                }
            }

            // --- 4. SOCIAL GRAPH: OPEN GRAPH (OG) ---
            // OG tags determine how your site looks on Facebook/LinkedIn
            $og_tags = ['og:title', 'og:description', 'og:image', 'og:type', 'og:url'];
            foreach ($og_tags as $tag) {
                if (!strpos($html, 'property="' . $tag . '"') && !strpos($html, "property='" . $tag . "'")) {
                    $issues[] = '<span class="fisa-issue-tag tag-high">Missing ' . strtoupper($tag) . '</span>';
                }
            }

            // --- 5. SOCIAL GRAPH: X / TWITTER CARDS ---
            if (!strpos($html, 'name="twitter:card"')) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">No X/Twitter Card Meta</span>';
            }

            // --- 6.  LOCAL TRUST SIGNALS ---
            // Google gauges trust (E-E-A-T) by the presence of standard legal pages
            $legal_terms = ['privacy-policy', 'terms-of-service', 'terms-and-conditions', 'cookie-policy'];
            $has_legal = false;
            foreach ($legal_terms as $term) {
                if (strpos($html, $term)) {
                    $has_legal = true;
                    break;
                }
            }
            if (!$has_legal) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Missing Trust Pages (E-E-A-T)</span>';
            }

            // --- 7. EXTERNAL LINK SECURITY ---
            // External links without noopener/noreferrer are performance and security risks
            if (preg_match('/target=["\']_blank["\'](?!.*rel=["\']noopener)/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Outbound Links without noopener/noreferrer</span>';
            }

            // --- 8. SCHEMA & ENTITY SECURITY ---
            // Ensure no malformed JSON-LD which can break the page or be exploited
            if (strpos($html, 'application/ld+json')) {
                if (strpos($html, '{"@context":') === false) {
                    $issues[] = '<span class="fisa-issue-tag tag-crit">Malformed Schema Data</span>';
                }
            } else {
                $issues[] = '<span class="fisa-issue-tag tag-high">Missing Schema JSON-LD</span>';
            }
            break;


        case 'semantic':

            // --- 2. STOP-WORD SLUG DILUTION ---
            // URLs with "and", "the", or "of" are less powerful.
            if (preg_match('/-(and|the|of|a|an|for|with)-/i', $url)) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Stop-words in URL Slug</span>';
            }

            // --- 3. OUTBOUND AUTHORITY ---
            // Linking to high-authority sites (Wikipedia, Govt, News) helps Google categorize you.
            if (!strpos($html, 'href="https://en.wikipedia.org') && !strpos($html, '.gov') && !strpos($html, '.org')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">No Outbound Authority Links</span>';
            }

            // --- 4. CONTENT DEPTH & SCAN-ABILITY ---
            // Long articles without a Table of Contents are hard for humans and bots to navigate.
            if (strlen($html) > 40000 && !strpos($html, 'table-of-contents') && !strpos($html, 'id="')) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Long Content: Needs TOC</span>';
            }




            // --- 7. QUESTION-BASED CONTENT (PPA Optimization) ---
            // Google loves "People Also Ask" content. We check for 'How', 'What', or 'Why' in H2/H3 tags.
            if (!preg_match('/<h[23][^>]*>(How|What|Why|Where|Can|Is)\s/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-info">No Question-Based Headings</span>';
            }

            // --- 8. BULLETED LIST DENSITY ---
            // Semantic clarity is improved by lists. Google often pulls these into "Position Zero" snippets.
            if (!strpos($html, '<ul') && !strpos($html, '<ol')) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Missing Semantic Lists (UL/OL)</span>';
            }

            // --- 9. BREADCRUMB NAVIGATION ---
            // Breadcrumbs provide a semantic path for both users and the Google "Knowledge Graph."
            if (!strpos($html, 'breadcrumb') && !strpos($html, 'v:Breadcrumb')) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">No Breadcrumb Schema Detected</span>';
            }

            // --- 10. CONTENT-TO-HTML RATIO (REFINED) ---
            // If the code is 90% tags and only 10% text, the semantic signal is "diluted" by bloat.
            $text_only = strip_tags($html);
            $ratio = strlen($text_only) / strlen($html);
            if ($ratio < 0.15) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Poor Semantic Signal (Code Bloat)</span>';
            }

            break;

        case 'ux':
            // --- 1. CALL TO ACTION (CTA) DETECTION ---
            // Every page should have a conversion goal (button, form, or tel link).
            if (!preg_match('/(button|cta|contact-form|wpcf7|gform|tel:)/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-high">No Clear CTA Detected</span>';
            }

            // --- 2. CLICK-TO-CALL (Local SEO) ---
            if (preg_match('/[0-9]{3}-[0-9]{3}-[0-9]{4}/', $html) && !strpos($html, 'href="tel:')) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Phone Not Clickable</span>';
            }

            // --- 3. INTERNAL LINK ANCHOR TEXT ---
            // If the script finds generic "click here" or "read more" links.
            if (preg_match('/(click here|read more|link|this page)/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Generic Link Anchor Text</span>';
            }

            // --- 4. MOBILE ACCESSIBILITY: TOUCH TARGETS ---
            // Buttons and links too close together are a "Mobile Usability" fail in Search Console.
            if (strpos($html, 'display:inline-block') && !strpos($html, 'padding')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Check Button Touch Targets</span>';
            }

            // --- 5. INTRUSIVE INTERSTITIALS (Pop-up Check) ---
            // Google penalizes sites where pop-ups cover the main content on mobile.
            if (preg_match('/(pum-|modal|popup|subscription-form|overlay)/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Potential Intrusive Popup</span>';
            }

            // --- 6. TARGET "_BLANK" RELATIONS ---
            // For UX, opening new tabs without warning can be disorienting.
            // For SEO, missing 'noopener' is a security/performance risk.
            if (preg_match('/target=["\']_blank["\'](?!.*rel=["\']noopener)/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Links without noopener</span>';
            }

            // --- 7. FONT LEGIBILITY & CONTRAST ---
            // If the script finds very small font sizes (10px-12px) in the CSS.
            if (preg_match('/font-size:\s*(10|11|12)px/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Tiny Font: Legibility Issue</span>';
            }

            // --- 8. INPUT ACCESSIBILITY (Form Labels) ---
            // Forms without <label> tags are hard for screen readers and "fat fingers."
            if (strpos($html, '<input') && !strpos($html, '<label')) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Form Missing Input Labels</span>';
            }

            // --- 9. NAVIGATIONAL BREADTH (The "Rule of 7") ---
            // If the main menu has more than 7-8 items, it creates "Choice Paralysis."
            $menu_count = substr_count($html, '<li class="menu-item');
            if ($menu_count > 40) { // Total menu items across the site
                 $issues[] = '<span class="fisa-issue-tag tag-info">Heavy Menu Navigation</span>';
            }

            // --- 10. CONTENT "ABOVE THE FOLD" PRIORITY ---
            // If the script finds a massive <div> or <header> before the first <p> or <h1>.
            preg_match('/<body[^>]*>(.*?)<p/is', $html, $fold_m);
            if (isset($fold_m[1]) && strlen($fold_m[1]) > 5000) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Content Pushed Below Fold</span>';
            }

            // --- 11. SCROLL POSITION INDICATORS ---
            // For long-form marketing guides, lack of "Back to Top" hurts UX.
            if (strlen($html) > 60000 && !strpos($html, 'back-to-top') && !strpos($html, 'smooth-scroll')) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Long Page: Needs Scroll Assist</span>';
            }
            break;

            case 'authority':
            // --- 1. EXTERNAL AUTHORITY RATIO ---
            // A healthy page links to at least one external authority (Edu/Gov/Org).
            if (!preg_match('/href=["\']https?:\/\/(?!(www\.)?' . parse_url(home_url(), PHP_URL_HOST) . ')/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-info">No Outbound Links</span>';
            }

            // --- 2. SOCIAL FOOTPRINT ---
            // Checks for links to major platforms (FB, IG, LI, X).
            if (!preg_match('/(facebook|instagram|linkedin|twitter|x\.com|youtube)/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-high">Missing Social Authority Links</span>';
            }

            // --- 3. INTERNAL LINK DEPTH ---
            // Pages with zero internal links are "Orphaned" and won't rank well.
            if (substr_count($html, 'href="' . home_url()) < 2) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Orphaned Content Risk</span>';
            }

            // --- 4. NOFOLLOW LEAKAGE ---
            // If the site uses 'nofollow' on its own internal links (a common SEO mistake).
            if (preg_match('/href=["\']' . preg_quote(home_url(), '/') . '.*rel=["\']nofollow/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-crit">Internal Nofollow Error</span>';
            }
            break;

        case 'hygiene':
            // --- 1. COMMENTED-OUT CODE ---
            // Large blocks of commented-out HTML/CSS slow down crawlers and bloat the DOM.
            if (strlen($html) > 100000 && preg_match_all('//s', $html, $m) > 10) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Excessive Commented Code</span>';
            }

            // --- 2. DEPRECATED HTML TAGS ---
            // Checks for <center>, <font>, or <b>/<i> (should be <strong>/<em>).
            if (preg_match('/<(center|font|u|strike|big)/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-warn">Deprecated HTML Tags</span>';
            }

            // --- 3. INLINE STYLE FREQUENCY ---
            // If the script finds more than 20 'style=' attributes in the body.
            if (substr_count($html, 'style="') > 20) {
                $issues[] = '<span class="fisa-issue-tag tag-high">High Inline Style Count</span>';
            }

            // --- 4. LINTING: EMPTY TAGS ---
            // Empty <div>, <p>, or <span> tags are often artifacts of poor page builders.
            if (preg_match('/<(div|p|span)[^>]*>\s*<\/\1>/i', $html)) {
                $issues[] = '<span class="fisa-issue-tag tag-info">Empty HTML Tags Found</span>';
            }
            break;

    }



    wp_send_json_success(['issues' => $issues]);
});

/**
 * 4. Rendering
 */
function fisa_render_aio_page() {
    fisa_aio_assets();
    ?>
    <div class="wrap">
        <h1>SEO Command Center <small>by thisismyurl</small></h1>
        
        <div class="fisa-card">
            <h2>Inventory & Cache</h2>
            <button id="fisa-fetch-inventory" class="button button-primary button-large">Fetch Site Content</button>
            <span id="fisa-inventory-loader" class="spinner is-active"></span>
        </div>

        <div class="fisa-table-container" style="display:none;">
            <div class="fisa-card">
                <h2>Audit Progress</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th style="width:40%;">Current Status / Results</th>
                            <th class="fisa-actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="fisa-results-body"></tbody>
                </table>
            </div>

            <?php include_once plugin_dir_path( __FILE__ ) . 'free-seo-glossary.html'; ?>

        </div>

        
    </div>
    <?php
}