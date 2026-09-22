<a href="{$oncoCommercialGaran.portalUrl}"
   class="onco-commercial-garan--nested"
   data-onco-commercial-garan="true"
   data-label-src="{if $garanLabelUrl}{$garanLabelUrl}{else}{url module='frontend' controller='OncoCommercialGaran' action='label' number=$garanNumber}{/if}"
   data-modal-title="{s name="ModalTitle" namespace="frontend/plugins/onco_commercial_garan"}Herstellergarantie (EU GARAN){/s}"
   data-portal-label="europa.eu/youreurope/commercial-guarantee-durability"
   data-portal-intro="{s name="PortalLinkIntro" namespace="frontend/plugins/onco_commercial_garan"}Weitere Informationen:{/s}"
   target="_blank"
   rel="nofollow noopener"
   aria-label="{$garanYears} {s name="NestedAria" namespace="frontend/plugins/onco_commercial_garan"}Jahre Herstellergarantie – Label anzeigen{/s}">
    {include file="plugin/onco_commercial_garan/nested_svg.tpl" garanYears=$garanYears}
</a>
