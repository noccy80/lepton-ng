CREATE TABLE groupmembers (
	id INT PRIMARY KEY AUTO_INCREMENT,
	userid INT NOT NULL,
	groupid INT NOT NULL,
	primarygroup TINYINT(1) NOT NULL DEFAULT 0,
	UNIQUE KEY membership(userid,groupid)
);
