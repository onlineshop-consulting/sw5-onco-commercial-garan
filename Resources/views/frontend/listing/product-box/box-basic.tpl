{extends file="parent:frontend/listing/product-box/box-basic.tpl"}

{block name="frontend_listing_box_article_buy"}
    {$smarty.block.parent}

    {block name="onco_commercial_garan_listing_label"}
        {if {config name="displayListingBuyButton"} && $oncoCommercialGaran.map[$sArticle.ordernumber]}
            {$garanItem = $oncoCommercialGaran.map[$sArticle.ordernumber]}
            <div class="onco-commercial-garan--container is--listing">
                {include file="plugin/onco_commercial_garan/nested_label.tpl" garanYears=$garanItem.years garanNumber=$sArticle.ordernumber garanLabelUrl=$garanItem.labelUrl}
            </div>
        {/if}
    {/block}
{/block}
