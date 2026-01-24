#!/usr/bin/env python3
"""
Publish a KB markdown file to WordPress via REST API.
- Parses YAML frontmatter
- Converts Markdown to HTML
- Ensures taxonomies exist (kb_category, kb_difficulty, post_tag)
- Creates or updates the KB post by slug

Requirements:
  pip install pyyaml markdown requests (markdown optional if already installed)

Env vars (.env or environment):
  WP_SITE_URL=https://wpshadow.com
  WP_USERNAME=thisismyurl
  WP_APP_PASSWORD=xxxx xxxx xxxx xxxx

Usage:
  python3 tools/publish_kb.py kb-articles/performance/missing-database-indexes.md
"""

import base64
import json
import os
import sys
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

try:
    import yaml  # type: ignore
except ImportError:  # pragma: no cover
    print("❌ Missing dependency: pyyaml. Install with: pip install pyyaml")
    sys.exit(1)

try:
    import markdown  # type: ignore
except ImportError:  # pragma: no cover
    markdown = None


def load_env():
    env = {}
    if os.path.exists('.env'):
        with open('.env') as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith('#') and '=' in line:
                    k, v = line.split('=', 1)
                    env[k] = v
    # Only override with real environment variables if they are set
    for key in ['WP_SITE_URL', 'WP_USERNAME', 'WP_APP_PASSWORD']:
        env_val = os.environ.get(key)
        if env_val:
            env[key] = env_val
    missing = [k for k, v in env.items() if k.startswith('WP_') and not v]
    if missing:
        print(f"❌ Missing env vars: {', '.join(missing)}")
        sys.exit(1)
    return env


def basic_auth_header(username: str, app_password: str) -> str:
    token = base64.b64encode(f"{username}:{app_password}".encode()).decode()
    return f"Basic {token}"


def http_request(url: str, method: str, headers: dict, data: dict | None = None):
    body = None
    if data is not None:
        body = json.dumps(data).encode()
        headers['Content-Type'] = 'application/json'
    req = urllib.request.Request(url, data=body, method=method)
    for k, v in headers.items():
        req.add_header(k, v)
    try:
        with urllib.request.urlopen(req, timeout=10) as resp:
            return resp.getcode(), json.loads(resp.read().decode())
    except urllib.error.HTTPError as e:  # pragma: no cover
        try:
            detail = e.read().decode()
        except Exception:
            detail = ''
        print(f"❌ HTTP {e.code} {e.reason}: {detail}")
        sys.exit(1)
    except urllib.error.URLError as e:  # pragma: no cover
        print(f"❌ Connection error: {e.reason}")
        sys.exit(1)


def parse_markdown(path: Path):
    text = path.read_text(encoding='utf-8')
    if not text.startswith('---'):
        print('❌ Missing frontmatter (---)')
        sys.exit(1)
    parts = text.split('---', 2)
    if len(parts) < 3:
        print('❌ Invalid frontmatter format')
        sys.exit(1)
    frontmatter_raw = parts[1]
    body = parts[2]
    meta = yaml.safe_load(frontmatter_raw) or {}
    return meta, body.strip()


def validate_article_content(meta: dict, body: str, path: Path) -> list:
    """
    Validate that article has real content, not placeholder instructions.
    Returns list of validation errors (empty list if valid).
    """
    errors = []
    
    # Check for placeholder patterns in frontmatter
    description = meta.get('description', '')
    if '[Brief description' in description or not description or len(description) < 20:
        errors.append('Description is placeholder or too short')
    
    related = meta.get('related_articles', [])
    if any('[related-article' in str(r) for r in related):
        errors.append('Related articles contain placeholders like [related-article-1]')
    
    # Check for placeholder patterns in body
    placeholder_patterns = [
        '[Clear, concise overview',
        '[Brief description',
        '[Real-world impact',
        '[describe benefit]',
        '[Result/confirmation text]',
        '[Steps for using WPShadow',
        '[Technical explanation',
        '[Benchmarking, optimization',
        '[Links to related KB',
        '[Common question about',
        '[Direct, helpful answer]',
        '[WPShadow Feature',
        '[How this article embodies',
        'Draft - Needs Content',
    ]
    
    for pattern in placeholder_patterns:
        if pattern in body:
            errors.append(f'Body contains placeholder: "{pattern}"')
            break  # Only report first placeholder found
    
    # Check minimum content length
    if len(body) < 500:
        errors.append(f'Article body too short ({len(body)} chars, minimum 500)')
    
    return errors


def principle_to_anchor(principle_id: str) -> str:
    """
    Convert principle ID like '#07-ridiculously-good' to anchor like 'ridiculously-good'.
    """
    # Remove # prefix and leading numbers/dashes
    anchor = principle_id.lstrip('#').lstrip('0123456789-')
    return anchor


def markdown_to_html(md: str, meta: dict = None) -> str:
    if markdown is None:
        print('❌ Missing dependency: markdown. Install with: pip install markdown')
        sys.exit(1)
    
    meta = meta or {}
    
    # Remove sections we don't want published
    lines = md.split('\n')
    filtered_lines = []
    skip_until_next_section = False
    in_quality_checklist = False
    in_read_on_wpshadow = False
    in_article_metadata = False
    in_core_principles = False
    in_related_features = False
    
    for i, line in enumerate(lines):
        # Skip "Read on WPShadow" section and block link
        if '> **Read on WPShadow:**' in line:
            in_read_on_wpshadow = True
            continue
        if in_read_on_wpshadow and line.startswith('---'):
            in_read_on_wpshadow = False
            skip_until_next_section = False
            continue
        if in_read_on_wpshadow:
            continue
        
        # Skip Quality Checklist section
        if '## ✓ Quality Checklist' in line:
            in_quality_checklist = True
            continue
        if in_quality_checklist and line.startswith('---'):
            in_quality_checklist = False
            continue
        if in_quality_checklist:
            continue
        
        # Skip Article Metadata section
        if '## Article Metadata' in line or '## 📋 Article Metadata' in line:
            in_article_metadata = True
            continue
        if in_article_metadata and line.startswith('---'):
            in_article_metadata = False
            continue
        if in_article_metadata:
            continue
        
        # Skip Core Principles section (will use frontmatter instead)
        if '## Core Principles' in line:
            in_core_principles = True
            continue
        if in_core_principles and line.startswith('---'):
            in_core_principles = False
            continue
        if in_core_principles:
            continue
        
        # Skip Related Features section (will handle separately if needed)
        if '## Related Features' in line or '## Related WPShadow Features' in line:
            in_related_features = True
            continue
        if in_related_features and line.startswith('---'):
            in_related_features = False
            continue
        if in_related_features:
            continue
        
        # Skip the first H1 title
        if i == 0 and line.startswith('# '):
            continue
        
        filtered_lines.append(line)
    
    md_filtered = '\n'.join(filtered_lines).strip()
    
    # Extract summary from TLDR section and opening paragraph from "What This Means"
    summary_text = ''
    summary_lines = []
    opening_paragraph = ''
    opening_lines = []
    in_summary = False
    in_opening = False
    summary_ended = False
    
    for line in md_filtered.split('\n'):
        if '## 📝 Summary (TLDR)' in line or '## Summary (TLDR)' in line:
            in_summary = True
            continue
        if in_summary and line.startswith('## '):
            summary_ended = True
            in_summary = False
        if in_summary and line.strip():
            summary_lines.append(line)
        
        # Capture opening paragraph (What This Means section)
        if '## What This Means' in line:
            in_opening = True
            continue
        if in_opening and line.startswith('## '):
            in_opening = False
        # Include blank lines to preserve paragraph breaks in markdown
        if in_opening and not line.startswith('---'):
            opening_lines.append(line)
    
    if summary_lines:
        summary_text = '\n'.join(summary_lines).strip()
    
    if opening_lines:
        opening_paragraph = '\n'.join(opening_lines).strip()
    
    # Remove TLDR section and What This Means section from body
    lines_out = []
    skip_summary = False
    skip_opening = False
    for line in md_filtered.split('\n'):
        if '## 📝 Summary (TLDR)' in line or '## Summary (TLDR)' in line:
            skip_summary = True
            continue
        if skip_summary and line.startswith('##'):
            skip_summary = False
        
        if '## What This Means' in line:
            skip_opening = True
            continue
        if skip_opening and line.startswith('##'):
            skip_opening = False
        
        if not skip_summary and not skip_opening:
            lines_out.append(line)
    md_filtered = '\n'.join(lines_out)
    
    # Replace tier headings with friendlier names
    md_filtered = md_filtered.replace('## Tier 1: Beginner Summary', '## Getting Started')
    md_filtered = md_filtered.replace('## Tier 2: Intermediate', '## How to Do It')
    md_filtered = md_filtered.replace('## Tier 3: Advanced', '## Technical Details')
    md_filtered = md_filtered.replace('## Tier 4: Developer', '## For Developers')
    
    # Remove the principles markdown section - will be added inline in HTML instead
    # (was adding as section at end, now we'll add as inline badges)
    
    # Add Related Features links
    # Note: Looking for 'related_features' in meta (not in current template, but supporting it)
    related_features = meta.get('related_features', [])
    if related_features:
        features_html = '\n\n## Related WPShadow Features\n\n'
        for feature_slug in related_features:
            # Convert slug to readable name
            feature_name = feature_slug.replace('-', ' ').title()
            features_html += f'- [{feature_name}](/features/{feature_slug})\n'
        md_filtered += features_html
    
    # Replace backup warnings with WPShadow backup reminder
    backup_reminder = '<div class="wp-block-notice notice-info"><strong>💾 Backup reminder:</strong> WPShadow includes an offsite backup tool with free registration. Make sure you\'re backed up before making database changes.</div>'
    md_filtered = md_filtered.replace('### ⚠️ Before You Start', f'### Before You Start\n\n{backup_reminder}')
    md_filtered = md_filtered.replace('**Create a backup.**', '**Create a backup.** WPShadow offers free offsite backups via our backup tool.')
    
    # Wrap code in <code> tags for inline code
    # (markdown will handle code blocks)
    
    # Convert markdown to HTML
    html_body = markdown.markdown(md_filtered, extensions=['extra', 'tables', 'fenced_code', 'toc'])
    
    # Extract headings for TOC
    headings = []
    for line in md_filtered.split('\n'):
        if line.startswith('##'):
            level = len(line) - len(line.lstrip('#'))
            text = line.lstrip('# ').strip()
            # Convert heading text to ID
            heading_id = text.lower().replace(' ', '-').replace(':', '').replace('(', '').replace(')', '')
            headings.append((level, text, heading_id))
    
    # Build TOC HTML with h2 (proper block format)
    toc_html = ''
    if headings:
        toc_html = '<!-- wp:heading -->\n<h2 class="wp-block-heading">Contents</h2>\n<!-- /wp:heading -->\n'
        toc_html += '<!-- wp:list {"className":"toc-list"} -->\n<ul class="toc-list">\n'
        for level, text, heading_id in headings:
            indent = '  ' * (level - 2)
            toc_html += f'{indent}<li><a href="#{heading_id}">{text}</a></li>\n'
        toc_html += '</ul><!-- /wp:list -->\n\n'
    
    # Build final HTML with WordPress Blocks
    final_html = ''
    
    # Summary block (proper block format with newlines)
    if summary_text:
        summary_html = markdown.markdown(summary_text, extensions=['extra'])
        final_html += '<!-- wp:group {"className":"summary-section"} -->\n'
        final_html += '<div class="wp-block-group summary-section">\n'
        final_html += summary_html
        final_html += '</div>\n'
        final_html += '<!-- /wp:group -->\n\n'
    
    # Opening paragraph block with special class (proper formatting)
    if opening_paragraph:
        opening_html = markdown.markdown(opening_paragraph, extensions=['extra'])
        # Add opening class to all paragraphs in the opening section
        opening_html = opening_html.replace('<p>', '<p class="opening">')
        # Wrap each paragraph in blocks
        opening_blocks = ''
        for p_tag in opening_html.split('</p>'):
            if p_tag.strip():
                opening_blocks += f'<!-- wp:paragraph {{"className":"opening"}} -->\n{p_tag}</p>\n<!-- /wp:paragraph -->\n'
        final_html += opening_blocks + '\n'
    
    # TOC
    final_html += toc_html
    
    # Main content wrapped in blocks
    # Split content by headings and paragraphs for proper block structure
    content_blocks = convert_html_to_blocks(html_body)
    final_html += content_blocks
    
    # Add Core Principles as inline badges at the end
    principles = meta.get('principles', [])
    if principles:
        final_html += '<!-- wp:paragraph {"className":"principles-badges"} -->\n'
        final_html += '<p class="principles-badges">'
        for i, principle in enumerate(principles):
            anchor = principle_to_anchor(principle)
            name = principle.lstrip('#').lstrip('0123456789-').replace('-', ' ').title()
            if i > 0:
                final_html += ' '
            final_html += f'<a href="/principles/#{anchor}" class="principle-badge">{name}</a>'
        final_html += '</p>\n'
        final_html += '<!-- /wp:paragraph -->\n'
    
    return final_html


def convert_html_to_blocks(html: str) -> str:
    """
    Convert HTML content to WordPress Gutenberg blocks.
    Wraps headings, paragraphs, lists, code blocks in proper block comments.
    """
    lines = html.split('\n')
    output = []
    in_list = False
    in_code = False
    in_table = False
    
    for line in lines:
        stripped = line.strip()
        
        # Handle headings
        if stripped.startswith('<h2'):
            if in_list:
                output.append('<!-- /wp:list -->\n')
                in_list = False
            output.append('<!-- wp:heading -->')
            output.append(line)
            output.append('<!-- /wp:heading -->')
        
        elif stripped.startswith('<h3'):
            if in_list:
                output.append('<!-- /wp:list -->\n')
                in_list = False
            output.append('<!-- wp:heading {"level":3} -->')
            output.append(line)
            output.append('<!-- /wp:heading -->')
        
        elif stripped.startswith('<h4'):
            if in_list:
                output.append('<!-- /wp:list -->\n')
                in_list = False
            output.append('<!-- wp:heading {"level":4} -->')
            output.append(line)
            output.append('<!-- /wp:heading -->')
        
        # Handle paragraphs
        elif stripped.startswith('<p>') and not in_code:
            if in_list:
                output.append('<!-- /wp:list -->\n')
                in_list = False
            output.append('<!-- wp:paragraph -->')
            output.append(line)
            output.append('<!-- /wp:paragraph -->')
        
        # Handle lists
        elif stripped.startswith('<ul>') and not in_list:
            in_list = True
            output.append('<!-- wp:list -->')
            output.append(line)
        elif stripped.startswith('</ul>') and in_list:
            output.append(line)
            output.append('<!-- /wp:list -->')
            in_list = False
        
        elif stripped.startswith('<ol>') and not in_list:
            in_list = True
            output.append('<!-- wp:list {"ordered":true} -->')
            output.append(line)
        elif stripped.startswith('</ol>') and in_list:
            output.append(line)
            output.append('<!-- /wp:list -->')
            in_list = False
        
        # Handle code blocks
        elif stripped.startswith('<pre><code') or stripped.startswith('<pre class'):
            in_code = True
            # Extract language from class if present
            lang_match = 'language-'
            if lang_match in line:
                lang_start = line.index(lang_match) + len(lang_match)
                lang_end = line.index('"', lang_start)
                language = line[lang_start:lang_end]
                output.append(f'<!-- wp:code {{"language":"{language}"}} -->')
            else:
                output.append('<!-- wp:code -->')
            output.append('<pre class="wp-block-code"><code>')
        elif stripped.startswith('</code></pre>') or (in_code and '</pre>' in stripped):
            output.append('</code></pre>')
            output.append('<!-- /wp:code -->')
            in_code = False
        
        # Handle tables
        elif stripped.startswith('<table'):
            in_table = True
            output.append('<!-- wp:table -->')
            output.append('<figure class="wp-block-table">')
            output.append(line)
        elif stripped.startswith('</table>') and in_table:
            output.append(line)
            output.append('</figure>')
            output.append('<!-- /wp:table -->')
            in_table = False
        
        # Handle divs (notices, etc)
        elif stripped.startswith('<div class="wp-block-notice'):
            output.append('<!-- wp:group {"className":"notice-info"} -->')
            output.append(line)
        elif stripped.endswith('</div>') and 'wp-block-notice' in ''.join(output[-3:]):
            output.append(line)
            output.append('<!-- /wp:group -->\n')
        
        else:
            output.append(line)
    
    # Close any open blocks
    if in_list:
        output.append('<!-- /wp:list -->')
    
    return '\n'.join(output)


def ensure_term(site: str, auth_header: str, taxonomy: str, name: str, slug: str | None = None) -> int:
    slug = slug or name
    base = f"{site}/wp-json/wp/v2/{taxonomy}"
    query = f"{base}?slug={urllib.parse.quote(slug)}"
    status, existing = http_request(query, 'GET', {'Authorization': auth_header})
    if isinstance(existing, list) and existing:
        return existing[0]['id']
    payload = {'name': name, 'slug': slug}
    status, created = http_request(base, 'POST', {'Authorization': auth_header}, payload)
    return created['id']


def find_post(site: str, auth_header: str, slug: str):
    url = f"{site}/wp-json/wp/v2/kb?slug={urllib.parse.quote(slug)}"
    status, data = http_request(url, 'GET', {'Authorization': auth_header})
    if isinstance(data, list) and data:
        return data[0]
    return None


def publish(article_path: Path):
    env = load_env()
    site = env['WP_SITE_URL'].rstrip('/')
    auth_header = basic_auth_header(env['WP_USERNAME'], env['WP_APP_PASSWORD'])

    meta, body_md = parse_markdown(article_path)
    
    # Validate article content before publishing
    validation_errors = validate_article_content(meta, body_md, article_path)
    if validation_errors:
        print(f'❌ Article validation failed: {article_path.name}')
        for error in validation_errors:
            print(f'   • {error}')
        print('\n💡 This article appears to be a stub with placeholder content.')
        print('   Please write real content before publishing.')
        sys.exit(1)
    
    slug = article_path.stem

    title = meta.get('title') or slug.replace('-', ' ').title()
    description = meta.get('description') or ''
    category = meta.get('category') or 'general'
    difficulty = meta.get('difficulty') or 'intermediate'
    tags = meta.get('tags') or []
    status = meta.get('status') or 'draft'

    read_time = meta.get('read_time')
    principles = meta.get('principles') or []
    related = meta.get('related_articles') or []
    course_link = meta.get('course_link') or ''
    course_name = meta.get('course_name') or ''
    kb_last_updated = meta.get('last_updated') or ''

    html = markdown_to_html(body_md, meta)

    # Ensure taxonomies exist
    cat_id = ensure_term(site, auth_header, 'kb_category', category, slug=category)
    diff_id = ensure_term(site, auth_header, 'kb_difficulty', difficulty, slug=difficulty)
    tag_ids = []
    for tag in tags:
        tag_id = ensure_term(site, auth_header, 'tags', tag, slug=tag)
        tag_ids.append(tag_id)

    existing = find_post(site, auth_header, slug)
    meta_fields = {
        'read_time': int(read_time) if read_time else None,
        'principles': principles,
        'related_articles': related,
        'course_link': course_link,
        'course_name': course_name,
        'article_status': status,
        'kb_last_updated': kb_last_updated,
    }
    # Strip None values so the API only receives populated meta
    meta_fields = {k: v for k, v in meta_fields.items() if v not in [None, '', []]}

    payload = {
        'title': title,
        'content': html,
        'slug': slug,
        'status': status,
        'author': 2,  # wpshadowteam
        'excerpt': description,
        'kb_category': [cat_id],
        'kb_difficulty': [diff_id],
        'tags': tag_ids,
        'meta': meta_fields,
    }

    if existing:
        post_id = existing['id']
        url = f"{site}/wp-json/wp/v2/kb/{post_id}"
        status_code, resp = http_request(url, 'PUT', {'Authorization': auth_header}, payload)
        action = 'updated'
    else:
        url = f"{site}/wp-json/wp/v2/kb"
        status_code, resp = http_request(url, 'POST', {'Authorization': auth_header}, payload)
        action = 'created'

    link = resp.get('link') or resp.get('guid', {}).get('rendered')
    print(f"✅ KB article {action}: {title}")
    if link:
        print(f"   URL: {link}")


def main():
    if len(sys.argv) != 2:
        print("Usage: python3 tools/publish_kb.py kb-articles/path/article.md")
        sys.exit(1)
    path = Path(sys.argv[1])
    if not path.exists():
        print(f"❌ File not found: {path}")
        sys.exit(1)
    publish(path)


if __name__ == '__main__':
    main()
