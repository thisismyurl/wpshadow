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


def markdown_to_html(md: str) -> str:
    if markdown is None:
        print('❌ Missing dependency: markdown. Install with: pip install markdown')
        sys.exit(1)
    
    # Remove sections we don't want published
    lines = md.split('\n')
    filtered_lines = []
    skip_until_next_section = False
    in_quality_checklist = False
    in_read_on_wpshadow = False
    
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
        
        # Skip the first H1 title
        if i == 0 and line.startswith('# '):
            continue
        
        filtered_lines.append(line)
    
    md_filtered = '\n'.join(filtered_lines).strip()
    
    # Extract summary from TLDR section
    summary_text = ''
    summary_lines = []
    in_summary = False
    summary_ended = False
    
    for line in md_filtered.split('\n'):
        if '## 📝 Summary (TLDR)' in line or '## Summary (TLDR)' in line:
            in_summary = True
            continue
        if in_summary and line.startswith('## '):
            summary_ended = True
            break
        if in_summary and line.strip():
            summary_lines.append(line)
    
    if summary_lines:
        summary_text = '\n'.join(summary_lines).strip()
    
    # Remove the TLDR section from body (we'll prepend summary separately)
    md_filtered = md_filtered.replace('## 📝 Summary (TLDR)\n', '').replace('## Summary (TLDR)\n', '')
    
    # Replace tier headings with friendlier names
    md_filtered = md_filtered.replace('## Tier 1: Beginner Summary', '## Getting Started')
    md_filtered = md_filtered.replace('## Tier 2: Intermediate', '## How to Do It')
    md_filtered = md_filtered.replace('## Tier 3: Advanced', '## Technical Details')
    md_filtered = md_filtered.replace('## Tier 4: Developer', '## For Developers')
    
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
    
    # Build TOC HTML
    toc_html = ''
    if headings:
        toc_html = '<div class="wp-block-columns toc-wrapper"><h3>Contents</h3><ul>\n'
        for level, text, heading_id in headings:
            indent = '  ' * (level - 2)
            toc_html += f'{indent}<li><a href="#{heading_id}">{text}</a></li>\n'
        toc_html += '</ul></div>\n\n'
    
    # Prepend summary (without label) and TOC
    final_html = ''
    if summary_text:
        summary_html = markdown.markdown(summary_text, extensions=['extra'])
        final_html = f'<div class="summary-section">{summary_html}</div>\n\n'
    
    final_html += toc_html + html_body
    
    return final_html


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

    html = markdown_to_html(body_md)

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
