mkdir /var/www/html/simdata
chmod 777 /var/www/html/simdata
chmod 777 /var/www/html/simdata/simoutput.txt
touch /var/www/html/simdata/simoutput.txt
chmod 777 /var/www/html/simdata/simoutput.txt
if ! pgrep -x "limssim" > /dev/null
then
    ( setsid /usr/bin/konsole -e "/home/cain/limssim" ) & 
fi
if ! pgrep -x "sambaserver" > /dev/null
then
    ( setsid /usr/bin/konsole -e "/home/cain/sambaserver" ) & 
fi
