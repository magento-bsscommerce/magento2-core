<?php
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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Core\Block\Adminhtml;

class Header extends \Magento\Config\Block\System\Config\Form
{
    /**
     * @param string $html
     * @return string
     */
    protected function _afterToHtml($html)
    {
        $headerHtml =
            '
            <div id="bss-core-header">
                <a target="_blank" class="logo" href="https://bsscommerce.com/" title="BSS Commerce">
                    <img src="'.$this->getBssCommerceLogo().'" 
                    title="BSS Commerce">
                </a>
                <a class="logo" href="https://partners.magento.com/portal/details/partner/id/1742/" target="_blank">
                    <img src="https://bsscommerce.com/pub/media/wysiwyg/logo_builder_1.png" alt="">
                </a>
                <div class="menu-top">
                    <ul>
                        <li><a target="_blank" href="https://bsscommerce.com/magento-extensions.html">Magento 1 Extensions</a></li>
                        <li><a target="_blank" href="https://bsscommerce.com/magento-2-extensions.html">Magento 2 Extensions</a></li>
                    </ul>
                </div>
            </div>';
        return $headerHtml . $html;
    }

    /**
     * @return string
     */
    public function getBssCommerceLogo() {
        return 'https://bsscommerce.com/pub/static/frontend/Bss/bsscommerce/en_US/images/logo.png';
    }
}
