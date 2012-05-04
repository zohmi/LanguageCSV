<?php

/**
 * Description of File
 *
 * @author david
 */
class Osdave_LanguageCsv_Block_File extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('osdave/languagecsv/list.phtml');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setChild('createButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('languagecsv')->__('Create CSV Language File'),
                    'onclick' => "csvFile.selectModule(this)",
                    'class'  => 'task'
                ))
        );
        $this->setChild('languagecsvsGrid',
            $this->getLayout()->createBlock('languagecsv/file_grid')
        );
    }

    public function getCreateButtonHtml()
    {
        return $this->getChildHtml('createButton');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('languagecsvsGrid');
    }
}