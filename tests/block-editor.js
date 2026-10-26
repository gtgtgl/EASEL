// Run with: node tests/block-editor.js
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const script = fs.readFileSync(require('node:path').join(__dirname, '../library/gutenberg/gutenberg.js'), 'utf8');

async function checkTerms(terms) {
  let block;
  let effect;
  const requests = [];
  const errors = [];
  const element = (type, props, key) => ({ type, props, key });
  vm.runInNewContext(script, {
    console: { log() {}, error(...args) { errors.push(args); } },
    window: {
      wp: {
        blocks: { registerBlockType(name, config) { assert.equal(name, 'easel/shortcode'); block = config; } },
        element: { useEffect(callback) { effect = callback; } },
        blockEditor: { useBlockProps() { return { className: 'wp-block-easel-shortcode' }; } },
        apiFetch({ path }) {
          requests.push(path);
          const page = Number(new URL(path, 'https://example.test').searchParams.get('page'));
          const result = terms.slice((page - 1) * 100, page * 100);
          if (page > 1 && !result.length) {
            return Promise.reject({ code: 'rest_post_invalid_page_number' });
          }
          return Promise.resolve(result);
        },
      },
      ReactJSXRuntime: { jsx: element, jsxs: element },
    },
  });
  assert.equal(block.apiVersion, 3);
  const attributes = Object.fromEntries(Object.entries(block.attributes).map(([key, value]) => [key, value.default]));
  let workTypes;
  const props = { attributes, setAttributes(update) { workTypes = update.workType; } };
  block.edit(props);
  effect();
  await new Promise(setImmediate);
  assert.equal(errors.length, 0, 'Loading all terms must not fail at the last page.');
  assert.equal(workTypes.length, terms.length);
  attributes.workType = workTypes;
  const editor = block.edit(props);
  assert.equal(editor.props.className, 'wp-block-easel-shortcode', 'Preserve the API v3 editor wrapper.');
  const saved = block.save({ attributes });
  const shortcode = saved.props.children.filter(value => typeof value === 'string').join('');
  assert.equal(shortcode, '[new_illust post_type=works orderby=post_date order=DESC work_type=   count=-1 class=""]', 'Existing saved shortcode format must be preserved.');
  return requests.length;
}

(async () => {
  const terms = Array.from({ length: 101 }, (_, i) => ({ id: i + 1, parent: i ? 1 : 0, slug: 'term-' + i, name: 'Term ' + i }));
  assert.equal(await checkTerms(terms), 2, 'More than 100 terms require pagination.');
  assert.equal(await checkTerms(terms.slice(0, 100)), 2, 'An exact page boundary must handle the API end-of-results error.');
  assert.equal(await checkTerms([{ id: 2, parent: 999, slug: 'orphan', name: 'Orphan' }]), 1, 'A missing parent must not crash the editor.');
  console.log('Block editor pagination and saved-content compatibility checks passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
