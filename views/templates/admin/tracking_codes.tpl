<div class="omniva_shipment_form">
    <div class="panel">
        <div class="panel-heading">
            {l s='Omniva Parcels' mod='omnivainternational'}
        </div>
        <div class="panel-heading additional">
            {l s='Omniva Tracking numbers' mod='omnivainternational'}
        </div>
        <div class="panel-body">
            {assign var='tracking_numbers_count' value=count($tracking_numbers)}
            <p>{l s='Order has %d parcels with following tracking numbers:' mod='omnivainternational' sprintf=[$tracking_numbers_count]}</p>
            {foreach from=$tracking_numbers item=tracking_number key=key}
                <li>{$tracking_number}</li>
            {/foreach}
        </div>
        <div id='print-label-heading' class="panel-heading additional">
            {l s='Print shiping label' mod='omnivainternational'}
        </div>
        <div class="panel-footer">
			<a href="{$omniva_admin_order_link}" target="_blank" id="print-labels" class="btn btn-default btn btn-primary">{l s='Print labels' mod='omnivainternational'}</a>
            <a href="{$omniva_admin_order_link}&downloadLabels=1" target="_blank" class="btn btn-default btn btn-success">{l s='Download labels' mod='omnivainternational'}</a>
		</div>
    </div>
</div>