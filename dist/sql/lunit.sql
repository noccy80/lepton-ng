DROP TABLE IF EXISTS lunitcases;
DROP TABLE IF EXISTS lunittests;
DROP TABLE IF EXISTS lunitresults;
DROP TABLE IF EXISTS lunitsessions;
DROP TABLE IF EXISTS lunitsession;

CREATE TABLE IF NOT EXISTS lunitcases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    casekey VARCHAR(64) NOT NULL UNIQUE KEY,
    description VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS lunittests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    caseid INT NOT NULL,
    testkey VARCHAR(64) NOT NULL,
    description VARCHAR(256),
    repeatcount INT,
    UNIQUE KEY casekey(caseid,testkey)
);

CREATE TABLE IF NOT EXISTS lunitsessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    completed DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS lunitresults (
    id INT PRIMARY KEY AUTO_INCREMENT,
    testid INT NOT NULL,
    caseid INT NOT NULL,
    sessionid INT NOT NULL,
    result ENUM('PASSED','FAILED','SKIPPED') NOT NULL,
    timemin FLOAT(9,2) NULL,
    timemax FLOAT(9,2) NULL,
    timeavg FLOAT(9,2) NULL,
    timeela FLOAT(9,2) NULL,
    message VARCHAR(255)
);