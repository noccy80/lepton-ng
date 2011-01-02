DROP TABLE IF EXISTS blogmedia;
CREATE TABLE blogmedia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    medianame VARCHAR(64) NOT NULL,
    mediasrc VARCHAR(255) NOT NULL,
    mediathumb VARCHAR(255) NULL,
    mediatype ENUM('attachment','image','video'),
    contenttype VARCHAR(64) NOT NULL,
    filesize BIGINT
);
