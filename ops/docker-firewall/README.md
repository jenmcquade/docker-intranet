Firewall rules for Docker published ports (based on [confd-firewall](https://hub.docker.com/r/colinmollenhour/confd-firewall/)).

[![](https://images.microbadger.com/badges/image/virtusai/docker-firewall.svg)](https://microbadger.com/images/virtusai/docker-firewall "Get your own image badge on microbadger.com")

Background
----------

This image allows firewall rules to be managed by a docker container which blocks traffic to the public interface from non-whitelisted addresses. Iptable rules are added to the `mangle` table.

Environment Variables
---------------------

 - FW_SERVICE - A prefix for the firewall table rules.
 - FW_PROTO - Comma-separated list of protocols to expose (e.g. tcp or udp or tcp,udp)
 - FW_PORTS - Comma-separated list of port numbers to expose (ranges allowed)
 - FW_STATIC - Comma-separated list of IPs/CIDRs to always allow.
 - FW_DISABLE - If set to 1, disables the firewall (removes the firewall table rules)

Usage
-----

Run with:

```
$ docker run -d --name docker-firewall --env FW_SERVICE=example --env FW_PROTO=tcp --env FW_PORTS=6379 --env FW_STATIC="8.9.10.11/30,18.19.20.21/30" --restart=always --cap-add=NET_ADMIN --net=host virtusai/docker-firewall
```

Or with docker-compose.yml:

```
version: '2'
services:
  firewall:
    restart: always
    image: virtusai/docker-firewall
    container_name: docker-firewall
    environment:
      - FW_SERVICE=example
      - FW_PROTO=tcp
      - FW_PORTS=6379
      - FW_STATIC=8.9.10.11/30,18.19.20.21/30
    cap_add:
      - NET_ADMIN
    network_mode: host
```

List affected rules:

*Raw*

```
$ sudo iptables-save -t mangle
```

*Formatted*

```
$ sudo iptables -L -n -v -t mangle
```

To persist the firewall rules, just run the container with the `--restart=always` option.

Credits
-------

 - [confd-firewall](https://hub.docker.com/r/colinmollenhour/confd-firewall/) (by [colinmollenhour](https://github.com/colinmollenhour))

Similar projects
----------------

 - [confd-firewall](https://hub.docker.com/r/colinmollenhour/confd-firewall/)
 - [docker-container-firewall](https://github.com/devrt/docker-container-firewall)
