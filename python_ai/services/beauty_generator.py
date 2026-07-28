from __future__ import annotations

import base64
import io
import uuid
from pathlib import Path
from typing import Any

from PIL import Image, ImageColor, ImageDraw, ImageFilter

from config import BASE_DIR, GENERATED_DIR

DEMO_FACE = BASE_DIR / "assets" / "demo-beauty-face.png"


def _color(value: str, fallback: str) -> tuple[int, int, int]:
    try:
        return ImageColor.getrgb(value.strip())
    except (ValueError, AttributeError):
        named = {
            "nude": "#B56D67", "rose": "#B84D61", "red": "#A71930",
            "berry": "#72263E", "pink": "#C85C78", "brown": "#714438",
            "black": "#2F211F", "dark brown": "#4B3028",
            "medium brown": "#705044", "soft brown": "#8A6658",
        }
        return ImageColor.getrgb(named.get(value.strip().lower(), fallback))


def _source_image(image_data: str | None) -> Image.Image:
    if image_data and "," in image_data:
        try:
            raw = base64.b64decode(image_data.split(",", 1)[1], validate=True)
            image = Image.open(io.BytesIO(raw)).convert("RGB")
            return image.resize((1024, 1024), Image.Resampling.LANCZOS)
        except Exception:
            pass
    return Image.open(DEMO_FACE).convert("RGB").resize(
        (1024, 1024), Image.Resampling.LANCZOS
    )


def _lip_preview(image: Image.Image, data: dict[str, Any], variant: int) -> None:
    red, green, blue = _color(str(data.get("color", "")), "#A71930")
    finish = str(data.get("finish", "Satin")).lower()
    layer = Image.new("RGBA", image.size, (0, 0, 0, 0))
    draw = ImageDraw.Draw(layer)

    # Calibrated to the bundled neutral face. Uploaded images should be
    # front-facing and centered for this deterministic demo.
    upper = [(409, 646), (472, 631), (512, 640), (553, 631), (616, 647),
             (558, 666), (512, 662), (465, 668)]
    lower = [(409, 647), (466, 670), (512, 681), (560, 669), (616, 647),
             (564, 699), (512, 711), (458, 698)]
    alpha = 142 if "tint" in finish else 190 if "satin" in finish else 214
    shade = (
        max(0, red - (variant - 2) * 12),
        max(0, green - (variant - 2) * 7),
        max(0, blue - (variant - 2) * 7),
        alpha,
    )
    draw.polygon(upper, fill=shade)
    draw.polygon(lower, fill=shade)
    if "gloss" in finish:
        draw.rounded_rectangle((472, 678, 553, 687), radius=5,
                               fill=(255, 245, 242, 115))
    if "ombre" in finish:
        draw.line((430, 650, 594, 650), fill=(70, 20, 25, 90), width=8)
    layer = layer.filter(ImageFilter.GaussianBlur(1.2))
    image.paste(layer, (0, 0), layer)


def _brow_preview(image: Image.Image, data: dict[str, Any], variant: int) -> None:
    red, green, blue = _color(str(data.get("color", "")), "#4B3028")
    shape = str(data.get("brow_shape", "Soft arch")).lower()
    thickness = str(data.get("thickness", "Medium")).lower()
    width = {"thin": 7, "medium": 11, "full": 15}.get(thickness, 11)
    lift = {"straight": 2, "natural": 10, "soft arch": 17,
            "high arch": 28, "rounded": 15}.get(shape, 17)
    lift += (variant - 2) * 3
    layer = Image.new("RGBA", image.size, (0, 0, 0, 0))
    draw = ImageDraw.Draw(layer)
    color = (red, green, blue, 155)
    for points in [
        [(304, 354), (360, 337), (423, 340 - lift), (485, 360)],
        [(540, 360), (602, 340 - lift), (665, 337), (721, 354)],
    ]:
        draw.line(points, fill=color, width=width, joint="curve")
        # Fine hair strokes prevent a solid sticker appearance.
        for index in range(0, len(points) - 1):
            x, y = points[index]
            draw.line((x, y + 5, x + 13, y - 7), fill=(red, green, blue, 105), width=2)
    layer = layer.filter(ImageFilter.GaussianBlur(.65))
    image.paste(layer, (0, 0), layer)


def generate_beauty_previews(data: dict[str, Any], count: int = 3) -> dict[str, Any]:
    area = str(data.get("area", "")).lower()
    if area not in {"lips", "eyebrows"}:
        raise ValueError("Beauty area must be lips or eyebrows.")
    source = _source_image(data.get("image_data"))
    images: list[str] = []
    for variant in range(1, count + 1):
        canvas = source.copy()
        if area == "lips":
            _lip_preview(canvas, data, variant)
        else:
            _brow_preview(canvas, data, variant)
        filename = GENERATED_DIR / f"beauty-{area}-{uuid.uuid4().hex}.png"
        canvas.save(filename, "PNG", optimize=True)
        images.append(f"/api/generated/{filename.name}")
    return {
        "provider": "demo",
        "model": "calibrated-beauty-preview-v1",
        "area": area,
        "images": images,
    }

