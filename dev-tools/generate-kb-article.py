#!/usr/bin/env python3
"""
KB Article Generator

Generates Knowledge Base article content from issue templates.
This script is used by the GitHub workflow to create initial KB article structure.

Usage:
    python3 generate-kb-article.py \
        --title "Article Title" \
        --category "Getting Started" \
        --purpose "What this solves" \
        --topics "topic1,topic2,topic3" \
        --output output.md
"""

import argparse
import sys
from datetime import datetime
from pathlib import Path


def generate_kb_article(
    title: str,
    category: str,
    purpose: str,
    topics: list,
    audience: list = None,
    issue_number: int = None,
) -> str:
    """Generate KB article markdown content."""
    
    if audience is None:
        audience = []
    
    # Generate slug from title
    slug = title.lower().replace(" ", "-").replace("–", "-")
    slug = "".join(c if c.isalnum() or c == "-" else "" for c in slug)
    slug = "-".join(filter(None, slug.split("-")))
    
    now = datetime.utcnow().isoformat() + "Z"
    
    # Build topics section
    topics_section = ""
    if topics:
        for topic in topics:
            topics_section += f"- {topic.strip()}\n"
    else:
        topics_section = "- To be determined\n"
    
    # Build audience section
    audience_section = ""
    if audience:
        audience_section = f"\nThis guide is intended for: {', '.join(audience)}.\n"
    
    # Build the article
    article = f"""---
title: "{title}"
slug: "{slug}"
description: "{purpose}"
category: "{category}"
date: {now}
updated: {now}
author: "community"
status: "draft"
"""
    
    if issue_number:
        article += f'issue: "{issue_number}"\n'
    
    article += f"""---

# {title}

## Overview

{purpose}{audience_section}

## Table of Contents

1. [Getting Started](#getting-started)
2. [Key Concepts](#key-concepts)
3. [Step-by-Step Guide](#step-by-step-guide)
4. [Troubleshooting](#troubleshooting)
5. [Related Resources](#related-resources)

## Getting Started

This section introduces the topic and provides context for readers.

### Prerequisites

- Requirement 1
- Requirement 2
- Requirement 3

### Quick Start

1. Step 1
2. Step 2
3. Step 3

## Key Concepts

### Concept 1

Explanation and details about this concept.

### Concept 2

Explanation and details about this concept.

## Step-by-Step Guide

### Method 1: Using Feature X

1. Open the WPShadow dashboard
2. Navigate to [section]
3. Click [button]
4. Fill in the details
5. Click Save

**Expected Result:** [What should happen]

### Method 2: Using Feature Y

1. Step 1
2. Step 2
3. Step 3

**Expected Result:** [What should happen]

## Troubleshooting

### Issue 1: Error Message X

**Cause:** Description of what causes this

**Solution:**
1. Action 1
2. Action 2
3. Action 3

### Issue 2: Feature Not Working

**Cause:** Description of what causes this

**Solution:**
1. Check that...
2. Verify that...
3. Try...

## Tips & Best Practices

- Tip 1
- Tip 2
- Tip 3

## Related Resources

- [Related KB Article](link)
- [Related Documentation](link)
- [WPShadow Repository](https://github.com/thisismyurl/wpshadow)

## Getting Help

If you're still having issues:

1. Check the [FAQ](link-to-faq)
2. Review the [troubleshooting guide](link)
3. [Create an issue](https://github.com/thisismyurl/wpshadow/issues) on GitHub
4. Join our [community discussions](link)

---

**Last Updated:** {now}

**Status:** This article is in draft. Review and update content before publishing.
"""
    
    return article


def main():
    parser = argparse.ArgumentParser(
        description="Generate Knowledge Base article markdown content"
    )
    parser.add_argument("--title", required=True, help="Article title")
    parser.add_argument("--category", default="General", help="Article category")
    parser.add_argument("--purpose", required=True, help="Article purpose/description")
    parser.add_argument(
        "--topics", default="", help="Comma-separated list of topics to cover"
    )
    parser.add_argument(
        "--audience", default="", help="Comma-separated target audience"
    )
    parser.add_argument("--issue", type=int, help="GitHub issue number")
    parser.add_argument("--output", help="Output file path (optional)")
    
    args = parser.parse_args()
    
    # Parse topics
    topics = [t.strip() for t in args.topics.split(",") if t.strip()]
    
    # Parse audience
    audience = [a.strip() for a in args.audience.split(",") if a.strip()]
    
    # Generate article
    article = generate_kb_article(
        title=args.title,
        category=args.category,
        purpose=args.purpose,
        topics=topics,
        audience=audience,
        issue_number=args.issue,
    )
    
    if args.output:
        output_path = Path(args.output)
        output_path.parent.mkdir(parents=True, exist_ok=True)
        output_path.write_text(article, encoding="utf-8")
        print(f"✅ KB article generated: {output_path}")
    else:
        print(article)


if __name__ == "__main__":
    main()
