<?php

/**
 * Description of Grid
 *
 * @author david
 */
class Osdave_LanguageCsv_Block_File_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        $this->setSaveParametersInSession(true);
        $this->setId('languagecsvsGrid');
        $this->setDefaultSort('name', 'asc');
    }

    /**
     * Init CSV Languages Files collection
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getSingleton('languagecsv/file_collection');
//        Mage::log($collection->load(), null, 'debug.log', true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Configuration of grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('file', array(
            'header'    => Mage::helper('languagecsv')->__('File'),
            'format'    => '<a href="' . $this->getUrl('*/*/download', array('name' => '$name')) .'">$name</a>',
            'index'     => 'name',
            'sortable'  => false,
            'filter'    => false
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('languagecsv')->__('Action'),
            'type'      => 'action',
            'width'     => '80px',
            'filter'    => false,
            'sortable'  => false,
            'actions'   => array(array(
                'url'       => $this->getUrl('*/*/delete', array('name' => '$name')),
                'caption'   => Mage::helper('adminhtml')->__('Delete'),
                'confirm'   => Mage::helper('adminhtml')->__('Are you sure you want to do this?')
            )),
            'index'     => 'type',
            'sortable'  => false
        ));

        return $this;
    }

}
