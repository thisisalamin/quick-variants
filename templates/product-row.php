<?php
global $product;
$visible_variants = get_query_var('visible_variants', array());
?>
<tr class="!border-b !border-gray-200" data-product-id="<?php echo $product->get_id(); ?>">
    <td class="p-4 align-middle !border !border-gray-200 w-24 text-center">
        <?php 
        $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
        $img_url = $image ? $image[0] : wc_placeholder_img_src();
        ?>
        <img src="<?php echo esc_url($img_url); ?>" 
             alt="<?php echo esc_attr(get_the_title()); ?>" 
             class="w-[60px] h-[60px] object-contain mx-auto"/>
    </td>
    <td class="p-4 align-middle !border text-black !border-gray-200 w-[400px]"><?php echo strtoupper(get_the_title()); ?></td>
    <td class="p-4 align-middle !border !border-gray-200 text-center">
        <div class="flex flex-row items-center justify-center gap-1">
            <?php if ($product->is_type('variable')): 
                $min_price = $product->get_variation_price('min');
                echo 'From ' . '<span class="text-black font-medium">' . wc_price($min_price) . '</span>';
            else:
                echo '<span class="text-black font-medium">' . $product->get_price_html() . '</span>';
            endif; ?>
        </div>
    </td>
    <td class="p-4 align-middle !border !border-gray-200 text-center">
        <?php if (!$product->is_type('variable')): ?>
            <div class="flex justify-center">
                <input type="number" min="1" value="1" class="w-20 p-2 border rounded text-center">
            </div>
        <?php endif; ?>
    </td>
    <td class="p-4 align-middle !border !border-gray-200 text-center">
        <?php if ($product->is_type('variable')): ?>
            <button class="toggle-variants bg-[#232323] text-white px-4 py-2.5 text-sm hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 w-full mx-auto font-bold" data-id="<?php echo $product->get_id(); ?>">
                SHOW VARIANTS
            </button>
        <?php else: ?>
            <button class="add-to-cart bg-[#232323] text-white px-4 py-2.5 text-sm hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 w-full mx-auto font-bold" data-id="<?php echo $product->get_id(); ?>">
                ADD TO CART
            </button>
        <?php endif; ?>
    </td>
</tr>
<?php
if ($product->is_type('variable')) {
    $variations = $product->get_available_variations();
    foreach ($variations as $variation) {
        if (!$variation['is_in_stock'] || !isset($variation['display_price']) || $variation['display_price'] <= 0) {
            continue;
        }
        $is_visible = in_array($product->get_id(), $visible_variants);
        ?>
        <tr class="variant-row variant-<?php echo $product->get_id(); ?> !border-b !border-gray-200 bg-gray-50" 
            style="display: <?php echo $is_visible ? 'table-row' : 'none'; ?>;">
            <td class="p-4 align-middle !border !border-gray-200">
                <img src="<?php echo esc_url($variation['image']['url']); ?>" 
                     alt="<?php echo esc_attr($variation['variation_description']); ?>"
                     class="w-[60px] h-[60px] object-contain mx-auto" />
            </td>
            <td class="p-4 align-middle !border !border-gray-200"><?php echo strtoupper(implode(', ', $variation['attributes'])); ?></td>
            <td class="p-4 align-middle !border !border-gray-200">
                <div class="flex flex-col items-center">
                    <span class="font-medium text-black"><?php echo wc_price($variation['display_price']); ?></span>
                </div>
            </td>
            <td class="p-4 align-middle !border !border-gray-200">
                <div class="flex justify-center">
                    <input type="number" min="1" value="1" class="w-20 p-2 border rounded text-center">
                </div>
            </td>
            <td class="p-4 align-middle !border !border-gray-200">
                <button class="add-to-cart bg-[#232323] text-white px-4 py-2.5 text-sm hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 w-full font-bold" 
                        data-id="<?php echo $variation['variation_id']; ?>">
                    ADD TO CART
                </button>
            </td>
        </tr>
        <?php
    }
}
?>
