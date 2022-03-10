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
<a href="{$href|escape:'html':'UTF-8'}" {if isset($blank) && $blank}target="_blank"{/if} title="{$action|escape:'html':'UTF-8'}" class="edit btn btn-default">
	<i class="icon-{$icon}"></i> {$action|escape:'html':'UTF-8'}
</a>
