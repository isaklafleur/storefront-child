<?php


class MlmSoftCouponGenerator
{
    private const ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * @var MlmSoftCouponGeneratorOptions
     */
    public $options;

    /**
     * Returns an instance of this class.
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new MlmSoftCouponGenerator();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->options = new MlmSoftCouponGeneratorOptions();
    }

    /**
     * @param string[] $userEmails
     * @param int $amount
     * @param int $codeLength
     * @param string $productCategory
     * @return array
     */
    public function generateCoupons($userEmails, $amount, $codeLength, $productCategory = '22')
    {
        $coupons = [];
        foreach ($userEmails as $userEmail) {
            $coupons[] = $this->generateCoupon($userEmail, $amount, $codeLength, '', $productCategory);
        }
        return $coupons;
    }

    /**
     * @param string $userEmail
     * @param int $amount
     * @param int $codeLength
     * @param string $description
     * @param string $productCategory
     * @param string $discountType
     * @return string
     */
    public function generateCoupon($userEmail, $amount, $codeLength, $description = '', $productCategory = '22', $discountType = 'smart_coupon')
    {
        $coupon = new WC_Coupon();
        $coupon->set_code($this->generateRandomCode($codeLength));
        $coupon->set_description($description);
        $coupon->set_discount_type($discountType);
        $coupon->set_amount($amount);
        $coupon->set_individual_use(true);
        $coupon->set_email_restrictions([$userEmail]);
        $coupon->set_product_categories([$productCategory]);
        $coupon->save();
        return $coupon;
    }

    private function generateRandomCode($length)
    {
        $alphabet = str_repeat(self::ALPHABET, (int)($length / mb_strlen(self::ALPHABET)) + 1);
        return mb_substr(str_shuffle($alphabet), 0, $length);
    }
}