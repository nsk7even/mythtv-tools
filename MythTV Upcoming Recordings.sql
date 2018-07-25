SELECT p.chanid, p.title, r.starttime
    FROM mythconverg.program p, mythconverg.recordmatch r
    WHERE r.starttime = p.starttime AND r.chanid = p.chanid AND r.starttime > now()
    ORDER BY r.starttime
    LIMIT 5;