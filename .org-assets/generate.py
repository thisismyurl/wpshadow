#!/usr/bin/env python3
"""
Generate WordPress.org listing assets (banner + icon) for This Is My URL Shadow.

Outputs into .org-assets/ at .org SVN /assets/ directory specs:
  - banner-1544x500.png
  - banner-772x250.png
  - icon-256x256.png
  - icon-128x128.png

Strategy:
  - OpenAI gpt-image-1 generates the visual background (no text — image
    models render text poorly).
  - PIL composites brand wordmark + tagline using Liberation Sans Bold
    on the banner.
  - Icon is generated as a clean diagnostic-dial mark, downscaled to
    256 and 128 with high-quality Lanczos.
"""
import base64
import json
import os
import sys
import urllib.request
import urllib.error
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

HERE = Path(__file__).resolve().parent
RAW = HERE / "raw"
RAW.mkdir(parents=True, exist_ok=True)

API_KEY = os.environ.get("OPENAI_API_KEY") or os.environ.get("THISISMYURL_OPENAI_API_KEY")
if not API_KEY:
    sys.exit("error: OPENAI_API_KEY not set")

ICON_PROMPT = (
    "A clean flat vector icon design. "
    "Centred composition: a circular gauge-dial mark in deep navy blue "
    "(#1e3a5f), drawn as a thin geometric arc forming roughly three-quarters "
    "of a circle, with a single highlighted arc segment in warm amber "
    "(#f59e0b) at the upper right indicating peak health. A small dark "
    "navy dot sits at the centre as a pivot point, and a single thin "
    "navy needle pointer extends from the centre dot toward the upper-right. "
    "Modern minimal flat vector aesthetic, two-colour palette (navy + amber "
    "on white). Pure white background. "
    "ABSOLUTELY NO LETTERS, NO TEXT, NO WORDS, NO ALPHABET CHARACTERS of "
    "any kind anywhere in the image. NO logos. NO WordPress 'W' mark or "
    "WordPress branding of any kind. NO icons inside the dial. "
    "The dial should be the only graphic element. "
    "No gradients, no drop shadows, no decorative flourishes. "
    "Perfectly centred, ample whitespace padding. Square 1:1 format."
)

BANNER_PROMPT = (
    "A clean modern hero banner background. Soft warm off-white background "
    "(#fafaf7). In the LEFT QUARTER ONLY of the canvas, a small stylised "
    "circular gauge-dial mark in deep navy blue (#1e3a5f) with a single "
    "highlighted arc segment in warm amber (#f59e0b) and a thin needle "
    "pointer. The dial is small relative to the canvas — it occupies less "
    "than 20% of the canvas width. The RIGHT THREE QUARTERS of the canvas "
    "is completely empty off-white negative space, intentionally left "
    "blank for text overlay. "
    "ABSOLUTELY NO LETTERS, NO TEXT, NO WORDS, NO LOGOS, NO 'W' marks, "
    "NO WordPress branding, NO icons inside or around the dial. "
    "Calm, technical, professional. No people, no laptops, no devices, "
    "no binary code, no padlocks, no shields, no security clichés, no "
    "stock-photography clichés. Modern flat vector illustration aesthetic. "
    "Wide horizontal landscape composition."
)


def call_openai_image(prompt: str, size: str, label: str) -> bytes:
    """Call OpenAI gpt-image-1; return PNG bytes."""
    print(f"[openai] generating {label} at {size} ...", flush=True)
    req = urllib.request.Request(
        "https://api.openai.com/v1/images/generations",
        data=json.dumps({
            "model": "gpt-image-1",
            "prompt": prompt,
            "size": size,
            "n": 1,
        }).encode("utf-8"),
        headers={
            "Authorization": f"Bearer {API_KEY}",
            "Content-Type": "application/json",
        },
        method="POST",
    )
    try:
        with urllib.request.urlopen(req, timeout=120) as resp:
            payload = json.loads(resp.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        body = e.read().decode("utf-8", errors="replace")
        sys.exit(f"openai error {e.code}: {body}")
    b64 = payload["data"][0]["b64_json"]
    return base64.b64decode(b64)


def write_png(data: bytes, path: Path) -> None:
    path.write_bytes(data)
    print(f"[write] {path.relative_to(HERE)} ({len(data) // 1024} KB)", flush=True)


def find_font(size: int) -> ImageFont.FreeTypeFont:
    candidates = [
        "/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf",
    ]
    for path in candidates:
        if os.path.exists(path):
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def find_font_regular(size: int) -> ImageFont.FreeTypeFont:
    candidates = [
        "/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
    ]
    for path in candidates:
        if os.path.exists(path):
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def make_icons(raw_path: Path) -> None:
    src = Image.open(raw_path).convert("RGBA")
    # Centre-crop to square if not already
    w, h = src.size
    side = min(w, h)
    src = src.crop(((w - side) // 2, (h - side) // 2,
                    (w + side) // 2, (h + side) // 2))
    for size in (256, 128):
        out = src.resize((size, size), Image.LANCZOS)
        out.save(HERE / f"icon-{size}x{size}.png", optimize=True)
        print(f"[write] icon-{size}x{size}.png", flush=True)


def make_banners(raw_path: Path) -> None:
    src = Image.open(raw_path).convert("RGB")
    sw, sh = src.size

    # WordPress.org banner sizes:
    #   1544x500 = 3.088:1
    #   772x250  = 3.088:1
    target_ratio = 1544 / 500
    src_ratio = sw / sh

    # Crop source to target aspect ratio (preserve left third where the dial sits)
    if src_ratio > target_ratio:
        new_w = int(sh * target_ratio)
        # Centre-crop horizontally
        left = (sw - new_w) // 2
        src_cropped = src.crop((left, 0, left + new_w, sh))
    else:
        new_h = int(sw / target_ratio)
        # Top-bias the crop to keep dial near vertical centre
        top = (sh - new_h) // 2
        src_cropped = src.crop((0, top, sw, top + new_h))

    title = "This Is My URL Shadow"
    tagline = "Local-first WordPress diagnostics and safer fixes"

    for w, h in [(1544, 500), (772, 250)]:
        scaled = src_cropped.resize((w, h), Image.LANCZOS).convert("RGBA")
        canvas = Image.new("RGBA", (w, h), (0, 0, 0, 0))
        canvas.paste(scaled, (0, 0))
        draw = ImageDraw.Draw(canvas)

        # Text occupies the right 60% of the canvas (the gauge mark is in
        # the left 25–40% of the generated background). Find the largest
        # title size that fits inside that box and looks balanced.
        text_left = int(w * 0.40)
        right_pad = int(w * 0.04)
        text_box_w = w - text_left - right_pad

        # Binary-search the title size that fits the text_box_w
        def fit_size(text: str, font_finder, max_h_frac: float, max_w: int) -> int:
            lo, hi = 12, int(h * max_h_frac)
            best = lo
            while lo <= hi:
                mid = (lo + hi) // 2
                f = font_finder(mid)
                bbox = draw.textbbox((0, 0), text, font=f)
                if (bbox[2] - bbox[0]) <= max_w:
                    best = mid
                    lo = mid + 1
                else:
                    hi = mid - 1
            return best

        title_size = fit_size(title, find_font, 0.22, text_box_w)
        tagline_size = fit_size(tagline, find_font_regular, 0.10, text_box_w)
        title_font = find_font(title_size)
        tagline_font = find_font_regular(tagline_size)

        title_bbox = draw.textbbox((0, 0), title, font=title_font)
        title_h = title_bbox[3] - title_bbox[1]

        tagline_bbox = draw.textbbox((0, 0), tagline, font=tagline_font)
        tagline_h = tagline_bbox[3] - tagline_bbox[1]

        gap = int(h * 0.06)
        block_h = title_h + gap + tagline_h
        block_top = (h - block_h) // 2 - title_bbox[1]

        draw.text((text_left, block_top), title,
                  font=title_font, fill=(30, 58, 95, 255))  # navy
        draw.text((text_left, block_top + title_h + gap), tagline,
                  font=tagline_font, fill=(80, 90, 110, 255))  # cool grey

        out_path = HERE / f"banner-{w}x{h}.png"
        canvas.convert("RGB").save(out_path, "PNG", optimize=True)
        print(f"[write] banner-{w}x{h}.png (title={title_size}px, tagline={tagline_size}px)", flush=True)


def main() -> int:
    icon_raw = RAW / "icon-1024.png"
    banner_raw = RAW / "banner-1536x1024.png"

    if not icon_raw.exists():
        data = call_openai_image(ICON_PROMPT, "1024x1024", "icon")
        write_png(data, icon_raw)
    else:
        print(f"[skip] {icon_raw.name} already exists", flush=True)

    if not banner_raw.exists():
        data = call_openai_image(BANNER_PROMPT, "1536x1024", "banner")
        write_png(data, banner_raw)
    else:
        print(f"[skip] {banner_raw.name} already exists", flush=True)

    make_icons(icon_raw)
    make_banners(banner_raw)
    print("[done]", flush=True)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
