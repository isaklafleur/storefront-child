<?php


class RBD_Plugin
{
    const PLUGIN_BASE_NAME = 'role-based-discounts/role-based-discounts.php';

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * @var RBD_Options
     */
    public $options;

    /**
     * Returns an instance of this class.
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new RBD_Plugin();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->options = new RBD_Options();
        $this->registerHooks();
    }

    public function registerHooks()
    {
        add_action('woocommerce_cart_calculate_fees', [$this, 'add_discount'], 20, 1);
    }

    public function add_discount($cart)
    {
        $userRole = $this->options->get_option_value(RBD_Options::USER_ROLE_KEY, '');
        $currentUser = wp_get_current_user();
        if (wc_user_has_role($currentUser, $userRole)) {
            $percentage = round($this->options->get_option_value(RBD_Options::DISCOUNT_VALUE_KEY, 0) / 100, 2);
            $discounts = $this->calcDiscounts($cart);

            foreach ($discounts as $cat_id => $data) {
                $discount = array_sum($data['discounts']);
                $name = $data['category']->name;
                $discount_value = $percentage * 100;
                $cart->add_fee("{$name} (-{$discount_value} %)", -$discount);
            }
        }
    }

    public function calcDiscounts($cart)
    {
        $categories = $this->getIncludedCategories($cart);
        $percentage = round($this->options->get_option_value(RBD_Options::DISCOUNT_VALUE_KEY, 0) / 100, 2);

        $items = $cart->get_cart_contents();
        $discounts = [];

        foreach ($items as $key => $item) {
            $category = $item['data']->get_category_ids();
            $quantity = $item['quantity'];
            if (isset($category[0], $categories[$category[0]])) {
                $categoryId = $category[0];
                if (!isset($discounts[$categoryId])) {
                    $discounts[$categoryId] = [
                        'quantity' => $quantity,
                        'products' => [$key => $item],
                        'discounts' => [$key => $item['line_subtotal'] * $percentage],
                        'sum' => $item['line_subtotal']
                    ];
                    $discounts[$categoryId]['category'] = $categories[$categoryId];
                } else {
                    $discounts[$categoryId]['quantity'] += $quantity;
                    $discounts[$categoryId]['products'][$key] = $item;
                    $discounts[$categoryId]['discounts'][$key] = $item['line_subtotal'] * $percentage;
                    $discounts[$categoryId]['sum'] += $item['line_subtotal'];
                }
            }
        }

        return $discounts;
    }

    public function getIncludedCategories($cart)
    {
        $excludedCategoriesOption = $this->options->get_option_value(RBD_Options::EXCLUDED_CATEGORIES_KEY, []);
        $excludedCategoriesSlugs = explode(',', $excludedCategoriesOption);
        /** @var WP_Term[] $excludedCategoriesArray */
        $excludedCategoriesArray = [];
        foreach ($excludedCategoriesSlugs as $slug) {
            $category = get_term_by('slug', trim($slug), 'product_cat');
            if (!empty($category)) {
                $excludedCategoriesArray[] = $category;
            }
        }
        /** @var WP_Term[] $excludedCategories */
        $excludedCategories = [];

        foreach ($excludedCategoriesArray as $key => $category) {
            $excludedCategories[$category->term_id] = $category;
        }

        /** @var WP_Term[] $categories */
        $categories = [];

        $items = $cart->get_cart_contents();
        foreach ($items as $item) {
            $category = $item['data']->get_category_ids();
            if (isset($category[0]) && !isset($excludedCategories[$category[0]])) {
                $productCategory = get_term_by('term_id', $category[0], 'product_cat');
                $categories[$productCategory->term_id] = $productCategory;
            }
        }

        ksort($categories);

        return $categories;
    }
}