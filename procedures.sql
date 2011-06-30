DELIMITER $$
DROP PROCEDURE IF EXISTS CreateInstructor$$
CREATE PROCEDURE CreateInstructor(	IN pFirstName VARCHAR(255),
									IN pLastName VARCHAR(255),
									IN pEmail VARCHAR(255))
BEGIN
	DECLARE EXIT HANDLER FOR 1062
	BEGIN
		ROLLBACK;
		SELECT -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		SELECT -1;
	END;
	
	INSERT INTO Instructor (FirstName, LastName, Email) VALUES (pFirstName, pLastName, pEmail);
	SELECT 1;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS CreateCourse$$
CREATE PROCEDURE CreateCourse(	IN pDept VARCHAR(4),
								IN pCourseNumber INT,
								IN pTitle VARCHAR(255),
								IN pCreditHours INT,
								IN pDescription TEXT,
								IN pStructure TEXT)
BEGIN
	DECLARE courseID INT DEFAULT -1;
	
	DECLARE EXIT HANDLER FOR 1062
	BEGIN
		ROLLBACK;
		SELECT -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		ROLLBACK;
		SELECT -1;
	END;
	
	START TRANSACTION;
	INSERT INTO Course (Dept, CourseNumber, Title, CreditHours, Description, Structure)
	VALUES (pDept, pCourseNumber, pTitle, pCreditHours, pDescription, pStructure);
	
	SELECT ID INTO courseID FROM Course WHERE Dept=pDept AND CourseNumber=pCourseNumber ORDER BY ID ASC;
		
	INSERT INTO TermsOffered (CourseID, Summer, Fall, Winter, Spring) VALUES (courseID, '0', '0', '0', '0');

	COMMIT;
	SELECT courseID;

END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS CreateCourseContent$$
CREATE PROCEDURE CreateCourseContent(	IN pCourseID INT,
										IN pContent VARCHAR(255))
BEGIN
	DECLARE EXIT HANDLER FOR 1062
	BEGIN
		ROLLBACK;
		SELECT -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		ROLLBACK;
		SELECT -1;
	END;
	
	INSERT INTO CourseContent (CourseID, Content) VALUES (pCourseID, pContent);
	SELECT 1;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS CreateLearningResource$$
CREATE PROCEDURE CreateLearningResource(	IN pCourseID INT,
											IN pResource VARCHAR(255))
BEGIN
	DECLARE EXIT HANDLER FOR 1062
	BEGIN
		ROLLBACK;
		SELECT -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		ROLLBACK;
		SELECT -1;
	END;
	
	INSERT INTO LearningResources (CourseID, Resource) VALUES (pCourseID, pResource);
	SELECT 1;
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
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		ROLLBACK;
		SELECT -1;
	END;
	
	UPDATE TermsOffered SET Summer=pSummer, Fall=pFall, Winter=pWinter, Spring=pSpring WHERE CourseID=pCourseID;
	SELECT 1;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS RemoveCourseContent$$
CREATE PROCEDURE RemoveCourseContent(IN pID INT)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		ROLLBACK;
		SELECT -1;
	END;
	
	DELETE FROM CourseContent WHERE ID=pID;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS RemoveLearningResource$$
CREATE PROCEDURE RemoveLearningResource(IN pID INT)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		ROLLBACK;
		SELECT -1;
	END;
	
	DELETE FROM RemoveLearningResource WHERE ID=pID;
END$$
DELIMITER ;
