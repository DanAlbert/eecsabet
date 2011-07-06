DELIMITER $$
DROP PROCEDURE IF EXISTS CreateInstructor$$
CREATE PROCEDURE CreateInstructor(	IN pFirstName VARCHAR(255),
									IN pLastName VARCHAR(255),
									IN pEmail VARCHAR(255))
BEGIN
	DECLARE EXIT HANDLER FOR 1062
	BEGIN
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
		SELECT -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
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
		SELECT -2;
	END;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
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
		SELECT -1;
	END;
	
	DELETE FROM CourseContent WHERE ID=pID;
	SELECT 1;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS RemoveLearningResource$$
CREATE PROCEDURE RemoveLearningResource(IN pID INT)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		SELECT -1;
	END;
	
	DELETE FROM LearningResources WHERE ID=pID;
	SELECT 1;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS CreateCourseInstance$$
CREATE PROCEDURE CreateCourseInstance(	IN pCourseID INT,
										IN pInstructor VARCHAR(255),
										IN pTermID INT)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		SELECT -1;
	END;
	
	INSERT INTO CourseInstance (CourseID, Instructor, TermID) VALUES (pCourseID, pInstructor, pTermID);
	SELECT 1;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS AllowFinalize$$
CREATE PROCEDURE AllowFinalize(IN pTermID INT)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
		SELECT -1;
	END;
	
	UPDATE TermState SET State='Finalized' WHERE TermID=pTermID;
	SELECT 1;
END$$
DELIMITER ;

DELIMITER $$
DROP PROCEDURE IF EXISTS GetInstructorsByTerm$$
CREATE PROCEDURE GetInstructorsByTerm(IN pTermID INT)
BEGIN
	SELECT DISTINCT	CONCAT (Instructor.FirstName, ' ', Instructor.LastName) AS Name,
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
