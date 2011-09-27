DROP TABLE IF EXISTS geonames_config;
CREATE TABLE geonames_config (
	cfgkey VARCHAR(64) NOT NULL PRIMARY KEY,
	cfgvalue TEXT
);
INSERT INTO geonames_config
	(cfgkey, cfgvalue)
VALUES
	('data.source','http://download.geonames.org/export/dump/'),
	('data.updated',NULL),
	('data.granularity',1),
	('cache.dir','app:/cache');


DROP TABLE IF EXISTS geonames_datasets;
CREATE TABLE geonames_datasets (
	setkey CHAR(2) NOT NULL PRIMARY KEY,
	setname VARCHAR(128) NOT NULL,
	active TINYINT(1) NOT NULL DEFAULT 1,
	url VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS geonames_hierarchy;
CREATE TABLE geonames_hierarchy (
    parentid INT NOT NULL,
    geoid INT NOT NULL PRIMARY KEY,
    htype VARCHAR(32)
);

DROP TABLE IF EXISTS geonames;
CREATE TABLE geonames (
	geoid INT NOT NULL PRIMARY KEY,
	name VARCHAR(200) NOT NULL,
	asciiname VARCHAR(200) NOT NULL,
	alternatenames VARCHAR(5000),
	latitude FLOAT(8,5),
	longitude FLOAT(8,5),
	featureclass CHAR(1) NOT NULL,
	featurecode VARCHAR(10) NOT NULL,
	countrycode CHAR(2),
	countrycodealt VARCHAR(60),
	admin1code VARCHAR(20),
	admin2code VARCHAR(80),
	admin3code VARCHAR(20),
	admin4code VARCHAR(20),
	population BIGINT,
	elevation INT,
	gtopo30 INT,
	timezone VARCHAR(40),
	modificationdate DATE
);

DROP TABLE IF EXISTS geonames_timezones;
CREATE TABLE geonames_timezones (
	timezoneid VARCHAR(40) PRIMARY KEY,
	gmtoffset FLOAT(2,1) NOT NULL,
	dstoffset FLOAT(2,1) NOT NULL,
	rawoffset FLOAT(2,1) NOT NULL
);

DROP TABLE IF EXISTS geonames_admin1codes;
CREATE TABLE geonames_admin1codes (
	admin1code VARCHAR(10) PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	longname VARCHAR(100) NOT NULL,
	geoid INT
);

DROP TABLE IF EXISTS geonames_admin2codes;
CREATE TABLE geonames_admin2codes (
	admin2code VARCHAR(20) PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	longname VARCHAR(100) NOT NULL,
	geoid INT
);

DROP TABLE IF EXISTS geonames_countryinfo;
CREATE TABLE geonames_countryinfo (
	isocode VARCHAR(20) PRIMARY KEY,
	iso3code VARCHAR(3) NOT NULL,
	isonumeric VARCHAR(10) NOT NULL,
	fips VARCHAR(10) NOT NULL,
	country VARCHAR(100) NOT NULL,
	capital VARCHAR(100) NOT NULL,
	area BIGINT,
	population BIGINT,
	continent CHAR(2) NOT NULL,
	tld VARCHAR(10) NOT NULL,
	currencycode VARCHAR(10),
	currencyname VARCHAR(60),
	phone VARCHAR(32),
	postalcode VARCHAR(32),
	postalcoderegex VARCHAR(32),
	languages VARCHAR(100),
	geoid INT,
	neighbours VARCHAR(100),
	equivalentfips VARCHAR(100)
);
