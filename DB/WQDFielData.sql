CREATE TABLE `wqdfiledata` (
  `wqdfiledataseq`            int AUTO_INCREMENT NOT NULL,
  `wqdfileseq`                int NOT NULL,
  `wqdfiledatadated`          date NOT NULL,
  `wqdfiledatareportno`       int NOT NULL,
  `wqdfiledatatotalchannels`  int NOT NULL,
  `wqdfiledatachecksum`       varchar(100) NOT NULL,
  /* Keys */
  PRIMARY KEY (`wqdfiledataseq`)
) ENGINE = InnoDB;