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
<script>
    var omniva_current_country = '{$omniva_current_country}';
    var terminals_radius = '{$terminals_radius}';
    var omniva_postcode = '{$omniva_postcode}';
    var omnivaint_terminal_reference = '{$omnivaint_terminal_reference}';
    var omniva_int_endpoint = '{$omniva_int_endpoint}';
    var omnivaTerminals = {$terminals_list|@json_encode nofilter}
    var show_omniva_map = {$omniva_map};
</script>
<div id="omnivalt_parcel_terminal_carrier_details" data-id-carrier="{$id_carrier}" style="display: none; margin-top: 10px;">
    <select class="" name="omnivalt_parcel_terminal" style = "width:100%;">{$parcel_terminals nofilter}</select>

    <style>
        {literal}
            #omnivalt_parcel_terminal_carrier_details{ margin-bottom: 5px }
        {/literal}
    </style>
{if $omniva_map != false } 
  <button type="button" id="show-omniva-map" class="btn btn-basic btn-sm omniva-btn" style = "display: none;">{l s='Show parcel terminals map' mod='omnivainternational'} <img src = "{$images_url}sasi.png" title = "{l s='Show parcel terminals map' mod='omnivainternational'}"/></button>
{/if}
</div>