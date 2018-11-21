CREATE TABLE IF NOT EXISTS `compilations` (  `id` int(11) NOT NULL,
 `name` varchar(255) NOT NULL
 ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

alter table  `compilations`   ADD PRIMARY KEY (`id`);

alter table  `compilations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=0;

insert into compilations (name)
 select distinct compilation from games
  where compilation is not null and compilation > ' ' order by 1;

CREATE TABLE IF NOT EXISTS `games_compilations` (
  `id` int(11) NOT NULL,
  `games_id` int(11) NOT NULL,
  `compilations_id` int(11) NOT NULL,
  `ord` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `games_compilations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `games_id` (`games_id`,`compilations_id`);

ALTER TABLE `games_compilations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=0;

insert into games_compilations (games_id, compilations_id)
 select g.id, c.id from games g, compilations c
   where g.compilation = c.name;
