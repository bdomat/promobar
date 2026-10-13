"""Build an installable PrestaShop ZIP from the committed source tree."""
import pathlib, re, subprocess, sys, zipfile, io
root = pathlib.Path(__file__).resolve().parents[1]
name = root.name
source = subprocess.check_output(['git', 'show', 'HEAD:'+name+'.php'], cwd=root).decode()
version = re.search(r"\$this->version = '([^']+)'", source).group(1)
out = pathlib.Path(sys.argv[1]) if len(sys.argv) > 1 else root/'dist'/f'{name}-{version}.zip'
out.parent.mkdir(parents=True, exist_ok=True)
data = subprocess.check_output(['git', 'archive', '--format=zip', '--prefix='+name+'/', 'HEAD'], cwd=root)
out.write_bytes(data)
with zipfile.ZipFile(io.BytesIO(data)) as archive:
    assert name+'/'+name+'.php' in archive.namelist()
    assert not any('/tests/' in p or '/.github/' in p for p in archive.namelist())
print(out.resolve())
