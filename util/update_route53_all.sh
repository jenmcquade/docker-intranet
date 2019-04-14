#!/bin/bash
## This script updates indiewebconsulting.com Route 53 entries with our Internet Service Provider's dynamic IP. 

set -e

## GLOBAL VARIABLES ARE USED IN THIS SCRIPT AND MUST BE SET IN THE HOST 
# The name server for the zone, can also be obtained from route53
nameserver=$AWS_NAMESERVER
#nameserver=ns-1602.awsdns-08.co.uk
zoneid=$AWS_ZONEID
#zoneid=Z17N2HVZH5EW0A
intranet_root=$INTRANET_ROOT

## Get the ISP's public IP address for the network and write it to the ip file
curl -o /home/phin/intranet/ip https://ipinfo.io/ip
newip=$(</home/phin/intranet/ip)

## Loop through domains and write out A records to a file


## Collect A records from file, loop through, and rewrite R53 records 

hostname="$domain.indiewebconsulting.com"

## This function is called to update the R53 records
# The AWS id for the zone containing the record, obtained by logging into aws route53
oldip=`dig +short "$hostname" @"$nameserver"`
echo "Your current IP is: $newip"
if [[ ! $newip =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]
then
    echo "Could not get current IP address: $oldip"
    exit 1
fi

# Get the IP address record that AWS currently has, using AWS's DNS server
if [[ ! $oldip =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]
then
    echo "Could not get old IP address: $oldip"
    exit 1
fi

echo "Rewriting $domain from $oldip to $newip..."

# Bail if everything is already up to date
if [ "$newip" == "$oldip" ]
then
    echo "$newip is already set to $oldip"
    exit 0
fi

# aws route53 client requires the info written to a JSON file
uuid=$(uuidgen)
uuid=${uuid,,}
tmp=$(mktemp $intranet_root/logs/aws/dynamic-dns.$uuid)
cat > ${tmp} << EOF
{
    "Comment": "Auto updating @ `date`",
    "Changes": [{
        "Action": "UPSERT",
        "ResourceRecordSet": {
            "ResourceRecords":[{ "Value": "$newip" }],
            "Name": "$hostname",
            "Type": "A",
            "TTL": 300
        }
    }]
}
EOF

echo "Changing IP address of $hostname from $oldip to $newip"
aws route53 change-resource-record-sets --hosted-zone-id $zoneid --change-batch "file://$tmp"
