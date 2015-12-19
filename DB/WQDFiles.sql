CREATE TABLE `wqdfiles` (
  `wqdfileseq`      int AUTO_INCREMENT NOT NULL,
  `wqdfiledate`     date NOT NULL,
  `wqdfilename`     varchar(500) NOT NULL,
  `wqdfolderseq`    int NOT NULL,
  `wqdlocationseq`  int NOT NULL,
  /* Keys */
  PRIMARY KEY (`wqdfileseq`)
) ENGINE = InnoDB;