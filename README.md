# SAMBA III DMS

Data Management System (DMS) for medical tests, quality control and instruments.

## Getting started

This project requires an apache web server, an sql (mysql or maridb) database and an installation of PhP (7.2 or newer).

In the case of SAMBA, the DMS should already be set up and running, accessible on the internal network at http://samba.local.

## Updating the DMS

When pulling an update, the DMS will understand that there is a version conflict with the database and bring everything up to date. If this fails for some reason, there is an option to retry.

## Upgrading from HTTP to HTTPS

A sysadmin will need to run the script tls.sh bundled with this repository and follow the steps presented within the terminal. Future images of the DMS will have TLS enabled by default.

## Debugging/Reporting Issues

If something isn't working as it seems, please follow these steps:

1. Check the logs (found within the settings page when using an admin clinician account). If any logs are found, back them up and send the files to a service engineer for inspection.
2. Attempt a database repair (option found within the settings page under the versions tab when using an admin clinician account).
3. Contact DRW/Cain Medical for further support.

- If /login does not work but /index.php does (albeit with an error), you probably need to AllowOverride All in the vhost in sites-available and enable a2enmod. An apache restart will then fix things!

- If logging isn't working, it's likely due to permissions. Please make sure that the web application has sufficient permissions to update log txt files.