CREATE ALGORITHM=UNDEFINED VIEW CourseInstanceInformation AS
SELECT	CourseInstance.ID AS CourseInstanceID,
		Course.Dept,
		Course.CourseNumber,
		Course.Description,
		CourseInstance.TermID,
		Instructor.Name,
		CourseInstance.State,
		CourseInstance.CommentPrep,
		CourseInstance.CommentPrepActions,
		CourseInstance.CommentChanges,
		CourseInstance.CommentCLO,
		CourseInstance.CommentRecs
FROM Course, CourseInstance, Instructor
WHERE Course.ID = CourseInstance.CourseID
AND Instructor.Email = CourseInstance.Instructor;

CREATE ALGORITHM=UNDEFINED VIEW CourseInstanceCLOInformation AS
SELECT	CourseInstance.ID AS CourseInstanceID,
		CLO.CLONumber,
		CLO.Title,
		CLO.Description,
		GROUP_CONCAT(DISTINCT CLOOutcomes.ABETOutcome ORDER BY CLOOutcomes.ABETOutcome ASC SEPARATOR ', ') AS Outcomes,
		CourseInstanceCLO.Assessed,
		CourseInstanceCLO.MeanScore,
		CourseInstanceCLO.MedianScore,
		CourseInstanceCLO.HighScore,
		CourseInstanceCLO.SatisfactoryScore
FROM CourseInstance, CourseInstanceCLO, CLO, CLOOutcomes
WHERE CourseInstance.CourseID=CLO.CourseID AND CourseInstance.ID=CourseInstanceCLO.CourseInstanceID AND CourseInstanceCLO.CLOID=CLO.ID AND CLOOutcomes.CLOID=CLO.ID
GROUP BY CourseInstance.ID, CLOOutcomes.CLOID
ORDER BY CourseInstance.ID, CLO.CLONumber;
