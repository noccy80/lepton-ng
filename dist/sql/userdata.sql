CREATE TABLE userdata (
	id INT PRIMARY KEY,

	displayname VARCHAR(48) NULL,
	website VARCHAR(64) NULL,
	
	firstname VARCHAR(48) NULL,
	lastname VARCHAR(48) NULL,

	sex ENUM('n/a','m','f') NOT NULL DEFAULT 'n/a',	
	country VARCHAR(2) NULL,

	ambient TEXT,
	
	UNIQUE KEY id(id)
);
