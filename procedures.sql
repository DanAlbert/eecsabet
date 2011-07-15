DELIMITER $$
DROP PROCEDURE IF EXISTS CreateInstructor$$
CREATE PROCEDURE CreateInstructor(	IN pFirstName VARCHAR(255),
									IN pLastName VARCHAR(255),
									IN pEmail VARCHAR(255),
									OUT pResult INT)
BEGIN
	DECLARE EXIT HANDLER FOR 1062
	BEGIN
		SET pResult = -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		SET pResult = -1;
	END;
	
	INSERT INTO Instructor (FirstName, LastName, Email)
	VALUES (pFirstName, pLastName, pEmail);
	
	SET pResult = 0;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS CreateCourse$$
CREATE PROCEDURE CreateCourse(	IN pDept VARCHAR(4),
								IN pCourseNumber INT,
								IN pTitle VARCHAR(255),
								IN pCreditHours INT,
								IN pDescription TEXT,
								IN pStructure TEXT,
								OUT pCourseID INT)
BEGIN
	DECLARE EXIT HANDLER FOR 1062
	BEGIN
		ROLLBACK;
		SET pCourseID = -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		ROLLBACK;
		SET pCourseID = -1;
	END;
	
	START TRANSACTION;
	INSERT INTO Course (Dept,
						CourseNumber,
						Title,
						CreditHours,
						Description,
						Structure)
	VALUES (pDept,
			pCourseNumber,
			pTitle,
			pCreditHours,
			pDescription,
			pStructure);
	
	SELECT LAST_INSERT_ID() INTO pCourseID;
		
	INSERT INTO TermsOffered (CourseID, Summer, Fall, Winter, Spring)
	VALUES (pCourseID, '0', '0', '0', '0');

	COMMIT;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS CreateCourseContent$$
CREATE PROCEDURE CreateCourseContent(	IN pCourseID INT,
										IN pContent VARCHAR(255),
										OUT pResult INT)
BEGIN
	DECLARE EXIT HANDLER FOR 1062
	BEGIN
		SET pResult = -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		SET pResult = -1;
	END;
	
	INSERT INTO CourseContent (CourseID, Content)
	VALUES (pCourseID, pContent);
	
	SET pResult = 0;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS CreateLearningResource$$
CREATE PROCEDURE CreateLearningResource(	IN pCourseID INT,
											IN pResource VARCHAR(255),
											OUT pResult INT)
BEGIN
	DECLARE EXIT HANDLER FOR 1062
	BEGIN
		SET pResult = -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		SET pResult = -1;
	END;
	
	INSERT INTO LearningResources (CourseID, Resource)
	VALUES (pCourseID, pResource);
	
	SET pResult = 0;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS RemoveCourseContent$$
CREATE PROCEDURE RemoveCourseContent(IN pID INT)
BEGIN
	DELETE FROM CourseContent
	WHERE ID=pID;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS RemoveLearningResource$$
CREATE PROCEDURE RemoveLearningResource(IN pID INT)
BEGIN
	DELETE FROM LearningResources
	WHERE ID=pID;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS UpdateTermsOffered$$
CREATE PROCEDURE UpdateTermsOffered(	IN pCourseID INT,
										IN pSummer BOOL,
										IN pFall BOOL,
										IN pWinter BOOL,
										IN pSpring BOOL)
BEGIN
	UPDATE TermsOffered
	SET Summer=pSummer, Fall=pFall, Winter=pWinter, Spring=pSpring
	WHERE CourseID=pCourseID;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS CreateCourseInstance$$
CREATE PROCEDURE CreateCourseInstance(	IN pCourseID INT,
										IN pInstructor VARCHAR(255),
										IN pTermID INT,
										OUT pResult INT)
BEGIN
	DECLARE EXIT HANDLER FOR 1062
	BEGIN
		SET pResult = -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		SET pResult = -1;
	END;
	
	INSERT INTO CourseInstance (CourseID, Instructor, TermID)
	VALUES (pCourseID, pInstructor, pTermID);
	
	SELECT LAST_INSERT_ID() INTO pResult;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS AllowFinalize$$
CREATE PROCEDURE AllowFinalize(IN pTermID INT)
BEGIN
	UPDATE TermState
	SET State='Finalized'
	WHERE TermID=pTermID;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS GetInstructorsByTerm$$
CREATE PROCEDURE GetInstructorsByTerm(IN pTermID INT)
BEGIN
	SELECT DISTINCT
		CONCAT (Instructor.FirstName, ' ', Instructor.LastName) AS Name,
		Instructor.Email
	FROM CourseInstance, Instructor
	WHERE	CourseInstance.TermID=pTermID AND
			Instructor.Email=CourseInstance.Instructor;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS GetUnfinishedCourses$$
CREATE PROCEDURE GetUnfinishedCourses(	IN pInstructor VARCHAR(255),
										IN pTermID INT)
BEGIN
	SELECT DISTINCT	CONCAT(	Course.Dept, ' ', Course.CourseNumber) AS Course,
							CourseInstance.ID AS InstanceID
	FROM CourseInstance, TermState, Course, Instructor
	WHERE	CourseInstance.TermID=TermState.TermID AND
			CourseInstance.State<>TermState.State AND
			TermState.TermID=pTermID AND
			Course.ID=CourseInstance.CourseID AND
			Instructor.Email=pInstructor;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS CreateCLO$$
CREATE PROCEDURE CreateCLO(	IN pCourseID INT,
							IN pDescription TEXT(255),
							IN pOutcomes VARCHAR(255),
							OUT pResult INT)
BEGIN
	DECLARE cloNumber INT DEFAULT 1;
	DECLARE cloID INT DEFAULT -1;
	DECLARE success INT DEFAULT 0;
	DECLARE i INT DEFAULT 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		ROLLBACK;
		SET pResult = 0;
	END;
	
	START TRANSACTION;
	
	SELECT MAX(CLO.CLONumber)+1 AS MaxCLONumber
	INTO cloNumber
	FROM MasterCLO, CLO
	WHERE MasterCLO.CourseID=pCourseID AND MasterCLO.CLOID=CLO.ID;
	
	IF cloNumber IS NULL THEN
		SET cloNumber = 1;
	END IF;
	
	INSERT INTO CLO (CourseID, CLONumber, Description)
	VALUES (pCourseID, cloNumber, pDescription);
	
	SELECT LAST_INSERT_ID() INTO cloID;
	
	INSERT INTO MasterCLO (CLOID, CourseID)
	VALUES (cloID, pCourseID);
	
	label: WHILE i<=CHAR_LENGTH(pOutcomes) DO
		CALL AssociateOutcome(cloID, SUBSTR(pOutcomes FROM i FOR 1), success);
		
		IF success=0 THEN
			ROLLBACK;
			LEAVE label;
		END IF;
		
		SET i = i + 1;
	END WHILE;
	
	COMMIT;
	SET pResult = success;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS AssociateOutcome$$
CREATE PROCEDURE AssociateOutcome(	IN pCLOID INT,
									IN pOutcome CHAR(1),
									OUT pSuccess INT)
BEGIN
	DECLARE outcomeID INT DEFAULT -1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		SET pSuccess = 0;
	END;
	
	SELECT Outcomes.ID
	INTO outcomeID
	FROM Outcomes, CLO, Course
	WHERE	NOT STRCMP(BINARY Outcomes.Outcome, BINARY pOutcome) AND
			Outcomes.Dept=Course.Dept AND
			CLO.CourseID=Course.ID AND
			CLO.ID=pCLOID;
	
	INSERT INTO CLOOutcomes (CLOID, OutcomeID) VALUES (pCLOID, outcomeID);
	
	SET pSuccess = 1;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS GetCourseCLOInformation$$
CREATE PROCEDURE GetCourseCLOInformation(IN pCourseID INT)
BEGIN
	SELECT	MasterCLO.CourseID AS CourseID,
			MasterCLO.CLOID AS CLOID,
			CLO.CLONumber,
			CLO.Description,
			GROUP_CONCAT(
				DISTINCT Outcomes.Outcome
				ORDER BY Outcomes.Outcome ASC SEPARATOR ', ') AS Outcomes
	FROM MasterCLO, CLO, CLOOutcomes, Outcomes
	WHERE	MasterCLO.CourseID=CLO.CourseID AND
			MasterCLO.CLOID=CLO.ID AND
			CLOOutcomes.CLOID=CLO.ID AND
			CLOOutcomes.OutcomeID=Outcomes.ID
	GROUP BY MasterCLO.CourseID, CLOOutcomes.CLOID
	ORDER BY MasterCLO.CourseID, CLO.CLONumber;
END$$
DELIMITER ;