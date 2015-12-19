ALTER TABLE eliveprod.folderuser RENAME eliveprod.locationusers;
ALTER TABLE eliveprod.locationusers
 CHANGE folderseq locationseq BIGINT(20) NOT NULL;