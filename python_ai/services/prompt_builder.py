def build_nail_prompt(data: dict, variation: int) -> str:
    addons = ", ".join(data.get("add_ons") or []) or "no raised decorations"
    directions = [
        "balanced salon-wearable composition",
        "one tasteful statement accent nail",
        "minimal luxurious variation with refined negative space",
    ]
    direction = directions[(variation - 1) % len(directions)]
    return (
        "Create a photorealistic professional nail art design preview. "
        f"Nail shape: {data['nail_shape']}. Length: {data['nail_length']}. "
        f"Color palette: {data['color']}. Style: {data['style']}. "
        f"Occasion: {data['occasion']}. Add-ons: {addons}. "
        f"Creative direction: {direction}. "
        f"Customer notes: {data.get('instructions') or 'none'}. "
        "Show one elegant manicured hand, realistic skin and nails, clean luxury "
        "salon lighting, uncluttered neutral background, no words, no logos, "
        "no watermark, anatomically correct fingers."
    )
