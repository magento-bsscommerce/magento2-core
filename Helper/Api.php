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

namespace Bss\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Api extends AbstractHelper
{
    const GRAPHQL_ENDPOINT = 'http://127.0.0.1/bsscommercer2019/graphql';

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * Api constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json
    )
    {
        parent::__construct($context);
        $this->json = $json;
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    public function getModules()
    {
        $query =
            'query {
                 modules {
                     items{
                        name
                        packages {
                            entity_id
                            product_url
                            api_name
                            title
                            user_guide
                        }
                     }
                    count
                 }
	        }';
        return $this->graphQlQuery($query);
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    public function getConfigs()
    {
        $query = '
        query {
            configs {
                popup_expire_time
                popup_delay_open_time
            }
	    }';
        return $this->graphQlQuery($query);
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    public function getNewProducts()
    {
        $query = '
        query {
            new_products {
                name
                sku
                image
                link
            }
	    }';
        return $this->graphQlQuery($query);
    }

    /**
     * @param $productIds
     * @return array
     * @throws \ErrorException
     */
    public function getRelatedProducts($productIds)
    {
        $productIds = implode(",", $productIds);
        $query = "
        query {
            related_products (product_ids: [$productIds]) {
                main_product
                related {
                    name
                    sku
                    image
                    link
                }
            }
	    }";
        return $this->graphQlQuery($query);
    }

    /**
     * @param string $query
     * @param array $variables
     * @param null|string $token
     * @return array
     * @throws \ErrorException
     */
    protected function graphQlQuery(string $query, array $variables = [], ?string $token = null): array
    {
        $headers = ['Content-Type: application/json'];
        if (null !== $token) {
            $headers[] = "Authorization: bearer $token";
        }
        if (false === $data = @file_get_contents(self::GRAPHQL_ENDPOINT, false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => $headers,
                    'content' => $this->json->serialize(['query' => $query, 'variables' => $variables]),
                ]
            ]))) {
            $error = error_get_last();
            throw new \ErrorException($error['message'], $error['type']);
        }

        return $this->json->unserialize($data);
    }
}
