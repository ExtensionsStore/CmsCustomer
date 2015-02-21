<?php

/**
 * CmsCustomer install
 *
 * @category   Aydus
 * @package    Aydus_CmsCustomer
 * @author     Aydus Consulting <davidt@aydus.com>
 */

$this->startSetup();
echo "CmsCustomer setup started...<br />";

$this->addAttribute('catalog_product', 'page_ids', array(
        'type'              => 'varchar',
        'backend'           => 'eav/entity_attribute_backend_array',
        'frontend'          => '',
        'label'             => 'CMS Pages',
        'input'             => 'multiselect',
        'class'             => '',
        'source'            => 'aydus_cmscustomer/page_source',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'note'              => 'CMS pages this product is associated with',
        'group'             => 'General'
));

$cmsPage = Mage::getModel('cms/page')->load('unauthorized','identifier');

if (!$cmsPage->getId()){
    
    $creationTime = date('Y-m-d H:i:s');
    
    $cmsPage->setTitle('Unauthorized')
    ->setStoreId(0)
    ->setRootTemplate('two_columns_left')
    ->setIdentifier('unauthorized')
    ->setContentHeading('Unauthorized')
    ->setContent('<p>Sorry, you\'re not authorized.</p>')
    ->setCreationTime($creationTime)
    ->setUpdateTime($creationTime)
    ->save();
}

echo "CmsCustomer setup ended<br />";
$this->endSetup();