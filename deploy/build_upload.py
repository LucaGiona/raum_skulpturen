#!/usr/bin/env python3
"""Baut einen upload-fertigen Ordner aus dem Projekt.

Nutzung:
    python3 deploy/build_upload.py --base-path "" --out ../homepage_skulptur_und_raum-upload
    python3 deploy/build_upload.py --base-path "/website-neu" --out ../homepage_skulptur_und_raum-upload-test

--base-path ist der zentrale Schalter: leer ("") fuer den Live-Upload ins
Domain-Stammverzeichnis, "/website-neu" (o.ae.) fuer den Test in einem
Unterordner. Absolute Web-Pfade (href/src/action, CSS url(), JS-Fetches,
PHP-Redirects, Pfade in den JSON-Datendateien) werden entsprechend
praefigiert. Dateisystem-Includes (__DIR__ . '/...') werden nicht angefasst.
"""
import argparse
import re
import shutil
from pathlib import Path

EXCLUDES = {
    ".git", ".gitignore", ".DS_Store", "AGENTS.md", "README.md",
    "STRATO-HANDOVER.md", "Claude outputs", "deploy",
}
EXCLUDE_FILES = {
    Path("admin/config.php"),
    Path("admin/config.example.php"),
    Path("admin/setup-code.example.php"),
    Path("mail-config.example.php"),
}

TEXT_SUFFIXES = {".html", ".css", ".js", ".php", ".json"}

PREFIX_GROUP = r"(?:css|js|assets|audio|admin|html)"
RE_ATTR = re.compile(r'(?<=["\'])(/' + PREFIX_GROUP + r'/[^"\']*|/contact\.php|/index\.html)')
RE_LOCATION = re.compile(
    r"(header\(\s*['\"]Location:\s*)(/" + PREFIX_GROUP + r"/[^'\"]*|/contact\.php|/index\.html)"
)


def should_skip(rel_path: Path) -> bool:
    if rel_path in EXCLUDE_FILES:
        return True
    for part in rel_path.parts:
        if part in EXCLUDES:
            return True
    return False


def rewrite(text: str, base_path: str) -> str:
    text = RE_ATTR.sub(lambda m: base_path + m.group(1), text)
    text = RE_LOCATION.sub(lambda m: m.group(1) + base_path + m.group(2), text)
    return text


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--base-path", required=True,
                     help='"" fuer Live (Domain-Root), z.B. "/website-neu" fuer den Testordner')
    ap.add_argument("--out", required=True, help="Zielordner (wird geleert/neu angelegt)")
    args = ap.parse_args()

    base_path = args.base_path.rstrip("/")
    if base_path and not base_path.startswith("/"):
        base_path = "/" + base_path

    src_root = Path(__file__).resolve().parent.parent
    out_root = Path(args.out).resolve()

    if out_root.exists():
        shutil.rmtree(out_root)
    out_root.mkdir(parents=True)

    changed = 0
    copied = 0
    for path in sorted(src_root.rglob("*")):
        rel = path.relative_to(src_root)
        if should_skip(rel):
            continue
        dest = out_root / rel
        if path.is_dir():
            dest.mkdir(parents=True, exist_ok=True)
            continue
        dest.parent.mkdir(parents=True, exist_ok=True)
        if base_path and path.suffix in TEXT_SUFFIXES:
            original = path.read_text(encoding="utf-8")
            rewritten = rewrite(original, base_path)
            dest.write_text(rewritten, encoding="utf-8")
            if rewritten != original:
                changed += 1
        else:
            shutil.copy2(path, dest)
        copied += 1

    print(f"Fertig: {copied} Dateien nach {out_root} kopiert, {changed} Dateien mit Basis-Pfad '{base_path or '(leer)'}' angepasst.")


if __name__ == "__main__":
    main()
