<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>Change your login information and account details.</p>
</section>

<form action="/process" method="POST">
    <?php if($form->getErrors()) : ?>
        <ul class="form-errors">
            <?php foreach($form->getErrors() as $errorKey => $error) : ?>
                <li><?= $error;?></li>
            <?php endforeach;?>
        </ul>
    <?php endif;?>
    <input type="hidden" name="action" value="edit-operator">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    <input type="hidden" name="id" class="form-operator-id" value="<?= $currentUser['id'];?>">

    <div class="form-fields">
        <div class="field">
            <label>First Name</label>
            <div class="input-wrapper <?= $form->getError('firstName') ? "error" : "";?>">
                <input spellcheck="false" type="text" name="firstName" value="<?= $currentUser['first_name'];?>" placeholder="eg. Jane">
            </div>
        </div>
        <div class="field">
            <label>Last Name</label>
            <div class="input-wrapper <?= $form->getError('lastName') ? "error" : "";?>">
                <input spellcheck="false" type="text" name="lastName" value="<?= $currentUser['last_name'];?>" placeholder="eg. Doe">
            </div>
        </div>
    </div>
    <div class="form-fields">
        <div class="field">
            <label>Change Password</label>
            <div class="input-wrapper <?= $form->getError('password') ? "error" : "";?>">
                <input spellcheck="false" type="password" name="password" placeholder="Enter a new password">
            </div>
        </div>
        <div class="field">
            <label>Repeat Password</label>
            <div class="input-wrapper <?= $form->getError('password2') ? "error" : "";?>">
                <input spellcheck="false" type="password" name="password2" placeholder="Ensure matching passwords">
            </div>
        </div>
    </div>
    <div class="w-full flex justify-center items-center gap-3 mt-3">
        <button type="submit" class="btn smaller-btn">Save Settings</button>
    </div>
</form>