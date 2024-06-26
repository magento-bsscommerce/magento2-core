/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Core
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
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
        },

        /**
         * Set Height Scroll
         */
        setHeightScroll: function () {
                const gridContainer = document.querySelector('#bss-promotions-container .grid-container');
                if (gridContainer) {
                    const gridItem = gridContainer.querySelector('.grid-item');

                    if (gridItem) {
                        const itemHeight = gridItem.offsetHeight;

                        // Lấy giá trị gap từ CSS
                        const gridStyles = window.getComputedStyle(gridContainer);
                        const gap = parseInt(gridStyles.gap);

                        const numRows = 2;

                        const containerHeight = (itemHeight * numRows) + (gap * (numRows - 1));
                        gridContainer.style.height = containerHeight + 'px';
                    }
                }
        }
    });
});
