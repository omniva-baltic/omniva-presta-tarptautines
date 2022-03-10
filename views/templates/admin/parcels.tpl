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
<form class="omniva_shipment_form" action="{$update_parcels_link}" method="post">
    <div class="panel" id="fieldset_{$f}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
        <div class="panel-heading additional">
            {l s="Parcels" mod='omnivainternational'}
        </div>
        {foreach from=$parcels item=parcel name='pracel_loop'}
            <div class="row parcel-row {if $smarty.foreach.pracel_loop.first}first{/if}">
                <div class="nested_fields nested_order_items">
                    <div class="row mt-3 mt-md-0">
                        <div class="col-md-6 col-12">
                            <div class="form-group integer optional order_items_x">
                                <input class="numeric integer required form-control" type="text" placeholder="{l s="Length" mod='omnivainternational'}"
                                       name="parcel[{$parcel.id}][x]"
                                       id="order_items_attributes_x_{$parcel.id}"
                                       value="{$parcel.length}">
                                <input class="numeric integer required form-control" type="text"
                                       placeholder="{l s="Width" mod='omnivainternational'}" name="parcel[{$parcel.id}][y]"
                                       id="order_items_attributes_y_{$parcel.id}"
                                       value="{$parcel.width}">
                                <input class="numeric integer required form-control" type="text"
                                       placeholder="{l s="Height" mod='omnivainternational'}"  name="parcel[{$parcel.id}][z]"
                                       id="order_items_attributes_z_{$parcel.id}"
                                       value="{$parcel.height}">
                                <span class="input-group-text input-group-addon-service-page">{l s="cm" mod='omnivainternational'}</span>
                            </div>
                        </div>
                        <div class="col-md-5 col-12 mt-3 mt-md-0">
                            <div class="row">
                                <div class="col-9 col-lg-8 ps-md-0">
                                    <div class="form-group decimal optional order_items_weight">
                                        <input
                                                class="numeric decimal required form-control form-control-service-page"
                                                type="text" min="0" placeholder="{l s="Weight" mod='omnivainternational'}" step="any"
                                                name="parcel[{$parcel.id}][weight]"
                                                id="order_items_attributes_weight_{$parcel.id}"
                                                value="{$parcel.weight}">
                                        <span class="input-group-text input-group-addon-service-page" id="basic-addon2">{l s="kg" mod='omnivainternational'}</span>
                                    </div>
                                </div>
                                <div class="col-3 col-lg-4 d-inline-flex justify-content-between more-fields my-auto">
                                    <a class="add_nested_fields_link" data-association-path="order_items" data-object-class="item" href="#">
                                        <img width="16px" src="{$images_url}/plus.svg">
                                    </a>
                                    <a class="remove_nested_fields_link" id="remove-icon" href="#">
                                        <img width="20px" src="{$images_url}/delete-shipment.svg">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
        <div class="panel-footer">
            <button type="submit" id="update-parcels" class="btn btn-default btn btn-primary" name="submitOptionsomniva_shipment">{l s="Update Parcels" mod='omnivainternational'}</button>
        </div>
    </div>
</form>
