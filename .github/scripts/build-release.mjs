import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';

// Use Git objects, not working-tree files: only the published commit is shipped.
function git(...args) {
  return execFileSync('git', args, { maxBuffer: 64 * 1024 * 1024 });
}

function main() {
  const args = process.argv.slice(2);
  if (args.length !== 2 || args[0] !== '--tag') {
    throw new Error('Usage: node .github/scripts/build-release.mjs --tag v1.7');
  }
  const tag = args[1];
  if (!/^v?\d+\.\d+(?:\.\d+)?$/.test(tag)) {
    throw new Error('Use a stable numeric release tag such as 1.7 or v1.7.0.');
  }
  const version = tag.replace(/^v/, '');
  const commit = git('rev-parse', '--verify', 'HEAD^{commit}').toString().trim();
  const style = git('show', `${commit}:style.css`).toString('utf8');
  const metadataBytes = git('show', `${commit}:theme-update.json`);
  const metadata = JSON.parse(metadataBytes.toString('utf8'));
  const themeVersion = style.match(/^Version:\s*(\S+)\s*$/m)?.[1];
  if (themeVersion !== version || metadata.version !== version) {
    throw new Error(`Version mismatch: style.css=${themeVersion}, theme-update.json=${metadata.version}, Release=${version}. Commit matching versions before publishing.`);
  }
  if (metadata.download_url !== 'https://easel.gt-gt.org/downloads/easel.zip') {
    throw new Error('download_url must match the existing /downloads/easel.zip distribution URL.');
  }
  const details = new URL(metadata.details_url);
  if (details.protocol !== 'https:' || details.hostname !== 'easel.gt-gt.org' || details.username || details.password) {
    throw new Error('details_url must be an HTTPS page on easel.gt-gt.org.');
  }

  const entries = git('ls-tree', '-r', '-z', commit).toString('utf8').split('\0').filter(Boolean);
  const files = [];
  for (const entry of entries) {
    const [info, file] = entry.split('\t');
    const allowed = !file.split('/').some(part => part.startsWith('.')) && (
      (!file.includes('/') && (/\.php$/.test(file) || ['style.css', 'screenshot.jpg', 'theme-update.json'].includes(file)))
      || (/^img\/.+\.(?:jpg|jpeg|png|gif|svg|webp|avif|ico)$/i.test(file))
      || (/^library\/(?!scss\/|dev\/).+\.(?:php|js|css|otf|ttf|woff2?|mo)$/i.test(file))
    );
    if (!allowed) continue;
    if (!info.startsWith('100644 blob ') && !info.startsWith('100755 blob ')) {
      throw new Error(`Distribution file must be a regular file: ${file}`);
    }
    files.push(file);
  }
  for (const required of ['style.css', 'functions.php', 'index.php', 'theme-update-checker.php', 'library/gutenberg.php', 'library/gutenberg/gutenberg.js', 'library/gutenberg/link-buttons.js', 'library/css/style.css', 'library/css/block-editor.css', 'library/css/link-buttons.css']) {
    if (!files.includes(required)) throw new Error(`Missing required distribution file: ${required}`);
  }
  const output = path.resolve('dist');
  fs.mkdirSync(output, { recursive: true });
  git('archive', '--format=zip', '--prefix=easel/', `--output=${path.join(output, 'easel.zip')}`, commit, '--', ...files);
  fs.writeFileSync(path.join(output, 'theme-update.json'), metadataBytes);
  const hashes = ['easel.zip', 'theme-update.json'].map(file => `${createHash('sha256').update(fs.readFileSync(path.join(output, file))).digest('hex')}  ${file}`);
  fs.writeFileSync(path.join(output, 'SHA256SUMS'), hashes.join('\n') + '\n');
  const plan = {
    tag, version, commit,
    download_url: metadata.download_url,
    metadata_url: 'https://easel.gt-gt.org/downloads/theme-update.json',
    publication_order: ['upload both files to a private temporary directory', 'verify both SHA-256 checksums on server', 'rename easel.zip into place', 'rename theme-update.json into place'],
    files,
  };
  fs.writeFileSync(path.join(output, 'release-plan.json'), JSON.stringify(plan, null, 2) + '\n');
  const summary = `## EASEL release ${version}\n\nCommit: \`${commit}\`\n\nZIP: ${metadata.download_url}\n\nJSON: ${plan.metadata_url}\n\nFiles in ZIP: ${files.length} (under easel/)\n\nServer upload: ${process.env.DEPLOY_ENABLED === 'true' ? 'ENABLED; review the sakura-production deployment before approval.' : 'DISABLED; artifacts only, no server connection.'}\n\nDownload the artifact and review release-plan.json, SHA256SUMS, the ZIP and JSON before enabling/approving deployment. SSH settings come from sakura-production Secrets.\n\nPublication order: ZIP first, JSON last, with server-side checksum verification and atomic file renames.\n`;
  if (process.env.GITHUB_STEP_SUMMARY) fs.appendFileSync(process.env.GITHUB_STEP_SUMMARY, summary);
  console.log(summary);
}

try { main(); } catch (error) {
  console.error(error.message);
  process.exitCode = 1;
}
