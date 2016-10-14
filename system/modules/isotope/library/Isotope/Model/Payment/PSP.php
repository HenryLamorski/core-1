<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Isotope\Template;


/**
 * Class PSP
 *
 * Handle PSP payments
 *
 * @property string $psp_pspid
 * @property string $psp_http_method
 * @property string $psp_hash_method
 * @property string $psp_hash_in
 * @property string $psp_hash_out
 * @property string $psp_dynamic_template
 * @property string $psp_payment_method
 */
abstract class PSP extends Payment implements IsotopePostsale
{
    /**
     * @inheritdoc
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        // If the order has already been placed through postsale
        if ($objOrder->isCheckoutComplete()) {
            return true;
        }

        // In processPayment, the parameters are always in GET
        $this->psp_http_method = 'GET';

        return $this->processPostsale($objOrder);
    }

    /**
     * Process post-sale request from the PSP payment server.
     *
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        if (!$this->validateSHASign()) {
            \System::log('Received invalid postsale data for order ID "' . $objOrder->getId() . '"', __METHOD__, TL_ERROR);
            return false;
        }

        // Validate payment data
        if ($objOrder->getCurrency() !== $this->getRequestData('currency')
            || $objOrder->getTotal() != $this->getRequestData('amount')
        ) {
            \System::log('Postsale checkout manipulation in payment for Order ID ' . $objOrder->getId() . '!', __METHOD__, TL_ERROR);
            return false;
        }

        // Validate payment status
        switch ($this->getRequestData('STATUS')) {

            /** @noinspection PhpMissingBreakStatementInspection */
            case 9:  // Zahlung beantragt (Authorize & Capture)
                $objOrder->setDatePaid(time());
                // no break

            case 5:  // Genehmigt (Authorize ohne Capture)
                $intStatus = $this->new_order_status;
                break;

            case 41: // Unbekannter Wartezustand
            case 51: // Genehmigung im Wartezustand
            case 91: // Zahlung im Wartezustand
            case 52: // Genehmigung nicht bekannt
            case 92: // Zahlung unsicher

                /** @var \Isotope\Model\Config $objConfig */
                if (($objConfig = $objOrder->getConfig()) === null) {
                    \System::log('Config for Order ID ' . $objOrder->getId() . ' not found', __METHOD__, TL_ERROR);
                    return false;
                }

                $intStatus = $objConfig->orderstatus_error;
                break;

            case 0:  // Ungültig / Unvollständig
            case 1:  // Zahlungsvorgang abgebrochen
            case 2:  // Genehmigung verweigert
            case 4:  // Gespeichert
            case 93: // Bezahlung verweigert
            default:
                return false;
        }

        if (!$objOrder->checkout()) {
            \System::log('Post-Sale checkout for Order ID "' . $objOrder->getId() . '" failed', __METHOD__, TL_ERROR);
            return false;
        }

        $objOrder->payment_data = json_encode($this->getRawRequestData());

        $objOrder->updateOrderStatus($intStatus);
        $objOrder->save();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        if (!$this->getRequestData('orderID')) {
            return null;
        }

        return Order::findByPk((int) $this->getRequestData('orderID'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $arrParams = $this->preparePSPParams($objOrder, $objModule);

        // SHA-1 must be generated on alphabetically sorted keys.
        // Use the natural order algorithm so ITEM10 gets listed after ITEM2
        // We can only use ksort($arrParams, SORT_NATURAL) as of PHP 5.4
        uksort($arrParams, 'strnatcasecmp');

        $strSHASign = '';
        foreach ($arrParams as $k => $v) {
            if ($v == '') {
                continue;
            }

            $strSHASign .= $k . '=' . htmlspecialchars_decode($v) . $this->psp_hash_in;
        }

        $arrParams['SHASIGN'] = strtoupper(hash($this->psp_hash_method, $strSHASign));

        /** @var Template|object $objTemplate */
        $objTemplate = new Template($this->strTemplate);
        $objTemplate->setData($this->arrData);

        $objTemplate->params   = $arrParams;
        $objTemplate->headline = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }

    /**
     * Gets the available payment methods
     *
     * @return array
     */
    abstract public function getPaymentMethods();

    /**
     * Prepare PSP params
     *
     * @param   IsotopePurchasableCollection $objOrder
     * @param   \Module                      $objModule
     *
     * @return  array
     */
    protected function preparePSPParams(IsotopePurchasableCollection $objOrder, $objModule)
    {
        $objBillingAddress = $objOrder->getBillingAddress();

        return array
        (
            'PSPID'         => $this->psp_pspid,
            'ORDERID'       => $objOrder->getId(),
            'AMOUNT'        => round($objOrder->getTotal() * 100),
            'CURRENCY'      => $objOrder->getCurrency(),
            'LANGUAGE'      => $GLOBALS['TL_LANGUAGE'] . '_' . strtoupper($GLOBALS['TL_LANGUAGE']),
            'CN'            => $objBillingAddress->firstname . ' ' . $objBillingAddress->lastname,
            'EMAIL'         => $objBillingAddress->email,
            'OWNERZIP'      => $objBillingAddress->postal,
            'OWNERADDRESS'  => substr($objBillingAddress->street_1, 0, 35),
            'OWNERADDRESS2' => substr($objBillingAddress->street_2, 0, 35),
            'OWNERCTY'      => strtoupper($objBillingAddress->country),
            'OWNERTOWN'     => substr($objBillingAddress->city, 0, 35),
            'OWNERTELNO'    => preg_replace('/[^- +\/0-9]/','', $objBillingAddress->phone),
            'ACCEPTURL'     => \Environment::get('base') . Checkout::generateUrlForStep('complete', $objOrder),
            'DECLINEURL'    => \Environment::get('base') . Checkout::generateUrlForStep('failed'),
            'BACKURL'       => \Environment::get('base') . Checkout::generateUrlForStep('review'),
            'PARAMPLUS'     => 'mod=pay&amp;id=' . $this->id,
            'TP'            => $this->psp_dynamic_template ? : ''
        );
    }

    /**
     * Gets the request data based on the chosen HTTP method
     *
     * @param string $strKey
     *
     * @return  mixed
     */
    private function getRequestData($strKey)
    {
        if ('GET' === $this->psp_http_method) {
            return $_GET[$strKey];
        }

        return $_POST[$strKey];
    }

    /**
     * Gets the raw request data based on the chosen HTTP method
     *
     * @return  array
     */
    private function getRawRequestData()
    {
        if ('GET' === $this->psp_http_method) {
            return $_GET;
        }

        return $_POST;
    }


    /**
     * Validate SHA-OUT signature
     *
     * @return bool
     */
    private function validateSHASign()
    {
        $strSHASign = '';
        $arrParams  = array();

        foreach ($this->getRawRequestData() as $key => $value) {
            if (in_array(strtoupper($key), static::$arrShaOut)) {
                $arrParams[$key] = $value;
            }
        }

        // SHA-1 must be generated on alphabetically sorted keys.
        // Use the natural order algorithm so ITEM10 gets listed after ITEM2
        // We can only use ksort($arrParams, SORT_NATURAL) as of PHP 5.4
        uksort($arrParams, 'strnatcasecmp');

        foreach ($arrParams as $k => $v) {
            if ($v == '') {
                continue;
            }

            $strSHASign .= strtoupper($k) . '=' . $v . $this->psp_hash_out;
        }

        if ($this->getRequestData('SHASIGN') == strtoupper(hash($this->psp_hash_method, $strSHASign))) {
            return true;
        }

        log_message(
            sprintf(
                "Received invalid postsale data.\nInput hash: %s\nCalculated hash: %s\nParameters: %s\n",
                $this->getRequestData('SHASIGN'),
                strtoupper(hash($this->psp_hash_method, $strSHASign)),
                print_r($arrParams, true)
            ),
            'isotope_psp.log'
        );

        return false;
    }
}
