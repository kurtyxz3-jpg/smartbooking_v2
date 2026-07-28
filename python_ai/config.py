from pathlib import Path
import os
from dotenv import load_dotenv

BASE_DIR = Path(__file__).resolve().parent
load_dotenv(BASE_DIR / ".env")

PROVIDER = os.getenv("AI_PROVIDER", "demo").strip().lower()
OPENAI_API_KEY = os.getenv("OPENAI_API_KEY", "").strip()
OPENAI_IMAGE_MODEL = os.getenv("OPENAI_IMAGE_MODEL", "gpt-image-1").strip()
OPENAI_IMAGE_SIZE = os.getenv("OPENAI_IMAGE_SIZE", "1024x1024").strip()
OPENAI_IMAGE_QUALITY = os.getenv("OPENAI_IMAGE_QUALITY", "medium").strip()
OPENAI_TIMEOUT = max(30, min(int(os.getenv("OPENAI_TIMEOUT_SECONDS", "300")), 900))
COMFYUI_URL = os.getenv("COMFYUI_URL", "http://127.0.0.1:8188").strip().rstrip("/")
COMFYUI_CHECKPOINT = os.getenv(
    "COMFYUI_CHECKPOINT", "v1-5-pruned-emaonly.safetensors"
).strip()
COMFYUI_WIDTH = max(256, min(int(os.getenv("COMFYUI_WIDTH", "512")), 768))
COMFYUI_HEIGHT = max(256, min(int(os.getenv("COMFYUI_HEIGHT", "512")), 768))
COMFYUI_STEPS = max(4, min(int(os.getenv("COMFYUI_STEPS", "12")), 30))
COMFYUI_TIMEOUT = max(60, min(int(os.getenv("COMFYUI_TIMEOUT", "900")), 1800))
COMFYUI_DEMO_FALLBACK = os.getenv(
    "COMFYUI_DEMO_FALLBACK", "true"
).strip().lower() in {"1", "true", "yes", "on"}
HOST = os.getenv("AI_HOST", "127.0.0.1").strip()
PORT = int(os.getenv("AI_PORT", "5000"))
GENERATED_DIR = BASE_DIR / "generated"
GENERATED_DIR.mkdir(parents=True, exist_ok=True)
