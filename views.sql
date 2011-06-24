CREATE ALGORITHM=UNDEFINED VIEW CourseInstanceInformation AS
SELECT	CourseInstance.ID AS CourseInstanceID,
		Course.Dept,
		Course.CourseNumber,
		Course.CreditHours,
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

CREATE ALGORITHM=UNDEFINED VIEW CourseCLOInformation AS
SELECT	MasterCLO.CourseID AS CourseID,
		MasterCLO.CLOID AS CLOID,
		CLO.CLONumber,
		CLO.Title,
		CLO.Description,
		GROUP_CONCAT(DISTINCT CLOOutcomes.ABETOutcome ORDER BY CLOOutcomes.ABETOutcome ASC SEPARATOR ', ') AS Outcomes
FROM MasterCLO, CLO, CLOOutcomes
WHERE MasterCLO.CourseID=CLO.CourseID AND MasterCLO.CLOID=CLO.ID AND CLOOutcomes.CLOID=CLO.ID
GROUP BY MasterCLO.CourseID, CLOOutcomes.CLOID
ORDER BY MasterCLO.CourseID, CLO.CLONumber;

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

CREATE ALGORITHM=UNDEFINED VIEW PrerequisiteInformation AS
SELECT	Prerequisites.CourseID,
		Prerequisites.PrerequisiteID,
		Prerequisites.IsCorequisite,
		Course.Dept,
		Course.CourseNumber
FROM Prerequisites, Course
WHERE Course.ID=Prerequisites.PrerequisiteID
ORDER BY Course.Dept ASC, Course.CourseNumber ASC;

CREATE ALGORITHM=UNDEFINED VIEW CourseInformation AS
SELECT	C1.ID,
		C1.Dept,
		C1.CourseNumber,
		C1.CreditHours,
		TermsOffered.Summer,
		TermsOffered.Fall,
		TermsOffered.Winter,
		TermsOffered.Spring,
		GROUP_CONCAT(DISTINCT CONCAT(C2.Dept, ' ', C2.CourseNumber) ORDER BY C2.Dept, C2.CourseNumber SEPARATOR ', ') AS Prerequisites,
		Prerequisites.IsCorequisite
FROM Course AS C1, Course AS C2, Prerequisites, TermsOffered
WHERE TermsOffered.CourseID=C1.ID AND Prerequisites.CourseID=C1.ID AND Prerequisites.PrerequisiteID=C2.ID
GROUP BY C1.ID, Prerequisites.IsCorequisite
ORDER BY C1.Dept ASC, C1.CourseNumber ASC;

/*CREATE ALGORITHM=UNDEFINED VIEW CourseInformation AS
SELECT	C1.ID,
		C1.Dept,
		C1.CourseNumber,
		C1.CreditHours,
		TermsOffered.Summer,
		TermsOffered.Fall,
		TermsOffered.Winter,
		TermsOffered.Spring,
		GROUP_CONCAT(DISTINCT CONCAT(C2.Dept, ' ', C2.CourseNumber) ORDER BY C2.Dept, C2.CourseNumber SEPARATOR ', ') AS Prerequisites,
		GROUP_CONCAT(DISTINCT CONCAT(C3.Dept, ' ', C3.CourseNumber) ORDER BY C3.Dept, C3.CourseNumber SEPARATOR ', ') AS Corequisites
FROM Course AS C1, Course AS C2, Course AS C3, Prerequisites, TermsOffered
WHERE TermsOffered.CourseID=C1.ID AND Prerequisites.CourseID=C1.ID AND ((Prerequisites.PrerequisiteID=C2.ID AND Prerequisites.IsCorequisite='0') OR (Prerequisites.PrerequisiteID=C3.ID AND Prerequisites.IsCorequisite='1')) AND C2.ID<>C3.ID
GROUP BY C1.ID
ORDER BY C1.Dept ASC, C1.CourseNumber ASC;*/

Prereq.CID	|	Prereq.PID	|	Prereq.CRec	|	C2.ID	|	C3.ID
272			|	112			|	0			|	112		|	112
272			|	112			|	0			|	271		|	112
272			|	112			|	0			|	272		|	112
272			|	271			|	1			|	112		|	112
272			|	271			|	1			|	271		|	112
272			|	271			|	1			|	272		|	112
272			|	112			|	0			|	112		|	271
272			|	112			|	0			|	271		|	271
272			|	112			|	0			|	272		|	271
272			|	271			|	1			|	112		|	271
272			|	271			|	1			|	271		|	271
272			|	271			|	1			|	272		|	271
272			|	112			|	0			|	112		|	272
272			|	112			|	0			|	271		|	272
272			|	112			|	0			|	272		|	272
272			|	271			|	1			|	112		|	272
272			|	271			|	1			|	271		|	272
272			|	271			|	1			|	272		|	272