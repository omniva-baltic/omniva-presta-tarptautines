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