{extends file="parent:frontend/detail/buy.tpl"}

{block name="frontend_detail_buy"}
    {$smarty.block.parent}

    {block name="onco_commercial_garan_detail_label"}
        {if $oncoCommercialGaran.map[$sArticle.ordernumber]}
            {$garanItem = $oncoCommercialGaran.map[$sArticle.ordernumber]}
            <div class="onco-commercial-garan--container is--detail">
                {include file="plugin/onco_commercial_garan/nested_label.tpl" garanYears=$garanItem.years garanNumber=$sArticle.ordernumber garanLabelUrl=$garanItem.labelUrl}
            </div>
        {/if}
    {/block}
{/block}
