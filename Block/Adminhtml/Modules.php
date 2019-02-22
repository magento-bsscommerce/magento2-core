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
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    private $fieldRenderer;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Bss\Core\Helper\Data $bssHelper
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
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->moduleList    = $moduleList;
        $this->layoutFactory = $layoutFactory;
        $this->bssHelper  = $bssHelper;
        $this->dataObjectFactory  = $dataObjectFactory;
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
                'name'  => 'dummy',
                'label' => 'Module',
                'current_ver' => 'Current Version',
                'latest_ver' => 'Latest Version'
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
     */
    protected function getFieldHtml($fieldset, $moduleCode)
    {
        $module = $this->bssHelper->getModuleInfo($moduleCode);

        if (!is_array($module)
            || !array_key_exists('version', $module)
            || !array_key_exists('description', $module)
        ) {
            return '';
        }

        $suite = null;
        if (isset($module['extra']['suite'])) {
            $suite = $module['extra']['suite'];
        }

        if ($this->bssHelper->isModuleEnable('Bss_Breadcrumbs') && $suite == 'seo-suite') {
            return '';
        }

        $moduleName = $module['description'];
        $apiName = $module['name'];

        $moduleName = str_replace('Bss', '', $moduleName);
        $moduleName = str_replace('Modules', '', $moduleName);
        $moduleName = str_replace('Module', '', $moduleName);
        $moduleName = trim($moduleName);

        $modules = $this->bssHelper->getRemoteModulesInfo();

        $latestVer = 'unknown';
        $moduleUrl = '#';
        if (isset($modules[$apiName])) {
            $latestVer = $this->getLatestVersion($modules[$apiName]);
            $moduleUrl = $this->getModuleUrl($modules[$apiName]);
        }

        $latestVerCol = $latestVer == 'unknown' ? $latestVer : "<a href = '$moduleUrl' target='_blank'>$latestVer</a>";

        $moduleVer = isset($module['extra']['suite-version']) ? $module['extra']['suite-version'] : $module['version'];
        $field = $fieldset->addField(
            $moduleCode,
            \Bss\Core\Block\Adminhtml\Form\Element\Columns::class,
            [
                'name'  => 'dummy',
                'label' => $moduleName,
                'current_ver' => $moduleVer,
                'latest_ver' => $latestVerCol
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
     * @param array $modulesInfo
     * @return string
     */
    protected function getModuleUrl($modulesInfo)
    {
        if (!isset($modulesInfo[0])) {
            return '#';
        }

        $moduleInfo = $modulesInfo[0];
        return isset($moduleInfo['product_url']) ? $moduleInfo['product_url'] : '#';
    }

    /**
     * Get module latest version from bsscommerce.com.
     *
     * @param array $modulesInfo
     * @return string
     */
    protected function getLatestVersion($modulesInfo)
    {
        if (count($modulesInfo) == 1) {
            $moduleInfo = $modulesInfo[0];
            $linkTitle = explode(" ", $moduleInfo['title']);
            $latestVer = ltrim($this->getLinkVersion($linkTitle), 'v');
            return $latestVer;
        }

        $latestVer = $this->getLatestByExactVersionEdition($modulesInfo);

        if (empty($latestVer)) {
            $latestVer = $this->getLatestByExactEdition($modulesInfo);
        }

        if (empty($latestVer)) {
            $latestVer = $this->getLatestByExactVersion($modulesInfo);
        }

        if (empty($latestVer)) {
            $latestVer = $this->getLatestByRelativeVersion($modulesInfo);
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
}
