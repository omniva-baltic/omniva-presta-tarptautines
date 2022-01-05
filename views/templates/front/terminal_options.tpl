{foreach $terminals as $terminal}
    <optgroup label = "{$terminal['city']}">
        <option value="{$terminal['id']}"  class="omnivaOption">{$terminal['name']}</option>;
    </optgroup>
{/foreach}