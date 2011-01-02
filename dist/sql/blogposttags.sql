DROP TABLE IF EXISTS blogposttags;
CREATE TABLE blogposttags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    postid INT NOT NULL,
    tagid INT NOT NULL,
    UNIQUE INDEX posttag(postid,tagid)
);
