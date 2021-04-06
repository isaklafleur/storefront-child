<?php


class MLMSoftWalletCoupons_Plugin
{
    const PLUGIN_BASE_NAME = 'mlm-soft-wallet-coupons/mlm-soft-wallet-coupons.php';

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * @var MLMSoftWalletCoupons_Options
     */
    public $options;

    /**
     * Returns an instance of this class.
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new MLMSoftWalletCoupons_Plugin();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->options = new MLMSoftWalletCoupons_Options();
        $this->registerHooks();
    }

    public function registerHooks()
    {
        add_action('woocommerce_before_cart', [$this, 'updateUserCoupon'], 10, 0);
        add_action('woocommerce_account_wc-smart-coupons_endpoint', [$this, 'updateUserCoupon'], 10, 0);

        add_filter('woocommerce_coupon_discount_types', [$this, 'crateCustomCouponDiscountType'], 10, 1);
        add_filter('woocommerce_coupon_custom_discounts_array', [$this, 'calcCustomDiscount'], 10, 2);

        add_action('woocommerce_checkout_order_created', [$this, 'performWalletOperationForOrder'], 10, 1);
    }

    public function updateUserCoupon()
    {
        $user = wp_get_current_user();
        $coupon = $this->getUserWalletCoupon($user->ID);

        global $MlmSoft;
        $wallets = $MlmSoft->get_wallets_balance($user->ID);
        if (isset($wallets->list[0])) {
            $coupon->set_amount(max($wallets->list[0]->balance, 0));
            $coupon->save();
        }
    }

    public function crateCustomCouponDiscountType($discount_types)
    {
        $discount_types['mlmsoft_wallet_discount'] = __('MLM Soft wallet discount', 'woocommerce');
        return $discount_types;
    }

    public function calcCustomDiscount($discounts, $coupon)
    {
        global $woocommerce;
        /** @var WC_Cart $cart */
        $cart = $woocommerce->cart;

        $rbd = RBD_Plugin::getInstance();
        $rbd_discounts = $rbd->calcDiscounts($cart);

        $totalRbdSum = 0;
        foreach ($rbd_discounts as $discount) {
            $totalRbdSum += array_sum($discount['discounts']);
        }

        $totalCartSum = 0;
        $cartContents = $cart->get_cart_contents();
        foreach ($cartContents as $cart_item_key => $cart_item) {
            $totalCartSum += $cart_item['line_subtotal'];
        }

        $totalCouponDiscount = floatval(min($totalCartSum - $totalRbdSum, $coupon->get_amount()));

        foreach ($cartContents as $cart_item_key => $cart_item) {
            if ($totalCouponDiscount == 0) {
                break;
            }
            $subtotal = $cart_item['line_subtotal'];
            $currentDiscountValue = min($subtotal, $totalCouponDiscount);

            $discounts[$cart_item_key] = wc_add_number_precision($currentDiscountValue);
            $totalCouponDiscount -= $currentDiscountValue;
        }
        return $discounts;
    }

    public function performWalletOperationForOrder($order) {
        $appliedCoupons = $order->get_coupons();
        if ($appliedCoupons) {
            $userId = get_current_user_id();
            $userCoupon = $this->getUserWalletCoupon($userId);
            foreach ($appliedCoupons as $key => $coupon) {
                if ($coupon->get_code() == $userCoupon->get_code()) {
                    global $MlmSoft;

                    $walletTypeId = $this->options->get_option_value(MLMSoftWalletCoupons_Options::MLMSOFT_WALLET_TYPE_ID_KEY, 0);
                    $operationTypeId = $this->options->get_option_value(MLMSoftWalletCoupons_Options::MLMSOFT_WALLET_OPERATION_ID_KEY, 0);

                    $discount = floatval($coupon->get_discount());
                    $MlmSoft->addWalletOperation($userId, -$discount, $walletTypeId, $operationTypeId);
                }
            }
        }
    }

    public function getUserWalletCoupon($userId)
    {
        $mlmSoftCouponGenerator = MlmSoftCouponGenerator::getInstance();

        $user = get_user_by('id', $userId);
        $couponCode = get_user_meta($user->ID, 'wallet_coupon_code', true);

        if (!$couponCode) {
            $coupon = $mlmSoftCouponGenerator->generateCoupon($user->user_email, 0, 15, '', 'mlmsoft_wallet_discount');
            add_user_meta($user->ID, 'wallet_coupon_code', $coupon->get_code());
        } else {
            $couponId = wc_get_coupon_id_by_code($couponCode);
            $coupon = new WC_Coupon($couponId);
            if (!$coupon->get_id()) {
                $coupon = $mlmSoftCouponGenerator->generateCoupon($user->user_email, 0, 15, '', 'mlmsoft_wallet_discount');
                update_user_meta($user->ID, 'wallet_coupon_code', $coupon->get_code());
                $coupon->save();
            }
        }
        return $coupon;
    }
}