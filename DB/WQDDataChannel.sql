CREATE TABLE `wqdfiledatachannels` (
  `wqdfiledatachannelseq`     int AUTO_INCREMENT NOT NULL,
  `wqdfiledataseq`            int NOT NULL,
  `wqdfiledatachannelnumber`  varchar(100) NOT NULL,
  `wqdfiledatachannelname`    varchar(100) NOT NULL,
  `wqdfiledatachannelvalue`   decimal NOT NULL,
  `wqdfiledatachannelstatus`  int NOT NULL,
  /* Keys */
  PRIMARY KEY (`wqdfiledatachannelseq`)
) ENGINE = InnoDB;