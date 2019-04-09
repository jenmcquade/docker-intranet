server { listen 80; server_name indiewebconsulting.com; include conf.d/certbot.inc; location / { include conf.d/proxy_set_header.inc; proxy_pass http://downstream_http_server_host; } } 
