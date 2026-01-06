# Nexus DMS

Data Management System (DMS) for medical tests, quality control and instruments (SAMBA III).

## Getting started

This project requires an apache web server, an sql (mysql or maridb) database and an installation of PhP (7.2 or newer).

In the case of Nexus, the DMS should already be set up and running, accessible on the internal network at https://192.168.0.2.

## Updating the DMS

When pulling an update, the DMS will understand that there is a version conflict with the database and bring everything up to date. If this fails for some reason, there is an option to retry.

## Upgrading from HTTP to HTTPS

A sysadmin will need to download the CA cert from /etc/ssl/certs/apache-selfsigned.crt and install it on any clients that wish to trust this SSL (ie, the tablet). TLS will work without this but browsers will not trust the authority and extra steps are needed to bypass browser security checks.

## Debugging/Reporting Issues

If something isn't working as it seems, please follow these steps:

1. Check the logs (found within the settings page when using an admin clinician account). If any logs are found, back them up and send the files to a service engineer for inspection.
2. Attempt a database repair (option found within the settings page under the versions tab when using an admin clinician account).
3. Contact DRW/Cain Medical for further support.

- If /login does not work but /index.php does (albeit with an error), you probably need to AllowOverride All in the vhost in sites-available and enable a2enmod. An apache restart will then fix things!

- If logging isn't working, it's likely due to permissions. Please make sure that the web application has sufficient permissions to update log txt files.

- If anything is suspected to be wrong with the tablet, head to settings -> general and turn on verbose logging. This should log the raw payloads the DMS is receiving from the tablet and will be helpful for debugging.

## Testing

Testing has been conducted and completed by DRW on 23/09/2025. Bug fixes and adjustments have been made by 25/09/2025.

To run the script which injects 100,000 random results into the database, open a terminal and run:

```
php /var/www/html/data/tests/send-100000.php
```

You can then delete any test data by running

```
php /var/www/html/data/tests/delete-tests.php
```

## Releases

For updates and releases, refer to the README and SETUP files in the releases folder of this repository.