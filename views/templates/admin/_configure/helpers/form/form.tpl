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
{extends file=$admin_default_tpl_path|cat:"helpers/form/form.tpl"}
{block name="legend"}
    <div class="panel-heading">
        {if isset($field.image) && isset($field.title)}
            <img src="{$field.image} alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
        {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
        {$field.title}
    </div>
    <div class="panel-heading additional">
        {l s="Change additional services for Omniva International shipment" mod='omnivainternational'}
    </div>
{/block}