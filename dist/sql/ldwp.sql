DROP TABLE IF EXISTS ldwpworkers;
CREATE TABLE ldwpworkers (
	uuid CHAR(37) NOT NULL PRIMARY KEY,
	description VARCHAR(128) NULL,
	state BLOB,
	status ENUM('idle','working','down') NOT NULL DEFAULT 'idle',
	currentjob CHAR(37) NULL,
	loadaverage DECIMAL(6,2) NULL,
	lastpoll DATETIME NULL
);

DROP TABLE IF EXISTS ldwpjobs;
CREATE TABLE ldwpjobs (
	uuid CHAR(37) NOT NULL PRIMARY KEY,
	state BLOB,
	status ENUM('pending','running','sleeping','completed','queued') NOT NULL DEFAULT 'pending',
	started DATETIME NULL,
	finished DATETIME NULL,
	percent TINYINT NOT NULL DEFAULT 0,
	activity VARCHAR(255) NOT NULL DEFAULT 0
);
