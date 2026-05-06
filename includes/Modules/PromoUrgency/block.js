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

    registerBlockType('woo-tweaks/promo-urgency', {
        title: 'Promo Urgency (Woo Tweaks)',
        icon: 'clock',
        category: 'text',
        edit: function (props) {
            return el(ServerSideRender, {
                block: 'woo-tweaks/promo-urgency',
                attributes: props.attributes,
            });
        },
        save: function () {
            return null; // Dynamic block rendered via PHP
        },
    });
})();
