# Bisnow-Media-Coding-Exercise
EDIT (08/14/2017):  This was a set of challenges I completed when interviewing for the position at Bisnow. I was told I was given a perfect score on all parts, and wound up working for Bisnow.

The code for questions 4 and 5 can be found in the Q4 and Q5 folders respectively. 
The answers to questions 1-3 can be found in their respective Q1,Q2,Q3 .txt files.

QUESTIONS:
1) You send roughly 30 Million emails monthly through an email infrastructure service similar to SendGrid and Mandrill, called SparkPost.  SparkPost sends the message events associated with those email sends back to you via a post web hook so you can store and utilize them at a later date. Attached to this email is an example json body that you would receive from Sparkpost. You can read a description of the events included in this json here: https://support.sparkpost.com/customer/portal/articles/1976204-webhook-event-reference.

Please describe the full stack of technologies you would use to receive the http requests webhook, process and store the data, and query these records in a meaningful way in the future. 

Which fields seem like they would be important for customer analytics? Which might be important for other reasons? What reasons?

------------------------------------------------------------------------------------------

2) The following two questions do not necessarily have a specific right or wrong answer, thus the how and why are important. What type of class or OOP programming structure would make sense to use in the following scenarios? How and Why?

2a. You are building an open source application framework, to handle sessions you would like to use Memcache by default but allow others to create modules for other session handling services.
2b. You have many classes which need to share some methods, some of these classes already extend another unrelated class, some do not.

------------------------------------------------------------------------------------------

3) You have a Mysql table with 500 Million rows. The structure is the following:

CREATE TABLE `buildings` (

 `id` int(11) NOT NULL AUTO_INCREMENT,

 `name` varchar(255) NOT NULL,

 `type` enum('Highrise','Lowrise','Retail','Industrial') NOT NULL,

`city` varchar(100) NOT NULL,

 PRIMARY KEY (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8

A sample query that would often need to run on the database is “SELECT * FROM posts WHERE type = ? AND city = ? LIMIT 500000”. Would you add an index or indices to this table other than the primary index? What are the pros and cons of doing so?

Assuming there are no other related tables or different querying scenarios, do you think mysql is an optimal approach here? Why or why not and what might an alternative be?

------------------------------------------------------------------------------------------

4) You have ten thousand strings of data separated by new lines \n in a text document. Contained within each string in no particular order is the following separated by whitespace:
A first and last name
A phone number
An email
A street address, including a numeric address number, street/avenue name, city and zip

Use php to Make a script which parses the file and separates the above fields from each row, storing them in a SQLite database. I have included an attachment with some sample data that you can use as a test to parse against.

------------------------------------------------------------------------------------------


5. Setup an HTML page with a header, navigation bar, content section and footer
	- In the header, center the words "Number Test"
	- In the navigation bar, have links to Google.com, Bisnow.com
	- In the footer on the left, place the words "Bisnow Media 2016"
	- In the footer on the right, place your name
	
5a. In the content section, create a form with 1 input field and a submit field
5b. In the input field, a user will enter a number between 1-1000. The form, using jQuery/AJAX should query a separate PHP file which does the following:
- Validate the user input
- If input is a multiple of three, return “Bisnow”
- If input is a multiple of five, return “Media”
- If input is a multiple of three and five, return “Bisnow Media“
- Save the user’s input to a MySQL tracking table

5c. When a user submits the form, the submit button should show a loading graphic. Users cannot submit the form again if input is valid.
5d. If input is not a number or outside the range, show an error on the form
5e. If the number is in the range, append the result from step 3 to the beginning of the form
