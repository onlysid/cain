<?php // Check if already logged in
if(Session::isLoggedIn()) {
    header("Location: /");
}

// Have we already tried entering the operatorId?
$passwordRequired = Session::get('password-required');
$operatorId = Session::get('provisional-operator');
$operatorFirstName = Session::get('provisional-operator-fname');
$operatorLastName = Session::get('provisional-operator-lname');
$accountCreation = Session::get('account-create');
$instruction = "Please enter your operator ID";
$title = "Login";
$formType = "login";
$buttonText = "Log in";

if($passwordRequired) {
    if($accountCreation) {
        $instruction = "Please create a password and fill out the following information to use Cain.";
        $title = "Create Account";
        $formType = "create-account";
        $buttonText = "Create Account";
    } else {
        $instruction = "Please enter your operator ID";
        $title = "Hello, $operatorFirstName";
    }
}
// The login form ?>

<div class="flex flex-col justify-center items-center w-full grow">
    <h1 class="text-center"><?= $title;?></h1>
    <div class="divider !-mt-2"></div>
    <form action="process" method="POST" id="loginForm" class="flex flex-col items-center gap-3 w-full">
        <p id="loginInstructions" class="text-center"><?= $instruction;?></p>
        <input required type="hidden" name="action" value="<?= $formType;?>">
        <input required type="hidden" name="return-path" value="/">
        <div class="form-fields <?= $operatorId ? "!hidden" : "";?>">
            <div class="input-wrapper">
                <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 64C150 64 64 150 64 256s86 192 192 192c17.7 0 32 14.3 32 32s-14.3 32-32 32C114.6 512 0 397.4 0 256S114.6 0 256 0S512 114.6 512 256v32c0 53-43 96-96 96c-29.3 0-55.6-13.2-73.2-33.9C320 371.1 289.5 384 256 384c-70.7 0-128-57.3-128-128s57.3-128 128-128c27.9 0 53.7 8.9 74.7 24.1c5.7-5 13.1-8.1 21.3-8.1c17.7 0 32 14.3 32 32v80 32c0 17.7 14.3 32 32 32s32-14.3 32-32V256c0-106-86-192-192-192zm64 192a64 64 0 1 0 -128 0 64 64 0 1 0 128 0z"/>
                </svg>
                <input required type="text" name="operatorId" placeholder="Operator ID" value="<?= $operatorId;?>" autocapitalize="none" autocomplete="username" maxlength="150">
            </div>
            <?php if($form->getError('operatorId')) : ?>
                <p class="form-error"><?= $form->getError('operatorId');?></p>
            <?php endif;?>
        </div>
        <?php if($passwordRequired) : ?>
            <div class="form-fields">
                <div class="input-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/>
                    </svg>
                    <input autofocus required type="password" name="password" placeholder="Password">
                </div>
                <?php if($form->getError('password')) : ?>
                    <p class="form-error"><?= $form->getError('password');?></p>
                <?php endif;
                if($accountCreation) : ?>
                    <div class="input-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/>
                        </svg>
                        <input type="password" required name="password2" placeholder="Repeat Password">
                    </div>
                    <?php if($form->getError('password2')) : ?>
                        <p class="form-error"><?= $form->getError('password2');?></p>
                    <?php endif;?>
                </div>
                <div class="form-fields">
                    <div class="input-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M362.7 19.3L314.3 67.7 444.3 197.7l48.4-48.4c25-25 25-65.5 0-90.5L453.3 19.3c-25-25-65.5-25-90.5 0zm-71 71L58.6 323.5c-10.4 10.4-18 23.3-22.2 37.4L1 481.2C-1.5 489.7 .8 498.8 7 505s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L421.7 220.3 291.7 90.3z"/>
                        </svg>
                        <input type="text" required name="firstName" placeholder="First Name" value="<?= $operatorFirstName;?>">
                    </div>
                    <?php if($form->getError('firstName')) : ?>
                        <p class="form-error"><?= $form->getError('firstName');?></p>
                    <?php endif;?>
                    <div class="input-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M362.7 19.3L314.3 67.7 444.3 197.7l48.4-48.4c25-25 25-65.5 0-90.5L453.3 19.3c-25-25-65.5-25-90.5 0zm-71 71L58.6 323.5c-10.4 10.4-18 23.3-22.2 37.4L1 481.2C-1.5 489.7 .8 498.8 7 505s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L421.7 220.3 291.7 90.3z"/>
                        </svg>
                        <input type="text" name="lastName" placeholder="Last Name (Optional)" value="<?= $operatorLastName;?>">
                    </div>
                <?php endif;?>
            </div>
        <?php endif;?>
        <button type="submit" class="btn alt mb-4 trigger-loading"><?= $buttonText;?></button>
    </form>
    <?php if($passwordRequired) : ?>
        <form action="process" method="POST" id="backToLogin">
            <input required type="hidden" name="action" value="back-to-login">
            <p>Not <?= ucfirst($operatorFirstName ?? $operatorId);?>? <button type="submit" class="a-tag">Go back</button></p>
        </form>
    <?php endif;?>
</div>