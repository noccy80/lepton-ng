CREATE TABLE IF NOT EXISTS userppp (
	id INT PRIMARY KEY,

	secretkey CHAR(64) NOT NULL,
	codeindex INT NOT NULL DEFAULT 0,
	
	UNIQUE KEY id(id)
);
