import assert from 'node:assert/strict';
import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

// Exercise the workflow's actual shell block with a mocked GitHub CLI. No API,
// Release creation, SSH connection or deployment is performed by this test.
const workflow = fs.readFileSync('.github/workflows/release.yml', 'utf8');
const block = workflow.match(/        run: \|\r?\n((?:          [^\n]*\n|\r?\n)+)/)?.[1];
assert.ok(block, 'Release resolution shell block is missing.');
const script = block.replace(/^ {10}/gm, '');
const bash = process.env.TEST_BASH || (process.platform === 'win32' ? 'C:/Program Files/Git/bin/bash.exe' : 'bash');
const fixture = fs.mkdtempSync(path.join(os.tmpdir(), 'easel-workflow-'));
try {
  for (const scenario of [
    { name: 'manual tag overrides workflow ref', input: 'v1.7', event: '', published: 'v1.7', expected: 'v1.7' },
    { name: 'manual input overrides event field', input: '1.7', event: 'v9.9', published: '1.7', expected: '1.7' },
    { name: 'published event', input: '', event: 'v1.7', published: 'v1.7', expected: 'v1.7' },
    { name: 'missing Release', input: 'v1.7', event: '', published: '', ghStatus: '1' },
    { name: 'draft or prerelease is filtered out', input: 'v1.7', event: '', published: '' },
    { name: 'different returned tag', input: 'v1.7', event: '', published: 'v1.8' },
    { name: 'empty tag', input: '', event: '', published: '' },
    { name: 'shell metacharacters', input: 'v1.7; exit 0', event: '', published: '' },
    { name: 'prerelease syntax', input: 'v1.7-rc1', event: '', published: '' },
  ]) {
    const output = path.join(fixture, 'output').replaceAll('\\', '/');
    fs.writeFileSync(output, '');
    const result = spawnSync(bash, ['-s'], {
      input: 'gh() { if [[ "$MOCK_GH_STATUS" != 0 ]]; then return 1; fi; printf "%s" "$MOCK_PUBLISHED_TAG"; }\n' + script,
      encoding: 'utf8',
      env: { ...process.env, INPUT_TAG: scenario.input, EVENT_TAG: scenario.event,
        MOCK_PUBLISHED_TAG: scenario.published, MOCK_GH_STATUS: scenario.ghStatus || '0',
        GITHUB_REPOSITORY: 'fixture/easel', GITHUB_OUTPUT: output, GITHUB_SHA: 'unrelated-workflow-commit' },
    });
    if (scenario.expected) {
      assert.equal(result.status, 0, `${scenario.name}: ${result.stderr}`);
      assert.equal(fs.readFileSync(output, 'utf8'), `tag=${scenario.expected}\n`, scenario.name);
    } else {
      assert.notEqual(result.status, 0, scenario.name);
      assert.equal(fs.readFileSync(output, 'utf8'), '', scenario.name);
    }
  }
  console.log('Release workflow tag resolution checks passed for manual and published events; invalid/unpublished tags rejected.');
} finally {
  assert.equal(path.dirname(fixture), path.resolve(os.tmpdir()));
  assert.ok(path.basename(fixture).startsWith('easel-workflow-'));
  fs.rmSync(fixture, { recursive: true, force: true });
}
