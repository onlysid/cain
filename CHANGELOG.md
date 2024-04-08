# Change Log

All notable changes to Cain will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

&nbsp;

## [v3.0.0] - 15/03/2024

Init!

### What's happened?

#### 08/04/2024

Firefox Fun, API adjustments and more settings.

-   Completed Send API.
-   Shifted some menu item positions.
-   Added curve generation logic for CSV creation.
-   Fixed older versions of Firefox compatibility issues with a variety of styles.
-   GitIgnore curves.
-   Assay Modules now has some AJAX-powered settings.
-   Sessions no longer destroy and redirect to logout as this tampers with API usage.

#### 05/04/2024

API Progress + AJAX Abstractions

-   Completed Instrument API.
-   Instrument checks in the settings page are now using AJAX (not yet completed)

#### 04/04/2024

API Progress

-   Completed Lookup API.

#### 04/04/2024

Settings overhaul

-   More DB updates to reflect changes to the settings table.
-   Removed info table in favour of versions.
-   Settings area is now built.
-   General settings now works.
-   Field selection logic implemented using bitmaps and a custom class.
-   Login now automatically presumes account creator wants to login to the account which has just been created!

#### 01/04/2024

Login and Session management

-   DB Updated to reflect changes in users table.
-   Login is multi-step, looking first for an operator then registering one where necessary.
-   Passwords hashed and salted.
-   Settings are now separated (area needs building).
-   Process logic now separated fully.
-   Form logic and styling added.

#### 25/03/2024

DB-Update logic improvement

-   DB Update is now stateful. State is held in the db and users trying to invoke the message will get the same status message no matter where they are in the process.

#### 22/03/2024

API, Session, Database abstracted. DB-Update logic finished.

-   Further updates to API logic and database structure.
-   403 Accounted for.
-   TODO: Could make better exception logic for scripts in pageRouter.
-   Added session logic.
-   Separated API functionality.
-   Added database connectivity.
-   Added option to view pages in a logged out state.
-   Started user authentication.

#### 20/03/2024

Page structure, API logic and some more UI adjustments.

-   Mobile menu completed.
-   Login screen and general UI completed.
-   Created page routing and API routing.
-   API general logic solidified.

#### 18/03/2024

General design structured.

-   Buttons, fonts and colour scheme implemented.
-   Menu and general screen layout created.
-   Mobile menu underway.

#### 15/03/2024

File structure created.

-   We have a project! Apache configs and file hierarchy has been created.
-   Tailwind has been integrated.
-   Some basic pages have been added.
