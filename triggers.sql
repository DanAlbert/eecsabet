-- Course
DELIMITER $$
DROP TRIGGER IF EXISTS ust_insert_Course$$
CREATE TRIGGER ust_insert_Course AFTER INSERT ON Course FOR EACH ROW
BEGIN
	INSERT INTO SyllabusTimestamp (CourseID) VALUES (NEW.ID);
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_update_Course$$
CREATE TRIGGER ust_update_Course AFTER UPDATE ON Course FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.ID;
END$$
DELIMITER ;

-- CourseContent
DELIMITER $$
DROP TRIGGER IF EXISTS ust_insert_CourseContent$$
CREATE TRIGGER ust_insert_CourseContent AFTER INSERT ON CourseContent FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=NEW.CourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_update_CourseContent$$
CREATE TRIGGER ust_update_CourseContent AFTER UPDATE ON CourseContent FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.CourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_delete_CourseContent$$
CREATE TRIGGER ust_delete_CourseContent AFTER DELETE ON CourseContent FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.CourseID;
END$$
DELIMITER ;

-- LearningResources
DELIMITER $$
DROP TRIGGER IF EXISTS ust_insert_LearningResources$$
CREATE TRIGGER ust_insert_LearningResources AFTER INSERT ON LearningResources FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=NEW.CourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_update_LearningResources$$
CREATE TRIGGER ust_update_LearningResources AFTER UPDATE ON LearningResources FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.CourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_delete_LearningResources$$
CREATE TRIGGER ust_delete_LearningResources AFTER DELETE ON LearningResources FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.CourseID;
END$$
DELIMITER ;

-- MasterCLO
DELIMITER $$
DROP TRIGGER IF EXISTS ust_insert_MasterCLO$$
CREATE TRIGGER ust_insert_MasterCLO AFTER INSERT ON MasterCLO FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=NEW.CourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_update_MasterCLO$$
CREATE TRIGGER ust_update_MasterCLO AFTER UPDATE ON MasterCLO FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.CourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_delete_MasterCLO$$
CREATE TRIGGER ust_delete_MasterCLO AFTER DELETE ON MasterCLO FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.CourseID;
END$$
DELIMITER ;

-- Prerequisites
DELIMITER $$
DROP TRIGGER IF EXISTS ust_insert_Prerequisites$$
CREATE TRIGGER ust_insert_Prerequisites AFTER INSERT ON Prerequisites FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=NEW.CourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_update_Prerequisites$$
CREATE TRIGGER ust_update_Prerequisites AFTER UPDATE ON Prerequisites FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.CourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_delete_Prerequisites$$
CREATE TRIGGER ust_delete_Prerequisites AFTER DELETE ON Prerequisites FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.CourseID;
END$$
DELIMITER ;

-- TermsOffered
DELIMITER $$
DROP TRIGGER IF EXISTS ust_insert_TermsOffered$$
CREATE TRIGGER ust_insert_TermsOffered AFTER INSERT ON TermsOffered FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=NEW.CourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_update_TermsOffered$$
CREATE TRIGGER ust_update_TermsOffered AFTER UPDATE ON TermsOffered FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.CourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ust_delete_TermsOffered$$
CREATE TRIGGER ust_delete_TermsOffered AFTER DELETE ON TermsOffered FOR EACH ROW
BEGIN
	UPDATE SyllabusTimestamp SET LastRevision=NOW() WHERE CourseID=OLD.CourseID;
END$$
DELIMITER ;

-- CourseInstance
DELIMITER $$
DROP TRIGGER IF EXISTS ts_insert_CourseInstance$$
CREATE TRIGGER ts_insert_CourseInstance AFTER INSERT ON CourseInstance FOR EACH ROW
BEGIN
	DECLARE termExists INT DEFAULT 0;
	SELECT COUNT(*) INTO termExists FROM TermState WHERE TermState.TermID=NEW.TermID;
	
	IF termExists=0 THEN
		INSERT INTO TermState (TermID) VALUES (NEW.TermID);
	END IF;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ts_update_CourseInstance$$
CREATE TRIGGER ts_update_CourseInstance AFTER UPDATE ON CourseInstance FOR EACH ROW
BEGIN
	DECLARE termExists INT DEFAULT 0;
	DECLARE numCourses INT DEFAULT 0;
	
	IF NEW.TermID<>OLD.TermID THEN
		SELECT COUNT(*) INTO termExists FROM TermState WHERE TermState.TermID=NEW.TermID;
		
		IF termExists=0 THEN
			INSERT INTO TermState (TermID) VALUES (NEW.TermID);
		END IF;
		
		SELECT COUNT(*) INTO numCourses FROM CourseInstance WHERE TermID=OLD.TermID;
		IF numCourses=0 THEN
			DELETE FROM TermState WHERE TermID=OLD.TermID;
		END IF;
	END IF;
END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS ts_delete_CourseInstance$$
CREATE TRIGGER ts_delete_CourseInstance AFTER DELETE ON CourseInstance FOR EACH ROW
BEGIN
	DECLARE numCourses INT DEFAULT 0;
	SELECT COUNT(*) INTO numCourses FROM CourseInstance WHERE TermID=OLD.TermID;
	IF numCourses=0 THEN
		DELETE FROM TermState WHERE TermID=OLD.TermID;
	END IF;
END$$
DELIMITER ;
