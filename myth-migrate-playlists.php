<?php

# myth-migrate-playlists.php v0.1
# (c) Nicolas Krzywinski http://www.nskComputing.de
#
# Created:	    2018 by Nicolas Krzywinski
# Description:	Migrates playlists from one MythTV database to a new one, while converting the song ids
# Last Changed: 2018-07-25
# Change Desc:	Created
# Remarks:		This is a quick and dirty script - USE AT YOUR OWN RISK!
# License:		GPL

# SETTINGS

$db_host = "hostname-of-your-mysql-system";
$db_user = "youruser-with-access-to-both-dbs";
$db_pwd = "yourpassword";

$old_db = "mythconverg_old";
$new_db = "mythconverg";

# CONNECT

$mysqli = new mysqli($db_host, $db_user, $db_pwd, $old_db);

if ($mysqli->connect_errno)
{
	echo "<h1>Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "</h1>";
	exit;
}

echo "<h3>Connected to: " . $mysqli->host_info . "</h3>\n";

$res = $mysqli->query("SET NAMES 'utf8'");
if ($res === false)
{
	echo "<h1>Error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "</h1>";
}


# QUERY OLD LIST

$res = $mysqli->query("select * from $old_db.music_playlists");
if ($res === false)
{
	echo "<h1>Error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "</h1>";
}

$res->data_seek(0);
while ($row = $res->fetch_assoc())
{
	$oldlists[$row["playlist_name"]] = $row;
}


echo "<h1>## PROCESS EACH PLAYLIST ##</h1>";

foreach ($oldlists as $listname => $oldlist)
{
	unset($namearray);
	unset($filenames);
	unset($idarray);
	unset($songids);
	unset($insert);
	
	echo "<h2>Processing playlist '$listname' ...</h2>";
	echo "<ul>";
	
	if ($oldlist["playlist_songs"] == "")
	{
		echo "<li>Playlist empty, skipping.</li></ul>";
		continue;
	}

	echo "<li><b># QUERY SONG FILENAMES</b></li>";

	$sql = "select filename from $old_db.music_songs where song_id in (" . $oldlist["playlist_songs"] . ")";
	$res = $mysqli->query($sql);
	if ($res === false)
	{
		echo "<h1>Error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "</h1>";
		echo "<li>SQL: $sql</li>";
		continue;
	}

	$res->data_seek(0);
	while ($row = $res->fetch_assoc())
	{
		$namearray[] = "'" . $mysqli->real_escape_string($row["filename"]) . "'";
	}
	$filenames = implode(",", $namearray);
	echo "<li>Detected " . count($namearray) . " songs for playlist '$listname': " . htmlentities($filenames, ENT_SUBSTITUTE) . "</li>";


	echo "<li><b># GET NEW SONG IDS</b></li>";

	$sql = "select song_id from $new_db.music_songs where filename in (" . $filenames . ")";
	$res = $mysqli->query($sql);
	if ($res === false)
	{
		echo "<h1>Error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "</h1>";
		echo "<li>SQL: " . htmlentities($sql) . "</li>";
		continue;
	}

	$res->data_seek(0);
	while ($row = $res->fetch_assoc())
	{
		$idarray[] = $row["song_id"];
	}
	$songids = implode(",", $idarray);
	echo "<li>Detected new song ids for playlist '$listname': $songids</li>";


	echo "<li><b># BUILD INSERT</b></li>";

	$insert = "INSERT INTO $new_db.music_playlists VALUES(NULL, '$listname', '$songids', '" .
		$oldlist["last_accessed"] . "', " . $oldlist["length"] . ", " . $oldlist["songcount"] . ", '" . $oldlist["hostname"] . "')";
	
	echo "<li>So, what about this insert? $insert</li>";
	
	if (@$_GET["mode"] == "insert")
	{
		echo "<li>INSERT mode is set, executing this insert now ...";
		
		$res = $mysqli->query($insert);
		if ($res === false)
		{
			echo "</li><h1>Error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "</h1>";
		}
		else
		{
			echo " done (id=" . $mysqli->insert_id . ")</li>";
		}
	}
	else
	{
		echo "<li>If this insert (and all others) seem ok, use get parameter 'mode=insert' to execute all the inserts, or click here: <a href=\"" . $PHP_SELF . "?mode=insert\">Reload with INSERTs ENABLED!</a></li>";
	}
	
	echo "</ul>";
}


?>
