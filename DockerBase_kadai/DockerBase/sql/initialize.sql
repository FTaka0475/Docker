CREATE USER IF NOT EXISTS 'data_user'@'localhost' IDENTIFIED BY 'data';
GRANT ALL PRIVILEGES ON * . * TO 'data_user'@'localhost';

CREATE USER IF NOT EXISTS 'data_user'@'%' IDENTIFIED BY 'data';
GRANT ALL PRIVILEGES ON * . * TO 'data_user'@'%';
alter user 'data_user'@'%' identified with mysql_native_password by 'data';

DROP DATABASE IF EXISTS master_db;
CREATE DATABASE IF NOT EXISTS master_db;

DROP DATABASE IF EXISTS sub_db;
CREATE DATABASE IF NOT EXISTS sub_db;

USE master_db;

DROP TABLE IF EXISTS cards;
CREATE TABLE IF NOT EXISTS cards(
    id INT PRIMARY KEY,
    name VARCHAR(255),
    next_id INT ,
    rate INT DEFAULT 10,
    rare INT DEFAULT 1
);


INSERT INTO cards(id, name, next_id, rate, rare) values 
(1, 'normalcard', 2, 100, 1), (2, 'rarecard', 3, 30, 2),(3, 'hypercard', 4, 5, 3), (4, 'card', NULL, 1, 4);

DROP TABLE IF EXISTS images;
CREATE TABLE IF NOT EXISTS images(
    id INT PRIMARY KEY AUTO_INCREMENT,
    card_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (card_id) REFERENCES cards(id)
);

INSERT INTO images (card_id, image_path) VALUES
(1, 'normal.png'),
(2, 'rare.png'),
(3, 'Hyper.png'),
(4, 'card.png');


USE sub_db;

DROP TABLE IF EXISTS users_name;
CREATE TABLE IF NOT EXISTS users_name(
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255)
);

DROP TABLE IF EXISTS users_cards;
CREATE TABLE IF NOT EXISTS users_cards(
    id INT PRIMARY KEY AUTO_INCREMENT, 
    user_id INT,
    card_id INT
);

DROP TABLE IF EXISTS initial_cards;
CREATE TABLE IF NOT EXISTS initial_cards(
    id INT PRIMARY KEY AUTO_INCREMENT,
    card_id INT
);

INSERT INTO initial_cards (card_id) VALUES (1), (1), (1);

/*-- 自分で考えてみたクエリこうぞう
SELECT id as "CardID" from cards;

SELECT a.id as "CardID", b.name as "ItemName" from users_cards a inner join master_db.cards b on a.card_id = b.id ;

SELECT a.id as "CardID", b.name as "ItemName" from users_cards a inner join master_db.cards b on a.card_id = b.id group by card_id;

SELECT a.card_id as "CardID", b.name as "ItemName" , count(card_id) as value from users_cards a inner join master_db.cards b on a.card_id = b.id where user_id = 1 group by card_id Having value >= 2;
select user_id, count(result_win) from battle_log_table where result_win = true group by user_id;

SELECT id , name from users_cards uc inner join master_db.cards ct on uc.card_id = ct.id where user_id = 1;

SELECT uc.id , ct.name as "ItemName" from users_cards uc inner join master_db.cards ct on uc.card_id = ct.id where user_id = 1 AND uc.card_id = 1;

SELECT uc.id , ct.name as "ItemName" from users_cards uc inner join master_db.cards ct on uc.card_id = ct.id where user_id = 1 AND uc.card_id = 1 AND uc.id != [強化するカードID];

update users_cards set card_id = 2 where [ここに強化先のカードID(id)を入れる];
delete from users_cards where [素材カードID(id)を入れる]





SELECT a.card_id as "CardID", b.name as "ItemName" , count(a.card_id) as value from sub_db.users_cards a inner join master_db.cards b on a.card_id = b.id where user_id = 1 group by a.card_id Having value >= 2;
SELECT uc.id , ct.name as "ItemName" from sub_db.users_cards uc inner join master_db.cards ct on uc.card_id = ct.id where user_id = 1 AND uc.card_id = 1;

SELECT uc.id , ct.name as "ItemName" from users_cards uc inner join master_db.cards ct on uc.card_id = ct.id where user_id = 1 AND uc.card_id = 1 AND uc.id != [強化するカードID];

update users_cards set card_id = 2 where id = [ここに強化先のカードID(id)を入れる];
UPDATE users_cards SET card_id = [新しいカードID] WHERE id = [ベースカードインスタンスID];
delete from users_cards where id = [素材カードID(id)を入れる];
DELETE FROM users_cards WHERE id = [素材カードインスタンスID];
--*/



