import base64
import io
import math
import random
import re
import time
import uuid
from pathlib import Path

import requests
from PIL import Image, ImageDraw, ImageFont

from config import (
    COMFYUI_CHECKPOINT,
    COMFYUI_DEMO_FALLBACK,
    COMFYUI_HEIGHT,
    COMFYUI_STEPS,
    COMFYUI_TIMEOUT,
    COMFYUI_URL,
    COMFYUI_WIDTH,
    GENERATED_DIR,
    OPENAI_API_KEY,
    OPENAI_IMAGE_MODEL,
    OPENAI_IMAGE_QUALITY,
    OPENAI_IMAGE_SIZE,
    OPENAI_TIMEOUT,
    PROVIDER,
)
from services.prompt_builder import build_nail_prompt


COLOR_LIBRARY = {
    "green": "#4f8065",
    "emerald": "#176b55",
    "sage": "#9aaa84",
    "mint": "#a7d8c5",
    "red": "#a62d36",
    "burgundy": "#641f2b",
    "maroon": "#6d2430",
    "pink": "#df8fa7",
    "blush": "#e8b7bd",
    "rose": "#c96579",
    "purple": "#76518d",
    "lavender": "#b7a1d1",
    "blue": "#527aa3",
    "navy": "#253a62",
    "teal": "#347f80",
    "orange": "#df8244",
    "yellow": "#e2bd4f",
    "gold": "#c89b3c",
    "silver": "#aaaeb4",
    "black": "#252122",
    "white": "#f8f6f1",
    "cream": "#eadfc9",
    "beige": "#d3b89c",
    "nude": "#d6a58e",
    "brown": "#79523f",
    "gray": "#858282",
    "grey": "#858282",
}

SKIN_COLORS = ["#dca98d", "#c98e70", "#b8785c"]
GOLD = "#c89b3c"
INK = "#3f281d"


def _font(size: int, bold: bool = False) -> ImageFont.ImageFont:
    candidates = (
        ["C:/Windows/Fonts/arialbd.ttf", "C:/Windows/Fonts/calibrib.ttf"]
        if bold
        else ["C:/Windows/Fonts/arial.ttf", "C:/Windows/Fonts/calibri.ttf"]
    )
    for candidate in candidates:
        try:
            return ImageFont.truetype(candidate, size)
        except OSError:
            continue
    return ImageFont.load_default()


def _palette(value: str) -> list[str]:
    text = str(value or "").lower()
    selected = []
    for hex_color in re.findall(r"#[0-9a-f]{6}\b", text):
        normalized = hex_color.upper()
        if normalized not in selected:
            selected.append(normalized)
    for name, color in COLOR_LIBRARY.items():
        if re.search(rf"\b{re.escape(name)}\b", text) and color not in selected:
            selected.append(color)
    if not selected:
        selected = ["#d6a58e", "#f8f6f1", GOLD]
    if len(selected) == 1:
        selected.extend(["#f8f6f1", GOLD])
    elif len(selected) == 2:
        selected.append(GOLD)
    return selected[:5]


def _nail_box(shape: str, x: int, y: int, width: int, height: int):
    shape = shape.lower()
    if "stiletto" in shape:
        return [(x, y + 35), (x + width // 2, y), (x + width, y + 35),
                (x + width - 5, y + height - 18), (x + width // 2, y + height),
                (x + 5, y + height - 18)]
    if "coffin" in shape or "ballerina" in shape:
        cut = max(10, width // 6)
        return [(x + cut, y), (x + width - cut, y), (x + width, y + 34),
                (x + width - 5, y + height), (x + 5, y + height),
                (x, y + 34)]
    if "almond" in shape:
        return [(x + width // 2, y), (x + width - 8, y + 30),
                (x + width, y + height - 36), (x + width // 2, y + height),
                (x, y + height - 36), (x + 8, y + 30)]
    return None


def _draw_nail(draw: ImageDraw.ImageDraw, box, shape: str, fill: str,
               outline: str = GOLD, width: int = 5):
    x, y, right, bottom = box
    points = _nail_box(shape, x, y, right - x, bottom - y)
    if points:
        draw.polygon(points, fill=fill)
        draw.line(points + [points[0]], fill=outline, width=width, joint="curve")
    elif "square" in shape.lower():
        draw.rounded_rectangle(box, radius=16, fill=fill, outline=outline, width=width)
    else:
        draw.rounded_rectangle(box, radius=(right - x) // 2,
                               fill=fill, outline=outline, width=width)


def _inside_nail(shape: str, x: int, y: int, width: int, height: int):
    return _nail_box(shape, x, y, width, height)


def _decorate(draw: ImageDraw.ImageDraw, box, shape: str, base: str,
              accent: str, addons: list[str], style: str, variant: int,
              rng: random.Random):
    x, y, right, bottom = box
    width, height = right - x, bottom - y
    text = " ".join(addons).lower()
    style_text = style.lower()

    if variant == 1:
        # French/diagonal tip using the selected accent.
        draw.rounded_rectangle(
            (x + 8, y + 8, right - 8, y + max(28, height // 5)),
            radius=max(8, width // 3), fill=accent
        )
    elif variant == 2:
        # Statement diagonal ribbon.
        for offset in range(-8, 10):
            draw.line((x + 10, bottom - 45 + offset, right - 10, y + 55 + offset),
                      fill=accent, width=2)
    else:
        # Refined center/negative-space stripe.
        stripe = max(8, width // 7)
        draw.rounded_rectangle(
            (x + width // 2 - stripe, y + 18,
             x + width // 2 + stripe, bottom - 18),
            radius=stripe, fill=accent
        )

    if "glitter" in text or "glam" in style_text:
        for _ in range(32):
            px = rng.randint(x + 12, right - 12)
            py = rng.randint(y + 18, bottom - 18)
            r = rng.choice([1, 2, 3])
            draw.ellipse((px - r, py - r, px + r, py + r),
                         fill=rng.choice(["#fff6bf", GOLD, "#ffffff"]))

    if "gold foil" in text or "foil" in text:
        for _ in range(10):
            px = rng.randint(x + 14, right - 14)
            py = rng.randint(y + 22, bottom - 24)
            draw.polygon([(px, py), (px + rng.randint(4, 12), py - 4),
                          (px + rng.randint(7, 15), py + 7),
                          (px - 3, py + rng.randint(5, 13))], fill=GOLD)

    if "rhinestone" in text or "pearl" in text:
        gem = "#e9f5f7" if "rhinestone" in text else "#fff7dc"
        for index in range(3):
            cy = bottom - 28 - index * 18
            draw.ellipse((x + width // 2 - 6, cy - 6,
                          x + width // 2 + 6, cy + 6),
                         fill=gem, outline=GOLD, width=2)

    if "floral" in text or "flower" in text:
        cx, cy = x + width // 2, y + height // 2
        for angle in range(0, 360, 72):
            dx = int(math.cos(math.radians(angle)) * 12)
            dy = int(math.sin(math.radians(angle)) * 12)
            draw.ellipse((cx + dx - 7, cy + dy - 7,
                          cx + dx + 7, cy + dy + 7),
                         fill="#fff7f3", outline=accent, width=1)
        draw.ellipse((cx - 5, cy - 5, cx + 5, cy + 5), fill=GOLD)

    if "chrome" in text:
        draw.line((x + 18, y + 24, right - 18, bottom - 30),
                  fill="#ffffff", width=7)


def _mix(color_a: str, color_b: str, amount: float) -> str:
    a = tuple(int(color_a[index:index + 2], 16) for index in (1, 3, 5))
    b = tuple(int(color_b[index:index + 2], 16) for index in (1, 3, 5))
    rgb = tuple(
        round(a[channel] * (1 - amount) + b[channel] * amount)
        for channel in range(3)
    )
    return "#{:02x}{:02x}{:02x}".format(*rgb)


def _gradient(size: tuple[int, int], top: str, bottom: str) -> Image.Image:
    strip = Image.new("RGB", (1, size[1]), top)
    pixels = strip.load()
    for y in range(size[1]):
        color = _mix(top, bottom, y / max(size[1] - 1, 1))
        pixels[0, y] = tuple(int(color[i:i + 2], 16) for i in (1, 3, 5))
    return strip.resize(size)


def _demo_nail_palette(value: str) -> list[str]:
    chosen = _palette(value)
    primary = chosen[0]
    if len(chosen) >= 2 and chosen[1] not in {"#f8f6f1", GOLD}:
        secondary = chosen[1]
    else:
        secondary = _mix(primary, "#ffffff", 0.34)
    return [
        primary,
        secondary,
        _mix(primary, "#171116", 0.24),
        _mix(primary, "#ffffff", 0.58),
        GOLD,
    ]


def _save_demo_illustrated(data: dict, number: int) -> Path:
    """Create an advanced free manicure illustration based on preferences."""
    scale = 2
    size = 1024
    canvas = _gradient(
        (size * scale, size * scale),
        ["#f7f0e8", "#f4eee7", "#f8f2e8"][number - 1],
        ["#eadacb", "#e6ded5", "#eee0c8"][number - 1],
    )
    draw = ImageDraw.Draw(canvas)
    rng = random.Random(f"advanced-{data}-{number}")

    def box(values):
        return tuple(int(value * scale) for value in values)

    palette = _demo_nail_palette(data.get("color", ""))
    border = [INK, "#a97821", "#d1aa46"][number - 1]
    skin = SKIN_COLORS[(number - 1) % len(SKIN_COLORS)]
    skin_shadow = _mix(skin, "#5b3528", 0.22)
    skin_light = _mix(skin, "#ffffff", 0.2)

    # Floating presentation card.
    draw.rounded_rectangle(
        box((44, 44, 980, 980)), radius=46 * scale,
        fill="#fffdfa", outline=border, width=5 * scale
    )
    draw.text(box((80, 72)), f"NBW SIGNATURE CONCEPT 0{number}",
              fill=GOLD, font=_font(17 * scale, True))
    draw.text(box((80, 107)), str(data["style"]).title(),
              fill=INK, font=_font(32 * scale, True))
    draw.text(box((80, 151)),
              f"{data['occasion']} · {data['nail_shape']} · {data['nail_length']}",
              fill="#765340", font=_font(18 * scale))

    # Soft shadow beneath the hand.
    draw.ellipse(box((190, 776, 840, 890)), fill="#ddcec1")

    # Palm and wrist.
    draw.rounded_rectangle(
        box((286, 485, 742, 858)), radius=125 * scale,
        fill=skin_shadow
    )
    draw.rounded_rectangle(
        box((274, 470, 730, 846)), radius=125 * scale,
        fill=skin
    )
    draw.rounded_rectangle(
        box((410, 760, 600, 938)), radius=60 * scale,
        fill=skin
    )

    # Fingers are staggered to resemble a relaxed top-down hand.
    finger_x = [273, 363, 453, 543, 633]
    finger_top = [308, 235, 207, 247, 330]
    finger_width = [79, 84, 87, 83, 75]
    finger_bottom = [605, 575, 570, 584, 620]

    for index in range(5):
        x = finger_x[index]
        top = finger_top[index]
        right = x + finger_width[index]
        bottom = finger_bottom[index]

        # Finger shadow and finger.
        draw.rounded_rectangle(
            box((x + 7, top + 12, right + 7, bottom + 12)),
            radius=(finger_width[index] // 2) * scale,
            fill=skin_shadow
        )
        draw.rounded_rectangle(
            box((x, top, right, bottom)),
            radius=(finger_width[index] // 2) * scale,
            fill=skin
        )
        draw.line(
            box((x + 17, bottom - 42, right - 17, bottom - 39)),
            fill=skin_light, width=2 * scale
        )

        length_text = str(data.get("nail_length", "")).lower()
        nail_height = 112
        if "medium" in length_text:
            nail_height = 138
        if "long" in length_text or "extra" in length_text:
            nail_height = 168

        margin = 10
        nail_box = (
            x + margin,
            top + 13,
            right - margin,
            min(top + 13 + nail_height, bottom - 50),
        )
        base = palette[(index + number - 1) % 4]
        accent = palette[(index + number) % 4]

        # Nail bed shadow, polish, and salon-gloss reflection.
        shadow_box = (
            nail_box[0] + 4, nail_box[1] + 7,
            nail_box[2] + 4, nail_box[3] + 7
        )
        _draw_nail(draw, box(shadow_box), data["nail_shape"],
                   _mix(base, "#000000", 0.25), outline=skin_shadow,
                   width=2 * scale)
        scaled_nail_box = box(nail_box)
        _draw_nail(draw, scaled_nail_box, data["nail_shape"],
                   base, outline=_mix(base, GOLD, 0.35), width=2 * scale)
        _decorate(
            draw, scaled_nail_box, data["nail_shape"], base, accent,
            list(data.get("add_ons") or []), data["style"], number, rng
        )
        highlight_x = (nail_box[0] + 13) * scale
        draw.rounded_rectangle(
            (
                highlight_x,
                (nail_box[1] + 22) * scale,
                highlight_x + 6 * scale,
                (nail_box[3] - 25) * scale,
            ),
            radius=3 * scale,
            fill=_mix(base, "#ffffff", 0.72)
        )

    # Color swatches and compact specification strip.
    draw.rounded_rectangle(
        box((78, 858, 946, 946)), radius=24 * scale,
        fill="#f8f2e9", outline="#e7d5c1", width=2 * scale
    )
    for index, color in enumerate(palette[:4]):
        left = 102 + index * 45
        draw.ellipse(box((left, 883, left + 26, 909)),
                     fill=color, outline="#ffffff", width=2 * scale)
    addons = list(data.get("add_ons") or [])
    addon_label = ", ".join(addons) if addons else "Clean finish"
    draw.text(box((300, 877)), f"Palette: {data['color']}",
              fill=INK, font=_font(17 * scale, True))
    draw.text(box((300, 907)), f"Details: {addon_label[:60]}",
              fill="#765340", font=_font(15 * scale))

    # Downsample for smoother, sharper edges.
    canvas = canvas.resize((size, size), Image.Resampling.LANCZOS)
    filename = GENERATED_DIR / f"nail-demo-advanced-{uuid.uuid4().hex}.png"
    canvas.save(filename, "PNG", optimize=True)
    return filename


def _photo_nail_layer(
    width: int,
    height: int,
    shape: str,
    base: str,
    accent: str,
    addons: list[str],
    variant: int,
    rng: random.Random,
) -> Image.Image:
    layer = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    mask = Image.new("L", (width, height), 0)
    mask_draw = ImageDraw.Draw(mask)
    inset = 3
    shape_name = shape.lower()

    if "stiletto" in shape_name:
        points = [
            (width // 2, inset), (width - inset, height // 3),
            (width - 7, height - inset), (7, height - inset),
            (inset, height // 3),
        ]
        mask_draw.polygon(points, fill=242)
    elif "coffin" in shape_name or "ballerina" in shape_name:
        cut = max(7, width // 6)
        points = [
            (cut, inset), (width - cut, inset), (width - inset, height // 4),
            (width - 6, height - inset), (6, height - inset),
            (inset, height // 4),
        ]
        mask_draw.polygon(points, fill=242)
    elif "lipstick" in shape_name:
        points = [
            (inset, height // 5), (width - inset, inset),
            (width - inset, height - inset), (inset, height - inset),
        ]
        mask_draw.polygon(points, fill=242)
    elif "mountain" in shape_name:
        points = [
            (width // 2, inset), (width - inset, height // 3),
            (width - 5, height - inset), (5, height - inset),
            (inset, height // 3),
        ]
        mask_draw.polygon(points, fill=242)
    elif "cutout" in shape_name:
        mask_draw.rounded_rectangle(
            (inset, inset, width - inset, height - inset),
            radius=max(10, width // 3), fill=242
        )
        notch = max(7, width // 6)
        mask_draw.polygon(
            [
                (width // 2 - notch, inset),
                (width // 2, inset + notch),
                (width // 2 + notch, inset),
            ],
            fill=0,
        )
    elif "rounded square" in shape_name or "squoval" in shape_name:
        mask_draw.rounded_rectangle(
            (inset, inset, width - inset, height - inset),
            radius=max(12, width // 3), fill=242
        )
    elif "square" in shape_name:
        mask_draw.rounded_rectangle(
            (inset, inset, width - inset, height - inset),
            radius=max(4, width // 12), fill=242
        )
    elif "almond" in shape_name:
        points = [
            (width // 2, inset), (width - 6, height // 4),
            (width - inset, height * 3 // 4), (width * 3 // 4, height - inset),
            (width // 4, height - inset), (inset, height * 3 // 4),
            (6, height // 4),
        ]
        mask_draw.polygon(points, fill=242)
    else:
        mask_draw.rounded_rectangle(
            (inset, inset, width - inset, height - inset),
            radius=max(12, width // 2), fill=242
        )

    polish = Image.new("RGBA", (width, height), base + "f2")
    art = ImageDraw.Draw(polish)

    if variant == 1:
        art.rounded_rectangle(
            (0, 0, width, max(13, height // 5)),
            radius=max(8, width // 3), fill=accent + "f5"
        )
    elif variant == 2:
        art.polygon(
            [(0, height * 3 // 4), (width, height // 4),
             (width, height // 2), (0, height)],
            fill=accent + "e8"
        )
    else:
        stripe = max(7, width // 6)
        art.rounded_rectangle(
            (width // 2 - stripe, 5, width // 2 + stripe, height - 5),
            radius=stripe, fill=accent + "e8"
        )

    details = " ".join(addons).lower()
    if "glitter" in details:
        for _ in range(22):
            x = rng.randint(7, width - 7)
            y = rng.randint(8, height - 8)
            radius = rng.choice([1, 1, 2])
            art.ellipse((x - radius, y - radius, x + radius, y + radius),
                        fill=rng.choice(["#ffffffdc", "#f6d778e8", "#d9eef8d8"]))
    if "gold foil" in details or "foil" in details:
        for _ in range(7):
            x = rng.randint(8, width - 12)
            y = rng.randint(12, height - 14)
            art.polygon([(x, y), (x + 7, y - 3), (x + 10, y + 6),
                         (x + 2, y + 10)], fill="#d3a134e8")
    if "rhinestone" in details or "pearl" in details:
        gem = "#eaf8fff0" if "rhinestone" in details else "#fff4d6f0"
        for index in range(3):
            y = height - 16 - index * 10
            art.ellipse((width // 2 - 3, y - 3, width // 2 + 3, y + 3),
                        fill=gem, outline="#c89b3cee", width=1)
    if "floral" in details or "flower" in details:
        cx, cy = width * 2 // 3, height // 2
        for dx, dy in [(0, -7), (7, 0), (0, 7), (-7, 0)]:
            art.ellipse((cx + dx - 4, cy + dy - 4,
                         cx + dx + 4, cy + dy + 4),
                        fill="#fff8f0e8")
        art.ellipse((cx - 3, cy - 3, cx + 3, cy + 3), fill="#d3a134f0")
    if "chrome" in details:
        art.line((width // 4, 9, width * 3 // 4, height - 10),
                 fill="#ffffffcc", width=4)

    # Curved glossy reflection that preserves a realistic polish appearance.
    art.rounded_rectangle(
        (max(7, width // 5), 10, max(11, width // 5 + 5), height * 2 // 3),
        radius=3, fill="#ffffffe0"
    )
    art.ellipse(
        (width // 2 - 8, height - 15, width // 2 + 8, height - 8),
        fill="#00000018"
    )
    polish.putalpha(mask)
    layer.alpha_composite(polish)
    return layer


def _apply_photo_polish(
    canvas: Image.Image,
    region: tuple[int, int, int, int, int],
    shape: str,
    base: str,
    accent: str,
    addons: list[str],
    style: str,
    variant: int,
    nail_index: int,
    rng: random.Random,
) -> None:
    """Fill a calibrated shape mask while retaining the photographed nail."""
    region_left, region_top, region_right, region_bottom, angle = region
    region_width = region_right - region_left
    region_height = region_bottom - region_top

    local_mask = Image.new("L", (region_width, region_height), 0)
    local_draw = ImageDraw.Draw(local_mask)
    shape_name = shape.lower()
    inset = 2
    if "stiletto" in shape_name or "mountain" in shape_name:
        local_draw.polygon(
            [
                (region_width // 2, inset),
                (region_width - inset, region_height // 3),
                (region_width - 4, region_height - 5),
                (4, region_height - 5),
                (inset, region_height // 3),
            ],
            fill=218,
        )
    elif "coffin" in shape_name or "ballerina" in shape_name:
        cut = max(5, region_width // 7)
        local_draw.polygon(
            [
                (cut, inset), (region_width - cut, inset),
                (region_width - inset, region_height // 4),
                (region_width - 4, region_height - 5),
                (4, region_height - 5), (inset, region_height // 4),
            ],
            fill=218,
        )
    elif "lipstick" in shape_name:
        local_draw.polygon(
            [
                (inset, region_height // 5), (region_width - inset, inset),
                (region_width - 4, region_height - 5),
                (4, region_height - 5),
            ],
            fill=218,
        )
    elif "cutout" in shape_name:
        local_draw.rounded_rectangle(
            (inset, inset, region_width - inset, region_height - 4),
            radius=max(9, region_width // 3),
            fill=218,
        )
        notch = max(5, region_width // 7)
        local_draw.polygon(
            [
                (region_width // 2 - notch, inset),
                (region_width // 2, inset + notch),
                (region_width // 2 + notch, inset),
            ],
            fill=0,
        )
    elif "square" in shape_name and "rounded" not in shape_name:
        shoulder = max(3, region_width // 10)
        local_draw.polygon(
            [
                (shoulder, inset), (region_width - shoulder, inset),
                (region_width - inset, region_height // 4),
                (region_width - 4, region_height - 7),
                (4, region_height - 7), (inset, region_height // 4),
            ],
            fill=218,
        )
    elif "rounded square" in shape_name or "squoval" in shape_name:
        local_draw.rounded_rectangle(
            (inset, inset, region_width - inset, region_height - 4),
            radius=max(10, region_width // 3),
            fill=218,
        )
    elif "almond" in shape_name:
        local_draw.polygon(
            [
                (region_width // 2, inset),
                (region_width - 5, region_height // 4),
                (region_width - inset, region_height * 3 // 4),
                (region_width * 3 // 4, region_height - 4),
                (region_width // 4, region_height - 4),
                (inset, region_height * 3 // 4),
                (5, region_height // 4),
            ],
            fill=218,
        )
    else:
        local_draw.rounded_rectangle(
            (inset, inset, region_width - inset, region_height - 4),
            radius=max(12, region_width // 2),
            fill=218,
        )

    # All real nail beds finish in a softly curved cuticle. Rebuild the
    # lower edge after applying the requested free-edge silhouette.
    cuticle_height = max(12, region_width // 2)
    local_draw.rectangle(
        (0, region_height - cuticle_height, region_width, region_height),
        fill=0,
    )
    local_draw.ellipse(
        (
            4,
            region_height - cuticle_height * 2,
            region_width - 4,
            region_height - 3,
        ),
        fill=218,
    )
    if angle:
        local_mask = local_mask.rotate(
            angle, resample=Image.Resampling.BICUBIC, expand=True
        )

    mask = Image.new("L", canvas.size, 0)
    center_x = (region_left + region_right) // 2
    center_y = (region_top + region_bottom) // 2
    paste_left = center_x - local_mask.width // 2
    paste_top = center_y - local_mask.height // 2
    mask.paste(local_mask, (paste_left, paste_top), local_mask)

    bounds = mask.getbbox()
    if not bounds:
        return
    left, top, right, bottom = bounds
    width = right - left
    height = bottom - top

    polish = Image.new("RGBA", canvas.size, (0, 0, 0, 0))
    art = ImageDraw.Draw(polish)
    art.rectangle((left, top, right, bottom), fill=base + "e8")

    style_text = style.lower()
    details = " ".join(addons).lower()
    if "french" in style_text:
        tip_y = top + max(7, height // 8)
        art.rounded_rectangle(
            (left, top, right, tip_y + 3),
            radius=max(5, width // 4),
            fill=accent + "72",
        )
    elif "marble" in style_text:
        art.line(
            (left + 5, bottom - 10, right - 5, top + 13),
            fill=accent + "62",
            width=max(2, width // 14),
        )
    elif "chrome" in style_text or "chrome" in details:
        stripe = max(2, width // 14)
        art.rounded_rectangle(
            (
                left + width // 2 - stripe,
                top + 8,
                left + width // 2 + stripe,
                bottom - 9,
            ),
            radius=stripe,
            fill=accent + "52",
        )

    if "glitter" in details:
        for _ in range(25):
            x = rng.randint(left + 3, right - 3)
            y = rng.randint(top + 3, bottom - 3)
            radius = rng.choice([1, 1, 2])
            art.ellipse(
                (x - radius, y - radius, x + radius, y + radius),
                fill=rng.choice(["#ffffffef", "#f4d56eee", "#d9efffee"]),
            )
    if "gold foil" in details or "foil" in details:
        for _ in range(7):
            x = rng.randint(left + 4, right - 9)
            y = rng.randint(top + 6, bottom - 9)
            art.polygon(
                [(x, y), (x + 6, y - 2), (x + 9, y + 5), (x + 2, y + 9)],
                fill="#d5a32af0",
            )
    if "rhinestone" in details or "pearl" in details:
        gem = "#eaf8ffff" if "rhinestone" in details else "#fff4d6ff"
        for index in range(3):
            x = left + width // 2
            y = bottom - 8 - index * 8
            art.ellipse((x - 3, y - 3, x + 3, y + 3),
                        fill=gem, outline="#c89b3cff", width=1)
    if "chrome" in details:
        art.line((left + width // 4, top + 4,
                  right - width // 4, bottom - 4),
                 fill="#ffffffdd", width=4)

    # Fine botanical accents imitate hand-painted salon line work.
    if (
        "floral" in details
        or "flower" in details
        or "floral" in style_text
    ):
        stem_x = right - max(8, width // 4)
        stem_top = top + height // 3
        stem_bottom = bottom - 8
        art.arc(
            (stem_x - width // 3, stem_top, stem_x + 5, stem_bottom),
            start=250,
            end=85,
            fill="#fffaf0e8",
            width=max(1, width // 18),
        )
        for offset, side in [(8, -1), (18, 1), (29, -1)]:
            cy = stem_bottom - offset
            cx = stem_x + side * 2
            art.arc(
                (cx - 5, cy - 3, cx + 5, cy + 4),
                start=20 if side > 0 else 160,
                end=200 if side > 0 else 340,
                fill="#fffaf0e8",
                width=max(1, width // 20),
            )

    # Curved reflections make the translucent color follow the nail surface.
    art.rounded_rectangle(
        (left + width // 4, top + 9, left + width // 4 + 2, bottom - 14),
        radius=1,
        fill="#ffffff76",
    )
    polish.putalpha(mask)
    canvas.alpha_composite(polish)

def _save_demo(data: dict, number: int) -> Path:
    """Render the selected shape and color on the previous realistic hand."""
    base_path = Path(__file__).resolve().parents[1] / "assets" / "demo-hand-base.png"
    if not base_path.exists():
        return _save_demo_illustrated(data, number)

    canvas = Image.open(base_path).convert("RGBA").resize(
        (1024, 1024), Image.Resampling.LANCZOS
    )
    palette = _demo_nail_palette(data.get("color", ""))
    addons = list(data.get("add_ons") or [])
    rng = random.Random(f"realistic-hand-shape-{data}-{number}")
    shape = str(data.get("nail_shape") or "Almond")
    length_name = str(data.get("nail_length") or "Medium").lower()
    height_factor = {
        "short": .82, "medium": 1.0, "long": 1.14, "extra long": 1.28,
    }.get(length_name, 1.0)

    # Calibrated against the natural nail plates in the previous hand image.
    # Each tuple is left, top, right, bottom, and rotation.
    nail_regions = [
        (290, 215, 323, 302, -5),  # pinky
        (398, 119, 436, 201, -2),  # ring
        (500, 73, 539, 151, 0),    # middle
        (604, 112, 644, 188, 2),   # index
        (723, 394, 765, 472, 22),  # thumb
    ]

    for index, (left, top, right, bottom, angle) in enumerate(nail_regions):
        base = palette[0]
        accent = palette[(number + 1) % 4]
        width = right - left
        natural_height = bottom - top
        height = min(natural_height, int(natural_height * height_factor))
        adjusted_top = bottom - height
        _apply_photo_polish(
            canvas,
            (left, adjusted_top, right, bottom, angle),
            shape,
            base,
            accent,
            addons,
            str(data.get("style") or ""),
            number,
            index,
            rng,
        )

    filename = GENERATED_DIR / f"nail-demo-realistic-hand-{uuid.uuid4().hex}.png"
    canvas.convert("RGB").save(filename, "PNG", optimize=True)
    return filename


def _save_openai(prompt: str) -> Path:
    if not OPENAI_API_KEY or OPENAI_API_KEY == "YOUR_REAL_API_KEY":
        raise RuntimeError(
            "OpenAI mode is selected but OPENAI_API_KEY is not configured."
        )
    response = requests.post(
        "https://api.openai.com/v1/images/generations",
        headers={
            "Authorization": f"Bearer {OPENAI_API_KEY}",
            "Content-Type": "application/json",
        },
        json={
            "model": OPENAI_IMAGE_MODEL,
            "prompt": prompt,
            "size": OPENAI_IMAGE_SIZE,
            "quality": OPENAI_IMAGE_QUALITY,
            "n": 1,
        },
        timeout=OPENAI_TIMEOUT,
    )
    if response.status_code >= 400:
        message = response.json().get("error", {}).get("message", response.text)
        raise RuntimeError(f"OpenAI image request failed: {message}")
    item = response.json()["data"][0]
    if item.get("b64_json"):
        content = base64.b64decode(item["b64_json"])
    elif item.get("url"):
        download = requests.get(item["url"], timeout=120)
        download.raise_for_status()
        content = download.content
    else:
        raise RuntimeError("OpenAI returned no image data.")
    image = Image.open(io.BytesIO(content)).convert("RGB")
    filename = GENERATED_DIR / f"nail-ai-{uuid.uuid4().hex}.png"
    image.save(filename, "PNG")
    return filename


def _comfyui_workflow(prompt: str, seed: int) -> dict:
    negative = (
        "text, watermark, logo, extra fingers, missing fingers, fused fingers, "
        "deformed hand, duplicate hand, malformed nails, blurry, low quality"
    )
    return {
        "3": {
            "class_type": "KSampler",
            "inputs": {
                "seed": seed,
                "steps": COMFYUI_STEPS,
                "cfg": 7.0,
                "sampler_name": "euler",
                "scheduler": "normal",
                "denoise": 1.0,
                "model": ["4", 0],
                "positive": ["6", 0],
                "negative": ["7", 0],
                "latent_image": ["5", 0],
            },
        },
        "4": {
            "class_type": "CheckpointLoaderSimple",
            "inputs": {"ckpt_name": COMFYUI_CHECKPOINT},
        },
        "5": {
            "class_type": "EmptyLatentImage",
            "inputs": {
                "width": COMFYUI_WIDTH,
                "height": COMFYUI_HEIGHT,
                "batch_size": 1,
            },
        },
        "6": {
            "class_type": "CLIPTextEncode",
            "inputs": {"text": prompt, "clip": ["4", 1]},
        },
        "7": {
            "class_type": "CLIPTextEncode",
            "inputs": {"text": negative, "clip": ["4", 1]},
        },
        "8": {
            "class_type": "VAEDecode",
            "inputs": {"samples": ["3", 0], "vae": ["4", 2]},
        },
        "9": {
            "class_type": "SaveImage",
            "inputs": {
                "filename_prefix": "SmartBooking/nail-design",
                "images": ["8", 0],
            },
        },
    }


def _save_comfyui(prompt: str) -> Path:
    client_id = uuid.uuid4().hex
    seed = random.SystemRandom().randint(1, 2**63 - 1)
    try:
        queued = requests.post(
            f"{COMFYUI_URL}/prompt",
            json={
                "prompt": _comfyui_workflow(prompt, seed),
                "client_id": client_id,
            },
            timeout=20,
        )
        queued.raise_for_status()
        prompt_id = queued.json()["prompt_id"]
    except (requests.RequestException, KeyError, ValueError) as exc:
        raise RuntimeError(
            "ComfyUI is unavailable or rejected the workflow. "
            "Confirm that ComfyUI is running on port 8188 and that the "
            f"checkpoint '{COMFYUI_CHECKPOINT}' is installed. Details: {exc}"
        ) from exc

    deadline = time.monotonic() + COMFYUI_TIMEOUT
    image_info = None
    while time.monotonic() < deadline:
        try:
            history_response = requests.get(
                f"{COMFYUI_URL}/history/{prompt_id}", timeout=15
            )
            history_response.raise_for_status()
            history = history_response.json().get(prompt_id, {})
            images = history.get("outputs", {}).get("9", {}).get("images", [])
            if images:
                image_info = images[0]
                break
            status = history.get("status", {})
            if status.get("status_str") == "error":
                raise RuntimeError("ComfyUI reported a workflow execution error.")
        except requests.RequestException as exc:
            raise RuntimeError(f"Lost connection to ComfyUI: {exc}") from exc
        time.sleep(2)

    if not image_info:
        raise RuntimeError(
            "ComfyUI did not finish before the configured timeout. "
            "CPU generation may need a higher COMFYUI_TIMEOUT value."
        )

    try:
        image_response = requests.get(
            f"{COMFYUI_URL}/view",
            params={
                "filename": image_info["filename"],
                "subfolder": image_info.get("subfolder", ""),
                "type": image_info.get("type", "output"),
            },
            timeout=60,
        )
        image_response.raise_for_status()
        image = Image.open(io.BytesIO(image_response.content)).convert("RGB")
    except (requests.RequestException, KeyError, OSError) as exc:
        raise RuntimeError(f"Could not download the ComfyUI result: {exc}") from exc

    filename = GENERATED_DIR / f"nail-comfyui-{uuid.uuid4().hex}.png"
    image.save(filename, "PNG", optimize=True)
    return filename


def generate_nail_designs(data: dict, count: int = 3) -> dict:
    images = []
    prompts = []
    result_provider = PROVIDER
    for number in range(1, count + 1):
        prompt = build_nail_prompt(data, number)
        prompts.append(prompt)
        if PROVIDER == "demo":
            path = _save_demo(data, number)
        elif PROVIDER == "comfyui":
            try:
                path = _save_comfyui(prompt)
            except RuntimeError:
                if not COMFYUI_DEMO_FALLBACK:
                    raise
                path = _save_demo(data, number)
                result_provider = "comfyui-fallback-demo"
        else:
            path = _save_openai(prompt)
        images.append(f"/api/generated/{path.name}")
    return {
        "provider": result_provider,
        "model": (
            "preference-preview-v2"
            if result_provider in {"demo", "comfyui-fallback-demo"}
            else COMFYUI_CHECKPOINT
            if PROVIDER == "comfyui"
            else OPENAI_IMAGE_MODEL
        ),
        "images": images,
        "prompt": prompts[0],
    }
