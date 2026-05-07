( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var useBlockProps = wp.blockEditor ? wp.blockEditor.useBlockProps : wp.editor.useBlockProps;

    registerBlockType( 'tweak-tools-sdf30s/direct-checkout', {
        edit: function() {
            var blockProps = useBlockProps();
            var buttonLabel = window.wooTweaksDirectCheckoutLabel || 'Acheter maintenant';
            return el(
                'div',
                blockProps,
                el(
                    'button',
                    {
                        className: 'button alt',
                        style: { background: 'none', color: '#007b5f', fontWeight: '600', border: 'solid 1px', padding: '10px' }
                    },
                    buttonLabel
                )
            );
        },
        save: function() {
            return null; // Block is rendered dynamically in PHP
        }
    } );
} )( window.wp );
