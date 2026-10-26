// Run with: node tests/link-buttons.js
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const blocks = new Map();
const el = (type, props, ...children) => ({ type, props: props || {}, children });
const useBlockProps = props => props;
useBlockProps.save = props => props;
function RichText() {}
RichText.Content = function Content() {};
const components = { PanelBody() {}, SelectControl() {}, TextControl() {}, ToggleControl() {} };
const wp = {
    blocks: { registerBlockType(name, config) { blocks.set(name, config); } },
    element: { createElement: el, Fragment: 'Fragment' },
    blockEditor: { InspectorControls() {}, RichText, useBlockProps },
    components,
    i18n: { __(text) { return text; } },
};
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../library/gutenberg/link-buttons.js'), 'utf8'), { window: { wp } });
assert.equal(blocks.size, 6);
function find(tree, type) {
    if (tree.type === type) return tree;
    for (const child of tree.children) {
        if (child && typeof child === 'object') {
            const found = find(child, type);
            if (found) return found;
        }
    }
}
for (const color of ['blue', 'red', 'yellow', 'green', 'navy', 'gray']) {
    const block = blocks.get('easel/' + color + '-button');
    assert.equal(block.apiVersion, 3);
    const attributes = { text: 'Read <strong>more</strong>', url: 'https://example.org/work?a=1&b=2', newTab: true };
    const saved = block.save({ attributes });
    assert.equal(saved.children[0].props.className, color + '-btn');
    const link = find(saved, RichText.Content);
    assert.equal(link.props.value, attributes.text);
    assert.equal(link.props.href, attributes.url);
    assert.equal(link.props.target, '_blank');
    assert.equal(link.props.rel, 'noopener noreferrer');
    const changes = [];
    const editor = block.edit({ attributes, setAttributes(update) { changes.push(update); } });
    find(editor, components.TextControl).props.onChange('/works/');
    find(editor, components.ToggleControl).props.onChange(false);
    find(editor, RichText).props.onChange('Updated text');
    assert.equal(changes[0].url, '/works/');
    assert.equal(changes[1].newTab, false);
    assert.equal(changes[2].text, 'Updated text');
    const alignmentControl = find(editor, components.SelectControl);
    assert.equal(alignmentControl.props.value, 'left');
    assert.equal(Array.from(block.supports.align).join(','), 'left,center,right');
    for (const align of ['left', 'center', 'right']) {
        alignmentControl.props.onChange(align);
        assert.equal(changes[changes.length - 1].align, align);
        const alignedEditor = block.edit({ attributes: { ...attributes, align }, setAttributes() {} });
        assert.equal(find(alignedEditor, components.SelectControl).props.value, align);
        const preview = find(alignedEditor, 'div');
        assert.equal(preview.props.style.textAlign, align, 'Editor preview must reflect the alignment without relying on core alignment classes.');
        assert.equal(preview.props.style.width, '100%', 'Preview must reserve enough width to align the button.');
        assert.equal(preview.props.style.float, 'none');
        assert.equal(find(block.save({ attributes: { ...attributes, align } }), RichText.Content).props.href, attributes.url);
    }
    for (const url of ['javascript:alert(1)', 'java\nscript:alert(1)', 'data:text/html,test']) {
        const unsafe = block.save({ attributes: { ...attributes, url, newTab: false } });
        const safeLink = find(unsafe, RichText.Content);
        assert.equal(safeLink.props.href, '');
        assert.equal(safeLink.props.target, undefined);
        assert.equal(safeLink.props.rel, undefined);
    }
    for (const url of ['/works/', '#section', 'mailto:artist@example.org', 'tel:0123456789']) {
        assert.equal(find(block.save({ attributes: { ...attributes, url } }), RichText.Content).props.href, url);
    }
}
console.log('All six link button blocks passed registration, editing, and saved-link checks.');
