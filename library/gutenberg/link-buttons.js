(function (wp) {
    'use strict';

    const el = wp.element.createElement;
    const { InspectorControls, RichText, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, TextControl, ToggleControl } = wp.components;
    const { __ } = wp.i18n;

    function safeUrl(value) {
        const url = (value || '').trim();
        const normalized = url.replace(/[\u0000-\u0020\u007f-\u009f]/g, '');
        if (/^[a-z][a-z\d+.-]*:/i.test(normalized) && !/^(https?|mailto|tel):/i.test(normalized)) {
            return '';
        }
        return url;
    }

    [
        ['blue', __('青', 'bonestheme')],
        ['red', __('赤', 'bonestheme')],
        ['yellow', __('黄', 'bonestheme')],
        ['green', __('緑', 'bonestheme')],
        ['navy', __('ネイビー', 'bonestheme')],
        ['gray', __('灰色', 'bonestheme')],
    ].forEach(function ([color, label]) {
        wp.blocks.registerBlockType('easel/' + color + '-button', {
            apiVersion: 3,
            title: __('EASEL リンクボタン', 'bonestheme') + '（' + label + '）',
            icon: 'admin-links',
            category: 'design',
            keywords: ['EASEL', __('リンク', 'bonestheme'), __('ボタン', 'bonestheme'), label],
            attributes: {
                text: { type: 'string', source: 'html', selector: 'a', default: __('リンクボタン', 'bonestheme') },
                url: { type: 'string', source: 'attribute', selector: 'a', attribute: 'href', default: '#' },
                newTab: { type: 'boolean', default: false },
            },
            supports: { html: false, align: ['left', 'center', 'right'] },
            edit: function ({ attributes, setAttributes }) {
                const alignment = ['left', 'center', 'right'].includes(attributes.align) ? attributes.align : 'left';
                // Core's editor alignment wrapper does not always add aligncenter
                // to this element. Apply the preview alignment directly as well.
                const blockProps = useBlockProps({
                    className: 'easel-link-button',
                    style: { textAlign: alignment, width: '100%', float: 'none' },
                });
                return el(wp.element.Fragment, null,
                    el(InspectorControls, null,
                        el(PanelBody, { title: __('リンク設定', 'bonestheme') },
                            el(SelectControl, {
                                label: __('ボタンの配置', 'bonestheme'),
                                value: attributes.align || 'left',
                                options: [
                                    { label: __('左揃え', 'bonestheme'), value: 'left' },
                                    { label: __('中央揃え', 'bonestheme'), value: 'center' },
                                    { label: __('右揃え', 'bonestheme'), value: 'right' },
                                ],
                                onChange: function (align) { setAttributes({ align }); },
                            }),
                            el(TextControl, {
                                label: __('リンク先URL', 'bonestheme'),
                                value: attributes.url,
                                onChange: function (url) { setAttributes({ url }); },
                            }),
                            el(ToggleControl, {
                                label: __('新しいタブで開く', 'bonestheme'),
                                checked: attributes.newTab,
                                onChange: function (newTab) { setAttributes({ newTab }); },
                            })
                        )
                    ),
                    el('div', blockProps,
                        el('span', { className: color + '-btn' },
                            el(RichText, {
                                tagName: 'a',
                                className: 'easel-link-button__link',
                                href: safeUrl(attributes.url),
                                value: attributes.text,
                                allowedFormats: ['core/bold', 'core/italic'],
                                placeholder: __('ボタンの文字を入力', 'bonestheme'),
                                onClick: function (event) { event.preventDefault(); },
                                onChange: function (text) { setAttributes({ text }); },
                            })
                        )
                    )
                );
            },
            save: function ({ attributes }) {
                return el('div', useBlockProps.save({ className: 'easel-link-button' }),
                    el('span', { className: color + '-btn' },
                        el(RichText.Content, {
                            tagName: 'a',
                            className: 'easel-link-button__link',
                            href: safeUrl(attributes.url),
                            target: attributes.newTab ? '_blank' : undefined,
                            rel: attributes.newTab ? 'noopener noreferrer' : undefined,
                            value: attributes.text,
                        })
                    )
                );
            },
        });
    });
})(window.wp);
