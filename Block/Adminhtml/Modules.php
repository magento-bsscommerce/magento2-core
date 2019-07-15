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

use Magento\Framework\Data\Form\Element\AbstractElement;

class Modules extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Bss\Core\Helper\Data
     */
    private $bssHelper;

    /**
     * @var \Bss\Core\Helper\Api
     */
    private $apiHelper;

    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    private $fieldRenderer;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var array
     */
    private $modules = [];

    /**
     * Modules constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Bss\Core\Helper\Data $bssHelper
     * @param \Bss\Core\Helper\Api $apiHelper
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Bss\Core\Helper\Data $bssHelper,
        \Bss\Core\Helper\Api $apiHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    )
    {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->moduleList = $moduleList;
        $this->layoutFactory = $layoutFactory;
        $this->bssHelper = $bssHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->apiHelper = $apiHelper;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);
        $html .= $this->getTitleHtml($element);
        $modules = $this->moduleList->getNames();

        $dispatchResult = $this->dataObjectFactory->create()->setData($modules);
        $modules = $dispatchResult->toArray();

        sort($modules);
        foreach ($modules as $moduleName) {
            if (strstr($moduleName, 'Bss_') === false
                || $moduleName === 'Bss_Core'
            ) {
                continue;
            }

            $html .= $this->getFieldHtml($element, $moduleName);
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * Get renderer object
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function getFieldRenderer()
    {
        if (empty($this->fieldRenderer)) {
            $layout = $this->layoutFactory->create();

            $this->fieldRenderer = $layout->createBlock(
                \Magento\Config\Block\System\Config\Form\Field::class
            );
        }

        return $this->fieldRenderer;
    }

    /**
     * Get html of title block.
     *
     * @param AbstractElement $fieldset
     * @return mixed
     */
    protected function getTitleHtml($fieldset)
    {
        $field = $fieldset->addField(
            'module_name',
            \Bss\Core\Block\Adminhtml\Form\Element\Columns::class,
            [
                'name' => 'dummy',
                'label' => 'Module',
                'current_ver' => 'Current Version',
                'latest_ver' => 'Latest Version',
                'user_guide' => 'User Guide'
            ]
        )->setRenderer($this->getFieldRenderer());

        return $field->toHtml();
    }

    /**
     * Get the Html for the element.
     *
     * @param AbstractElement $fieldset
     * @param string $moduleCode
     * @return string
     * @throws \ErrorException
     */
    protected function getFieldHtml($fieldset, $moduleCode)
    {
        $localModule = $this->bssHelper->getModuleInfo($moduleCode);

        if (!is_array($localModule)
            || !array_key_exists('version', $localModule)
            || !array_key_exists('description', $localModule)
        ) {
            return '';
        }

        $suite = null;
        if (isset($localModule['extra']['suite'])) {
            $suite = $localModule['extra']['suite'];
        }

        if ($this->bssHelper->isModuleEnable('Bss_Breadcrumbs') && $suite == 'seo-suite') {
            return '';
        }

        $moduleName = $localModule['description'];
        $apiName = $localModule['name'];

        $moduleName = str_replace('Bss', '', $moduleName);
        $moduleName = str_replace('Modules', '', $moduleName);
        $moduleName = str_replace('Module', '', $moduleName);
        $moduleName = trim($moduleName);

        $modules = $this->apiHelper->getModules();
        $this->modules = $modules['data']['modules']['items'];

        $latestVer = 'unknown';
        $moduleUrl = '#';
        $userGuide = '';
        $module = $this->searchByModule($apiName);
        print_r('<pre>');
        print_r($latestVer);
        if (!empty($module)) {
            $latestVer = $this->getLatestVersion($module);
            $moduleUrl = $this->getModuleUrl($module);
            print_r('<pre>');
            print_r($module['packages']);
            $userGuide = $module['packages'][0]['user_guide'];
            $userGuide = "<a href = '$userGuide' target='_blank'>Link</a>";
        }

        $latestVerCol = $latestVer == 'unknown' ? $latestVer : "<a href = '$moduleUrl' target='_blank'>$latestVer</a>";

        $moduleVer = isset($localModule['extra']['suite-version']) ? $localModule['extra']['suite-version'] : $localModule['version'];
        $field = $fieldset->addField(
            $moduleCode,
            \Bss\Core\Block\Adminhtml\Form\Element\Columns::class,
            [
                'name' => 'dummy',
                'label' => $moduleName,
                'current_ver' => $moduleVer,
                'latest_ver' => $latestVerCol,
                'user_guide' => $userGuide
            ]
        )->setRenderer($this->getFieldRenderer());

        return $field->toHtml();
    }

    /**
     * Return header html for fieldset
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        if ($element->getIsNested()) {
            $html = '<tr class="nested"><td colspan="4"><div class="' . $this->_getFrontendClass($element) . '">';
        } else {
            $html = '<div class="' . $this->_getFrontendClass($element) . '">';
        }

        $html .= '<div class="entry-edit-head admin__collapsible-block">' .
            '<span id="' .
            $element->getHtmlId() .
            '-link" class="entry-edit-head-link"></span>';

        $html .= $this->_getHeaderTitleHtml($element);

        $html .= '</div>';
        $html .= '<input id="' .
            $element->getHtmlId() .
            '-state" name="config_state[' .
            $element->getId() .
            ']" type="hidden" value="' .
            (int)$this->_isCollapseState(
                $element
            ) . '" />';
        $html .= '<fieldset class="' . $this->_getFieldsetCss() . '" id="' . $element->getHtmlId() . '">';
        $html .= '<legend>' . $element->getLegend() . '</legend>';

        $html .= $this->_getHeaderCommentHtml($element);

        // field label column
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="bss-label"/><colgroup class="bss-value"/>';
        if ($this->getRequest()->getParam('website') || $this->getRequest()->getParam('store')) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }

    /**
     * Get bsscommerce.com module url.
     *
     * @param array $modules
     * @return string
     */
    protected function getModuleUrl($modules)
    {
        $packages = $modules['packages'];
        if (!isset($packages[0])) {
            return '#';
        }

        $packages = $packages[0];
        return isset($packages['product_url']) ? $packages['product_url'] : '#';
    }

    /**
     * Get module latest version from bsscommerce.com.
     *
     * @param array $module
     * @return string
     */
    protected function getLatestVersion($module)
    {
        $packages = $module['packages'];
        if (count($packages) == 1) {
            $moduleInfo = $packages[0];
            $linkTitle = explode(" ", $moduleInfo['title']);
            $latestVer = ltrim($this->getLinkVersion($linkTitle), 'v');
            return $latestVer;
        }

        $latestVer = $this->getLatestByExactVersionEdition($packages);

        if (empty($latestVer)) {
            $latestVer = $this->getLatestByExactEdition($packages);
        }

        if (empty($latestVer)) {
            $latestVer = $this->getLatestByExactVersion($packages);
        }

        if (empty($latestVer)) {
            $latestVer = $this->getLatestByRelativeVersion($packages);
        }

        if (empty($latestVer)) {
            return 'unknown';
        }

        return max($latestVer);
    }

    /**
     * Get module latest version by exact current Magento version and Edition.
     *
     * @param array $modulesInfo
     * @return array
     */
    protected function getLatestByExactVersionEdition($modulesInfo)
    {
        $magentoVer = $this->bssHelper->getMagentoVersion();
        $curEdition = $this->bssHelper->getMagentoEdition() == 'Community' ? 'CE' : 'EE';

        $latestVer = [];
        foreach ($modulesInfo as $moduleInfo) {
            $linkTitle = explode(" ", $moduleInfo['title']);
            $curlinkVer = ltrim($this->getLinkVersion($linkTitle), 'v');

            if (strpos($curlinkVer, $magentoVer) !== false && strpos($curlinkVer, $curEdition) !== false) {
                $latestVer[] = $curlinkVer;
            }
        }

        return $latestVer;
    }

    /**
     * Get module latest version by exact current Magento Edition.
     *
     * @param array $modulesInfo
     * @return array
     */
    protected function getLatestByExactEdition($modulesInfo)
    {
        $magentoRelativeVer = $this->bssHelper->getMagentoRelativeVersion();
        $curEdition = $this->bssHelper->getMagentoEdition() == 'Community' ? 'CE' : 'EE';

        $latestVer = [];
        foreach ($modulesInfo as $moduleInfo) {
            $linkTitle = explode(" ", $moduleInfo['title']);
            $curlinkVer = ltrim($this->getLinkVersion($linkTitle), 'v');

            if (strpos($curlinkVer, $magentoRelativeVer) !== false
                && strpos($curlinkVer, $curEdition) !== false) {
                $latestVer[] = $curlinkVer;
            }
        }

        return $latestVer;
    }

    /**
     * Get module latest version by exact current Magento Version.
     *
     * @param array $modulesInfo
     * @return array
     */
    protected function getLatestByExactVersion($modulesInfo)
    {
        $magentoVer = $this->bssHelper->getMagentoVersion();

        $latestVer = [];
        foreach ($modulesInfo as $moduleInfo) {
            $linkTitle = explode(" ", $moduleInfo['title']);
            $curlinkVer = ltrim($this->getLinkVersion($linkTitle), 'v');

            if (strpos($curlinkVer, $magentoVer) !== false) {
                $latestVer[] = $curlinkVer;
            }
        }

        return $latestVer;
    }

    /**
     * Get module latest version by current Magento Relative Version.
     *
     * @param array $modulesInfo
     * @return array
     */
    protected function getLatestByRelativeVersion($modulesInfo)
    {
        $magentoRelativeVer = $this->bssHelper->getMagentoRelativeVersion();

        $latestVer = [];
        foreach ($modulesInfo as $moduleInfo) {
            $linkTitle = explode(" ", $moduleInfo['title']);
            $curlinkVer = ltrim($this->getLinkVersion($linkTitle), 'v');

            if (strpos($curlinkVer, $magentoRelativeVer) !== false) {
                $latestVer[] = $curlinkVer;
            }
        }

        return $latestVer;
    }

    /**
     * Get version from link title.
     *
     * @param array $linkTitle
     * @return string
     */
    protected function getLinkVersion($linkTitle)
    {
        $index = (count($linkTitle) - 1);
        return $linkTitle[$index];
    }

    /**
     * @param string $apiName
     * @return array
     */
    protected function searchByModule($apiName)
    {
        $indexOfModule = array_search($apiName, array_column($this->modules, 'name'));
        if ($indexOfModule !== false) {
            return $this->modules[$indexOfModule];
        }
        return [];
    }
}
