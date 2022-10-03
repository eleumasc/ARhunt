CREATE TABLE IF NOT EXISTS `users` (
  `nickname` varchar(24) NOT NULL,
  `password` char(64) NOT NULL,
  `email` varchar(254) NOT NULL,
  `emailVerified` tinyint(4) NOT NULL DEFAULT '0',
  `emailVerificationCode` char(16) NOT NULL,
  `signupTime` datetime NOT NULL,
  `signinTime` datetime DEFAULT NULL,
  `lastSigninTime` datetime DEFAULT NULL,
  PRIMARY KEY (`nickname`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `hunts` (
  `id` char(16) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text NOT NULL,
  `author` varchar(24) NOT NULL,
  `callTime` datetime NOT NULL,
  `callLat` decimal(9,7) NOT NULL,
  `callLng` decimal(10,7) NOT NULL,
  `playTime` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_hunts_users` (`author`),
  CONSTRAINT `FK_hunts_users` FOREIGN KEY (`author`) REFERENCES `users` (`nickname`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `media` (
  `id` char(16) NOT NULL,
  `hunt` char(16) NOT NULL,
  `name` varchar(120) NOT NULL,
  `filename` varchar(260) NOT NULL,
  `type` varchar(64) NOT NULL,
  `subtype` varchar(64) NOT NULL,
  `length` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_media_hunts` (`hunt`),
  CONSTRAINT `FK_media_hunts` FOREIGN KEY (`hunt`) REFERENCES `hunts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `teams` (
  `id` char(16) NOT NULL,
  `hunt` char(16) NOT NULL,
  `name` varchar(120) NOT NULL,
  `color` char(6) NOT NULL,
  `sequence` int(11) NOT NULL DEFAULT '1',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `closeTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_teams_hunts` (`hunt`),
  CONSTRAINT `FK_teams_hunts` FOREIGN KEY (`hunt`) REFERENCES `hunts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `players` (
  `hunt` char(16) NOT NULL,
  `user` varchar(24) NOT NULL,
  `team` char(16) DEFAULT NULL,
  `lastModified` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (`hunt`,`user`),
  KEY `FK_players_users` (`user`),
  KEY `FK_players_teams` (`team`),
  CONSTRAINT `FK_players_hunts` FOREIGN KEY (`hunt`) REFERENCES `hunts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_players_teams` FOREIGN KEY (`team`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_players_users` FOREIGN KEY (`user`) REFERENCES `users` (`nickname`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `steps` (
  `team` char(16) NOT NULL,
  `sequence` int(11) NOT NULL,
  `text` text NOT NULL,
  `media` char(16) DEFAULT NULL,
  `takeLat` decimal(9,7) NOT NULL,
  `takeLng` decimal(10,7) NOT NULL,
  `taken` tinyint(4) NOT NULL DEFAULT '0',
  `takeTime` datetime DEFAULT NULL,
  `takeUser` varchar(24) DEFAULT NULL,
  PRIMARY KEY (`team`,`sequence`),
  KEY `FK_steps_media` (`media`),
  KEY `FK_steps_users` (`takeUser`),
  CONSTRAINT `FK_steps_media` FOREIGN KEY (`media`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_steps_teams` FOREIGN KEY (`team`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_steps_users` FOREIGN KEY (`takeUser`) REFERENCES `users` (`nickname`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `questions` (
  `id` char(16) NOT NULL,
  `team` char(16) NOT NULL,
  `sequence` int(11) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_questions_steps` (`team`,`sequence`),
  CONSTRAINT `FK_questions_steps` FOREIGN KEY (`team`, `sequence`) REFERENCES `steps` (`team`, `sequence`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `choices` (
  `id` char(16) NOT NULL,
  `question` char(16) NOT NULL,
  `right` tinyint(4) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `picked` tinyint(4) NOT NULL DEFAULT '0',
  `pickTime` datetime DEFAULT NULL,
  `pickUser` varchar(24) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_choices_questions` (`question`),
  KEY `FK_choices_users` (`pickUser`),
  CONSTRAINT `FK_choices_questions` FOREIGN KEY (`question`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_choices_users` FOREIGN KEY (`pickUser`) REFERENCES `users` (`nickname`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
