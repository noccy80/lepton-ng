CREATE TABLE IF NOT EXISTS acl (
	subject CHAR(37) NOT NULL,
	object CHAR(37) NOT NULL,
	access ENUM('allow','deny','ignore') NOT NULL DEFAULT 'ignore',
	UNIQUE KEY aclentry(subject,object)
);
