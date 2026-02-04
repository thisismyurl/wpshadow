#!/usr/bin/env python3
"""
Reorganize WPShadow diagnostics into 10 main category folders.

Maps all loose diagnostic files and scattered folders into logical 10 categories:
1. security
2. performance
3. code-quality
4. seo
5. design
6. settings
7. monitoring
8. workflows
9. wordpress-health (WordPress native health)
10. content (high-volume content-related diagnostics)

This script:
- Creates the 10 main category folders
- Moves loose PHP files to appropriate folders
- Consolidates single-file folders into main categories
- Removes empty folders
"""

import os
import shutil
import re
from pathlib import Path
from collections import defaultdict

BASE_PATH = Path('/workspaces/wpshadow/includes/diagnostics/tests')

# Define the 10 main categories and their keywords/patterns
CATEGORY_MAPPING = {
    'security': {
        'keywords': [
            'security', 'injection', 'xss', 'csrf', 'sql', 'auth', 'password',
            'encryption', 'ssl', 'tls', 'vulnerability', 'attack', 'privilege',
            'escalation', 'access-control', 'jwt', 'jwt-token', 'totp', 'twofa', '2fa',
            'session', 'cookie', 'header', 'owasp', 'deserialization', 'serialization',
            'xml', 'xxe', 'xpath', 'yaml', 'json', 'command-injection', 'nosql',
            'path-traversal', 'redirect', 'ransomware', 'malicious', 'upload',
            'rate-limiting', 'brute', 'ddos', 'bot', 'firewall', 'intrusion',
            'zero-day', 'breach', 'compromise', 'backdoor', 'trojan', 'crypto',
            'hash', 'randomness', 'fallback-authentication', 'key-management',
            'certificate', 'certificate-pinning', 'certificate-transparency',
            'cors', 'csp', 'x-frame-options', 'mime-type', 'sniffing',
            'strict-transport-security', 'http-strict-transport-security',
            'cache-poisoning', 'zip-slip', 'dependency-confusion', 'dependency',
            'vulnerability-scanning', 'static-analysis', 'package-vulnerability',
            'gdpr', 'data-export', 'sensitive-data', 'exposure', 'log', 'incident',
            'policy', 'lockout', 'weak-password', 'weakssl', 'weak-ssl', 'compliance',
            'container-security', 'segmentation', 'network-segmentation',
            'webhook-validation', 'request-forgery', 'race-condition',
            'environment', 'environmental-variable-exposure', 'permutation-abuse',
            'ip-blocking', 'subdomain-takeover', 'version-control', 'version-header',
            'tabnabbing', 'traffic-analysis', 'server-response-timing',
            'unicode-normalization', 'user-agent-validation', 'form-validation',
            'email-validation', 'email-header', 'email-injection', 'request-size',
            'keep-alive', 'load-balancer-health', 'memory-leak', 'object-deserialization',
            'function-argument-type', 'reflection', 'text-truncation', 'sql-truncation',
            'query-string', 'javascript-deserialization', 'javascript-source-map',
            'source-map', 'field-injection', 'language-injection', 'log-injection'
        ],
        'folder_patterns': [
            'security', 'admin-security', 'media-security', 'csp', 'cors', 'csrf',
            'xss', 'injection', 'auth', 'encryption', 'ssl', 'tls', 'firewall'
        ]
    },
    'performance': {
        'keywords': [
            'performance', 'cache', 'caching', 'optimization', 'speed', 'lazy',
            'cdn', 'compression', 'minify', 'bundle', 'database-query', 'query',
            'database-connection', 'database', 'memory', 'cpu', 'load', 'scalability',
            'scaling', 'vertical-scaling', 'horizontal-scaling', 'database-query-optimization',
            'image', 'media', 'responsive', 'srcset', 'webp', 'avif', 'progressive',
            'critical', 'lcp', 'fid', 'cls', 'web-vital', 'core-web-vital',
            'largest-contentful-paint', 'first-input-delay', 'cumulative-layout-shift',
            'json-ld', 'feeds', 'feed', 'rss', 'cache-control', 'cache-poisoning',
            'keep-alive', 'gzip', 'brotli', 'deflate', 'http2', 'http-2',
            'multiplexing', 'connection-pooling', 'batch', 'network-request-batching',
            'service-worker', 'pwa', 'progressive-web-app', 'manifest',
            'offline', 'service-worker-not-implemented', 'service-worker-cache'
        ],
        'folder_patterns': [
            'performance', 'cache', 'caching', 'optimization', 'speed', 'cdn',
            'database', 'database-query', 'media', 'image', 'lazy', 'feed'
        ]
    },
    'code-quality': {
        'keywords': [
            'code-quality', 'code', 'quality', 'standard', 'best-practice', 'linting',
            'formatting', 'architecture', 'design-pattern', 'refactor', 'technical-debt',
            'deprecated', 'deprecation', 'compatibility', 'theme-compatibility',
            'plugin-ecosystem', 'library', 'dependency', 'version', 'api', 'rest-api',
            'endpoint', 'schema', 'cpt', 'custom-post-type', 'taxonomy',
            'rewrite-rule', 'hook', 'filter', 'action', 'error-handling',
            'error-rate', 'error-monitoring', 'monitoring', 'logging', 'logging-not-configured',
            'instrumentation', 'tracing', 'debug', 'analysis', 'testing',
            'unit-test', 'integration-test', 'e2e-test', 'accessibility',
            'wcag', 'aria', 'semantic', 'html', 'css', 'javascript',
            'type-checking', 'function-argument-type', 'unsafe-reflection',
            'function-argument-type-checking', 'reflection', 'documentation',
            'design-system', 'design-system-not-documented', 'zod', 'typescript',
            'graceful-degradation', 'feature-degradation', 'feature-detection',
            'zero-configuration', 'zero-config', 'convention', 'over-configuration'
        ],
        'folder_patterns': [
            'code', 'quality', 'api', 'rest-api', 'cpt', 'custom-post-types',
            'schema', 'testing', 'accessibility', 'design-system'
        ]
    },
    'seo': {
        'keywords': [
            'seo', 'search', 'engine', 'optimization', 'keyword', 'content',
            'link', 'internal-link', 'external-link', 'backlink', 'authority',
            'domain-authority', 'page-authority', 'snippet', 'featured-snippet',
            'schema', 'schema-markup', 'structured-data', 'json-ld', 'microdata',
            'og-tag', 'meta', 'title', 'description', 'heading', 'h1', 'h2',
            'hierarchy', 'slug', 'permalink', 'url-structure', 'canonical',
            'duplicate-content', 'orphan', 'sitemap', 'robots-txt', 'readability',
            'reading-level', 'word-count', 'paragraph', 'sentence', 'jargon',
            'synonym', 'semantic-search', 'related-content', 'topic-cluster',
            'pillar-content', 'cluster-content', 'long-tail', 'voice-search',
            'local-seo', 'local', 'local-business', 'local-schema', 'google-business',
            'citation', 'review', 'rating', 'star', 'rich-result', 'how-to',
            'faq', 'product', 'recipe', 'event', 'breadcrumb', 'pagination',
            'social-sharing', 'schema', 'blog', 'publishing', 'pub-date',
            'author', 'contributor', 'post', 'article', 'news', 'mobile-first',
            'amp', 'accelerated-mobile', 'web-story', 'instant-article',
            'featured-image', 'image-alt-text', 'image-optimization', 'image-seo',
            'video-seo', 'video', 'podcast', 'transcription', 'audio',
            'search-console', 'analytics', 'click-through-rate', 'ctr',
            'impression', 'position', 'rank', 'ranking', 'traffic', 'bounce-rate',
            'time-on-page', 'time-on-site', 'engagement', 'scroll-depth',
            'search-intent', 'content-intent', 'e-e-a-t', 'experience', 'expertise',
            'authority', 'trustworthiness', 'brand', 'branded-search',
            'white-hat', 'black-hat', 'gray-hat', 'white-label', 'private-label',
            'affiliate', 'nofollow', 'sponsored', 'geo-location', 'geo-target',
            'international', 'hreflang', 'language', 'region', 'x-default',
            'keyword-cannibalization', 'keyword-gap', 'keyword-stuffing',
            'cloaking', 'doorway-page', 'thin-content', 'auto-generated'
        ],
        'folder_patterns': [
            'seo', 'content', 'keyword', 'link', 'search', 'semantic', 'local',
            'video', 'podcast', 'audio', 'analytics'
        ]
    },
    'design': {
        'keywords': [
            'design', 'ux', 'ui', 'user-experience', 'interface', 'layout',
            'responsive', 'mobile', 'tablet', 'desktop', 'breakpoint',
            'grid', 'flexbox', 'container-query', 'mobile-first', 'desktop-first',
            'dark-mode', 'light-mode', 'theme', 'color', 'contrast', 'typography',
            'font', 'typeface', 'spacing', 'padding', 'margin', 'border-radius',
            'shadow', 'elevation', 'depth', 'gesture', 'touch', 'click', 'hover',
            'focus', 'active', 'visited', 'disabled', 'loading', 'error',
            'success', 'warning', 'info', 'notification', 'toast', 'alert',
            'modal', 'dialog', 'popup', 'tooltip', 'menu', 'navigation',
            'breadcrumb', 'pagination', 'tabs', 'accordion', 'disclosure',
            'button', 'link', 'form', 'input', 'textarea', 'checkbox',
            'radio', 'select', 'datepicker', 'timepicker', 'colorpicker',
            'slider', 'progress', 'spinner', 'skeleton', 'placeholder',
            'error-state', 'empty-state', 'loading-state', 'success-state',
            'accessibility', 'wcag', 'aria', 'keyboard-navigation', 'focus-management',
            'screen-reader', 'semantic-html', 'landmark', 'heading', 'list',
            'table', 'form-label', 'skip-link', 'target-size', 'touch-target',
            'animation', 'transition', 'motion', 'reduce-motion', 'prefersreduces-motion',
            'micro-interaction', 'feedback', 'confirmation', 'preview',
            'drag-drop', 'drag-and-drop', 'sortable', 'resizable',
            'icon', 'image', 'illustration', 'animation', 'video',
            'call-to-action', 'cta', 'button', 'link', 'badge', 'pill',
            'tag', 'label', 'caption', 'subtitle', 'quote', 'blockquote',
            'divider', 'separator', 'spacer', 'gutter', 'bleed',
            'card', 'panel', 'container', 'section', 'article',
            'header', 'footer', 'sidebar', 'drawer', 'panel',
            'composition', 'layout-component', 'page-layout', 'template',
            'pattern', 'component', 'widget', 'module', 'block',
            'design-system', 'design-system', 'design-token', 'token',
            'design-guideline', 'design-principle', 'design-pattern',
            'usability', 'heuristic', 'principle', 'best-practice',
            'user-research', 'user-testing', 'a-b-test', 'multivariate-test',
            'heat-map', 'scroll-map', 'session-replay', 'user-behavior'
        ],
        'folder_patterns': [
            'design', 'design-system', 'accessibility', 'mobile', 'responsive',
            'typography', 'theme', 'animation', 'component', 'layout',
            'navigation', 'touch', 'form', 'button', 'icon'
        ]
    },
    'settings': {
        'keywords': [
            'setting', 'configuration', 'config', 'option', 'preference',
            'setup', 'install', 'initialize', 'bootstrap', 'register',
            'activate', 'deactivate', 'enable', 'disable', 'plugin', 'theme',
            'multisite', 'network', 'blog', 'site', 'environment', 'wp-config',
            'php', 'version', 'extension', 'module', 'requirement', 'compatibility',
            'permalinks', 'permalink', 'slug', 'rewrite', 'rewrite-rule',
            'rest-api', 'custom-post-type', 'taxonomy', 'custom-taxonomy',
            'role', 'capability', 'permission', 'user-role', 'user-permission',
            'privacy', 'data-collection', 'tracking', 'analytics', 'telemetry',
            'comments', 'comment-moderation', 'spam-filter', 'blacklist', 'whitelist',
            'user-management', 'user-profile', 'password', 'authentication',
            'email', 'notifications', 'subscribe', 'subscription', 'unsubscribe',
            'newsletter', 'contact-form', 'feedback-form', 'support-form',
            'media', 'media-library', 'file-upload', 'file-size', 'file-type',
            'attachment', 'featured-image', 'thumbnail', 'image-size',
            'permalink', 'url-structure', 'index', 'sitemap', 'robots-txt',
            'rss', 'feed', 'atom', 'json-feed', 'feedburner', 'feed-reader'
        ],
        'folder_patterns': [
            'settings', 'configuration', 'plugin', 'theme', 'multisite',
            'permalinks', 'forms', 'comments', 'uploads', 'export', 'import'
        ]
    },
    'monitoring': {
        'keywords': [
            'monitoring', 'monitor', 'health', 'status', 'check', 'verification',
            'audit', 'audit-log', 'activity-log', 'event-log', 'logging',
            'alert', 'notification', 'email-alert', 'sms-alert', 'webhook',
            'uptime', 'downtime', 'availability', 'reliability', 'sla',
            'performance-tracking', 'performance-monitoring', 'web-performance',
            'web-vitals', 'core-web-vitals', 'field-data', 'lab-data',
            'error-monitoring', 'error-tracking', 'error-rate', 'error-budget',
            'incident', 'incident-response', 'incident-management', 'post-mortem',
            'diagnostics', 'diagnostic', 'scan', 'health-check', 'site-health',
            'security-check', 'security-audit', 'vulnerability-scan',
            'update-check', 'plugin-update', 'theme-update', 'wordpress-update',
            'backup', 'backup-verify', 'backup-restore', 'restore-point',
            'database-backup', 'file-backup', 'incremental-backup', 'full-backup',
            'encryption', 'encrypted', 'encryption-key', 'key-management',
            'compliance', 'compliance-check', 'gdpr-compliance', 'ccpa-compliance',
            'privacy-compliance', 'data-privacy', 'data-protection',
            'certificate', 'ssl-certificate', 'tls-certificate', 'expiration',
            'dns', 'dns-record', 'dns-check', 'propagation', 'mx-record',
            'mx-check', 'spf', 'dkim', 'dmarc', 'email-authentication',
            'external-integration', 'api-integration', 'third-party', 'webhook',
            'webhook-test', 'webhook-retry', 'webhook-validation', 'ping'
        ],
        'folder_patterns': [
            'monitoring', 'health', 'backup', 'compliance', 'audit', 'alert',
            'incident', 'database', 'export', 'verification'
        ]
    },
    'workflows': {
        'keywords': [
            'workflow', 'automation', 'automated', 'automate', 'batch', 'scheduled',
            'cron', 'cron-job', 'task', 'queue', 'queue-job', 'job', 'background-job',
            'async', 'asynchronous', 'promise', 'callback', 'webhook', 'event',
            'event-listener', 'event-hook', 'action', 'filter', 'trigger',
            'scheduled-post', 'scheduled-email', 'scheduled-task', 'scheduled-export',
            'publish', 'publishing', 'bulk-action', 'bulk-edit', 'bulk-delete',
            'import', 'import-export', 'export', 'csv', 'json', 'xml',
            'migration', 'migrate', 'data-migration', 'content-migration',
            'backup', 'restore', 'archive', 'retention', 'cleanup',
            'send-email', 'email-campaign', 'email-sequence', 'email-automation',
            'notification', 'send-notification', 'push-notification', 'sms',
            'webhook', 'webhook-delivery', 'webhook-retry', 'webhook-signature',
            'integration', 'integrate', 'zapier', 'ifttt', 'make', 'integromat',
            'external-api', 'api-call', 'rest-api', 'graphql', 'webhook-delivery'
        ],
        'folder_patterns': [
            'workflow', 'automation', 'queue', 'batch', 'scheduled', 'import-export',
            'publishing', 'tools'
        ]
    },
    'wordpress-health': {
        'keywords': [
            'wordpress', 'wordpress-health', 'site-health', 'wp-health',
            'wp-core', 'core', 'wordpress-core', 'rest-api', 'json-api',
            'admin', 'admin-bar', 'gutenberg', 'editor', 'block-editor',
            'theme', 'theme-quality', 'theme-compatibility', 'child-theme',
            'plugin', 'plugin-conflict', 'plugin-compatibility', 'plugin-ecosystem',
            'hook', 'action-hook', 'filter-hook', 'capability', 'role',
            'user', 'user-count', 'user-role', 'user-permission', 'admin-user',
            'post', 'page', 'post-type', 'custom-post-type', 'post-status',
            'revision', 'auto-save', 'draft', 'scheduled', 'revision-count',
            'comment', 'comment-count', 'spam-comment', 'comment-moderation',
            'media', 'attachment', 'unattached-media', 'orphan-media',
            'taxonomy', 'category', 'tag', 'custom-taxonomy', 'unused-taxonomy',
            'menu', 'navigation-menu', 'custom-menu', 'menu-item',
            'widget', 'sidebar', 'inactive-widget', 'unused-widget',
            'active-theme', 'active-plugin', 'must-use-plugin', 'mu-plugin',
            'dropins', 'drop-in-plugins', 'object-cache', 'cache-drop-in',
            'database', 'database-prefix', 'table-prefix', 'wp_prefix',
            'debug', 'wp-debug', 'debug-log', 'debug-display', 'wp-cron',
            'loopback-request', 'http-request', 'remote-request',
            'file-permission', 'directory-permission', 'file-owner',
            'symlink', 'symlink-check', 'file-system', 'filesystem',
            'site-url', 'home-url', 'url-mismatch', 'url-consistency'
        ],
        'folder_patterns': [
            'wordpress-health', 'site_health', 'admin', 'plugins', 'theme',
            'gutenberg', 'editor', 'cpt', 'comments', 'multisite'
        ]
    },
    'content': {
        'keywords': [
            'content', 'article', 'blog', 'post', 'page', 'copywriting',
            'copy', 'writing', 'grammar', 'spelling', 'punctuation', 'style',
            'tone', 'voice', 'brand-voice', 'brand-tone', 'consistency',
            'cta', 'call-to-action', 'conversion', 'engagement', 'call-out',
            'summary', 'excerpt', 'description', 'meta-description', 'snippet',
            'h1', 'heading', 'subheading', 'hierarchy', 'heading-hierarchy',
            'list', 'bulleted-list', 'numbered-list', 'unordered-list',
            'table', 'data-table', 'structured-data', 'schema', 'json-ld',
            'form', 'form-field', 'form-input', 'form-submission',
            'button', 'button-text', 'button-label', 'button-color',
            'image', 'alt-text', 'image-description', 'image-title',
            'video', 'video-embed', 'video-thumbnail', 'video-description',
            'link', 'internal-link', 'external-link', 'broken-link',
            'related-posts', 'related-content', 'suggested-content',
            'comment', 'user-comment', 'comment-engagement', 'comment-moderation',
            'social-media', 'social-sharing', 'social-proof', 'testimonial',
            'review', 'rating', 'star-rating', 'user-review', 'product-review',
            'toc', 'table-of-contents', 'outline', 'navigation',
            'update', 'content-update', 'evergreen-content', 'seasonal-content',
            'publishing', 'publish-date', 'publish-schedule', 'publication-schedule',
            'author', 'author-byline', 'author-bio', 'contributor', 'editor',
            'category', 'tag', 'keyword', 'topic', 'category-description',
            'archive', 'archive-page', 'archive-title', 'archive-description',
            'search', 'search-result', 'search-query', 'search-relevance',
            'pagination', 'page-numbering', 'load-more', 'infinite-scroll',
            'feed', 'rss-feed', 'atom-feed', 'json-feed', 'feed-item',
            'reading-time', 'word-count', 'reading-level', 'readability',
            'excerpt', 'truncate', 'more-tag', 'read-more', 'continue-reading',
            'featured-content', 'featured-post', 'featured-image', 'hero',
            'hero-image', 'featured-section', 'spotlight', 'trending',
            'popular', 'most-viewed', 'most-commented', 'most-shared'
        ],
        'folder_patterns': [
            'content', 'blog', 'post', 'article', 'reading', 'readability',
            'publishing', 'forms', 'comments', 'feed', 'rss', 'media'
        ]
    }
}

def get_category_for_file(filename):
    """Determine which category a diagnostic file belongs to."""
    # Convert filename to lowercase for matching
    name_lower = filename.lower()
    
    # Score each category
    scores = defaultdict(int)
    
    for category, patterns in CATEGORY_MAPPING.items():
        for keyword in patterns['keywords']:
            if keyword in name_lower:
                scores[category] += 1
    
    # Return category with highest score
    if scores:
        return max(scores, key=scores.get)
    
    # Default to 'settings' for unmatched files
    return 'settings'

def categorize_folder(folder_name):
    """Determine which category an existing folder belongs to."""
    folder_lower = folder_name.lower()
    
    for category, patterns in CATEGORY_MAPPING.items():
        for pattern in patterns['folder_patterns']:
            if pattern in folder_lower or folder_lower == pattern:
                return category
    
    # Try keyword matching on folder name
    scores = defaultdict(int)
    for category, patterns in CATEGORY_MAPPING.items():
        for keyword in patterns['keywords']:
            if keyword in folder_lower:
                scores[category] += 1
    
    if scores:
        return max(scores, key=scores.get)
    
    return 'settings'

def main():
    """Reorganize all diagnostics into 10 main categories."""
    
    print("🔍 Analyzing diagnostic structure...")
    
    # Create main category directories if they don't exist
    for category in CATEGORY_MAPPING.keys():
        category_path = BASE_PATH / category
        category_path.mkdir(exist_ok=True)
        print(f"✓ Category folder ensured: {category}")
    
    # Process loose PHP files
    print("\n📁 Processing loose diagnostic files...")
    loose_files = list(BASE_PATH.glob('class-diagnostic-*.php'))
    print(f"Found {len(loose_files)} loose diagnostic files")
    
    for php_file in loose_files:
        category = get_category_for_file(php_file.name)
        destination = BASE_PATH / category / php_file.name
        
        if php_file != destination:
            shutil.move(str(php_file), str(destination))
            print(f"  → {php_file.name} → {category}/")
    
    # Process existing folders and move their contents to main categories
    print("\n📂 Processing existing folders...")
    all_folders = [f for f in BASE_PATH.iterdir() if f.is_dir()]
    print(f"Found {len(all_folders)} existing folders")
    
    for folder in sorted(all_folders):
        category = categorize_folder(folder.name)
        
        # Skip if it's already a main category
        if folder.name == category:
            continue
        
        # Move files from this folder to the main category
        files_in_folder = list(folder.glob('*'))
        if files_in_folder:
            print(f"\n  Processing: {folder.name} → {category}")
            
            for item in files_in_folder:
                if item.is_file():
                    dest = BASE_PATH / category / item.name
                    
                    # Handle file conflicts
                    if dest.exists():
                        print(f"    ⚠️  {item.name} already exists in {category}, skipping")
                    else:
                        shutil.move(str(item), str(dest))
                        print(f"    ✓ {item.name}")
                elif item.is_dir():
                    # Move subdirectories with their contents
                    dest = BASE_PATH / category / item.name
                    if not dest.exists():
                        shutil.move(str(item), str(dest))
                        print(f"    ✓ {item.name}/ (subfolder)")
    
    # Clean up empty folders
    print("\n🧹 Cleaning up empty folders...")
    empty_count = 0
    
    for folder in sorted(BASE_PATH.iterdir()):
        if folder.is_dir() and folder.name in CATEGORY_MAPPING:
            continue  # Skip main categories
        
        if folder.is_dir():
            try:
                if not list(folder.iterdir()):  # If folder is empty
                    folder.rmdir()
                    print(f"  ✓ Removed: {folder.name}/")
                    empty_count += 1
            except OSError:
                pass  # Folder not empty, skip
    
    # Summary
    print("\n✅ Reorganization complete!")
    print(f"\n📊 Final structure:")
    for category in sorted(CATEGORY_MAPPING.keys()):
        cat_path = BASE_PATH / category
        if cat_path.exists():
            file_count = len(list(cat_path.rglob('*')))
            print(f"  {category}: {file_count} items")

if __name__ == '__main__':
    main()
