<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;

use Isotope\Model\Attribute;

class To0020030029 extends Base
{
    /**
     * @var \Contao\Database
     */
    private $db;

    public function run($blnInstalled)
    {
        $this->db = \Database::getInstance();

        if ($blnInstalled) {
            $collections = $this->db->execute(
                "SELECT uniqid, COUNT(id) AS total
                FROM tl_iso_product_collection
                WHERE uniqid IS NOT NULL
                GROUP BY uniqid
                HAVING total>1"
            );

            while ($collections->next()) {
                $this->db
                    ->prepare("UPDATE tl_iso_product_collection SET uniqid=NULL WHERE uniqid=?")
                    ->execute($collections->uniqid)
                ;
            }
        }
    }
}
