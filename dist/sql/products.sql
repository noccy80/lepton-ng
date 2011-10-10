
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(37) NOT NULL UNIQUE KEY,
    categoryid INT NULL,
    itemid VARCHAR(32) NOT NULL UNIQUE KEY,
    itemtype ENUM('PRODUCT','BILLABLE','DISCOUNT') NOT NULL,
    visibility ENUM('VISIBLE','HIDDEN','DELETED') NOT NULL DEFAULT 'VISIBLE',
    itemname VARCHAR(256) NOT NULL,
    description TEXT,
    unitstock INT NULL,
    unitprice FLOAT(10,2) NOT NULL,
    currency VARCHAR(3) NULL,
    ambient BLOB
);

DROP TABLE IF EXISTS productcategories;
CREATE TABLE productcategories (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    parentid INT NOT NULL DEFAULT 0,
    uuid VARCHAR(37) NOT NULL UNIQUE KEY,
    categoryslug VARCHAR(32) NOT NULL UNIQUE KEY,
    categorytype ENUM('PRODUCT','BILLABLE','DISCOUNT') NOT NULL,
    visibility ENUM('VISIBLE','HIDDEN','DELETED') NOT NULL DEFAULT 'VISIBLE',
    categoryname VARCHAR(256) NOT NULL,
    description TEXT
);

INSERT INTO productcategories
    (id,parentid,uuid,categoryslug,categorytype,visibility,categoryname,description)
VALUES 
    (1,0,'0ffa1cf5-1efc-41b1-bfb5-8ffa48cd8acf','category','PRODUCT','VISIBLE','Product Category','A category that contains products')
;

INSERT INTO products
    (uuid,categoryid,itemid,itemtype,visibility,itemname,description,unitstock,unitprice,currency)
VALUES
    ('d022800c-7b35-4700-9d98-e13523f5dbe8',1,'PROD0001','PRODUCT','VISIBLE','A product','This is a product',200,199,'USD');