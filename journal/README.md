![logo]

# Innovation/Ideation Journal
- This directory will eventually be migrated to its own branch off of master, to free up clone size. Right now, it makes for a good conversation piece. Yes, there are binaries in the master branch. No, we don't think that's weird.
- Drawing/Whiteboard files are directly exported from iOS Bamboo and committed here with a comment regarding its contents.
- We avoid timelines here.
- Not all initial investigations into requirements in these documents are required for phase 1, v1.0.0 Jonah release.
- When appropriate, "Pie in the Sky" dreams are labeled and captured until later scoping to avoid creep.

[logo]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/iwc.png "Indie Web Consulting, Tacoma, WA"
[aedan]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/484AAC3C-2B16-4C02-B964-53B5AAE146F5.png "Project AEDAN"
[thanos]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/D2FF716A-8417-421B-A733-CFE26A121B42.png "Terminal/HTTPS/Android v.N Operating System"
[specs]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/2DEB7871-0BC5-495E-9B08-71AAA75427B9.png "Hardware specifications"
[infinity]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/4DD99231-94AA-4814-B3A5-57502F003291.png "Infinity Bootloader"
[cost-breakdown]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/217EEA2D-CA0B-4C04-98DB-600388F2CAF0.png
[partitions-emmc]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/ABAD1D2E-FB59-4BED-B4CA-88F23253AD63.png "eMMC Boot Partitions" 
[partitions-sd]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/A4A86962-B32A-417A-BED3-6EE96C588C9C.png "SD Boot Partitions"
[firmware-composition-1]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/F0F65D7B-0232-43C5-A720-C22AC69AE609.png "Firmware Composition Page 1"
[firmware-composition-2]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/FADA0817-643E-4D8C-92F0-BF577D9DAB7A.png "Firmware Composition Page 2" 
[firmware-todo-1]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/3149A497-43F1-4878-B1BD-CF58A5806C9B.png "Firmware ToDo Page 1"
[firmware-todo-2]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/1F8F7597-5FCC-48EF-BEED-F16309982257.png "Firmware ToDo Page 2"
[firmware-todo-3]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/71099C95-3DAC-44BB-95A8-55B37DA83AB2.png "Firmware ToDo Page 3"
[firmware-test-notes-1]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/F6F57D26-BA94-44C7-AF43-7DDA61E0470F.png "Firmware tests notes Page 1"
[firmware-test-notes-2]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/1F8F7597-5FCC-48EF-BEED-F16309982257.png "Firmware tests notes Page 2"
[firmware-test-notes-3]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/BC73026E-E341-42A2-81D2-0AFD3614C3A4.png "Firmware tests notes Pages 3"
[firmware-test-notes-4]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/AFCC3E65-8697-453B-B216-56DF4F8477C0.png "Firmware tests notes Pages 4"
[firmware-test-notes-5]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/C2F0EF42-9139-4BF9-AFEE-7A507B11CF1F.png "Firmware tests notes Pages 5"
[firmware-test-notes-6]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/923FD070-1B43-4E3E-AFD9-4DE0EA4D8740.png "Firmware tests notes Pages 6"
[firmware-test-notes-7]: https://github.com/jonmcquade/docker-intranet/blob/master/journal/182F2CF4-1F65-4E56-BDB0-2914E4F326C0.png "Firmware tests notes Pages 7"

## Hardware Specifications
![specs]

## Cost Breakdown
![cost-breakdown]

## Phase 1 Projects
### Project AEDAN
![aedan]
### TH^nOS
![thanos]

## Firmware notes
### Testing firmware installations on the Le Potato
![firmware-test-notes-1]![firmware-test-notes-2]![firmware-test-notes-3]![firmware-test-notes-4]![firmware-test-notes-5]![firmware-test-notes-6]![firmware-test-notes-7]
### Partitions
#### eMMC
![partitions-emmc]
#### SD Card
![partitions-sd]
### Burn Tools
![firmware-composition-1]![firmware-composition-2]
### TODO/Things to consider
![firmware-todo-1]![firmware-todo-2]![firmware-todo-3]
### Boot Scripting
![infinity]

## Links we've found helpful so far

### Bootloaders
- [Amlogic Boot Loader](http://openlinux.amlogic.com/wiki/index.php/Arm/Boot_Loader)
- [XDA Developers Amlogic Tools](https://forum.xda-developers.com/android-stick--console-computers/amlogic/opensource-amlogic-tools-t3786991)

### SBC Manufacturers
- [Libre Le Potato Homepage](https://libre.computer/products/boards/aml-s905x-cc/)
- [Libre Mainline Linux with eMMC Support](https://libre.computer/2018/04/08/aml-s905x-cc-mainline-linux-preview-image-8-with-emmc-support/)
- [Rasberry Pi boot binaries](https://github.com/andreiw/RaspberryPiPkg/tree/master/Binary/prebuilt/2019Feb18-GCC5/RELEASE)

### Libre Le Potato burn tool images 
- [Libre Product Support Le Potato Images](https://libre.computer/products/boards/aml-s905x-cc/)

### Amlogic chipset help 
- [Openlinux Buildroot for Libre Le Potato Amlogic SOC](http://share.loverpi.com/board/libre-computer-project/libre-computer-board-aml-s905x-cc/soc-amlogic/buildroot/buildroot_openlinux_kernel_4.9_20170814_s905x.pdf)

### Message boards
- [Setting hostname and domain](https://unix.stackexchange.com/questions/322883/how-to-correctly-set-hostname-and-domain-name)
- [How to install Cerbot plugins?](https://devops.stackexchange.com/questions/3757/how-to-install-certbot-plugins)
- [How do I produce a CA signed public key?](https://security.stackexchange.com/questions/108508/how-do-i-produce-a-ca-signed-public-key)
- [How to remove `<none>` images after building in Docker](https://forums.docker.com/t/how-to-remove-none-images-after-building/7050) 
- [Libre Computer projects on UG wiki](https://lcpugwiki.readthedocs.io/en/latest/)

### Android Development and OEM documentation
- [Android Kernel for arm64](https://android.googlesource.com/kernel/arm64/)
- [Upstream Linux 4.4 repo for Android](https://android.googlesource.com/kernel/common/+/refs/heads/upstream-linux-4.4.y)

### OS-specific help libraries
- [Writing Armbian to eMMC on Le Potato](https://forum.armbian.com/topic/5668-le-potato-writing-armbian-to-emmc/)
- [Armbian Documentation](https://docs.armbian.com/)
- [ChromiumOS Dev Guide: Building](https://chromium.googlesource.com/chromiumos/docs/+/master/developer_guide.md#building-chromium-os)

### IoT Devices, Blogs, and Hardware reviews
- [Green Pi Thumb Project](https://mtlynch.io/greenpithumb/)
- [Le Potato Wiki on LoveRPI](http://wiki.loverpi.com/sbc:libre-computer-aml-s905x-cc)

### Smart Home / DIY
- [Snips Private-By-Design Voice Platform](https://makers.snips.ai/)
- [Indie Web Consulting parts wishlist](http://wishes.indiewebconsulting.com/)
- [Smart Gardening System from Rasberry Pi](https://www.switchdoc.com/2018/11/new-smart-garden-system-raspberry-pi/)
- [Build an automatic Rasberry Pi greenhouse](https://tutorials-raspberrypi.com/build-your-own-automatic-raspberry-pi-greenhouse/)
- [Setup a Rasberry Pi security camera livestream](https://tutorials-raspberrypi.com/raspberry-pi-security-camera-livestream-setup/)
- [Hydroponic gardening with a Rasberry Pi](https://www.raspberrypi.org/magpi/hydroponic-gardening/)

### Amazon Web Services
- [Cerbot DNS Route53 Documentation](https://certbot-dns-route53.readthedocs.io/en/stable/)
- [Route53 AWS CLI Reference](https://docs.aws.amazon.com/cli/latest/reference/route53/index.html)

### GitHub Projects
- [Indie Web Consulting Group on GitHub](https://github.com/indiewebconsulting)
- [This project's home](https://github.com/jonmcquade/docker-intranet)
- [Watchtower](https://github.com/v2tec/watchtower)
- [Portainer](https://github.com/portainer/portainer-compose/blob/master/docker-compose.yml)
- [Docker Nginx Certbot](https://github.com/staticfloat/docker-nginx-certbot)
- [Docker-SMTP](https://github.com/namshi/docker-smtp)
- [ElasticMQ](https://github.com/softwaremill/elasticmq)
- [AWS CLI in Docker](https://github.com/mikesir87/aws-cli-docker)
- [OpenSSL on Alpine](https://github.com/gitphill/openssl-alpine)
- [Nginx Proxy Alpine Letsencrypt Route53 Nginx Configuration Example](https://github.com/tokyohomesoc/nginx-proxy-alpine-letsencrypt-route53/blob/Release/nginx.conf)
- [Docker Mailserver](https://github.com/tomav/docker-mailserver)
- [iRedmail Docker Server](https://github.com/lejmr/iredmail-docker)
- [Moodle in Docker](https://github.com/moodlehq/moodle-docker)
- [Steps to clear out the history of a GitHub repository](https://gist.github.com/stephenhardy/5470814)
- [Armbian Build Tools](https://github.com/armbian/build)
- [GitHub Topic: Gardening](https://github.com/topics/gardening)

### APIs / Manpages
- [DNSCrypt Proxy](http://manpages.ubuntu.com/manpages/xenial/man8/dnscrypt-proxy.8.html)
- [DNSMASQ](http://www.thekelleys.org.uk/dnsmasq/docs/dnsmasq-man.html)
- [Rsyslog](https://linux.die.net/man/8/rsyslogd)

### Caching
- [Redis](https://github.com/docker-library/redis)
- [Redis in Docker](https://hub.docker.com/_/redis)
- [Squid Proxy Caching Server](https://linux.die.net/man/8/squid)

### Containerization, Orchestration, Virtualization and Emulation (COVE)
- [This project's Vagrantfile for building, burning, and testing from x64 hosts](https://github.com/jonmcquade/docker-intranet/blob/master/Vagrantfile)
- [Install Docker on Debian](https://docs.docker.com/install/linux/docker-ce/debian/#install-docker-ce-1)
- [Portainer Documentation](https://portainer.readthedocs.io/en/latest/deployment.html)
- [A complete guide to Docker ARG, ENV and .env](https://vsupalov.com/docker-arg-env-variable-guide/)
- [Docker Compose Documentation - Up](https://docs.docker.com/compose/reference/up/)
- [Create a Docker swarm](https://docs.docker.com/engine/swarm/swarm-tutorial/create-swarm/)
- [Kubernetes vs. Docker Swarm Comparison Guide](https://hackernoon.com/kubernetes-vs-docker-swarm-a-complete-comparison-guide-15ba3ac6f750)
- [Kubernetes and ownCloud on Le Potato](http://containerized.me/arming-kubernetes-with-openebs-1/)

### Media Streaming and VOD
- [Nginx RTMP Module](https://github.com/arut/nginx-rtmp-module)
- [Facebook Live Streaming API](https://developers.facebook.com/docs/live-video-api)
- [Youtube Live Streaming API](https://developers.google.com/youtube/v3/live/getting-started)
- [Twitch for Developers](https://dev.twitch.tv/why-twitch/)
- [Widevine DRM](https://www.widevine.com/solutions/widevine-drm)

### Web Apps
- Web Sockets over HTTP2: [Kestrel Server in ASP .NET Core 2.2](https://docs.microsoft.com/en-us/aspnet/core/fundamentals/servers/kestrel?view=aspnetcore-2.2)
- [ASP .NET Core Docker Sample](https://github.com/dotnet/dotnet-docker/blob/master/samples/aspnetapp/README.md)

### Linux (non-OS specific)
- [Linux Upstream](https://www.kernel.org/)
- [Libretech Mainline Linux Fork](https://github.com/libre-computer-project/libretech-linux)
- [aarch64 Linux GNU C++ Manpage](https://linux.die.net/man/1/aarch64-linux-gnu-gcc)

### ARM64v8 Development
- [Official Support for Windows 10 on ARM](https://blogs.windows.com/buildingapps/2018/11/15/official-support-for-windows-10-on-arm-development/)
- [Windows 10 on ARM documentation](https://docs.microsoft.com/en-us/windows/arm/)

### GPIO Development / Python for embedded devices

### Security / SSL / Encryption and Decryption / Authentication / Identity Management
- [DNSCrypt Installation HowTo](https://www.linuxuprising.com/2018/10/install-and-enable-dnscrypt-proxy-2-in.html)
- [DNSCrypt Performance Tweaks](https://github.com/jedisct1/dnscrypt-proxy/wiki/Caching)
- [How to secure Nginx with Let's Encrypt on Alpine](https://www.cyberciti.biz/faq/how-to-install-letsencrypt-free-ssltls-for-nginx-certificate-on-alpine-linux/)
- [OpenSSL command cheatsheet](https://medium.freecodecamp.org/openssl-command-cheatsheet-b441be1e8c4a)

### Serverless

### Etc / Other
- [Poison ROM for S905x devices](https://forum.xda-developers.com/android-stick--console-computers/amlogic/s905x-devices-poison-rom-t3803867)
- [ATV Experience Aftermarket Android TV for S905X chipsets](https://www.atvxperience.com/#link_tab-S905X)
- [LineageOS for Android](https://lineageos.org/)
- [Chromium OS Nightly Builds](https://chromium.arnoldthebat.co.uk/?dir=.%2Fdaily)
