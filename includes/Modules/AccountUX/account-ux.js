/**
 * Enhanced Account UX Logic
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Only run on the account details form
        const $form = $('form.woocommerce-EditAccountForm');
        if (!$form.length) return;

        // 1. Wrap and hide Email section
        const $emailField = $form.find('#account_email').closest('p');
        if ($emailField.length) {
            $emailField.hide().addClass('wt-retractable-section');
            $('<a href="#" class="wt-account-toggle-btn" data-target="email">' + wtAccountData.i18n.edit_email + '</a>')
                .insertBefore($emailField);
        }

        // 2. Wrap and hide Password section
        const $passwordFieldset = $form.find('fieldset');
        if ($passwordFieldset.length) {
            $passwordFieldset.hide().addClass('wt-retractable-section');
            $('<a href="#" class="wt-account-toggle-btn" data-target="password">' + wtAccountData.i18n.change_pass + '</a>')
                .insertBefore($passwordFieldset);
        }

        // Handle toggle clicks
        $(document).on('click', '.wt-account-toggle-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const target = $btn.data('target');
            let $section;

            if (target === 'email') {
                $section = $emailField;
            } else if (target === 'password') {
                $section = $passwordFieldset;
            }

            if ($section && $section.length) {
                $section.slideToggle();
                $btn.toggleClass('active');
                
                // Update text if active
                if ($btn.hasClass('active')) {
                    $btn.text(target === 'email' ? wtAccountData.i18n.hide_email : wtAccountData.i18n.hide_pass);
                } else {
                    $btn.text(target === 'email' ? wtAccountData.i18n.edit_email : wtAccountData.i18n.change_pass);
                }
            }
        });
    });
})(jQuery);
