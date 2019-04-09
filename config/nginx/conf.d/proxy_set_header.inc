proxy_set_header X-Forwarded-By $server_addr:$server_port; proxy_set_header X-Forwarded-For $remote_addr; proxy_set_header X-Forwarded-Proto $scheme; proxy_set_header Host $host;
