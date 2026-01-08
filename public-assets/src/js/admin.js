"use strict";

(function ($) {
    $(document).ready(function () {
        const spinner = $('#plugin-action-spinner');
        const pluginTable = $('.yivic-plugins-installer__table tbody');

        function handlePluginAction(button, action, data, successText) {
            showSpinner();
            button.text(`${action}ing...`).prop('disabled', true);

            $.post(ajaxurl, data)
                .done(response => {
                    if (response.success && successText) {
                        button.replaceWith(`<span class="yivic-plugins-installer__status-text yivic-plugins-installer__status-text--active">${successText}</span>`);
                    }
                    setTimeout(() => location.reload(), 100);
                })
                .fail(() => {
                    button.text(`${action} Failed`).css('background-color', 'red').prop('disabled', false);
                })
                .always(() => hideSpinner());
        }

        function showSpinner() {
            spinner.css({ display: 'flex', opacity: '1' });
        }

        function hideSpinner() {
            spinner.css('opacity', '0');
            setTimeout(() => spinner.css('display', 'none'), 500);
        }

        function updatePluginCount() {
            const counts = {
                all: 0,
                active: 0,
                inactive: 0,
                mustuse: 0,
                "not-installed": 0
            };

            pluginTable.find('tr').each(function () {
                const row = $(this);
                const status = row.find('.yivic-plugins-installer__status').attr('class').split('--')[1];
                if (counts.hasOwnProperty(status)) {
                    counts[status]++;
                }
                counts.all++;
            });

            $('.yivic-plugins-installer__tabs .all .count').text(`(${counts.all})`);
            $('.yivic-plugins-installer__tabs .active .count').text(`(${counts.active})`);
            $('.yivic-plugins-installer__tabs .inactive .count').text(`(${counts.inactive})`);
            $('.yivic-plugins-installer__tabs .mustuse .count').text(`(${counts.mustuse})`);
        }

        // Plugin Status Filtering
        $('.yivic-plugins-installer__tabs a').on('click', function (e) {
            e.preventDefault();
            const status = $(this).parent().attr('class');
            $('.yivic-plugins-installer__tabs a').removeClass('current');
            $(this).addClass('current');

            pluginTable.find('tr').each(function () {
                const row = $(this);
                const rowStatus = row.find('.yivic-plugins-installer__status').attr('class').split('--')[1];
                if (status === 'all' || rowStatus === status) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        });

        // Event Delegation for Buttons (Fixes Deactivate Button Issue)
        $(document).on('click', '.yivic-plugins-installer__button--install', function () {
            handlePluginAction($(this), 'Install', {
                action: 'yivic_install_plugin',
                plugin_slug: $(this).data('slug')
            });
        });

        $(document).on('click', '.yivic-plugins-installer__button--activate', function () {
            handlePluginAction($(this), 'Activate', {
                action: 'yivic_activate_plugin',
                plugin_path: $(this).data('path'),
                plugin_file: $(this).data('file')
            }, 'Activated');
        });

        $(document).on('click', '.yivic-plugins-installer__button--deactivate', function () {
            handlePluginAction($(this), 'Deactivate', {
                action: 'yivic_deactivate_plugin',
                plugin_path: $(this).data('path'),
                plugin_file: $(this).data('file')
            }, 'Deactivated');
        });

        /*
        * Ajax request that will hide the Yivic REST API admin notice or message.
        */
        function dismissAdminNotice() {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    nonce: yivicDismissNotice.nonce,
                    action: 'yivic_rest_api_dismiss_notice',
                },
                dataType: 'json',
            });
        }

        // Dismiss notice
        $(document).on(
            'click',
            '.yivic-rest-api-notice .notice-dismiss',
            function () {
                dismissAdminNotice();
            }
        );

        updatePluginCount();
    });
})(jQuery);