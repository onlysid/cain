# Change Log

All notable changes to the SAMBA III DMS will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

&nbsp;

## [v3.3.3] - 02/07/2025

Lookup/Operator bug fixes.

### Fixed
-   When an operator is found in LIMS, DMS now correctly confirms regardless of casing of response.
-   Network tab now accounts for non-eth0-named primary ethernet services returned from ifconfig.

&nbsp;

## [v3.3.2] - 25/06/2025

Instrument bug fixes.

### Fixed
-   Active instruments always show up in the active instruments tab.
-   Active instruments continue to show up in the "show hidden instruments" tab.
-   Instrument refresh does not add additional unnecessary modals.

&nbsp;

## [v3.3.1] - 24/06/2025

LIMS simulator permissions.

### Changed
-   Only a service engineer can adjust the LIMS simulator.

&nbsp;

## [v3.3.0] - 18/06/2025

LIMS updates and UI adjustments.

### Added
-   /settings endpoint and functionality to retreive a specific setting's value.
-   DMS Ethernet IP addr to the network settings.
-   LIMS middleware app version to the versions page.
-   LIMS simulator toggle to automatically adjust network settings for LIMS simulation.
-   LIMS simulator tab in settings area which allows admins to view logs for LIMS simulation.
-   Ability to clear LIMS simulator logs.
-   LIMS simulator database and methods to create, read and delete said data.
-   Example htaccess file in case it needs to be rebuilt.

### Changed
-   Assay modules now use the module's friendly name where possible.

&nbsp;

## [v3.2.2] - 14/05/2025

UI changes related to listing db items and list of known issues.

### Changed

-   AM Page doesn't show DB UID.
-   Instrument timeout on DMS reduced from 2hrs to 5mins.

### Added

-   Method to toggle instrument visibility on instrument timeout.

### Known Issues

-   AM will provide a friendly name so this requires an API specification upgrade.
-   Lot QC may need tweaking to account for results which are "Some Positive".
-   May need to revert 3.2.1 change where DMS no longer parses "Some Positive" for combined multiplex tests.

&nbsp;

## [v3.2.1] - 12/04/2025

Adjustments to logging verbosity and some fixes

### Changed

-   Results are now unopinionated and unmanipulated by the DMS for API version 3.0 and above. What the DMS is sent is law.
-   DRW about information updated.
-   Assay modules now only get referenced by their serial number.
-   Removed dummy code related to instrument/tablet versions.
-   Changed settings menu icons.

### Added

-   Logging is now more verbose optionally.

### Fixed

-   Lots QC automatically checks the master results table.
-   DataField API now uses correct tablet fields.
-   Instrument unlocking now shows up correctly.
-   Better mobile responsiveness on assay module page

&nbsp;

## [v3.2.0] - 19/03/2025

Changing the way results work slightly and some QOL updates. Updating according to API spec 3.0.

### Changed

-   Accent colour changed to Fuchsia.
-   Font size increased globally by 12.5%.
-   Send API now accepts API version 3.0 formats.
-   Master results now form the source of truth for all results. The standard results that LIMS relies on have foreign key references to their master results.
-   You can now copy and paste error messages on the update screen and, for particularly long messages, the whole screen is now scrollable.
-   Setup SQL script now only takes users as far as v0.0.0. The db update script does the rest.
-   Lots QC results now reference their master ID rather than individual result IDs. This is so that one test yields one QC test even if it has multiple targets.
-   Sent to LIMS status now works according to the constituent results, not to the result itself. Considerations made for if "part" of the result has been sent to LIMS.
-   Backup/Delete now includes all CT values and master result parsing.
-   Allow more time and memory for upgrades (as this is a large one).
-   .htaccess is now case insensitive in its search for phpmyadmin.
-   For longer database updates, the browser now refreshes to counteract the browser timeout.

### Added

-   Master results table
-   CT values are now stored and displayed for each result.
-   Generic error page thrown when anything in the higher level application goes wrong.
-   More robust upgrade logic so that any failures in the upgrades are well logged for debugging.
-   Send API examples in the codebase for each supported API version.
-   Robust logic for backwards compatibility.
-   Exceptions now get logged, never displayed.

### Fixed

-   Lots QC results now have better parsing logic to accompany the newer API result logic.

&nbsp;

## [v3.1.8] - 13/12/2024

Fixed lot QC not altering on certain PhP versions.

### Fixed

-   When changing Lot QC result on PhP 7.x, the result would not display correctly on the results screen.

&nbsp;

## [v3.1.7] - 03/12/2024

Empty result handling bug fix and lot information parsing.

### Added

-   Lot information is now provided by the tablet in the /send API.
-   Production year added to lots.

### Changed

-   Expiration date is now just a month picker.
-   Date/month pickers now close on selection. Datetime pickers still require closing.
-   Lots are now considered "expired" after the end of the expiration month.
-   Removed unnecessary console logging.

### Fixed

-   When receiving an empty result, display it as invalid.
-   When submitting lot changes, lots no longer switch to unverified.

&nbsp;

## [v3.1.6] - 02/12/2024

HL7 settings/LIMS bug fixes and other adjustments.

### Changed

-   Can no longer edit the type of QC test when editing a specific QC test.
-   Disabled right click app-wide.
-   Remove links from about page.
-   Page name changes.
-   Moved Positive Patient ID to Cain protocol from HL7.
-   Changed Cain protocol name to Proprietary.
-   Removed quick links from individual assay module page.
-   Allow selection of unverified in manual QC mode.
-   When switching to automatic QC, make sure to run automatic QC check on all unverified lots.
-   Added positive patient ID to both LIMS protocols.
-   When switching to automatic QC or changing the number of tests required on auto QC, a popup warns users that this will affect past results.
-   Added references to lots.
-   Added option and setting to select default field name on dashboard for patient identification.
-   Cleaned up individual assay module page status and removed references to empty data.

### Fixed

-   Updating HL7 Port now works.
-   Assay modules page now updates every 5s, not 50.
-   LIMS status now correctly shows on the footer.
-   Changed default service engineer password.
-   Fixed timed out user seeing debug in code on individual assay module pages.
-   Fixed logout not working from footer of certain settings pages.
-   Automatic DB Update no longer fully recreates the settings table. It instead recreates it if necessary but updates it where possible.

&nbsp;

## [v3.1.5] - 26/11/2024

Menu and UI ease of use adjustments. Small QC Policy fixes.

### Changed

-   List of users now accessible via the settings menu.
-   Added footer to the settings screen.
-   Removed "Logout" option from the settings menu.
-   Removed QC Policy page.
-   Added QC Policy information to the Lots page.
-   Added showFooter flag for general pages.
-   Changed the name of QC Settings to Lot QC Policy
-   Adjusted the way Automatic QC checking works (now it reverts to unverified and overrides a failure if new data is presented in the autoQCCheck)
-   Added QC Details to the modal result box.

### Fixed

-   Changing QC Policy doesn't change QC Policy on Lots tab.
-   Adjusting QC Pass on Lots does not work.
-   Similarly for the QC Results

&nbsp;

## [v3.1.4] - 22/11/2024

Added patient ID to the results table and minified CSS.

&nbsp;

## [v3.1.3] - 19/11/2024

DB update multi-window support and default instrument QC test types

### Added

-   Default instrument QC test types.

### Fixed

-   When loading the site in another window during update, errors prevented the update screen from rendering.

&nbsp;

## [v3.1.2] - 19/11/2024

Added legacy database considerations and debug info.

### Added

-   Added PhP and MariaDB version to the versions page

### Fixed

-   db-update now adds default values to fields previously provided by the tablet but are no longer, despite being required by LIMS middleware.

&nbsp;

## [v3.1.1] - 14/11/2024

Added logging, cleaned up db-update and some bug fixing.

### Added

-   Logs for login/authentication.
-   Logs for QC testing.
-   Logs for object deletion.
-   Logs for errors.
-   Redacted details for service engineers.
-   Bundled in PhPMyAdmin for ease of installation.
-   Setup script created.

### Changed

-   db-update now checks for useless files and removes them.

### Fixed

-   db-update is now more careful about collation and foreign key issues.
-   If we do not have write permissions for some reason, we now make sure log files are stored elsewhere.
-   Sessions sometimes aren't accessible for similar permission-based reasons.

&nbsp;

## [v3.1.0] - 12/11/2024

Large changes to results, QC tests, logging and general structure of application.

### Added

-   Lots page
-   Lots QC page
-   Assay module individual pages
-   Assay module QC type creation
-   Logging system framework (actual logging yet to come)

### Changed

-   Results can now be invalid based on controls
-   Results API now accepts JSON format
-   Results now support more complex multiplex tests
-   Results now accept QC tests
-   Instruments now have individual pages
-   Instruments can now be locked
-   Instruments now have loggable QC tests
-   Instrument QC tests can now be added from the settings page
-   Lots can now be viewed based on QC status
-   Lots can now expire
-   Lots can have their expiration and delivery dates altered
-   Lots QC results can now be viewed and verified IF the QC policy allows for this
-   API has been updated to support Revision 6

### Fixed

-   APIs which depend on LIMS no longer take time to process if the system knows LIMS is unavailable.

&nbsp;

## [v3.0.1] - 12/09/2024

Bug fixes, tests and API changes

### Changed

-   APIs are now case insensitive for backwards compatibility.
-   /operator has been tested

&nbsp;

## [v3.0.0] - 15/03/2024

Init!

### What's happened?

#### 30/04/2024

Added backup/delete and implemented filtering functionality

-   You can now export and delete results (which also deletes the CSV files)
-   Filtering now works. You can filter on search, positive results, sex, age, sent to LIMS status and a date range.
-   Permissions are now strict and applied sitewide.

#### 26/04/2024

Added filtering modal and fixed some styling.

-   Calendar input now centers as there are overflow issues with having the calendar exist below the field.
-   Filtering modal now exists.
-   Added age demographic information.
-   LIMS status now shows in the result modal and printout.

#### 25/04/2024

Datepicker, backup/export page and delete/print

-   Added printing results.
-   Started filter functionality.
-   Created area for backing up/exporting.
-   Included a datepicker library for backup functionality.
-   Hotfixes for quotation-filled passwords (spell checking now disabled on form fields).

#### 24/04/2024

Lots, notices and and form errors sorted.

-   Lots now display on the /lots page.
-   Operator lookup now fails instantly if LIMS is not switched on.
-   Sent to LIMS now works.
-   Form fields now provide ample feedback with some UI changes.
-   Notices now pop up.

#### 23/04/2024

Graphs implemented

-   Results now load graphs if there is a valid CSV file.
-   Sorting now resets pagination.
-   Adjusted the API for retrieving data for the curves for results.

#### 22/04/2024

Graph library added, small styling changes and bug fixes.

-   Results now correctly load when you click on them.
-   ChartJS integrated.
-   Added parsed results to the result modals.
-   Added example API JSON files.

#### 19/04/2024

User control completion, field selection visibility implementation and some bug fixes.

-   Users can now be added, deleted, edited and deactivated.
-   Field visibility now fully impacts the app.
-   QC policy rebranded to make it clear that it is not editable.
-   Implemented config API.
-   Account settings now work.
-   Some minor DB changes.

#### 18/04/2024

Assay/Operator API adjustments

-   Changed Assay Modules to sort by time remaining and by last connected and to remove all items not associated with a tablet.
-   Changed API for instruments to be sent in parallel.
-   Added unfinished API to handle config settings communication between tablet and DMS.
-   Adjusted Operator API to give more feedback to the tablet and to prompt authentication wherever possible.
-   Operator API now passes back all operator info to the tablet.

#### 16/04/2024

Shaking things about

-   Moving some menu items around
-   Assay modules are now more intricate
-   Config now shows 10000 limit
-   QC policy now displayed on the main menu
-   User logins now displayed on the main menu

#### 15/04/2024

Small adjustments

-   Settings adjustments (password required, session timeout)
-   Icon / UI adjustments (login form, logout touchable area)
-   About section completed
-   Warning section added (db/result count warnings)

#### 11/04/2024

Table progress

-   Table sorting and pagination now works.
-   Added flags = 100 for results by default.
-   Timestamps are now bigints.
-   Config file gets automatically generated from the db-config-sample.php file.
-   Dummy default text in many pages has been added.

#### 10/04/2024

Table progress

-   Tables rows now open and close.
-   Table filtering now works.

#### 09/04/2024

Settings... and main UI start

-   Most settings now complete.
-   Updated changelog styling!
-   Started making the main big table...

#### 08/04/2024

Firefox Fun, API adjustments and more settings.

-   Completed Send API.
-   Shifted some menu item positions.
-   Added curve generation logic for CSV creation.
-   Fixed older versions of Firefox compatibility issues with a variety of styles.
-   GitIgnore curves.
-   Assay Modules now has some AJAX-powered settings.
-   Sessions no longer destroy and redirect to logout as this tampers with API usage.
-   Added lookup dummy to process_data.
-   Added a few more general settings.
-   Adjusted lookup confirmation code.

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
