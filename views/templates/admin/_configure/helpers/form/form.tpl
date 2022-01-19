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