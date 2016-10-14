<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;


class To0020010064 extends \System
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {

            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();

            foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $config) {
                if ('mediaManager' === $config['inputType']) {
                    $arrFields[] = $field;
                }
            }

            if (0 === count($arrFields)) {
                return;
            }

            $objProducts = \Database::getInstance()->query("
                SELECT * FROM tl_iso_product WHERE language=''
            ");

            while ($objProducts->next()) {
                $arrUpdate = array();

                foreach ($arrFields as $field) {
                    $arrData = deserialize($objProducts->$field);

                    if (!empty($arrData) && is_array($arrData)) {
                        foreach ($arrData as $k => $image) {
                            if ($image['translate'] == '') {
                                $arrData[$k]['translate'] = 'none';
                            }
                        }

                        $arrUpdate[$field] = serialize($arrData);
                    }
                }

                if (0 !== count($arrUpdate)) {
                    \Database::getInstance()->prepare(
                        "UPDATE tl_iso_product %s WHERE id=?"
                    )->set($arrUpdate)->execute($objProducts->id);
                }
            }
        }
    }
}
