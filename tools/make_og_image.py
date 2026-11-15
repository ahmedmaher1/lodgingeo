#!/usr/bin/env python3
"""Generate og-image.png (1200x630) and apple-touch-icon.png (180x180) with Pillow."""
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parent.parent
FONTS = ["/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", "/Library/Fonts/Arial Bold.ttf", "C:/Windows/Fonts/arialbd.ttf"]
FONTS_REG = ["/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", "/Library/Fonts/Arial.ttf", "C:/Windows/Fonts/arial.ttf"]


def font(paths, size):
    for p in paths:
        if Path(p).is_file():
            return ImageFont.truetype(p, size)
    return ImageFont.load_default()


def og():
    img = Image.new("RGB", (1200, 630), "#ffffff")
    d = ImageDraw.Draw(img)
    text, size = "Lodgingeo — Global Hotels Database", 64
    f = font(FONTS, size)
    while size > 20 and d.textlength(text, font=f) > 1040:
        size -= 2
        f = font(FONTS, size)
    d.text((80, 240), text, font=f, fill="#1a1a1a")
    d.line((80, 330, 1120, 330), fill="#1a1a1a", width=2)
    d.text((80, 360), "lodgingeo.com", font=font(FONTS_REG, 32), fill="#555555")
    img.save(ROOT / "og-image.png", optimize=True)


def icon():
    img = Image.new("RGB", (180, 180), "#ffffff")
    d = ImageDraw.Draw(img)
    d.rectangle((6, 6, 173, 173), outline="#1a1a1a", width=8)
    d.polygon([(56, 40), (80, 40), (80, 118), (126, 118), (126, 140), (56, 140)], fill="#0b57d0")
    img.save(ROOT / "apple-touch-icon.png", optimize=True)


if __name__ == "__main__":
    og()
    icon()
    print("wrote og-image.png, apple-touch-icon.png")
