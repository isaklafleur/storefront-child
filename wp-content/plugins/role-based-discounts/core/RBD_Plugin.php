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
            $product_category = [];

            foreach ($items as $item) {
                $category = $item['data']->get_category_ids();
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                if (isset($category[0]) && !isset($excludedCategories[$category[0]])) {
                    $productCat = get_term_by('term_id', $category[0], 'product_cat');
                    $categories[$productCat->term_id] = $productCat;
                    if (!isset($product_category[$category[0]])) {
                        $product_category[$category[0]] = [
                            'quantity' => $quantity,
                            'products' => [
                                $product_id
                            ],
                            'sum' => $item['line_subtotal']
                        ];
                    } else {
                        $product_category[$category[0]]['quantity'] += $quantity;
                        $product_category[$category[0]]['products'][] = $product_id;
                        $product_category[$category[0]]['sum'] += $item['line_subtotal'];
                    }
                }
            }

            foreach ($product_category as $cat_id => $data) {
                $discount = $data['sum'] * $percentage;
                $name = $categories[$cat_id]->name;
                $discount_value = $percentage * 100;
                $cart->add_fee("{$name} (-{$discount_value} %)", -$discount);
            }
        }
    }
}