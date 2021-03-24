<?php


class CE_ProcessCart
{
    const BRAND_PARTNER_OPTION = 'brandPartner';
    const AFFILIATE_OPTION = 'affiliate';

    public $affiliateSubscriptionSKU;
    public $brandPartnerSubscriptionSKU;

    private $plugin;

    public function __construct()
    {
        $this->plugin = CE_ProcessPlugin::getInstance();
        $this->affiliateSubscriptionSKU = $this->plugin->getOptionValue(CE_ProcessOptions::AFFILIATE_SUBSCRIPTION_KEY);
        $this->brandPartnerSubscriptionSKU = $this->plugin->getOptionValue(CE_ProcessOptions::BRAND_PARTNER_SUBSCRIPTION_KEY);
    }

    public function addToCart($productOption, $maxQuantity = 1)
    {
        $productSKU = $this->getProductSKU($productOption);
        if ($productSKU && !$this->checkAvailabilityInCart($productSKU, $maxQuantity)) {
            $productId = wc_get_product_id_by_sku($productSKU);
            if ($productId) {
                global $woocommerce;
                $woocommerce->cart->add_to_cart($productId, 1, 0);
            }
        }
    }

    public function clearCart()
    {
        $cart = WC()->cart;
        $cartContent = $cart->get_cart_contents();

        $affiliateSKU = $this->getProductSKU(self::AFFILIATE_OPTION);
        $brandPartnerSKU = $this->getProductSKU(self::BRAND_PARTNER_OPTION);

        foreach ($cartContent as $key => $contentItem) {
            /** @var WC_Product $product */
            $product = $contentItem['data'];
            if (($sku = $product->get_sku()) && ($sku == $affiliateSKU || $sku == $brandPartnerSKU))
            {
                $cart->remove_cart_item($key);
            }
        }
    }

    private function getProductSKU($productOption)
    {
        $subscriptionKey = $productOption . 'SubscriptionSKU';
        return isset($this->$subscriptionKey) ? $this->$subscriptionKey : false;
    }

    private function checkAvailabilityInCart($productSKU, $maxQuantity)
    {
        $cart = WC()->cart;
        $cartContent = $cart->get_cart_contents();

        foreach ($cartContent as $contentItem) {
            /** @var WC_Product $product */
            $product = $contentItem['data'];
            if ($product->get_sku() == $productSKU && $contentItem['quantity'] >= $maxQuantity)
            {
                return true;
            }
        }
        return false;
    }
}