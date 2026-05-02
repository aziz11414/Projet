from pathlib import Path
import re


ROOT = Path(__file__).resolve().parent
SOURCE = ROOT / "Documentation_EduPredict.md"
OUTPUT = ROOT / "Documentation_EduPredict.pdf"


PAGE_WIDTH = 595
PAGE_HEIGHT = 842
LEFT = 54
RIGHT = 54
TOP = 60
BOTTOM = 55
LINE_GAP = 5


FONTS = {
    "body": ("F1", 11),
    "h1": ("F2", 22),
    "h2": ("F2", 16),
    "h3": ("F2", 13),
    "code": ("F3", 9),
}


AVG_WIDTH = {
    "F1": 0.52,
    "F2": 0.56,
    "F3": 0.60,
}


def escape_pdf_text(value: str) -> str:
    return value.replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)")


def normalize(line: str) -> str:
    return line.replace("\t", "    ").rstrip("\n")


def wrap_text(text: str, font_key: str, font_size: int, max_width: int) -> list[str]:
    if not text:
        return [""]

    char_width = font_size * AVG_WIDTH[font_key]
    limit = max(20, int(max_width / char_width))

    words = text.split(" ")
    lines = []
    current = ""
    for word in words:
        candidate = word if not current else current + " " + word
        if len(candidate) <= limit:
            current = candidate
        else:
            if current:
                lines.append(current)
            if len(word) <= limit:
                current = word
            else:
                for i in range(0, len(word), limit):
                    chunk = word[i:i + limit]
                    if i == 0:
                        current = chunk
                    else:
                        lines.append(current)
                        current = chunk
    if current:
        lines.append(current)
    return lines or [""]


def parse_markdown(lines: list[str]) -> list[dict]:
    blocks = []
    in_code = False

    for raw in lines:
        line = normalize(raw)

        if line.strip().startswith("```"):
            in_code = not in_code
            blocks.append({"type": "blank"})
            continue

        if in_code:
            blocks.append({"type": "code", "text": line})
            continue

        stripped = line.strip()
        if not stripped:
            blocks.append({"type": "blank"})
            continue

        if stripped.startswith("# "):
            blocks.append({"type": "h1", "text": stripped[2:].strip()})
            continue

        if stripped.startswith("## "):
            blocks.append({"type": "h2", "text": stripped[3:].strip()})
            continue

        if stripped.startswith("### "):
            blocks.append({"type": "h3", "text": stripped[4:].strip()})
            continue

        if re.match(r"^[-*] ", stripped):
            blocks.append({"type": "bullet", "text": stripped[2:].strip()})
            continue

        if re.match(r"^\d+\.\s+", stripped):
            prefix = re.match(r"^(\d+\.)\s+", stripped).group(1)
            content = re.sub(r"^\d+\.\s+", "", stripped)
            blocks.append({"type": "number", "prefix": prefix, "text": content})
            continue

        blocks.append({"type": "paragraph", "text": stripped})

    return blocks


def layout_blocks(blocks: list[dict]) -> list[list[tuple[str, int, int, str]]]:
    pages = []
    page = []
    y = PAGE_HEIGHT - TOP

    def ensure_space(height: int):
        nonlocal page, y, pages
        if y - height < BOTTOM:
            pages.append(page)
            page = []
            y = PAGE_HEIGHT - TOP

    for block in blocks:
        block_type = block["type"]

        if block_type == "blank":
            ensure_space(10)
            y -= 10
            continue

        if block_type == "h1":
            font, size = FONTS["h1"]
            lines = wrap_text(block["text"], font, size, PAGE_WIDTH - LEFT - RIGHT)
            height = len(lines) * (size + LINE_GAP) + 10
            ensure_space(height)
            for line in lines:
                page.append((font, size, LEFT, y, line))
                y -= size + LINE_GAP
            y -= 10
            continue

        if block_type == "h2":
            font, size = FONTS["h2"]
            lines = wrap_text(block["text"], font, size, PAGE_WIDTH - LEFT - RIGHT)
            height = len(lines) * (size + LINE_GAP) + 8
            ensure_space(height)
            for line in lines:
                page.append((font, size, LEFT, y, line))
                y -= size + LINE_GAP
            y -= 8
            continue

        if block_type == "h3":
            font, size = FONTS["h3"]
            lines = wrap_text(block["text"], font, size, PAGE_WIDTH - LEFT - RIGHT)
            height = len(lines) * (size + LINE_GAP) + 6
            ensure_space(height)
            for line in lines:
                page.append((font, size, LEFT, y, line))
                y -= size + LINE_GAP
            y -= 6
            continue

        if block_type == "code":
            font, size = FONTS["code"]
            lines = wrap_text(block["text"] or " ", font, size, PAGE_WIDTH - LEFT - RIGHT - 18)
            height = len(lines) * (size + 3)
            ensure_space(height)
            for line in lines:
                page.append((font, size, LEFT + 12, y, line))
                y -= size + 3
            continue

        if block_type == "bullet":
            font, size = FONTS["body"]
            prefix = "- "
            lines = wrap_text(block["text"], font, size, PAGE_WIDTH - LEFT - RIGHT - 18)
            height = len(lines) * (size + LINE_GAP)
            ensure_space(height)
            for index, line in enumerate(lines):
                rendered = prefix + line if index == 0 else "  " + line
                page.append((font, size, LEFT, y, rendered))
                y -= size + LINE_GAP
            continue

        if block_type == "number":
            font, size = FONTS["body"]
            prefix = block["prefix"] + " "
            lines = wrap_text(block["text"], font, size, PAGE_WIDTH - LEFT - RIGHT - 24)
            height = len(lines) * (size + LINE_GAP)
            ensure_space(height)
            for index, line in enumerate(lines):
                rendered = prefix + line if index == 0 else (" " * len(prefix)) + line
                page.append((font, size, LEFT, y, rendered))
                y -= size + LINE_GAP
            continue

        if block_type == "paragraph":
            font, size = FONTS["body"]
            lines = wrap_text(block["text"], font, size, PAGE_WIDTH - LEFT - RIGHT)
            height = len(lines) * (size + LINE_GAP) + 4
            ensure_space(height)
            for line in lines:
                page.append((font, size, LEFT, y, line))
                y -= size + LINE_GAP
            y -= 4
            continue

    if page:
        pages.append(page)

    return pages


def build_pdf(pages: list[list[tuple[str, int, int, str]]]) -> bytes:
    objects = []

    def add_object(data: bytes) -> int:
        objects.append(data)
        return len(objects)

    font1 = add_object(b"<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>")
    font2 = add_object(b"<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>")
    font3 = add_object(b"<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>")

    page_ids = []
    content_ids = []
    pages_placeholder = None

    for page in pages:
        commands = ["BT"]
        for font_key, font_size, x, y, text in page:
            commands.append(f"/{font_key} {font_size} Tf")
            commands.append(f"1 0 0 1 {x} {y} Tm")
            commands.append(f"({escape_pdf_text(text)}) Tj")
        commands.append("ET")
        stream = "\n".join(commands).encode("latin-1", errors="replace")
        content = f"<< /Length {len(stream)} >>\nstream\n".encode("latin-1") + stream + b"\nendstream"
        content_id = add_object(content)
        content_ids.append(content_id)
        page_ids.append(None)

    pages_placeholder = add_object(b"")

    for content_id in content_ids:
        page_obj = (
            f"<< /Type /Page /Parent {pages_placeholder} 0 R /MediaBox [0 0 {PAGE_WIDTH} {PAGE_HEIGHT}] "
            f"/Resources << /Font << /F1 {font1} 0 R /F2 {font2} 0 R /F3 {font3} 0 R >> >> "
            f"/Contents {content_id} 0 R >>"
        ).encode("latin-1")
        page_id = add_object(page_obj)
        page_ids[content_ids.index(content_id)] = page_id

    kids = " ".join(f"{page_id} 0 R" for page_id in page_ids)
    pages_obj = f"<< /Type /Pages /Kids [ {kids} ] /Count {len(page_ids)} >>".encode("latin-1")
    objects[pages_placeholder - 1] = pages_obj

    catalog_id = add_object(f"<< /Type /Catalog /Pages {pages_placeholder} 0 R >>".encode("latin-1"))

    buffer = bytearray(b"%PDF-1.4\n%\xe2\xe3\xcf\xd3\n")
    xref = [0]

    for index, obj in enumerate(objects, start=1):
        xref.append(len(buffer))
        buffer.extend(f"{index} 0 obj\n".encode("latin-1"))
        buffer.extend(obj)
        buffer.extend(b"\nendobj\n")

    xref_pos = len(buffer)
    buffer.extend(f"xref\n0 {len(objects)+1}\n".encode("latin-1"))
    buffer.extend(b"0000000000 65535 f \n")
    for offset in xref[1:]:
        buffer.extend(f"{offset:010d} 00000 n \n".encode("latin-1"))

    trailer = (
        f"trailer\n<< /Size {len(objects)+1} /Root {catalog_id} 0 R >>\n"
        f"startxref\n{xref_pos}\n%%EOF"
    ).encode("latin-1")
    buffer.extend(trailer)
    return bytes(buffer)


def main():
    source_text = SOURCE.read_text(encoding="utf-8")
    blocks = parse_markdown(source_text.splitlines())
    pages = layout_blocks(blocks)
    pdf_bytes = build_pdf(pages)
    OUTPUT.write_bytes(pdf_bytes)
    print(str(OUTPUT))


if __name__ == "__main__":
    main()
