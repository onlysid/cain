#!/usr/bin/sudo bash
if [ -z "$1" ]
  then
    echo "Not enough arguments supplied"
fi
if [ -L /dev/disk/by-label/UPDATE ]
  then
    date >> /home/cain/mount.log 2>&1
    echo "Detected UPDATE USB Stick..." >> /home/cain/mount.log 2>&1
    echo "Args:" $1  >> /home/cain/mount.log 2>&1
    /bin/mkdir -p /media/cain/UPDATE 
    /usr/bin/systemd-mount -G -A --no-block $1 /media/cain/UPDATE
  else
    date >> /home/cain/mount.log 2>&1
    echo "Detected NO ACCCESS USB Stick, not mounting..." >> /home/cain/mount.log 2>&1
    echo "Args:" $1 >> /home/cain/mount.log 2>&1
fi
