{extends file="parent:frontend/checkout/confirm_item_product.tpl"}

{block name='frontend_checkout_cart_item_details_essential_features'}
    {$smarty.block.parent}

    {block name="onco_commercial_garan_confirm_item_label"}
        {if $oncoCommercialGaran.map[$sBasketItem.ordernumber]}
            {$garanItem = $oncoCommercialGaran.map[$sBasketItem.ordernumber]}
            <div class="onco-commercial-garan--container is--confirm">
                {include file="plugin/onco_commercial_garan/nested_label.tpl" garanYears=$garanItem.years garanNumber=$sBasketItem.ordernumber garanLabelUrl=$garanItem.labelUrl}
            </div>
        {/if}
    {/block}
{/block}
