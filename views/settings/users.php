<?php // General Settings

// Hospital Info Settings Subset
$userSettingsInfo = ['session_expiration', 'password_required'];
$userSettings = array_intersect_key($settings, array_flip($userSettingsInfo));
?>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>Update general user settings. To view all users, <a href="/users">Click Here</a></p>
</section>

<form action="/process" method="POST">
    <input type="hidden" name="action" value="user-general-settings">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    
    <div class="form-fields">
        <label for="userTimeout" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
            <div class="flex flex-col w-full">
                <div class="shrink">Time user out?</div>
                <div class="description !text-xs text-grey mr-4">Should a user ever be timed out?</div>
            </div>
            <div class="checkbox-wrapper">
                <input class="tgl" name="userTimeout" id="userTimeout" type="checkbox" <?= $userSettings['session_expiration'] != 0 ? "checked" : "";?>>
                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="userTimeout"><span></span></label>
            </div>
        </label>
    </div>
    <div class="form-fields">
        <label for="passwordRequired" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
            <div class="flex flex-col w-full">
                <div class="shrink">Require Password</div>
                <div class="description !text-xs text-grey mr-4">Do non-admins require a password?</div>
            </div>
            <div class="checkbox-wrapper">
                <input class="tgl" name="passwordRequired" id="passwordRequired" type="checkbox" <?= ($userSettings['password_required'] == 2 || $userSettings['password_required'] == 3) ? "checked" : "";?>>
                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="passwordRequired"><span></span></label>
            </div>
        </label>
        <label for="adminPasswordRequired" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
            <div class="flex flex-col w-full">
                <div class="shrink">Require Admin Password</div>
                <div class="description !text-xs text-grey mr-4">Do admins require a password?</div>
            </div>
            <div class="checkbox-wrapper">
                <input class="tgl" name="adminPasswordRequired" id="adminPasswordRequired" type="checkbox" <?= $userSettings['password_required'] == 1 || $userSettings['password_required'] == 3 ? "checked" : "";?>>
                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="adminPasswordRequired"><span></span></label>
            </div>
        </label>
    </div>
    <div id="userTimeoutAmount" class="form-fields <?= $userSettings['session_expiration'] > 0 ? "active": "";?>">
        <div class="field">
            <label for="sessionTimeout">User Timeout (in minutes)</label>
            <div class="input-wrapper">
                <input required id="sessionTimeout" type="number" min="0" name="sessionTimeout" value="<?= $userSettings['session_expiration'];?>">
            </div>
        </div>
    </div>
    <button class="btn smaller-btn trigger-loading" type="submit">Save Settings</button>
</form>