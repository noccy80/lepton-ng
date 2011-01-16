DROP TABLE IF EXISTS blogmeta;
CREATE TABLE blogmeta (
	id INT PRIMARY KEY AUTO_INCREMENT,
	postid INT NOT NULL,
	meta VARCHAR(32),
	value VARCHAR(256),
	UNIQUE KEY postmeta(postid,meta)
);
