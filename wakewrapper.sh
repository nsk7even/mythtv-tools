#!/bin/bash
# Sets RTC wake time and inserts the time into LCDd.conf for letting the display inform about the next wakeup time when the system is off

if [ -z "$1" ]
	then
		echo "Usage $0 <time>"
		exit 1
fi

# set wake time
wakestring=`/usr/sbin/rtcwake --date "$1" --mode no`

# grab the confirmed wake time
wakestring=`rtcwake --mode show | grep Alarm`

# extract only time of confirmation output
waketime=${wakestring:15:16}

# convert utc time string to local time
waketime=`date --date="$waketime +0000" +"%d. %b %T"`

# update LCDd.conf with wake time
sed -i -e "s/GoodBye = \"[^H].*/GoodBye = \"$waketime\"/g" testLCDd.conf

# reload LCDd to let it use the new config
#systemctl reload-or-restart LCDd.service
