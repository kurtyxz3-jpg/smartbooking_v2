from config import OPENAI_API_KEY, OPENAI_IMAGE_MODEL, PROVIDER


def main() -> None:
    configured = bool(
        OPENAI_API_KEY
        and OPENAI_API_KEY not in {"YOUR_REAL_API_KEY", "YOUR_OPENAI_API_KEY"}
    )
    print(f"Provider: {PROVIDER}")
    print(f"Model: {OPENAI_IMAGE_MODEL}")
    print(f"OpenAI API key: {'configured' if configured else 'missing'}")
    if PROVIDER == "openai" and not configured:
        raise SystemExit(
            "Add a real OPENAI_API_KEY to .env or use AI_PROVIDER=demo."
        )


if __name__ == "__main__":
    main()
