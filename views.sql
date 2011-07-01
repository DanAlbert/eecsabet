DROP VIEW IF EXISTS CourseInstanceInformation;
CREATE ALGORITHM=UNDEFINED VIEW CourseInstanceInformation AS
SELECT	CourseInstance.ID AS CourseInstanceID,
		Course.Dept,
		Course.CourseNumber,
		Course.Title,
		Course.CreditHours,
		Course.Description,
		CourseInstance.TermID,
		Instructor.FirstName,
		Instructor.LastName,
		CourseInstance.State,
		CourseInstance.CommentPrep,
		CourseInstance.CommentPrepActions,
		CourseInstance.CommentChanges,
		CourseInstance.CommentCLO,
		CourseInstance.CommentRecs
FROM Course, CourseInstance, Instructor
WHERE Course.ID = CourseInstance.CourseID
AND Instructor.Email = CourseInstance.Instructor;

DROP VIEW IF EXISTS CourseCLOInformation;
CREATE ALGORITHM=UNDEFINED VIEW CourseCLOInformation AS
SELECT	MasterCLO.CourseID AS CourseID,
		MasterCLO.CLOID AS CLOID,
		CLO.CLONumber,
		CLO.Description,
		GROUP_CONCAT(DISTINCT CLOOutcomes.ABETOutcome ORDER BY CLOOutcomes.ABETOutcome ASC SEPARATOR ', ') AS Outcomes
FROM MasterCLO, CLO, CLOOutcomes
WHERE MasterCLO.CourseID=CLO.CourseID AND MasterCLO.CLOID=CLO.ID AND CLOOutcomes.CLOID=CLO.ID
GROUP BY MasterCLO.CourseID, CLOOutcomes.CLOID
ORDER BY MasterCLO.CourseID, CLO.CLONumber;

DROP VIEW IF EXISTS CourseInstanceCLOInformation;
CREATE ALGORITHM=UNDEFINED VIEW CourseInstanceCLOInformation AS
SELECT	CourseInstance.ID AS CourseInstanceID,
		CLO.ID AS CLOID,
		CLO.CLONumber,
		CLO.Description,
		GROUP_CONCAT(DISTINCT CLOOutcomes.ABETOutcome ORDER BY CLOOutcomes.ABETOutcome ASC SEPARATOR ', ') AS Outcomes,
		CourseInstanceCLO.Assessed,
		CourseInstanceCLO.MeanScore,
		CourseInstanceCLO.MedianScore,
		CourseInstanceCLO.HighScore,
		CourseInstanceCLO.SatisfactoryScore
FROM CourseInstance, CourseInstanceCLO, CLO, CLOOutcomes
WHERE	CourseInstance.CourseID=CLO.CourseID AND
		CourseInstance.ID=CourseInstanceCLO.CourseInstanceID AND
		CourseInstanceCLO.CLOID=CLO.ID AND
		CLOOutcomes.CLOID=CLO.ID AND
		CourseInstance.State<>'Sent'
GROUP BY CourseInstance.ID, CLOOutcomes.CLOID
UNION
SELECT	C1.ID AS CourseInstanceID,
		CLO.ID AS CLOID,
		CLO.CLONumber,
		CLO.Description,
		GROUP_CONCAT(DISTINCT CLOOutcomes.ABETOutcome ORDER BY CLOOutcomes.ABETOutcome ASC SEPARATOR ', ') AS Outcomes,
		CourseInstanceCLO.Assessed,
		CourseInstanceCLO.MeanScore,
		CourseInstanceCLO.MedianScore,
		CourseInstanceCLO.HighScore,
		CourseInstanceCLO.SatisfactoryScore
FROM CourseInstance AS C1, CourseInstance AS C2, CourseInstanceCLO, CLO, CLOOutcomes
WHERE	C2.ID = (	SELECT C3.ID
					FROM CourseInstance AS C3
					WHERE C3.TermID=(	SELECT MAX(C4.TermID)
										FROM CourseInstance AS C4
										WHERE	C4.CourseID=C3.CourseID AND
												C3.CourseID=C2.CourseID AND
												C2.CourseID=C1.CourseID AND
												C4.ID<>C1.ID
										GROUP BY C4.CourseID)) AND
		C2.CourseID=CLO.CourseID AND
		C2.ID=CourseInstanceCLO.CourseInstanceID AND
		CourseInstanceCLO.CLOID=CLO.ID AND
		CLOOutcomes.CLOID=CLO.ID AND
		C1.State='Sent'
GROUP BY C1.ID, CLOOutcomes.CLOID
UNION
SELECT	C1.ID AS CourseInstanceID,
		CLO.ID AS CLOID,
		CLO.CLONumber,
		CLO.Description,
		GROUP_CONCAT(DISTINCT CLOOutcomes.ABETOutcome ORDER BY CLOOutcomes.ABETOutcome ASC SEPARATOR ', ') AS Outcomes,
		NULL,
		NULL,
		NULL,
		NULL,
		NULL
FROM CourseInstance AS C1, CourseInstanceCLO, CLO, CLOOutcomes
WHERE	1 = (	SELECT COUNT(*)
				FROM CourseInstance AS C2
				WHERE C2.CourseID=C1.CourseID
				GROUP BY C2.CourseID) AND
		C1.CourseID=CLO.CourseID AND
		C1.ID=CourseInstanceCLO.CourseInstanceID AND
		CourseInstanceCLO.CLOID=CLO.ID AND
		CLOOutcomes.CLOID=CLO.ID AND
		C1.State='Sent'
GROUP BY C1.ID, CLOOutcomes.CLOID
ORDER BY CourseInstanceID, CLONumber;

DROP VIEW IF EXISTS PrerequisiteInformation;
CREATE ALGORITHM=UNDEFINED VIEW PrerequisiteInformation AS
SELECT	Prerequisites.CourseID,
		Prerequisites.PrerequisiteID,
		Prerequisites.IsCorequisite,
		C1.Dept,
		C1.CourseNumber,
		GROUP_CONCAT(DISTINCT CONCAT(C2.Dept, ' ', C2.CourseNumber) ORDER BY C2.Dept ASC, C2.CourseNumber ASC SEPARATOR ', ') AS Alternatives
FROM Prerequisites, PrerequisiteAlternatives, Course AS C1, Course AS C2
WHERE	C1.ID=Prerequisites.PrerequisiteID AND
		C1.ID=PrerequisiteAlternatives.PrerequisiteID AND
		C2.ID=PrerequisiteAlternatives.AlternativeID AND
		Prerequisites.CourseID=PrerequisiteAlternatives.CourseID
GROUP BY Prerequisites.PrerequisiteID, Prerequisites.CourseID
UNION
SELECT	P1.CourseID,
		P1.PrerequisiteID,
		P1.IsCorequisite,
		Course.Dept,
		Course.CourseNumber,
		NULL
FROM Prerequisites AS P1, Course
WHERE	Course.ID=P1.PrerequisiteID AND
		Course.ID NOT IN (	SELECT Prerequisites.PrerequisiteID
							FROM Prerequisites, PrerequisiteAlternatives, Course AS C1, Course AS C2
							WHERE	C1.ID=Prerequisites.PrerequisiteID AND
									C1.ID=PrerequisiteAlternatives.PrerequisiteID AND
									C2.ID=PrerequisiteAlternatives.AlternativeID AND
									Prerequisites.CourseID=PrerequisiteAlternatives.CourseID AND
									P1.CourseID=Prerequisites.CourseID)
ORDER BY Dept ASC, CourseNumber ASC;

DROP VIEW IF EXISTS CourseInformation;
CREATE ALGORITHM=UNDEFINED VIEW CourseInformation AS
SELECT	C1.ID AS CourseID,
		C1.Dept,
		C1.CourseNumber,
		C1.Title,
		C1.CreditHours,
		C1.Description,
		C1.Structure,
		TermsOffered.Summer,
		TermsOffered.Fall,
		TermsOffered.Winter,
		TermsOffered.Spring,
		GROUP_CONCAT(DISTINCT CONCAT(C2.Dept, ' ', C2.CourseNumber) ORDER BY C2.Dept, C2.CourseNumber SEPARATOR ', ') AS Prerequisites,
		GROUP_CONCAT(DISTINCT CONCAT(C3.Dept, ' ', C3.CourseNumber) ORDER BY C3.Dept, C3.CourseNumber SEPARATOR ', ') AS Corequisites
FROM Course AS C1 LEFT OUTER JOIN Prerequisites AS P1 ON C1.ID=P1.CourseID LEFT OUTER JOIN Prerequisites AS P2 ON C1.ID=P2.CourseID, Course AS C2, Course AS C3, TermsOffered
WHERE TermsOffered.CourseID=C1.ID AND P1.IsCorequisite='0' AND P1.PrerequisiteID=C2.ID AND P2.PrerequisiteID=C3.ID AND P2.IsCorequisite='1'
GROUP BY C1.ID
UNION
SELECT	C1.ID AS CourseID,
		C1.Dept,
		C1.CourseNumber,
		C1.Title,
		C1.CreditHours,
		C1.Description,
		C1.Structure,
		TermsOffered.Summer,
		TermsOffered.Fall,
		TermsOffered.Winter,
		TermsOffered.Spring,
		GROUP_CONCAT(DISTINCT CONCAT(C2.Dept, ' ', C2.CourseNumber) ORDER BY C2.Dept, C2.CourseNumber SEPARATOR ', ') AS Prerequisites,
		NULL
FROM Course AS C1 LEFT OUTER JOIN Prerequisites AS P1 ON P1.CourseID=C1.ID, Course AS C2, TermsOffered
WHERE C1.ID=TermsOffered.CourseID AND P1.IsCorequisite='0' AND P1.PrerequisiteID=C2.ID AND C1.ID NOT IN (
	SELECT	C1.ID AS CourseID
	FROM Course AS C1 LEFT OUTER JOIN Prerequisites AS P1 ON C1.ID=P1.CourseID LEFT OUTER JOIN Prerequisites AS P2 ON C1.ID=P2.CourseID, Course AS C2, Course AS C3, TermsOffered
	WHERE TermsOffered.CourseID=C1.ID AND P1.IsCorequisite='0' AND P1.PrerequisiteID=C2.ID AND P2.PrerequisiteID=C3.ID AND P2.IsCorequisite='1')
GROUP BY C1.ID
UNION
SELECT	C1.ID AS CourseID,
		C1.Dept,
		C1.CourseNumber,
		C1.Title,
		C1.CreditHours,
		C1.Description,
		C1.Structure,
		TermsOffered.Summer,
		TermsOffered.Fall,
		TermsOffered.Winter,
		TermsOffered.Spring,
		NULL,
		GROUP_CONCAT(DISTINCT CONCAT(C2.Dept, ' ', C2.CourseNumber) ORDER BY C2.Dept, C2.CourseNumber SEPARATOR ', ') AS Prerequisites		
FROM Course AS C1 LEFT OUTER JOIN Prerequisites AS P1 ON P1.CourseID=C1.ID, Course AS C2, TermsOffered
WHERE C1.ID=TermsOffered.CourseID AND P1.IsCorequisite='1' AND P1.PrerequisiteID=C2.ID AND C1.ID NOT IN (
	SELECT	C1.ID AS CourseID
	FROM Course AS C1 LEFT OUTER JOIN Prerequisites AS P1 ON C1.ID=P1.CourseID LEFT OUTER JOIN Prerequisites AS P2 ON C1.ID=P2.CourseID, Course AS C2, Course AS C3, TermsOffered
	WHERE TermsOffered.CourseID=C1.ID AND P1.IsCorequisite='0' AND P1.PrerequisiteID=C2.ID AND P2.PrerequisiteID=C3.ID AND P2.IsCorequisite='1')
GROUP BY C1.ID
UNION
SELECT	C1.ID AS CourseID,
		C1.Dept,
		C1.CourseNumber,
		C1.Title,
		C1.CreditHours,
		C1.Description,
		C1.Structure,
		TermsOffered.Summer,
		TermsOffered.Fall,
		TermsOffered.Winter,
		TermsOffered.Spring,
		P1.PrerequisiteID,
		P2.PrerequisiteID
FROM Course AS C1 LEFT OUTER JOIN Prerequisites AS P1 ON P1.CourseID=C1.ID LEFT OUTER JOIN Prerequisites AS P2 ON P2.CourseID=C1.ID, TermsOffered
WHERE C1.ID=TermsOffered.CourseID AND P1.PrerequisiteID IS NULL AND P2.PrerequisiteID IS NULL
ORDER BY Dept, CourseNumber;

DROP VIEW IF EXISTS NaggingInformation;
CREATE ALGORITHM=UNDEFINED VIEW NaggingInformation AS
SELECT DISTINCT	Instructor.FirstName,
				Instructor.LastName,
				Instructor.Email,
				CONCAT(Course.Dept, ' ', Course.CourseNumber) AS Course,
				CourseInstance.ID AS InstanceID,
				CourseInstance.State AS State
FROM CourseInstance, TermState, Course, Instructor
WHERE	CourseInstance.TermID=TermState.TermID AND
		CourseInstance.State<(SELECT State+0 FROM TermState WHERE TermID=CourseInstance.TermID) AND
		TermState.TermID=(SELECT MAX(TermID) FROM TermState) AND
		Course.ID=CourseInstance.CourseID AND
		Instructor.Email=CourseInstance.Instructor;

DROP VIEW IF EXISTS TermStateInformation;
CREATE ALGORITHM=UNDEFINED VIEW TermStateInformation AS
SELECT	CourseInstance.TermID AS Term,
		TermState.State AS State
FROM CourseInstance, TermState
WHERE CourseInstance.TermID=TermState.TermID;

DROP VIEW IF EXISTS CurrentTermStateInformation;
CREATE ALGORITHM=UNDEFINED VIEW CurrentTermStateInformation AS
SELECT	Term AS CurrentTerm,
		State AS CurrentState
FROM TermStateInformation
WHERE CurrentTerm=(SELECT MAX(TermID) FROM TermState);

DROP VIEW IF EXISTS PrerequisiteAlternativesInformation;
CREATE ALGORITHM=UNDEFINED VIEW PrerequisiteAlternativesInformation AS
SELECT DISTINCT	C1.ID AS CourseID,
				GROUP_CONCAT(DISTINCT CONCAT(C2.Dept, ' ', C2.CourseNumber) SEPARATOR ' or ') AS Prerequisite,
				Prerequisites.IsCorequisite
FROM Prerequisites, PrerequisiteAlternatives, Course AS C1, Course AS C2, Course AS C3
WHERE	C1.ID=PrerequisiteAlternatives.CourseID AND
		C1.ID=Prerequisites.CourseID AND
		C2.ID=Prerequisites.PrerequisiteID AND
		C2.ID=PrerequisiteAlternatives.PrerequisiteID AND
		C3.ID=PrerequisiteAlternatives.AlternativeID
GROUP BY C1.ID
UNION
SELECT DISTINCT	C1.ID AS CourseID,
				CONCAT(C2.Dept, ' ', C2.CourseNumber) AS Prerequisite,
				Prerequisites.IsCorequisite
FROM Prerequisites, Course AS C1, Course AS C2
WHERE	C1.ID=Prerequisites.CourseID AND
		C2.ID=Prerequisites.PrerequisiteID AND
		C2.ID NOT IN (	SELECT DISTINCT	C2.ID AS CourseID
						FROM	Prerequisites,
								PrerequisiteAlternatives,
								Course AS C1,
								Course AS C2,
								Course AS C3
						WHERE	C1.ID=PrerequisiteAlternatives.CourseID AND
								C1.ID=Prerequisites.CourseID AND
								C2.ID=Prerequisites.PrerequisiteID AND
								C2.ID=PrerequisiteAlternatives.PrerequisiteID AND
								C3.ID=PrerequisiteAlternatives.AlternativeID);
