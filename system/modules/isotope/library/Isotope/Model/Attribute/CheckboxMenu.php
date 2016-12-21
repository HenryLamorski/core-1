<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeAttributeWithOptions;

/**
 * Attribute to implement CheckboxMenu widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class CheckboxMenu extends AbstractAttributeWithOptions
{
    /**
     * @inheritdoc
     */
    public function prepareOptionsWizard($objWidget, $arrColumns)
    {
        unset($arrColumns['group']);

        return $arrColumns;
    }

    /**
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        if (!$this->variant_option && $this->optionsSource === IsotopeAttributeWithOptions::SOURCE_NAME) {
            $arrData['fields'][$this->field_name]['eval']['multiple'] = false;
            $arrData['fields'][$this->field_name]['sql'] = "char(1) NOT NULL default ''";
            $arrData['fields'][$this->field_name]['options'];
        } else {
            $arrData['fields'][$this->field_name]['eval']['multiple'] = true;
            $arrData['fields'][$this->field_name]['sql'] = 'blob NULL';
        }
    }
}
