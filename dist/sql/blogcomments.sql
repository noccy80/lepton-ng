DROP TABLE IF EXISTS blogcomments;
CREATE TABLE blogcomments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent INT NOT NULL DEFAULT 0,
    postid INT NOT NULL,
    commenttype ENUM('comment','pingback','response') NOT NULL DEFAULT 'comment',
    commentdate DATETIME NOT NULL,
    authorname VARCHAR(128) NOT NULL,
    authorwebsite VARCHAR(255) NULL,
    authoremail VARCHAR(128) NOT NULL,
    authorip VARCHAR(64) NOT NULL,
    commentstatus ENUM('approved','pending','trash','spam') NOT NULL DEFAULT 'approved',
    content TEXT,
    contenttype VARCHAR(64) NOT NULL DEFAULT 'text/plain'
);
