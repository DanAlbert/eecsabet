CREATE TABLE Instructor
(
	Email VARCHAR(255),
	Name VARCHAR(255),
	PRIMARY KEY (Email)
) ENGINE=InnoDB;

CREATE TABLE Course
(
	ID INT AUTO_INCREMENT NOT NULL,
	Dept VARCHAR(4),
	CourseNumber INT,
	PRIMARY KEY(ID),
	UNIQUE (Dept, CourseNumber)
) ENGINE=InnoDB;

-- If multiple instructors for same term, do we need a separate instance?
-- Will the different sections have different metrics?
-- TODO: Needs Comments and State
CREATE TABLE CourseInstance
(
	ID INT AUTO_INCREMENT NOT NULL,
	CourseID INT NOT NULL,
	Instructor VARCHAR(255) NOT NULL,
	Term SET('Fall', 'Winter', 'Spring', 'Summer') NOT NULL,
	Year INT NOT NULL,
	State SET ('Sent', 'Viewed', 'Approved', 'Ready', 'Finalized'),
	Comments TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (ID),
	UNIQUE (CourseID, Instructor, Term, Year),
	FOREIGN KEY (CourseID) REFERENCES Course (ID),
	FOREIGN KEY (Instructor) REFERENCES Instructor (Email)
) ENGINE=InnoDB;

CREATE TABLE CLO
(
	ID INT AUTO_INCREMENT NOT NULL,
	CourseID INT,
	CLONumber INT,
	Title VARCHAR(255),
	Description TEXT,
	PRIMARY KEY (ID),
	FOREIGN KEY (CourseID) REFERENCES Course (ID)
) ENGINE=InnoDB;

CREATE TABLE CLOOutcomes
(
	CLOID INT NOT NULL,
	ABETOutcome CHAR(1) NOT NULL,
	PRIMARY KEY (CLOID, ABETOutcome),
	FOREIGN KEY (CLOID) REFERENCES CLO (ID)
) ENGINE=InnoDB;

CREATE TABLE MasterCLO
(
	CLOID INT NOT NULL,
	CourseID INT NOT NULL,
	PRIMARY KEY (CLOID, CourseID),
	FOREIGN KEY (CLOID) REFERENCES CLO (ID),
	FOREIGN KEY (CourseID) REFERENCES Course (ID)
) ENGINE=InnoDB;

CREATE TABLE CourseInstanceCLO
(
	CLOID INT NOT NULL,
	CourseInstanceID INT NOT NULL,
	Assessed VARCHAR(255),
	MeanScore INT,
	MedianScore INT,
	HighScore INT,
	SatisfactoryScore INT,
	PRIMARY KEY (CLOID, CourseInstanceID),
	FOREIGN KEY (CLOID) REFERENCES CLO (ID),
	FOREIGN KEY (CourseInstanceID) REFERENCES CourseInstance (ID)
) ENGINE=InnoDB;