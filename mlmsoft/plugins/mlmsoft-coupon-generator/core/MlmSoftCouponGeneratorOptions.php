<?php

class MlmSoftCouponGeneratorOptions
{
    const PLUGIN_PREFIX = 'mlmsoft_coupon_generator_';
    const TYPE_TEXT_AREA_FIELD = 'textarea';
    const TYPE_TEXT_FIELD = 'text';
    const TYPE_PASSWORD = 'password';

    public $options = [];
    public $generateParams = [];

    public function __construct()
    {
        /*  Setting up actions... */
        add_action('admin_menu', array($this, 'coupon_generator_add_options'));
        add_action('admin_init', array($this, 'coupon_generator_register_settings'));

        $this->init();
    }


    /**
     * Get Wp options from Custom Settings page
     */
    public function init()
    {
        $this->addGenerateParam('users_for_generation', 'List of users', self::TYPE_TEXT_AREA_FIELD);
        $this->addGenerateParam('amount_to_give', 'Amount to give', self::TYPE_TEXT_FIELD);
        $this->addGenerateParam('amount_to_deduct', 'Amount to deduct', self::TYPE_TEXT_FIELD);
        $this->addOption('wallet_type_id', 'Wallet type id', self::TYPE_TEXT_FIELD);
        $this->addOption('operation_type_id', 'Operation type id', self::TYPE_TEXT_FIELD);

        foreach ($this->options as $key => $option) {
            if (is_array($this->options[$key])) {
                $this->options[$key]['value'] = get_option($option['id']);
            }
        }

        return $this->options;
    }


    public function coupon_generator_add_options()
    {
        add_submenu_page('woocommerce-marketing', 'Coupon generator', 'Generate coupons', 'manage_woocommerce', 'mlmsoft_coupon_generator_settings', [$this, 'coupon_generator_options_callback']);
    }


    public function coupon_generator_options_callback()
    {
        ?>
        <div>
            <h1> MLMSoft coupon generator</h1>
            <form action="<?php echo get_admin_url() . "admin-post.php" ?>" method="POST" class="repeater">
                <input type="hidden" name="action" value="generate" />
                <?php
                $this->showParamsForm();
                submit_button('Generate', 'submit', 'generate');
                ?>
            </form>
            <form action="options.php" method="POST" class="repeater">
                <?php
                settings_fields("coupon_generator_options");
                do_settings_sections("mlmsoft_coupon_generator_settings");
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    private function addOption($alias, $label, $type, $section = 1, $value = '',  $afterLabel = '')
    {
        $this->options[$alias] =
        [
            'id' => self::PLUGIN_PREFIX . $alias,
            'label' => $label,
            'type' => $type,
            'after_label' => $afterLabel,
            'value' => $value,
            'section' => $section
        ];
    }

    private function addGenerateParam($alias, $label, $type, $section = 1, $value = '',  $afterLabel = '')
    {
        $this->generateParams[$alias] =
        [
            'id' => self::PLUGIN_PREFIX . $alias,
            'label' => $label,
            'type' => $type,
            'after_label' => $afterLabel,
            'value' => $value,
            'section' => $section
        ];
    }

    private function showParamsForm()
    {
        echo '<table class="form-table" role="presentation"><tbody>';
        foreach ($this->generateParams as $key => $param) {
            echo '<tr><th scope="row">' . $param['label'] .'</th>';
            echo '<td>';
            $func = 'callback_for_' . $param['type'];
            $this->$func($param);
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }


    public function coupon_generator_register_settings()
    {
        //Add sections
        add_settings_section('coupon_generator_section_1', 'Coupon generator params', '', 'mlmsoft_coupon_generator_settings');

        foreach ($this->options as $key => $option) {

            if (is_array($this->options[$key])) {

                register_setting('coupon_generator_options', $option['id'], '');

                add_settings_field(
                    $option['id'],
                    (isset($option['label'])) ? $option['label'] : $key,
                    array($this, 'callback_for_' . $option['type']),
                    'mlmsoft_coupon_generator_settings',
                    'coupon_generator_section_' . $option['section'],
                    array(
                        'id' => $option['id'],
                        'after_label' => $option['after_label'],
                        'options' => isset($option['options']) ? $option['options'] : []
                    )
                );

            }

        }
    }


    /**
     * Callback for particular option # 1
     */

    public function callback_for_text($arg)
    {

        ?><input type="text" name="<?php echo $arg['id'] ?>" id="<?php echo $arg['id'] ?>"
                 value="<?php echo esc_attr(get_option($arg['id'])) ?>" size="40" /> <?php

        if ($arg['after_label']) {
            ?>  <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
        }

    }

    public function callback_for_textarea($arg)
    {

        ?><textarea name="<?php echo $arg['id'] ?>" id="<?php echo $arg['id'] ?>" cols="40"
                    rows="4"><?php echo esc_attr(get_option($arg['id'])) ?></textarea><?php

        if ($arg['after_label']) {
            ?>  <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
        }

    }


    /**
     * Callback for particular option # 2
     */

    public function callback_for_password($arg)
    {

        ?><input type="password" name="<?php echo $arg['id'] ?>" id="<?php echo $arg['id'] ?>"
                 value="<?php echo esc_attr(get_option($arg['id'])) ?>" /> <?php

        if ($arg['after_label']) {
            ?>  <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
        }

    }


    /**
     * Callback for particular option # 3
     */

    public function callback_for_checkbox($arg)
    {
        ?><input type="checkbox" id="<?php echo $arg['id'] ?>" name="<?php echo $arg['id'] ?>"
                 value="1" <?php checked('1' == get_option($arg['id'])); ?> /> <?php

        if ($arg['after_label']) {
            ?> <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
        }
    }

    public function callback_for_select($arg)
    {
        ?><select id="<?= $arg['id'] ?>" name="<?= $arg['id'] ?>">
        <?php foreach ($arg['options'] as $value => $label) {
            echo "<option value='$value'>$label</option>";
        }

        if ($arg['after_label']) {
            ?> <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
        }
        echo '</select>';
    }


}

