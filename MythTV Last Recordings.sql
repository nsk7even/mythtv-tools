SELECT c.name,
	convert_tz(r.starttime, 'UTC', 'Europe/Berlin') as starttime,
	convert_tz(r.endtime, 'UTC', 'Europe/Berlin') as endtime,
	r.title, r.category, r.category_type
FROM mythconverg.recordedprogram r, mythconverg.channel c
WHERE r.chanid = c.chanid AND r.endtime < utc_timestamp()
ORDER BY r.starttime desc
LIMIT 5;