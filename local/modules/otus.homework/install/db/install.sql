CREATE TABLE IF NOT EXISTS otus_homework
(
    ID INT AUTO_INCREMENT PRIMARY KEY,
    CRM_ENTITY_ID INT NOT NULL,
    CRM_ENTITY_TYPE INT NOT NULL,
    NAME  VARCHAR
(
    255
),
    VALUE TEXT
    );

INSERT INTO otus_homework (CRM_ENTITY_ID, CRM_ENTITY_TYPE, NAME, VALUE)
VALUES (1, 1, 'Поле привязанное к Лиду', 'Значение поля лида'),
       (12, 2, 'Поле привязанное к Сделке', 'Значение поля привязанного к Сделке'),
       (3, 3, 'Поле привязанное к Контакту', 'Значение поля Контакта'),
       (1, 4, 'Поле привязанное к Компании', 'Значение поля Компании');
