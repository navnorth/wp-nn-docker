<?php

defined('ABSPATH') or die("Cannot access pages directly.");

class MasterDetailWDTColumn extends WDTColumn
{

    protected $_jsDataType = 'masterdetail';
    protected $_dataType = 'masterdetail';
    protected $_linkButtonAttribute = 0;
    protected $_linkButtonLabel = '';
    protected $_linkButtonClass = '';


    /**
     * MasterDetailWDTColumn constructor.
     * @param array $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'masterdetail';
        $this->setLinkButtonAttribute(WDTTools::defineDefaultValue($properties, 'linkButtonAttribute', 0));
        $this->setLinkButtonLabel(WDTTools::defineDefaultValue($properties, 'linkButtonLabel', ''));
        $this->setLinkButtonClass(WDTTools::defineDefaultValue($properties, 'linkButtonClass', ''));
    }


    /**
     * @param $content
     * @return mixed|string
     * @throws Exception
     */
    public function prepareCellOutput($content)
    {

        $buttonClass = $this->getLinkButtonClass();
        $tableSettings = WDTConfigController::loadTableFromDB($this->getParentTable()->getWpID());
        $advancedSettings = json_decode($tableSettings->advanced_settings);
        $masterDetailRenderPage = $advancedSettings->masterDetailRenderPage;
        $masterDetailRenderPost = $advancedSettings->masterDetailRenderPost;
        $masterDetailRender = $advancedSettings->masterDetailRender;
        $formattedValue = '';

        if ($this->getLinkButtonAttribute() == 1 && $content !== '') {
            $buttonLabel = $this->getLinkButtonLabel() !== '' ? $this->getLinkButtonLabel() : $content;
            if ($masterDetailRender == 'popup'){
                $formattedValue = "<a class='master_detail_column_btn'><button class='{$buttonClass}'>{$buttonLabel}</button></a>";
            } else if ($masterDetailRender == 'wdtNewPage' || $masterDetailRender == 'wdtNewPost'){
                $renderAction = $masterDetailRender == 'wdtNewPage' ? $masterDetailRenderPage : $masterDetailRenderPost;
                $formattedValue = "<form class='wdt_md_form' method='post' target='_blank' action='{$renderAction}'>
                                       <input class='wdt_md_hidden_data' type='hidden' name='wdt_details_data' value=''>
                                       <input class='master_detail_column_btn {$buttonClass}' type='submit' value='{$buttonLabel}'>
                                       </form>";
            }
        } else {
            if ($content == '') {
                return null;
            } else {
                if ($masterDetailRender == 'popup'){
                    $formattedValue = "<a class='master_detail_column_btn'>{$content}</a>";
                } else if ($masterDetailRender == 'wdtNewPage' || $masterDetailRender == 'wdtNewPost'){
                    $renderAction = $masterDetailRender == 'wdtNewPage' ? $masterDetailRenderPage : $masterDetailRenderPost;
                    $formattedValue = "<form class='wdt_md_form' method='post' target='_blank' action='{$renderAction}'>
                                       <input class='wdt_md_hidden_data' type='hidden' name='wdt_details_data' value=''>
                                       <input class='master_detail_column_btn md-link' type='submit' value='{$content}'>
                                       </form>";
                }
            }
        }

        $formattedValue = apply_filters('wpdatatables_filter_details_cell', $formattedValue, $this->getParentTable()->getWpId());
        return $formattedValue;
    }

    /**
     * @return int
     */
    public function getLinkButtonAttribute()
    {
        return $this->_linkButtonAttribute;
    }

    /**
     * @param int $linkButtonAttribute
     */
    public function setLinkButtonAttribute($linkButtonAttribute)
    {
        $this->_linkButtonAttribute = $linkButtonAttribute;
    }


    /**
     * @return string
     */
    public function getLinkButtonLabel()
    {
        return $this->_linkButtonLabel;
    }

    /**
     * @param string $linkButtonLabel
     */
    public function setLinkButtonLabel($linkButtonLabel)
    {
        $this->_linkButtonLabel = $linkButtonLabel;
    }


    /**
     * @return string
     */
    public function getLinkButtonClass()
    {
        return $this->_linkButtonClass;
    }

    /**
     * @param string $linkButtonClass
     */
    public function setLinkButtonClass($linkButtonClass)
    {
        $this->_linkButtonClass = $linkButtonClass;
    }
}
