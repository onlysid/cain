sudo apt install openssl
openssl req -x509 -nodes -days 36500 -newkey rsa:2048 -keyout apache-selfsigned.key -out apache-selfsigned.crt -subj "/C=UK/ST=DRW/L=DRW/O=DRW/OU=HUB/CN=192.168.0.1"
sudo a2enmod ssl
sudo mv apache-selfsigned.key /etc/ssl/private
sudo mv apache-selfsigned.crt /etc/ssl/certs
sudo cp files/self-default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
sudo a2ensite default-ssl.conf
sudo systemctl restart apache2
echo "HUB: HTTPS is enabled but browser will complain until you accept the self signed certificate"




