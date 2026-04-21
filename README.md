sawmen mitra (232014071)  software engineering 


Pre-Edu Platform
API Reference Documentation
Base URL: /pre_edu_platform	Format: application/json	Auth: Session + CSRF	DB: MySQL / MariaDB


1. Authentication Flow
All state-mutating endpoints require a valid PHP session and a CSRF token. Follow these steps in order:

•	Step 1 — Call GET /get_csrf_token.php to start a session and receive a CSRF token.
•	Step 2 — POST to /register.php or /login.php with credentials and the CSRF token. On success, parent_id is stored in the session.
•	Step 3 — All subsequent requests must include the session cookie (automatic in browsers) and csrf_token in the JSON body.

Security Note: Session cookies are HttpOnly. The CSRF token is stored server-side in $_SESSION['csrf_token'] and validated on every protected endpoint.

2. API Endpoints
2.1  Get CSRF Token

GET	/get_csrf_token.php

Starts a PHP session and returns (or reuses) the CSRF token. Must be called before any write operation.

Response — 200 OK
{
  "csrf_token": "a3f9e2...64 hex characters"
}

2.2  Register

POST	/register.php  →  /api/auth/register.php

Creates a new parent account. The password is hashed with bcrypt before storage.

Request Body  (application/json)
Field	Type	Required	Description
name	string	required	Parent's full name
email	string	required	Valid email address — must be unique
password	string	required	Minimum 8 characters
csrf_token	string	required	Token from /get_csrf_token.php

Example Request
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "secret123",
  "csrf_token": "a3f9e2..."
}

Responses
Status	Description
201 Created	Registration successful — returns { success: true, id, message }
409 Conflict	Email already registered
422 Unprocessable	Missing fields / invalid email / password shorter than 8 chars
403 Forbidden	Invalid or missing CSRF token
405 Method Not Allowed	Non-POST request received
500 Internal Server Error	Database insertion failure

2.3  Login

POST	/login.php  →  /api/auth/login.php

Authenticates the parent and starts an authenticated session. On success the session is regenerated and parent_id is stored.

Request Body  (application/json)
Field	Type	Required	Description
email	string	required	Registered email address
password	string	required	Account password
csrf_token	string	required	Token from /get_csrf_token.php

Example Request
{
  "email": "jane@example.com",
  "password": "secret123",
  "csrf_token": "a3f9e2..."
}

Responses
Status	Description
200 OK	Login successful — returns { success: true, id, name }; session cookie is set
401 Unauthorized	Incorrect password
404 Not Found	No account found for the provided email
422 Unprocessable	One or more required fields are missing
403 Forbidden	Invalid or missing CSRF token
405 Method Not Allowed	Non-POST request received

Session Regeneration: On success, session_regenerate_id(true) is called to prevent session fixation. The existing CSRF token is preserved across the regeneration.

2.4  Check Session

POST	/check_session.php

Verifies the parent is logged in and retrieves the name of a specific child, confirming ownership.

Request Body  (application/x-www-form-urlencoded or JSON)
Field	Type	Required	Description
childId	integer	required	ID of the child to look up

Responses
Status	Description
200 — success	Child found: { status: "success", childName: "Tasbe" }
200 — error	Not logged in: { status: "error", message: "Not logged in" }
200 — error	Child not found or not owned by parent: { status: "error", message: "Child not found" }

Note: This endpoint returns HTTP 200 in all cases; check the 'status' field in the JSON body to determine success or failure.

2.5  Save Child (Upsert)

POST	/save_child.php

Creates or updates a child profile under the authenticated parent. Uses an upsert strategy: if a child with the given name already exists under the parent, screen_time is updated; otherwise a new record is created along with a matching stars row.

Request Body  (application/json)
Field	Type	Required	Description
childName	string	required	Child's name
screenTime	integer	required	Daily screen time limit in minutes (must be >= 0)
csrf_token	string	required	Current session CSRF token

Example Request
{
  "childName": "Tasbe",
  "screenTime": 30,
  "csrf_token": "a3f9e2..."
}

Responses
Status	Description
200 — success	{ status: "success", child_id: 5, child_name: "Tasbe" }
200 — error	Not logged in, invalid input, or invalid CSRF token — see message field

Upsert Logic: Looks up children by parent_id + name. If found, UPDATE screen_time. If not found, INSERT child row then INSERT into stars table with earned_stars = 0.

2.6  Log Challenge Completion

POST	/log_challenge_completion.php

Records a child's completion of a daily challenge and awards stars. Requires the parent session, a valid child (owned by the parent), a valid challenge, and a non-negative star count.

Request Body  (application/json)
Field	Type	Required	Description
child_id	integer	required	ID of the child (must belong to the session parent)
challenge_id	integer	required	ID of the daily_challenges record
stars	integer	required	Stars earned for this completion (>= 0)
csrf_token	string	required	Current session CSRF token

Example Request
{
  "child_id": 4,
  "challenge_id": 1,
  "stars": 1,
  "csrf_token": "a3f9e2..."
}

Responses
Status	Description
200 — success	{ status: "success" }
200 — error	Validation failures: invalid IDs, missing CSRF, session expired, child/challenge not found
200 — error	DB write failure — { status: "error", message: "..." }

Database Side Effects
Table	Operation	Description
challenge_progress	INSERT	Inserts a row with child_id, challenge_id, stars_earned, and completed_at timestamp
stars	UPSERT	Inserts or increments earned_stars for the child using ON DUPLICATE KEY UPDATE
children	UPDATE	Increments children.stars by the awarded amount

3. Database Schema
Database: pre_edu_platform  |  Engine: InnoDB  |  Charset: utf8mb4

parents
Column	Type	Constraint
id	int(11)	PRIMARY KEY, AUTO_INCREMENT
name	varchar(100)	NOT NULL
email	varchar(100)	NOT NULL, UNIQUE
password	varchar(255)	NOT NULL — bcrypt hash

children
Column	Type	Constraint
id	int(11)	PRIMARY KEY, AUTO_INCREMENT
parent_id	int(11)	FOREIGN KEY → parents(id) ON DELETE CASCADE
name	varchar(100)	Child name
screen_time	int(11)	Daily screen time limit in minutes
stars	int(11)	DEFAULT 0 — cumulative stars earned

daily_challenges
Column	Type	Constraint
id	int(11)	PRIMARY KEY, AUTO_INCREMENT
title	varchar(255)	Challenge title
description	text	HTML description of the challenge
date	date	Optional date association

challenge_progress
Column	Type	Constraint
id	int(11)	PRIMARY KEY, AUTO_INCREMENT
child_id	int(11)	FOREIGN KEY → children(id) ON DELETE CASCADE
challenge_id	int(11)	FOREIGN KEY → daily_challenges(id) ON DELETE CASCADE
stars_earned	int(11)	Stars awarded for this completion
completed_at	timestamp	DEFAULT current_timestamp()

stars
Column	Type	Constraint
child_id	int(11)	PRIMARY KEY, FOREIGN KEY → children(id) ON DELETE CASCADE
earned_stars	int(11)	DEFAULT 0 — aggregate stars (ON DUPLICATE KEY UPDATE)

Cascade Deletes: Deleting a parent cascades to their children, which cascade to challenge_progress and stars. Deleting a daily_challenges row cascades to all related challenge_progress rows.
