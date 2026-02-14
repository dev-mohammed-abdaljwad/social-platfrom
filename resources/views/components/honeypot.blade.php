{{-- Honeypot fields — invisible to real users, bots will fill them --}}
<div style="position: absolute; left: -9999px; top: -9999px; opacity: 0; height: 0; width: 0; overflow: hidden;"
    aria-hidden="true" tabindex="-1">
    <label for="hp_name">Leave this empty</label>
    <input type="text" name="hp_name" id="hp_name" value="" autocomplete="off" tabindex="-1">
</div>
<input type="hidden" name="hp_time" value="{{ base64_encode(time()) }}">