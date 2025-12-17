# Nexus Release Files

In this directory, we have 3 files:

* update_app.zip
* update_web.zip
* Cain_Installer_<release_date>.tar.gz

## Updates

For systems which are already built and are in a production environment, place update_app and update_web into an FAT32 formatted USB drive called "UPDATE", enter the USB stick into the running system. The middleware running on the device will be triggered to perform the update.

If update_app is provided, the system will then restart with the new updates.

If only update_web is provided, the update occurs automatically. Navigate to the web app and there will be confirmation that it is either updating or has updated. If no confirmation is visible, head to versions as it may have been triggered already by another client and it may have already updated. 

> ⚠️ **Important**
>
> The USB stick **must be formatted as FAT32** and **must be named "UPDATE"**. If it is not, it will never mount and the middleware will never see it.