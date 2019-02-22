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

class Data extends AbstractHelper
{
    const BSS_WSDL_END_POINT = 'http://bsscommerce.com/api/soap/?wsdl=1';
    const BSS_MODULES_INFO_API_USERNAME = 'core_api_username';
    const BSS_MODULES_INFO_API_PASSWORD = 'MwNq9@LAhmWe3mz.';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * @var array
     */
    private $modulesInfo;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem\Driver\File $filesystem
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem\Driver\File $filesystem,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
        $this->json = $json;
    }

    /**
     * Get Store config values
     *
     * @param string $path
     * @return mixed
     */
    public function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get current Magento version.
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Get current Magento relative version.
     *
     * @return string
     */
    public function getMagentoRelativeVersion()
    {
        $magentoVer = $this->getMagentoVersion();
        $relativeVerTemp = explode('.', $magentoVer);

        if (empty($relativeVerTemp)) {
            return '';
        }

        unset($relativeVerTemp[count($relativeVerTemp) - 1]);
        $relativeVerTemp[] = 'x';
        $relativeVer = implode('.', $relativeVerTemp);

        return $relativeVer;
    }

    /**
     * Get current Magento Edition.
     *
     * @return string
     */
    public function getMagentoEdition()
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * Get installed module info by composer.json.
     *
     * @param string $moduleCode
     * @return array|bool|float|int|mixed|string|null
     */
    public function getModuleInfo($moduleCode)
    {
        try {
            $dir = $this->moduleReader->getModuleDir('', $moduleCode);
            $file = $dir . '/composer.json';

            $string = $this->filesystem->fileGetContents($file);
            $json = $this->json->unserialize($string);

            return $json;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get bsscommerce.com modules information.
     *
     * @return array
     */
    public function getRemoteModulesInfo()
    {
        try {
            if (!$this->modulesInfo) {
                $client = new \SoapClient(self::BSS_WSDL_END_POINT);
                $sessionId = $client->login(self::BSS_MODULES_INFO_API_USERNAME, self::BSS_MODULES_INFO_API_PASSWORD);
                $resultList = $client->call($sessionId, 'soapapi_product.list', [[]]);
                $this->modulesInfo = $resultList;
            }

            return $this->modulesInfo;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Is module enabled?
     *
     * @param string $moduleName
     * @return bool
     */
    public function isModuleEnable($moduleName)
    {
        return $this->_moduleManager->isEnabled($moduleName);
    }
}
