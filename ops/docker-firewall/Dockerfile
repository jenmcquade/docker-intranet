FROM		debian:stretch-slim


ENV 		DEBIAN_FRONTEND noninteractive

RUN 		apt-get update && \
			apt-get install -y --no-install-recommends ca-certificates iproute2 iptables && \
			rm -rf /var/lib/apt/lists/*

COPY 		iptables.sh /usr/local/bin/
COPY 		docker-entrypoint.sh /

RUN 		chmod +x /usr/local/bin/iptables.sh && \
			chmod +x /docker-entrypoint.sh

ENTRYPOINT 	["/docker-entrypoint.sh"]