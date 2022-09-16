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
<script type="text/javascript">
    var omnivaIntdata = [];
    omnivaIntdata.text_select_terminal = '{l s='Select terminal' mod='omnivainternational'}';
    omnivaIntdata.text_search_placeholder = '{l s='Enter postcode' mod='omnivainternational'}';
    omnivaIntdata.not_found = '{l s='Place not found' mod='omnivainternational'}';
    omnivaIntdata.text_enter_address = '{l s='Enter postcode/address' mod='omnivainternational'}';
    omnivaIntdata.text_show_in_map = '{l s='Show in map' mod='omnivainternational'}';
    omnivaIntdata.text_show_more = '{l s='Show more' mod='omnivainternational'}';
    omnivaIntdata.omniva_plugin_url = '{$module_url}';
    omnivaIntdata.images_url = '{$images_url}';
    var omnivalt_parcel_terminal_error = '{l s='Please select parcel terminal' mod='omnivainternational'}';
</script>


    <script type="text/javascript">
      var select_terminal = "{l s='Pasirinkti terminalą'  mod='omnivainternational'}";
      var text_search_placeholder = "{l s='įveskite adresą' mod='omnivainternational'}";
    </script>

<script>
    var omnivaSearch = "{l s='Įveskite adresą paieškos laukelyje, norint surasti paštomatus'  mod='omnivainternational'}";
    {literal}
        var modal = document.getElementById('omnivaLtModal');
        window.document.onclick = function(event) {
            if (event.target == modal || event.target.id == 'omnivaLtModal' || event.target.id == 'terminalsModal') {
              document.getElementById('omnivaLtModal').style.display = "none";
            } 
        }
    {/literal}
</script>
<div id="omnivaLtModal" class="modal">
    <div class="omniva-modal-content">
            <div class="omniva-modal-header">
            <span class="close" id="terminalsModal">&times;</span>
            <h5 style="display: inline">{l s='Omniva parcel terminals' mod='omnivainternational'}</h5>
            </div>
            <div class="omniva-modal-body" style="/*overflow: hidden;*/">
                <div id = "omnivaMapContainer"></div>
                <div class="omniva-search-bar" >
                    <h4 style="margin-top: 0px;">{l s='Parcel terminals addresses' mod='omnivainternational'}</h4>
                    <div id="omniva-search">
                    <form>
                    <input type = "text" placeholder = "{l s='Enter postcode' mod='omnivainternational'}"/>
                    <button type = "submit" id="map-search-button"></button>
                    </form>                    
                    <div class="omniva-autocomplete scrollbar" style = "display:none;"><ul></ul></div>
                    </div>
                    <div class = "omniva-back-to-list" style = "display:none;">{l s='Back to list' mod='omnivainternational'}</div>
                    <div class="found_terminals scrollbar" id="style-8">
                      <ul>
                      
                      </ul>
                    </div>
                </div>
        </div>
    </div>
</div>
