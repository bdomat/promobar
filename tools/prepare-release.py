"""Validate a stable release tag and prepare the existing installable package."""
import hashlib
import os
from pathlib import Path
import re
import subprocess
import zipfile

root = Path(__file__).resolve().parents[1]
module = root.name
tag = os.environ["RELEASE_TAG"]
if not re.fullmatch(r"v(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)", tag):
    raise SystemExit("Only stable vMAJOR.MINOR.PATCH tags can be released")
version = tag[1:]
source = subprocess.check_output(["git", "show", f"HEAD:{module}.php"], cwd=root).decode()
match = re.search(r"\$this->version = '([^']+)'", source)
if not match or match.group(1) != version:
    raise SystemExit("Tag does not match the module version")
head = subprocess.check_output(["git", "rev-parse", "HEAD"], cwd=root).decode().strip()
tag_head = subprocess.check_output(["git", "rev-parse", f"refs/tags/{tag}^{{commit}}"], cwd=root).decode().strip()
if head != tag_head:
    raise SystemExit("Checkout is not the tagged commit")
subprocess.run(["git", "merge-base", "--is-ancestor", "HEAD", "origin/main"], cwd=root, check=True)
changelog = (root / "CHANGELOG.md").read_text()
section = re.search(r"^## " + re.escape(version) + r"(?:[^\n]*)\n(.*?)(?=^## |\Z)", changelog, re.M | re.S)
if not section:
    raise SystemExit("Missing changelog for the release version")
output = root / "dist"
output.mkdir(exist_ok=True)
archive = output / f"{module}.zip"
subprocess.run(["python3", str(root / "tools" / "build.py"), str(archive)], cwd=root, check=True)
with zipfile.ZipFile(archive) as package:
    if package.testzip() is not None:
        raise SystemExit("ZIP CRC verification failed")
    if not all(path.startswith(module + "/") and ".." not in Path(path).parts for path in package.namelist()):
        raise SystemExit("Unexpected archive path")
    if package.read(f"{module}/{module}.php").decode() != source:
        raise SystemExit("Packaged source differs from the tagged commit")
digest = hashlib.sha256(archive.read_bytes()).hexdigest()
(output / "SHA256SUMS.txt").write_text(f"{digest}  {archive.name}\n")
notes = f"## {module} {version}\n\n" + section.group(1).strip()
notes += f"\n\nInstallation : télécharger `{archive.name}`, puis l'importer dans le gestionnaire de modules PrestaShop. Les archives Source code ne sont pas le paquet installable.\n\nCommit : `{head}`\n"
(output / "RELEASE_NOTES.md").write_text(notes)
