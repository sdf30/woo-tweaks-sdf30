jQuery(document).ready(function($) {
    var textarea = $("#woo_tweaks_custom_css");
    if (textarea.length && typeof wooTweaksCodeMirror !== 'undefined') {
        var editor = wp.codeEditor.initialize(textarea, wooTweaksCodeMirror);
        
        // Sync CodeMirror to textarea on change
        editor.codemirror.on("change", function(cm) {
            editor.codemirror.save();
            textarea.trigger("change");
        });
        
        // Also force sync on form submit
        $("form#mainform").on("submit", function() {
            editor.codemirror.save();
        });
    }
});
