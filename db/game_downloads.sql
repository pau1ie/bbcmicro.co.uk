SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `game_downloads` (
  `id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `downloads` int(11) NOT NULL DEFAULT '0',
  `gamepages` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `game_downloads`
  ADD PRIMARY KEY (`id`,`year`);
COMMIT;

