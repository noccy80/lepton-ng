DROP TABLE IF EXISTS statictext;
CREATE TABLE statictext (
	id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	slug varchar(64) NOT NULL,
	datemodified datetime NOT NULL,
	content text,
	UNIQUE KEY slug (slug)
);
