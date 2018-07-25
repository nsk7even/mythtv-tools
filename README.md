# mythtv-tools
Tools for administering and maintaining a MythTV system

# wakewrapper.sh
Wrapper script for letting MythTV set the RTC wakeup time, to additionally update the LCD display. After the system has shut down, the display shows the next wakeup time then.

Note: you have to modify line #23 if you have a second goodbye line configured in your LCDd.conf and if your text of this second line does not start with "H" - otherwise both LCD lines are updated with the next wakeup time.
Update the "H" in this line with whatever the first character of your text in the second goodbye line is.

Example (my LCDd.conf):
...
GoodBye = "Hello @"
GoodBye = "{last wakeup time}"
...

The expression in line #23 searches all lines that start with "GoodBye = ", but skips all lines that proceed with "H". All remaining lines are replaced by "GoodBye = {next wakeup time}".
