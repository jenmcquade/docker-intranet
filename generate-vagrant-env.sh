#!/bin/sh
cd /home/vagrant/intranet
echo "#!/bin/sh" >> environment_tmp1.tmp
sed '/^#/ d' .env > environment_tmp1.tmp
sed 's/^/export /' environment_tmp1.tmp > environment_tmp2.tmp
sed '/^export \n/ d' environment_tmp2.tmp > environment.sh
cp environment.sh /home/vagrant/.bashrc
rm environment_tmp*
