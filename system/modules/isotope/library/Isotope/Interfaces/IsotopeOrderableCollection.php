<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Interfaces;

use Isotope\Model\Address;
use Isotope\Model\ProductCollectionSurcharge;

/**
 * IsotopeOrderableCollection describes a product collection that can have order information.
 */
interface IsotopeOrderableCollection extends IsotopeProductCollection
{
    /**
     * Return boolean whether collection has payment
     *
     * @return bool
     */
    public function hasPayment();

    /**
     * Return boolean whether collection requires payment
     *
     * @return bool
     */
    public function requiresPayment();

    /**
     * Return payment method for this collection
     *
     * @return IsotopePayment|null
     */
    public function getPaymentMethod();

    /**
     * Set payment method for this collection
     *
     * @param IsotopePayment $objPayment
     */
    public function setPaymentMethod(IsotopePayment $objPayment = null);

    /**
     * Return surcharge for current payment method
     *
     * @return ProductCollectionSurcharge|null
     */
    public function getPaymentSurcharge();

    /**
     * Get billing address for collection
     *
     * @return  \Isotope\Model\Address|null
     */
    public function getBillingAddress();

    /**
     * Set billing address for collection
     *
     * @param Address $objAddress
     */
    public function setBillingAddress(Address $objAddress = null);

    /**
     * Return boolean whether collection has shipping
     *
     * @return bool
     */
    public function hasShipping();

    /**
     * Return boolean whether collection requires shipping
     *
     * @return bool
     */
    public function requiresShipping();

    /**
     * Return shipping method for this collection
     *
     * @return IsotopeShipping|null
     */
    public function getShippingMethod();

    /**
     * Set shipping method for this collection
     *
     * @param IsotopeShipping $objShipping
     */
    public function setShippingMethod(IsotopeShipping $objShipping = null);

    /**
     * Return surcharge for current shipping method
     *
     * @return ProductCollectionSurcharge|null
     */
    public function getShippingSurcharge();

    /**
     * Get shipping address for collection
     *
     * @return  Address|null
     */
    public function getShippingAddress();

    /**
     * Set shipping address for collection
     *
     * @param Address $objAddress
     */
    public function setShippingAddress(Address $objAddress = null);

    /**
     * Returns the generated document number or empty string if not available.
     *
     * @return string
     */
    public function getDocumentNumber();
}
