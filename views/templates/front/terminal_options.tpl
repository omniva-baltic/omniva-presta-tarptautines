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
{foreach $terminals as $terminal}
    <optgroup label = "{$terminal['city']}">
        <option value="{$terminal['id']}"  class="omnivaOption">{$terminal['name']}</option>;
    </optgroup>
{/foreach}