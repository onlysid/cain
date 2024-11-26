<?php // Users

$operators = getOperators($currentUser['id']);
$userTypes = ["Clinician" => 1, "Admin Clinician" => 2];

// Page setup?>

<!-- List all users -->
<table id="usersTable">
    <thead>
        <th><span class="hidden sm:inline-block mr-1">Operator</span>ID</th>
        <th>Name</th>
        <th>Type</th>
        <th></th>
    </thead>

    <tbody>
        <?php if(count($operators) > 0) :
            // List all the operators
            foreach($operators as $operator) : ?>
                <tr class="user" data-modal-open="user<?= $operator['id'];?>">
                    <td><?= $operator['operator_id'];?></td>
                    <td><?= $operator['first_name'] ? ucfirst($operator['first_name']) . " " . ucfirst($operator['last_name'] ?? "") : "Unknown";?></td>
                    <td><?= $operator['user_type'] == 1 ? "Clinician" : "Admin";?></td>
                    <td class="end">
                        <div class="table-controls">
                            <button class="details tooltip" title="View/Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z"/>
                                </svg>
                            </button>
                            <div class="tooltip" title="<?= $operator['status'] == 0 ? 'Inactive' : 'Active';?>">
                                <div class="status-indicator <?= $operator['status'] == 1 ? 'active' : '';?>"></div>
                            </div>
                            <button data-modal-open="delete<?= $operator['id'];?>" class="table-button tooltip delete-user-button" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php
            endforeach;
        // Otherwise, we have no operators other than ourselves.
        else : ?>
            <tr>
                <td colspan="4" class="text-center">No operators to show.</td>
            </tr>
        <?php endif;?>
    </tbody>
</table>

<!-- All the modals -->
<div class="modal-wrapper">
    <?php foreach($operators as $operator) :
        // Check for errors in this user's editing
        $hasErrors = $form->getValue("form") == "edit" && $form->getValue("id") == $operator['id'];?>

        <div id="user<?= $operator['id'];?>" class="generic-modal <?= $hasErrors ? "active" : "";?>">
            <div class="close-modal" data-modal-close>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                </svg>
            </div>

            <h2 class="mb-3"><?= $operator['operator_id'];?></h2>
            <form action="/process" method="POST">

                <?php if($hasErrors) : ?>
                    <ul class="form-errors">
                        <?php foreach($form->getErrors() as $errorKey => $error) : ?>
                            <li><?= $error;?></li>
                        <?php endforeach;?>
                    </ul>
                <?php endif;?>

                <input type="hidden" name="action" value="edit-operator">
                <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                <input type="hidden" name="id" class="form-operator-id" value="<?= $operator['id'];?>">

                <div class="form-fields">
                    <div class="field">
                        <label>First Name</label>
                        <div class="input-wrapper <?= ($hasErrors && $form->getError('password2')) ? "error" : "";?>">
                            <input spellcheck="false" type="text" name="firstName" value="<?= $operator['first_name'];?>" placeholder="eg. Jane">
                        </div>
                    </div>
                    <div class="field">
                        <label>Last Name</label>
                        <div class="input-wrapper <?= ($hasErrors && $form->getError('password2')) ? "error" : "";?>">
                            <input type="text" spellcheck="false" name="lastName" value="<?= $operator['last_name'];?>" placeholder="eg. Doe">
                        </div>
                    </div>
                </div>
                <div class="form-fields">
                    <div class="field">
                        <label>Change Password</label>
                        <div class="input-wrapper <?= ($hasErrors && $form->getError('password2')) ? "error" : "";?>">
                            <input type="password" spellcheck="false" name="password" placeholder="Enter a new password">
                        </div>
                    </div>
                    <div class="field">
                        <label>Repeat Password</label>
                        <div class="input-wrapper <?= ($hasErrors && $form->getError('password2')) ? "error" : "";?>">
                            <input type="password" spellcheck="false" name="password2" placeholder="Ensure matching passwords">
                        </div>
                    </div>
                </div>
                <div class="form-fields">
                    <div class="field">
                        <label>User Type</label>
                        <div class="input-wrapper select-wrapper">
                            <select name="userType">
                                <?php foreach($userTypes as $userType => $userTypeValue) : ?>
                                    <option <?= ($userTypeValue == $operator['user_type']) ? 'selected' : '';?> value="<?= $userTypeValue;?>"><?= $userType;?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="w-full flex justify-center items-center gap-3 mt-3">
                    <button type="submit" class="btn smaller-btn trigger-loading">Apply</button>
                    <div class="cursor-pointer btn smaller-btn close-user-modal close-modal no-styles" data-modal-close>Cancel</div>
                </div>
            </form>

            <div class="divider"></div>

            <div class="flex gap-2 justify-center">
                <form action="/process" method="POST" class="w-auto">
                    <input type="hidden" name="action" value="toggle-operator-status">
                    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                    <input type="hidden" name="id" class="form-operator-id" value="<?= $operator['id'];?>">

                    <button type="submit" class="btn smaller-btn trigger-loading <?= $operator['status'] == '1' ? 'deactivate' : 'activate';?>"> <?= $operator['status'] == '1' ? 'Deactivate' : 'Activate';?> User</button>
                </form>
                <button data-modal-open="delete<?= $operator['id'];?>" class="delete-user-button btn smaller-btn tooltip" title="Delete">
                    <svg class="h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                    </svg>
                </button>
            </div>

        </div>

        <div id="delete<?= $operator['id'];?>" class="user-modal generic-modal">
            <div class="close-user-modal close-modal" data-modal-close>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                </svg>
            </div>
            <svg class="fill-red-500 h-10 w-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
            </svg>
            <p class="text-center">Are you sure you want to delete <span id="operatorToDelete" class="font-black text-red-500"><?= $operator['operator_id'];?></span>?</p>
            <form action="/process" method="POST">
                <input type="hidden" name="action" value="delete-operator">
                <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                <input type="hidden" name="id" class="form-operator-id" value="<?= $operator['id'];?>">
                <div class="w-full flex justify-center items-center gap-3 mt-3">
                    <button type="submit" class="btn smaller-btn trigger-loading">Yes</button>
                    <div class="cursor-pointer btn smaller-btn close-user-modal close-modal no-styles" data-modal-close>Cancel</div>
                </div>
            </form>
        </div>

    <?php endforeach;?>

    <?php $addUserError = $form->getValue("form") == "add";?>
    <div id="newUserModal" class="user-modal generic-modal <?= $addUserError ? "active" : "";?>">
        <div class="close-user-modal close-modal" data-modal-close>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
            </svg>
        </div>
        <h2>Add a new user</h2>
        <form action="/process" method="POST">
            <?php if($addUserError) : ?>
                <ul class="form-errors">
                    <?php foreach($form->getErrors() as $errorKey => $error) : ?>
                        <li><?= $error;?></li>
                    <?php endforeach;?>
                </ul>
            <?php endif;?>
            <input type="hidden" name="action" value="add-operator">
            <input type="hidden" name="return-path" value="<?= $currentURL;?>">

            <div class="form-fields">
                <div class="field">
                    <label>Operator ID</label>
                    <div class="input-wrapper <?= $form->getError('operatorId') ? "error" : "";?>">
                        <input spellcheck="false" type="text" name="operatorId" value="<?= $form->getValue('operatorId');?>" placeholder="eg. 012345678">
                    </div>
                </div>
            </div>
            <div class="form-fields">
                <div class="field">
                    <label>First Name</label>
                    <div class="input-wrapper <?= $form->getError('firstName') ? "error" : "";?>">
                        <input spellcheck="false" type="text" name="firstName" value="<?= $form->getValue('firstName');?>" placeholder="eg. Jane">
                    </div>
                </div>
                <div class="field">
                    <label>Last Name</label>
                    <div class="input-wrapper <?= $form->getError('lastName') ? "error" : "";?>">
                        <input type="text" spellcheck="false" name="lastName" value="<?= $form->getValue('lastName');?>" placeholder="eg. Doe">
                    </div>
                </div>
            </div>
            <div class="form-fields">
                <div class="field">
                    <label>Password</label>
                    <div class="input-wrapper <?= $form->getError('password') ? "error" : "";?>">
                        <input type="password" spellcheck="false" name="password" placeholder="Enter a password">
                    </div>
                </div>
                <div class="field">
                    <label>Repeat Password</label>
                    <div class="input-wrapper <?= $form->getError('password2') ? "error" : "";?>">
                        <input type="password" spellcheck="false" name="password2" placeholder="Enter password again">
                    </div>
                </div>
            </div>
            <div class="form-fields">
                <div class="field">
                    <label>User Type</label>
                    <div class="input-wrapper <?= $form->getError('userType') ? "error" : "";?> select-wrapper">
                        <select name="userType">
                            <?php foreach($userTypes as $userType => $userTypeValue) : ?>
                                <option <?= $form->getValue('userType') == $userTypeValue ? "selected" : "";?> value="<?= $userTypeValue;?>"><?= $userType;?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="w-full flex justify-center items-center gap-3 mt-3">
                <button type="submit" class="btn smaller-btn trigger-loading">Create Operator</button>
                <div class="cursor-pointer btn smaller-btn close-user-modal close-modal no-styles" data-modal-close>Cancel</div>
            </div>
        </form>
    </div>
</div>


<div class="grow flex items-end">
    <div class="flex gap-3 flex-wrap">
        <button data-modal-open="newUserModal" class="w-full sm:w-auto btn border-btn new-user-button">Add New User</button>
        <a href="/settings/users" class=" w-full sm:w-auto btn border-btn">General User Settings</a>
        <a href="/settings" class=" w-full sm:w-auto btn border-btn">My Account Settings</a>
    </div>
</div>