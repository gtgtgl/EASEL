import assert from 'node:assert/strict';
import { execFileSync, spawnSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { inflateRawSync } from 'node:zlib';

const builder = path.resolve('.github/scripts/build-release.mjs');
const fixture = fs.mkdtempSync(path.join(os.tmpdir(), 'easel-release-build-'));
const git = (...args) => execFileSync('git', args, { cwd: fixture, stdio: 'pipe' });
function write(file, contents) {
  fs.mkdirSync(path.dirname(path.join(fixture, file)), { recursive: true });
  fs.writeFileSync(path.join(fixture, file), contents);
}
function commit() {
  git('add', '.');
  git('-c', 'user.name=Release Test', '-c', 'user.email=release@example.invalid', 'commit', '--quiet', '-m', 'fixture');
}
function build(tag) {
  return spawnSync(process.execPath, [builder, '--tag', tag], { cwd: fixture, encoding: 'utf8' });
}
function unzip(buffer) {
  const end = buffer.lastIndexOf(Buffer.from([0x50, 0x4b, 0x05, 0x06]));
  assert.ok(end >= 0);
  let offset = buffer.readUInt32LE(end + 16);
  const files = new Map();
  while (buffer.readUInt32LE(offset) === 0x02014b50) {
    const nameLength = buffer.readUInt16LE(offset + 28);
    const name = buffer.subarray(offset + 46, offset + 46 + nameLength).toString();
    const local = buffer.readUInt32LE(offset + 42);
    const start = local + 30 + buffer.readUInt16LE(local + 26) + buffer.readUInt16LE(local + 28);
    const data = buffer.subarray(start, start + buffer.readUInt32LE(offset + 20));
    files.set(name, buffer.readUInt16LE(offset + 10) === 8 ? inflateRawSync(data) : data);
    offset += 46 + nameLength + buffer.readUInt16LE(offset + 30) + buffer.readUInt16LE(offset + 32);
  }
  return files;
}
try {
  git('init', '--quiet');
  write('.gitignore', '/dist/\n');
  write('style.css', '/*\nTheme Name: EASEL\nVersion: 1.7\n*/\n');
  write('theme-update.json', JSON.stringify({ version: '1.7', details_url: 'https://easel.gt-gt.org/works/easel-ver1-7/', download_url: 'https://easel.gt-gt.org/downloads/easel.zip' }));
  const required = ['functions.php', 'index.php', 'theme-update-checker.php', 'library/gutenberg.php', 'library/gutenberg/gutenberg.js', 'library/gutenberg/link-buttons.js', 'library/css/style.css', 'library/css/block-editor.css', 'library/css/link-buttons.css'];
  for (const file of required) write(file, 'committed-runtime');
  const excluded = ['tests/leak.php', '.github/workflows/release.yml', '.env', 'COMPATIBILITY.md', 'RELEASING.md', 'library/scss/style.scss', 'library/dev/leak.php', 'library/js/scripts.js.map', 'library/.env'];
  for (const file of excluded) write(file, 'development-only');
  write('img/default.jpg', 'image');
  write('library/fonts/font.otf', 'font');
  write('library/translation/ja.mo', 'translation');
  commit();
  write('functions.php', 'uncommitted-change');
  write('local-only.php', 'untracked-secret');
  assert.equal(build('v1.7').status, 0);
  const zip = unzip(fs.readFileSync(path.join(fixture, 'dist/easel.zip')));
  assert.equal(zip.get('easel/functions.php').toString(), 'committed-runtime');
  assert.equal(zip.has('easel/local-only.php'), false);
  for (const file of required) assert.ok(zip.has(`easel/${file}`), file);
  for (const file of ['img/default.jpg', 'library/fonts/font.otf', 'library/translation/ja.mo']) assert.ok(zip.has(`easel/${file}`), file);
  for (const file of excluded) assert.equal(zip.has(`easel/${file}`), false, file);
  assert.ok([...zip.keys()].every(name => name.startsWith('easel/')));
  assert.equal(zip.get('easel/theme-update.json').toString(), fs.readFileSync(path.join(fixture, 'dist/theme-update.json'), 'utf8'));
  for (const line of fs.readFileSync(path.join(fixture, 'dist/SHA256SUMS'), 'utf8').trim().split('\n')) {
    const [hash, file] = line.split('  ');
    assert.equal(hash, createHash('sha256').update(fs.readFileSync(path.join(fixture, 'dist', file))).digest('hex'));
  }
  assert.notEqual(build('v1.8').status, 0, 'Tag mismatch must fail.');
  assert.notEqual(build('v1.7-rc1').status, 0, 'Prerelease tag must fail.');
  const metadata = JSON.parse(fs.readFileSync(path.join(fixture, 'theme-update.json')));
  metadata.version = '1.8';
  write('theme-update.json', JSON.stringify(metadata));
  commit();
  assert.notEqual(build('1.7').status, 0, 'JSON mismatch must fail.');
  metadata.version = '1.7';
  metadata.download_url = 'https://example.invalid/wrong.zip';
  write('theme-update.json', JSON.stringify(metadata));
  commit();
  assert.notEqual(build('1.7').status, 0, 'Wrong public ZIP URL must fail.');
  metadata.download_url = 'https://easel.gt-gt.org/downloads/easel.zip';
  write('theme-update.json', JSON.stringify(metadata));
  write('style.css', '/*\nVersion: 1.8\n*/\n');
  commit();
  assert.notEqual(build('1.7').status, 0, 'Theme header mismatch must fail.');
  write('style.css', '/*\nVersion: 1.7\n*/\n');
  fs.unlinkSync(path.join(fixture, 'library/gutenberg/link-buttons.js'));
  commit();
  assert.notEqual(build('1.7').status, 0, 'Missing runtime assets must fail.');
  console.log('Release build checks passed: versions, runtime assets, ZIP layout, development exclusions, committed content, SHA-256.');
} finally {
  // Only remove the directory created above, beneath the OS temporary directory.
  assert.equal(path.dirname(fixture), path.resolve(os.tmpdir()));
  assert.ok(path.basename(fixture).startsWith('easel-release-build-'));
  fs.rmSync(fixture, { recursive: true, force: true });
}
