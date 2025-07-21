# Setup instructions for the Nexus Hub

Nexus is designed to work on Kubuntu 22.04. This whole process will require internet and the hub can be built using the following instructions:

1. Go to https://cdimage.ubuntu.com/kubuntu/releases/22.04/release/ and download the kubuntu-22.04.5-desktop-amd64.iso. This file is quite large so it may take a while. DO NOT UPGRADE BEYOND 22.04, it will not work.

2. Put this onto a USB stick (flash the ISO using something like https://etcher.balena.io/) - there are many ways to do this and it is likely OS dependent.

3. Enter the USB into the Nexus and enter boot menu (turn it on and press del/esc/f2/f10, depending on device).

4. Try or install kubuntu then set up Kubuntu with the user “cain” (must be this and must be lowercase). This should be minimal installation, using the whole disk (not partitioned). It must be set up to log in automatically and have the password: 4!cUnXMT.

5. Remove the USB stick and restart the nexus.

6. Copy the install zip file into a folder on the Nexus hub.

7. Unzip that folder and open a terminal.

8. CD into the terminal and run ./InstallCain.sh

9. This requires a super user password, so give it the password set in step 4.

That's it! The install script will gather and set up everything that's needed to make the WAP, the app and the webserver. Firefox will open once completed to trigger the DMS updates and you should see a completion message shortly after.