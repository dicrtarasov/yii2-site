/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 04.01.22 23:25:12
 */

((window, $) => {
    'use strict';

    /**
     * @param {HTMLElement|jQuery<HTMLElement>} widget
     * @constructor
     */
    function DicrAdminEditTabs(widget)
    {
        const $widget = $(widget);

        // смена названия вкладки dropdown-menu при переключении подменю
        $widget.on('shown.bs.tab', '.dropdown-item', function () {
            // ссылка родительского таба
            // noinspection JSCheckFunctionSignatures
            const $toggle = $(this).closest('.dropdown').find('.dropdown-toggle');

            // сохраняем оригинальную метку
            if (!$toggle.data('orig-label')) {
                $toggle.data('orig-label', $toggle.text());
            }

            $toggle.text($(this).text());
        });

        // восстановление оригинального названия вкладки dropdown при уходе в другой таб
        $widget.on('hidden.bs.tab', '.dropdown-toggle', function () {
            const $origLabel = $(this).data('orig-label');

            if ($origLabel) {
                $(this).text($origLabel);
            }
        });
    }

    // noinspection JSUnusedGlobalSymbols

    /**
     * jQuery plugin.
     *
     * @returns {jQuery}
     */
    $.fn.dicrSiteAdminEditTabs = function () {
        return this.each(function () {
            $(this).data('widget', new DicrAdminEditTabs(this));
        });
    };
})(window, jQuery);
