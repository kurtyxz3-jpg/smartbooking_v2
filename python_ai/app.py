from flask import Flask, jsonify, request, send_from_directory
from config import (
    COMFYUI_CHECKPOINT,
    COMFYUI_URL,
    GENERATED_DIR,
    HOST,
    OPENAI_API_KEY,
    OPENAI_IMAGE_MODEL,
    PORT,
    PROVIDER,
)
from services.nail_generator import generate_nail_designs
from services.beauty_generator import generate_beauty_previews

app = Flask(__name__)


@app.get("/")
@app.get("/api/health")
def health():
    if PROVIDER == "demo":
        configured = True
        model = "preference-preview-v2"
    elif PROVIDER == "comfyui":
        configured = bool(COMFYUI_URL and COMFYUI_CHECKPOINT)
        model = COMFYUI_CHECKPOINT
    else:
        configured = (
            bool(OPENAI_API_KEY)
            and OPENAI_API_KEY != "YOUR_REAL_API_KEY"
        )
        model = OPENAI_IMAGE_MODEL
    ready = configured
    return jsonify({
        "success": True,
        "status": "healthy" if ready else "configuration_required",
        "service": "SmartBooking Python AI",
        "provider": PROVIDER,
        "model": model,
        "configured": configured,
        "ready": ready,
    })


@app.post("/api/v1/nail-designs/generate")
def generate():
    data = request.get_json(silent=True) or {}
    required = ["color", "style", "nail_shape", "nail_length", "occasion"]
    missing = [field for field in required if not str(data.get(field, "")).strip()]
    if missing:
        return jsonify({
            "success": False,
            "message": "Missing required fields: " + ", ".join(missing),
        }), 422
    try:
        result = generate_nail_designs(data, min(max(int(data.get("count", 3)), 1), 3))
        return jsonify({
            "success": True,
            "message": "Nail design concepts created successfully.",
            **result,
        })
    except Exception as exc:
        app.logger.exception("Nail generation failed")
        return jsonify({"success": False, "message": str(exc)}), 503


@app.post("/api/v1/beauty-preview/generate")
def generate_beauty():
    data = request.get_json(silent=True) or {}
    area = str(data.get("area", "")).strip().lower()
    if area not in {"lips", "eyebrows"}:
        return jsonify({
            "success": False,
            "message": "Choose either lips or eyebrows.",
        }), 422
    if not str(data.get("color", "")).strip():
        return jsonify({
            "success": False,
            "message": "Choose a preview color.",
        }), 422
    try:
        result = generate_beauty_previews(
            data, min(max(int(data.get("count", 3)), 1), 3)
        )
        return jsonify({
            "success": True,
            "message": "Three beauty preview concepts were created.",
            **result,
        })
    except Exception as exc:
        app.logger.exception("Beauty preview generation failed")
        return jsonify({"success": False, "message": str(exc)}), 503


@app.get("/api/generated/<path:filename>")
def generated(filename: str):
    return send_from_directory(GENERATED_DIR, filename)


@app.errorhandler(404)
def not_found(_error):
    return jsonify({"success": False, "message": "API route not found."}), 404


if __name__ == "__main__":
    app.run(host=HOST, port=PORT, debug=False)
