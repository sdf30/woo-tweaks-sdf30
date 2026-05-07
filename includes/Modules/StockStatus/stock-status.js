/**
 * Stock Status Shortcuts - Admin Script
 */
jQuery(document).ready(function($) {
    $(document).on('click', '.wt-toggle-stock', function(e) {
        e.preventDefault();

        const $link = $(this);
        const $row = $link.closest('tr');
        const productId = $link.data('id');
        const newStatus = $link.data('status');
        const nonce = $link.data('nonce');
        
        // Find the stock column cell
        const $stockCell = $row.find('.column-is_in_stock');
        
        // UI Feedback: Loading state
        $link.css('opacity', '0.5').css('pointer-events', 'none');
        const originalText = $link.text();
        $link.text(wtStockData.i18n.updating);

        $.ajax({
            url: wtStockData.ajax_url,
            type: 'POST',
            data: {
                action: 'woo_tweaks_toggle_stock',
                product_id: productId,
                status: newStatus,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update the stock cell content if found
                    if ($stockCell.length && response.data.html_badge) {
                        $stockCell.html(response.data.html_badge);
                    }

                    // Toggle link attributes for the next action
                    if (newStatus === 'outofstock') {
                        $link.text(wtStockData.i18n.mark_in);
                        $link.data('status', 'instock');
                        $link.css('color', '#007b5f'); // Green
                    } else {
                        $link.text(wtStockData.i18n.mark_out);
                        $link.data('status', 'outofstock');
                        $link.css('color', '#d63638'); // Red
                    }
                } else {
                    alert(response.data.message || wtStockData.i18n.error);
                    $link.text(originalText);
                }
            },
            error: function() {
                alert(wtStockData.i18n.error);
                $link.text(originalText);
            },
            complete: function() {
                $link.css('opacity', '1').css('pointer-events', 'auto');
            }
        });
    });
});
