DROP TABLE IF EXISTS blogcategories;
CREATE TABLE blogcategories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent INT NOT NULL DEFAULT 0,
    slug VARCHAR(64) NULL,
    category VARCHAR(64) NOT NULL
);
