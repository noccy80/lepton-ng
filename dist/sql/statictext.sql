DROP TABLE IF EXISTS statictext;
CREATE TABLE statictext (
	id int(11) NOT NULL AUTO_INCREMENT,
	slug varchar(64) NOT NULL,
	datemodified datetime NOT NULL,
	content text,
	PRIMARY KEY (id),
	UNIQUE KEY slug (slug)
)
