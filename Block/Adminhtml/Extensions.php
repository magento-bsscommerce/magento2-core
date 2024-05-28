<?php

declare(strict_types=1);

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
 * @copyright  Copyright (c) 2017-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Core\Block\Adminhtml;

use Bss\Core\Helper\Api;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Serialize\Serializer\Json;

class Extensions extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Bss_Core::extensions.phtml';

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Api
     */
    private $apiHelper;

    public function __construct(
        Template\Context $context,
        Json $serializer,
        Api $apiHelper,
        array $data = [],
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
        $this->apiHelper = $apiHelper;
    }

    public function getPromotionsDataJson(): string
    {
        return $this->serializer->serialize($this->getPromotionsData());
    }

    private function getPromotionsData(): array
    {
        return $this->apiHelper->getPromotions();
    }
}
