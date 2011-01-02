DROP TABLE IF EXISTS blogcategories;
CREATE TABLE blogcategories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(64) NULL,
    category VARCHAR(64) NOT NULL
);
