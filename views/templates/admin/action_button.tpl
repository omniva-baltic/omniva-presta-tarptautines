<span class="btn-group-action">
    <span class="btn-group">
        <a      class="btn btn-default{if isset($data_button.class) && $data_button.class} {$data_button.class}{/if}"
                {if isset($data_button.href) && $data_button.href}
                    href="{$data_button.href}"
                {/if}
        >
            <i class="{$data_button.icon}"></i> {$data_button.title}
        </a>
    </span>
</span>