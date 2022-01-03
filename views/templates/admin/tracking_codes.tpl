<div class="omniva_shipment_form">
    <div class="panel">
        <div class="panel-heading">
            {l s='Omniva Parcels' mod='omnivainternational'}
        </div>
        <div class="panel-heading additional">
            {l s='Omniva Tracking numbers' mod='omnivainternational'}
        </div>
        <div class="panel-body">
            {foreach from=$tracking_numbers item=tracking_number key=key}
                <li>{$tracking_number}</li>
            {/foreach}
        </div>
        <div id='print-label-heading' class="panel-heading additional">
            {l s='Print shiping label' mod='omnivainternational'}
        </div>
        <div class="panel-footer">
			<button type="button" id="print-labels" class="btn btn-default btn btn-primary">{l s='Print labels' mod='omnivainternational'}</button>
		</div>
    </div>
</div>