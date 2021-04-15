<?php
/**
 * @var $customEnrollmentProcess CE_Process
 * @var $stepNum integer
 */

$userType = $customEnrollmentProcess->getStepPayload(1)['type'];
$allowPurchaseRejection = $userType == 'Affiliate';

if (isset($_REQUEST['product-pack']) && isset(CE_ProcessOptions::PRODUCT_PACKS[$_REQUEST['product-pack']])) {
    $productPack = $_REQUEST['product-pack'];

    $purchaseRejection = $allowPurchaseRejection && $_REQUEST['dont-want-product-pack'] == 'on';
    $customEnrollmentProcess->setStepPayload(2, [
        'product-pack' => $productPack,
        'purchase-rejection' => $purchaseRejection
    ]);

    if ($purchaseRejection) {
        wp_redirect(wc_get_checkout_url());
        exit;
    }
    $plugin = CE_ProcessPlugin::getInstance();

    $productSKU = $plugin->getOptionValue($productPack . CE_ProcessOptions::PRODUCT_PACK_POSTFIX, '');
    $productId = wc_get_product_id_by_sku($productSKU);
    if (!$productId) {
        wp_redirect(wc_get_checkout_url());
        exit;
    }
    $productUrl = get_permalink($productId);
    $customEnrollmentProcess->addAfterAddToCartRedirectAction($productId, wc_get_checkout_url());
    wp_redirect($productUrl);
    exit;
}

get_header('shop'); ?>

<header>
    <h1 style="text-align: center;"><?php echo 'Upgrade to ' . $customEnrollmentProcess->getStepPayload(1)['type'] ?></h1>
</header>

<form method="POST" class="woocommerce-form woocommerce-form-register">
    <p class="form-row form-row-wide">
        <label for="reg_select_product_pack">Select product pack</label>
        <select name="product-pack" id="reg_select_product_pack" class="input-text">
            <?php foreach (CE_ProcessOptions::PRODUCT_PACKS as $key => $label) { ?>
                <option value="<?php echo $key ?>"><?php echo $label ?></option>
            <?php } ?>
        </select>
    </p>
    <?php if ($allowPurchaseRejection) { ?>
        <p class="form-row form-row-wide">
            <label for="reg_dont_want_product_pack">I don`t want a product pack</label>
            <input type="checkbox" name="dont-want-product-pack" id="reg_dont_want_product_pack" class="input-checkbox"/>
        </p>
    <?php } ?>
    <div class="clear"></div>
    <input type="submit" value="Continue">
</form>
