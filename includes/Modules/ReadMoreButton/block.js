/**
 * Minimal Block Registration for Gutenberg Inserter.
 * Uses global wp objects directly.
 */
(function () {
    var el = wp.element.createElement;
    var registerBlockType = wp.blocks.registerBlockType;
    var ServerSideRender = wp.serverSideRender;

    if (!registerBlockType || !el || !ServerSideRender) {
        return;
    }

    registerBlockType('tweak-tools-sdf30/read-more-button', {
        title: 'Read More (Woo Tweaks)',
        icon: 'external',
        category: 'woocommerce',
        edit: function (props) {
            return el(ServerSideRender, {
                block: 'tweak-tools-sdf30/read-more-button',
                attributes: props.attributes,
            });
        },
        save: function () {
            return null; // Dynamic block rendered via PHP
        },
    });
})();
