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
        $additional =
            '
            <style>
            .logo {
                float: left;
                margin: 0 15px 0 0;
                padding-top: 6px;
            }
            a:visited, .alink:visited {
                color: #006bb4;
                text-decoration: none;
            }
            .menu-top {
                display: block;
                position: relative;
                right: inherit;
                top: inherit;
                visibility: visible;
                float: left;
            }
            .menu-top ul {
                padding: 0;
                margin: 0;
                border-left: 1px solid #f2f2f2;
            }
            .menu-top ul li {
                display: inline-block;
                padding: 0 10px;
            }
            .menu-top ul li a {
                height: 54px;
                line-height: 54px;
                color: #303030;
                font-weight: 700;
                font-size: 16px;
            }
            .menu-top ul li a:hover{
                text-decoration: none;
            }
            </style>
            <a class="logo" href="https://bsscommerce.com/" title="BSS Commerce">
                <img src="https://bsscommerce.com/pub/static/version1562819228/frontend/Bss/bsscommerce/en_US/images/logo.png" title="" alt="" width="95" height="54">
            </a>
            <a class="logo" href="https://partners.magento.com/portal/details/partner/id/1742/" target="_blank" rel="nofollow noopener">
                <img src="https://bsscommerce.com/pub/media/wysiwyg/logo_builder_1.png" alt="">
            </a>
            <div class="menu-top">
                <ul>
                    <li><a href="https://bsscommerce.com/magento-extensions.html">Magento 1 Extensions</a></li>
                    <li><a href="https://bsscommerce.com/magento-2-extensions.html">Magento 2 Extensions</a></li>
                </ul>
            </div>';
        return $additional . $html;
    }
}
