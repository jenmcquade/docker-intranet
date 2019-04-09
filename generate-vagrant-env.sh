#!/bin/sh

sed '/^#/ d' .env > environment_tmp1.sh
sed 's/^/export /' environment_tmp1.sh > environment_tmp2.sh
sed '/^export \n/ d' environment_tmp2.sh > environment.sh
#grep -vo '^[^\n].*' environment_tmp2.sh > environment.sh
rm environment_tmp*
