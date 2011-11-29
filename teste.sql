CREATE TABLE IF NOT EXISTS `teste` (
  `codigo` int(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `dependencia` int(5) unsigned zerofill DEFAULT NULL,
  `nome` varchar(20) NOT NULL,
  `sobrenome` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  KEY `dependencia` (`dependencia`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
ALTER TABLE `teste` ADD CONSTRAINT `teste_ibfk_1` FOREIGN KEY (`dependencia`) REFERENCES `teste` (`codigo`) ON DELETE SET NULL ON UPDATE CASCADE;