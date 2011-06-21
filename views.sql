CREATE ALGORITHM=UNDEFINED VIEW CourseInstanceInformation AS
SELECT	CourseInstance.ID AS CourseInstanceID,
		Course.Dept,
		Course.CourseNumber,
		CourseInstance.Term,
		CourseInstance.Year,
		Instructor.Name
FROM Course, CourseInstance, Instructor
WHERE Course.ID = CourseInstance.CourseID
AND Instructor.Email = CourseInstance.Instructor;

CREATE ALGORITHM=UNDEFINED VIEW CourseInstanceCLOInformation AS
SELECT	CourseInstance.ID AS CourseInstanceID,
		CLO.CLONumber,
		CLO.Title,
		CLO.Description,
		GROUP_CONCAT(CLOOutcomes.ABETOutcome SEPARATOR ', ') AS Outcomes,
		CourseInstanceCLO.Assessed,
		CourseInstanceCLO.MeanScore,
		CourseInstanceCLO.MedianScore,
		CourseInstanceCLO.HighScore,
		CourseInstanceCLO.SatisfactoryScore,
		CourseInstanceCLO.State
FROM CourseInstance, CourseInstanceCLO, CLO, CLOOutcomes
WHERE CourseInstance.CourseID=CLO.CourseID AND CourseInstance.ID=CourseInstanceCLO.CourseInstanceID AND CourseInstanceCLO.CLOID=CLO.ID AND CLOOutcomes.CLOID=CLO.ID
GROUP BY CLOOutcomes.CLOID;
