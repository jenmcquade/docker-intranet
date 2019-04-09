server {
        listen 80;
        listen [::]:80;
        server_name patron.shockedandapplaud.com;
        return 301 https://$server_name$request_uri;
}


server {
        listen 443 ssl;
	listen [::]:443;
        server_name patron.shockedandapplaud.com;

        add_header Strict-Transport-Security "max-age=31536000";
        ssl_certificate /etc/letsencrypt/live/patron.shockedandapplaud.com/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/patron.shockedandapplaud.com/privkey.pem;
        ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        ssl_prefer_server_ciphers on;
        ssl_ciphers 'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH:AES256+ECDHE';

	location / {
		proxy_set_header X-Real-IP $remote_addr;
		proxy_set_header Host $host;
		proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
		proxy_pass http://localhost:8302;
	}
}
     
