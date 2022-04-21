{**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Mijora
 *  @copyright 2013-2022 Mijora
 *  @license   license.txt
 *}
<div class="omniva_shipment_form">
    <div class="panel">
        <div class="panel-heading">
            {l s='Omniva Parcels' mod='omnivainternational'}
        </div>
        <div class="panel-heading additional">
            {l s='Omniva Tracking numbers' mod='omnivainternational'}
        </div>

        <div class="panel-body">
            {if isset($shipment_id) && !$shipment_id}
                {l s='Shipment is not yet created. Please check information in Omniva International Shipment panel and register the shipment.' mod='omnivainternational'}
            {elseif isset($shipment_id) && $shipment_id && empty($tracking_numbers)}
                {l s="Please note that due to a technical issue during shipment registration, address card will appears in system later. If you haven't received the address card within 1 working day, please contact us by email at" mod='omnivainternational'} <b>{l s='tarptautines@omniva.lt'}</b>.                
            {elseif isset($shipment_id) && $shipment_id && !empty($tracking_numbers)}
                {assign var='tracking_numbers_count' value=count($tracking_numbers)}
                <p>{l s='Order has %d parcels with following tracking numbers:' mod='omnivainternational' sprintf=[$tracking_numbers_count]}</p>
                {foreach from=$tracking_numbers item=tracking_number key=key}
                    <li>{$tracking_number}</li>
                {/foreach}
                <div id='print-label-heading' class="panel-heading additional">
                    {l s='Shipment Actions' mod='omnivainternational'}
                </div>
            {/if}
        </div>
        {if isset($shipment_id) && $shipment_id && !empty($tracking_numbers)}
            <div class="panel-footer">
                <a href="{$omniva_admin_order_link}" target="_blank" id="print-labels" class="btn btn-default btn btn-primary">{l s='Print labels' mod='omnivainternational'}</a>
                <a href="{$omniva_admin_order_link}&downloadLabels=1" target="_blank" class="btn btn-default btn btn-success">{l s='Download labels' mod='omnivainternational'}</a>
                {if isset($orderHasManifest) && !$orderHasManifest}
                    <button type='button' id='cancel-order' class="btn btn-default btn btn-danger">{l s='Cancel Order' mod='omnivainternational'}</button>
                {/if}
            </div>
        {/if}
    </div>
</div>