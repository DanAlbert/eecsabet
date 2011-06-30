DELIMITER $$
DROP PROCEDURE IF EXISTS CreateInstructor$$
CREATE PROCEDURE CreateInstructor(	IN pFirstName VARCHAR(255),
									IN pLastName VARCHAR(255),
									IN pEmail VARCHAR(255))
BEGIN
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
	INSERT INTO SyllabusTimestamp (CourseID) VALUES (courseID);

	COMMIT;
	SELECT courseID;

END$$
DELIMITER ;