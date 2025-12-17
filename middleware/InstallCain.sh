#!/bin/bash
echo "Installing SambaServer and LIMs Simulator on new server"

# check were user tomserver if not quit! As it doesn't work if not!
if [ $USER = "cain" ]; then
    echo "Correct User cain detected"
else
    echo "The username isn't 'cain', please create a new account with username tomerver, reboot and login again as the new user"
    exit 1
fi

# check if the ufw linux filewall is on and if so add the rules
sudo ufw status | egrep -q inactive
if test $? -eq 0; then
    echo "Linux Firewall not Detected"
else
    echo "Detected Linux Firewall adding rules for Tom Access"
    sudo ufw allow 80/tcp
    sudo ufw allow 443/tcp
    sudo ufw allow 22222/udp
    sudo ufw allow 30000:40000/tcp
fi

sudo apt -y install isc-dhcp-server x11vnc openjdk-11-jdk net-tools mariadb-server libmariadb3 libfreeimage3 php-mysql php-bz2 php-zip apache2 php php-mbstring php-intl php-curl openssl wpasupplicant pmount exfat-utils 
#usbguard

# install kernel 6.15.7 for the wifi and security fixes
#echo "Please wait this may take a while..."
#mkdir kernel
#cd kernel
#wget https://kernel.ubuntu.com/mainline/v6.15.7/amd64/linux-headers-6.15.7-061507-generic_6.15.7-061507.202507171742_amd64.deb
#wget https://kernel.ubuntu.com/mainline/v6.15.7/amd64/linux-headers-6.15.7-061507_6.15.7-061507.202507171742_all.deb
#wget https://kernel.ubuntu.com/mainline/v6.15.7/amd64/linux-image-unsigned-6.15.7-061507-generic_6.15.7-061507.202507171742_amd64.deb
#wget https://kernel.ubuntu.com/mainline/v6.15.7/amd64/linux-modules-6.15.7-061507-generic_6.15.7-061507.202507171742_amd64.deb
#sudo dpkg -i linux-headers-6.15.7-061507-generic_6.15.7-061507.202507171742_amd64.deb
#sudo dpkg -i linux-headers-6.15.7-061507_6.15.7-061507.202507171742_all.deb
#sudo dpkg -i linux-image-unsigned-6.15.7-061507-generic_6.15.7-061507.202507171742_amd64.deb
#sudo dpkg -i linux-modules-6.15.7-061507-generic_6.15.7-061507.202507171742_amd64.deb
#cd ..
#echo "You need to reboot to use the Wifi fix in the newer kernel."

# Setup the DB
cat files/main.sql files/phpmyadmin.sql cain-main/data/setup.sql > install.sql
sudo mysql < install.sql
rm install.sql
# setup logs
sudo cp files/uvcdynctrl /lib/udev/uvcdynctrl
sudo rm /var/log/uvcdynctrl-udev.log
sudo journalctl --vacuum-size=100M
# setup the Website
sudo rm /var/www/html/index.html
sudo cp -R cain-main/* /var/www/html/
sudo cp cain-main/includes/db-config-sample.php /var/www/html/includes
sudo touch /var/www/html/simdata/simoutput.txt
#cp files/config.inc.php phpmyadmin
#sudo cp -R phpmyadmin /var/www/html/
sudo cp files/htaccess /var/www/html/.htaccess
sudo usermod -a -G www-data cain
sudo chown -R cain:www-data /var/www/html
sudo chmod -R 775 /var/www/html
sudo chmod 664 /var/www/html/simdata/simoutput.txt
php -v | egrep -q "PHP 7.2"
if test $? -eq 0; then
    echo "PHP 7.2 Detected"
    sudo cp files/php7.2.ini /etc/php/7.2/apache2/php.ini
fi
php -v | egrep -q "PHP 7.4"
if test $? -eq 0; then
    echo "PHP 7.4 Detected"
    sudo cp files/php7.4.ini /etc/php/7.4/apache2/php.ini
fi
php -v | egrep -q "PHP 8.1"
if test $? -eq 0; then
    echo "PHP 8.1 Detected"
    sudo cp files/php8.1.ini /etc/php/8.1/apache2/php.ini
fi
php -v | egrep -q "PHP 8.3"
if test $? -eq 0; then
    echo "PHP 8.3 Detected"
    sudo cp files/php8.3.ini /etc/php/8.3/apache2/php.ini
fi
sudo a2enmod rewrite
sudo cp files/000-default.conf /etc/apache2/sites-available/
sudo systemctl restart apache2
sudo cp files/50-server.cnf /etc/mysql/mariadb.conf.d/
sudo systemctl restart mysql
sudo cp -P lib/* /usr/local/lib
sudo ldconfig /usr/local/lib
sudo cp files/dhcpd.conf /etc/dhcpd/
sudo cp files/dhcpd.conf /etc/dhcp/
sudo cp files/isc-dhcp-server /etc/default/
sudo systemctl restart isc-dhcp-server
sudo cp files/00-install-config.yaml /etc/netplan
sudo mv /etc/netplan/01-network-manager-all.yaml /etc/netplan/01-network-manager-all.yaml-old 
sudo chmod 600 /etc/netplan/*.yaml
sudo netplan apply
sudo cp files/00-automount.rules /etc/udev/rules.d/
sudo cp files/mount-update.sh /etc/udev/rules.d/
sudo chmod 700 /etc/udev/rules.d/mount-update.sh
sudo chmod 600 /etc/udev/rules.d/00-automount.rules
sudo udevadm control --reload-rules
sudo systemctl mask udisks2.service
sudo systemctl stop udisks2.service
sudo systemctl daemon-reload
mkdir /home/cain/logs
mkdir /home/cain/share
mkdir /home/cain/share/csv
openssl req -x509 -nodes -days 36500 -newkey rsa:2048 -keyout apache-selfsigned.key -out apache-selfsigned.crt -subj "/C=UK/ST=DRW/L=DRW/O=DRW/OU=HUB/CN=192.168.0.1" -addext "subjectAltName = IP:192.168.0.1"
sudo a2enmod ssl
sudo mv apache-selfsigned.key /etc/ssl/private
sudo mv apache-selfsigned.crt /etc/ssl/certs
sudo cp files/self-default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
sudo a2ensite default-ssl.conf
sudo systemctl restart apache2
tar -xf config.tar.gz -C /home/cain/
cp files/product-*.txt /home/cain
cp files/profile /home/cain/.profile
cp files/sambaserver /home/cain
cp files/limssim /home/cain
cp files/StartSambaServer.sh /home/cain
chmod +x /home/cain/StartSambaServer.sh
chmod +x /home/cain/sambaserver
chmod +x /home/cain/limssim
mkdir /var/www/html/simdata
sudo chown -R cain:cain /var/www/html/simdata
chmod 777 /var/www/html/simdata
chmod 777 /var/www/html/simdata/simoutput.txt
touch /var/www/html/simdata/simoutput.txt
chmod 777 /var/www/html/simdata/simoutput.txt
sudo chown -R cain:cain /var/www/html/simdata
echo "Please wait, 5 seconds ..."
sleep 5
echo "Completed"
# open firefox at the Tom Setup page!
firefox https://192.168.0.2/ &
