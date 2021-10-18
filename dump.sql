-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Хост: 10.0.0.234:3319
-- Время создания: Окт 18 2021 г., 19:59
-- Версия сервера: 10.5.12-MariaDB-log
-- Версия PHP: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `vm`
--

-- --------------------------------------------------------

--
-- Структура таблицы `banknotes`
--

CREATE TABLE `banknotes` (
  `denom` int(11) NOT NULL COMMENT 'Номинал купюры',
  `quantity` int(11) DEFAULT 0 COMMENT 'Кол-во купюр этого номинала'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `banknotes`
--

INSERT INTO `banknotes` (`denom`, `quantity`) VALUES
(50, 0),
(100, 0),
(200, 0),
(500, 0),
(1000, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `coins`
--

CREATE TABLE `coins` (
  `denom` int(11) NOT NULL COMMENT 'Номинал монеты',
  `quantity` int(11) DEFAULT 0 COMMENT 'Кол-во монет этого номинала'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `coins`
--

INSERT INTO `coins` (`denom`, `quantity`) VALUES
(1, 100),
(2, 100),
(5, 100),
(10, 100);

-- --------------------------------------------------------

--
-- Структура таблицы `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `seance_id` int(11) DEFAULT NULL COMMENT 'ID сеанса',
  `action` varchar(32) DEFAULT NULL COMMENT 'Действие',
  `difference` int(11) NOT NULL DEFAULT 0 COMMENT 'Величина изменения баланса',
  `balance` int(11) NOT NULL DEFAULT 0 COMMENT 'Новое значение баланса',
  `date_added` datetime DEFAULT current_timestamp() COMMENT 'Дата внесения записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(32) DEFAULT NULL COMMENT 'Наименование товара',
  `quantity` int(11) DEFAULT 0 COMMENT 'Кол-во товара',
  `price` int(11) DEFAULT 0 COMMENT 'Цена товара'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `name`, `quantity`, `price`) VALUES
(1, 'Товар 1', 10, 10),
(2, 'Товар 2', 5, 20),
(3, 'Товар 3', 3, 30),
(4, 'Товар 4', 20, 40),
(5, 'Товар 5', 15, 50);

-- --------------------------------------------------------

--
-- Структура таблицы `seances`
--

CREATE TABLE `seances` (
  `id` int(11) NOT NULL,
  `balance` int(11) DEFAULT NULL COMMENT 'Текущий баланс сеанса',
  `date_start` datetime DEFAULT NULL COMMENT 'Дата начала сеанса',
  `date_end` datetime DEFAULT NULL COMMENT 'Дата завершения сеанса'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `banknotes`
--
ALTER TABLE `banknotes`
  ADD PRIMARY KEY (`denom`);

--
-- Индексы таблицы `coins`
--
ALTER TABLE `coins`
  ADD PRIMARY KEY (`denom`);

--
-- Индексы таблицы `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logs_ibfk_1` (`seance_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `seances`
--
ALTER TABLE `seances`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `seances`
--
ALTER TABLE `seances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`seance_id`) REFERENCES `seances` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
