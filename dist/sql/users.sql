CREATE TABLE users (
	id INT PRIMARY KEY AUTO_INCREMENT,
	username VARCHAR(32) NOT NULL,
	
	salt VARCHAR(32) NOT NULL,
	password VARCHAR(32) NOT NULL,

	email VARCHAR(64) NULL,
	flags VARCHAR(64) NOT NULL DEFAULT '',
	
	registered DATETIME NOT NULL,
	lastlogin DATETIME NULL,
	lastip VARCHAR(32),

	uuid VARCHAR(37) NOT NULL,
	
	UNIQUE KEY username(username)
);
