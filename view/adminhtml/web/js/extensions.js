/**
 *  Amasty Base Extensions UI Component
 */

define([
    'uiComponent',
    'mage/translate'
], function (Component, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bss_Core/extensions/extensions',
            promotionsData: []
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            return this;
        }
    });
});
