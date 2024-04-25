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
        $instruction = "Please enter your password";
        $title = "Hello, $operatorFirstName";
    }
}
// The login form ?>

<!-- <script>
    window.addEventListener('load', function startCamera() {
        navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            const videoElement = document.getElementById('camera-feed');
            videoElement.srcObject = stream;
        })
        .catch(error => {
            console.error('Error accessing camera:', error);
        });
    });
</script>

<style>
      video {
        width: 100%;
        height: auto;
      }
      canvas {
        display: none;
      }
</style> -->
<!-- <video id="camera-feed" autoplay></video> -->

<div id="login" class="flex flex-col justify-center items-center w-full grow">
    <h1 class="text-center"><?= $title;?></h1>
    <div class="divider !-mt-2"></div>
    <form action="process" method="POST" id="loginForm" class="flex flex-col items-center gap-3 w-full">
        <?php if($form->getErrors()) : ?>
            <ul class="form-errors">
                <?php foreach($form->getErrors() as $errorKey => $error) : ?>
                    <li><?= $error;?></li>
                <?php endforeach;?>
            </ul>
        <?php endif;?>
        <p id="loginInstructions" class="text-center"><?= $instruction;?></p>
        <input required type="hidden" name="action" value="<?= $formType;?>">
        <input required type="hidden" name="return-path" value="/">
        <div class="form-fields <?= $operatorId ? "!hidden" : "";?>">
            <div class="input-wrapper <?= $form->getError('operatorId') ? "error" : "";?>">
                <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                    <path d="M0 96l576 0c0-35.3-28.7-64-64-64H64C28.7 32 0 60.7 0 96zm0 32V416c0 35.3 28.7 64 64 64H512c35.3 0 64-28.7 64-64V128H0zM64 405.3c0-29.5 23.9-53.3 53.3-53.3H234.7c29.5 0 53.3 23.9 53.3 53.3c0 5.9-4.8 10.7-10.7 10.7H74.7c-5.9 0-10.7-4.8-10.7-10.7zM176 192a64 64 0 1 1 0 128 64 64 0 1 1 0-128zm176 16c0-8.8 7.2-16 16-16H496c8.8 0 16 7.2 16 16s-7.2 16-16 16H368c-8.8 0-16-7.2-16-16zm0 64c0-8.8 7.2-16 16-16H496c8.8 0 16 7.2 16 16s-7.2 16-16 16H368c-8.8 0-16-7.2-16-16zm0 64c0-8.8 7.2-16 16-16H496c8.8 0 16 7.2 16 16s-7.2 16-16 16H368c-8.8 0-16-7.2-16-16z"/>
                </svg>
                <input required spellcheck="false" type="text" name="operatorId" placeholder="Operator ID" value="<?= $operatorId;?>" autocapitalize="none" autocomplete="username" maxlength="150">
            </div>
        </div>
        <?php if($passwordRequired) : ?>
            <div class="form-fields">
                <div class="input-wrapper <?= $form->getError('password') ? "error" : "";?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/>
                    </svg>
                    <input autofocus spellcheck="false" required type="password" name="password" placeholder="Password">
                </div>
                <?php if($accountCreation) : ?>
                    <div class="input-wrapper <?= $form->getError('password2') ? "error" : "";?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/>
                        </svg>
                        <input type="password" spellcheck="false" required name="password2" placeholder="Repeat Password">
                    </div>
                </div>
                <div class="form-fields">
                    <div class="input-wrapper <?= $form->getError('firstName') ? "error" : "";?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M362.7 19.3L314.3 67.7 444.3 197.7l48.4-48.4c25-25 25-65.5 0-90.5L453.3 19.3c-25-25-65.5-25-90.5 0zm-71 71L58.6 323.5c-10.4 10.4-18 23.3-22.2 37.4L1 481.2C-1.5 489.7 .8 498.8 7 505s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L421.7 220.3 291.7 90.3z"/>
                        </svg>
                        <input type="text" required spellcheck="false" name="firstName" placeholder="First Name" value="<?= $operatorFirstName;?>">
                    </div>
                    <div class="input-wrapper <?= $form->getError('lastName') ? "error" : "";?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M362.7 19.3L314.3 67.7 444.3 197.7l48.4-48.4c25-25 25-65.5 0-90.5L453.3 19.3c-25-25-65.5-25-90.5 0zm-71 71L58.6 323.5c-10.4 10.4-18 23.3-22.2 37.4L1 481.2C-1.5 489.7 .8 498.8 7 505s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L421.7 220.3 291.7 90.3z"/>
                        </svg>
                        <input type="text" spellcheck="false" name="lastName" placeholder="Last Name (Optional)" value="<?= $operatorLastName;?>">
                    </div>
                <?php endif;?>
            </div>
        <?php endif;?>
        <button type="submit" class="btn alt trigger-loading"><?= $buttonText;?></button>
    </form>
    <?php if($passwordRequired) : ?>
        <form action="process" method="POST" id="backToLogin" class="mt-3">
            <input required type="hidden" name="action" value="back-to-login">
            <p>Not <?= ucfirst($operatorFirstName ?? $operatorId);?>? <button type="submit" class="a-tag">Go back</button></p>
        </form>
    <?php endif;?>
</div>