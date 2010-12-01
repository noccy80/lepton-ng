CREATE TABLE IF NOT EXISTS wikipages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pagens VARCHAR(64) NOT NULL,
    pagename VARCHAR(255) NOT NULL,
    pagetitle VARCHAR(255) NOT NULL,
    revision INT NOT NULL DEFAULT 1,
    content TEXT,
    markuptype VARCHAR(32) NOT NULL DEFAULT 'wiki',
    created DATETIME NOT NULL,
    locked TINYINT NOT NULL DEFAULT 0,
    author INT NOT NULL,
    UNIQUE KEY pageid(pagens,pagename,revision)
);
