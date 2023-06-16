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
    var omnivaInt_current_country = '{$omniva_current_country}';
    var omnivaInt_postcode = '{$omniva_postcode}';
    var omnivaInt_endpoint = '{$omniva_int_endpoint}';
</script>
<div class="omnivaInt_carrier" style="display:none;"
    data-reference="{$omnivaint_terminal_reference}"
    data-type="{if empty($omnivaint_terminal_type)}courier{else}terminal{/if}"
    data-terminal_type="{$omnivaint_terminal_type}"
    data-terminal_radius="{$terminals_radius}"
></div>