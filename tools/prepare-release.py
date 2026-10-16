"""Validate a stable release tag and prepare the existing installable package."""
import hashlib
import io
import os
from pathlib import Path
import re
import subprocess
import zipfile
import xml.etree.ElementTree as ET

root = Path(__file__).resolve().parents[1]
module = root.name
release_ref = os.environ["RELEASE_REF"]
source = subprocess.check_output(["git", "show", f"HEAD:{module}.php"], cwd=root).decode()
match = re.search(r"\$this->version = '([^']+)'", source)
if not match or not re.fullmatch(r"(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)", match.group(1)):
    raise SystemExit("Only stable MAJOR.MINOR.PATCH module versions can be released")
version = match.group(1)
tag = "v" + version
for manifest in sorted(root.glob("config*.xml")):
    committed_xml = subprocess.check_output(["git", "show", "HEAD:" + manifest.name], cwd=root)
    metadata = ET.fromstring(committed_xml)
    if metadata.findtext("name") != module or metadata.findtext("version") != version:
        raise SystemExit("Manifest name or version differs: " + manifest.name)
if release_ref not in ("refs/heads/main", "refs/tags/" + tag):
    raise SystemExit("Release ref does not match main or the stable module version")
head = subprocess.check_output(["git", "rev-parse", "HEAD"], cwd=root).decode().strip()
main_head = subprocess.check_output(["git", "rev-parse", "origin/main"], cwd=root).decode().strip()
if head != main_head:
    raise SystemExit("Only the exact main commit can be published")
if release_ref.startswith("refs/tags/"):
    tag_head = subprocess.check_output(["git", "rev-parse", f"refs/tags/{tag}^{{commit}}"], cwd=root).decode().strip()
    if head != tag_head:
        raise SystemExit("Checkout is not the tagged commit")
changelog = (root / "CHANGELOG.md").read_text()
section = re.search(r"^## " + re.escape(version) + r"(?:[^\n]*)\n(.*?)(?=^## |\Z)", changelog, re.M | re.S)
if not section:
    raise SystemExit("Missing changelog for the release version")
output = root / "dist"
output.mkdir(exist_ok=True)
archive = output / f"{module}.zip"
# Use the same git-archive packaging and export-ignore rules as tools/build.py.
def archive_at(ref):
    return subprocess.check_output(["git", "archive", "--format=zip", "--prefix=" + module + "/", ref], cwd=root)

def payload(data):
    with zipfile.ZipFile(io.BytesIO(data)) as package:
        if package.testzip() is not None:
            raise SystemExit("ZIP CRC verification failed")
        return {name: package.read(name) for name in package.namelist() if not name.endswith("/")}

data = archive_at(head)
source_sha = head
existing_tag = subprocess.run(["git", "rev-parse", "--verify", "refs/tags/" + tag + "^{commit}"], cwd=root, capture_output=True, text=True)
if existing_tag.returncode == 0:
    source_sha = existing_tag.stdout.strip()
    subprocess.run(["git", "merge-base", "--is-ancestor", source_sha, head], cwd=root, check=True)
    tagged_data = archive_at(source_sha)
    if payload(tagged_data) != payload(data):
        raise SystemExit("Existing tag has different installable files; bump the module version")
    # Preserve the exact original ZIP when only excluded CI/tooling changed.
    data = tagged_data
archive.write_bytes(data)
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
notes += f"\n\nInstallation : télécharger `{archive.name}`, puis l'importer dans le gestionnaire de modules PrestaShop. Les archives Source code ne sont pas le paquet installable.\n\nCommit : `{source_sha}`\n"
(output / "RELEASE_NOTES.md").write_text(notes)
if os.environ.get("GITHUB_OUTPUT"):
    with open(os.environ["GITHUB_OUTPUT"], "a") as output_file:
        output_file.write(f"tag={tag}\nsha={source_sha}\nmain_sha={head}\n")
