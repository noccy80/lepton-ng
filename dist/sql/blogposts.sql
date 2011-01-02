DROP TABLE IF EXISTS blogposts;
CREATE TABLE blogposts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(64) UNIQUE NOT NULL,
    pubdate DATETIME NOT NULL,
    creator INT NOT NULL,
    guid VARCHAR(255) NOT NULL,
    uuid VARCHAR(40) NOT NULL,
    categoryid INT,
    excerpt TEXT,
    content TEXT,
    contenttype VARCHAR(64) NOT NULL DEFAULT 'text/html',
    commentstatus ENUM('closed','open') NOT NULL DEFAULT 'open',
    pingbackstatus ENUM('closed','open') NOT NULL DEFAULT 'open',
    poststatus ENUM('draft','published'),
    postmeta TEXT,
    hits INT NOT NULL DEFAULT 0
);
