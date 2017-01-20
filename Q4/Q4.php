<!--
Thoughts about solving:
- All addresses provided end in a 5 digit zip code. They can start with a word or number.
- All strings containing an @ are an email address.
- Phone numbers all contain 10 numbers, however they may be -,.,( or space delimited
- Names can be two or three words long, and can contain ' or -, could be all upper cased or first letter caps (though I won't necessarily assume they cannot be lowercased)

The trickiest part is that there is no way to discern between a name an address, when the address starts with a word. Let's say we had

John Van Dixon Ranch to Market 2034, Robert Lee, TX 76945

Here there is no information provided that would allow us to discern between John Van that lives on Dixon Ranch to Market, or John Van Dixon who lives on Ranch to Market. I think a perfect solution that involves no access of real world data sources is not possible (for example something that allowed our code to understand that Van in a name is typically followed by another word, or that Ranch to Market is a valid name of a road in Texas), but we can get pretty close without it.

(**) If we split each line based on things we can almost always identify correctly (emails, phone numbers) then we will be left with one of two scenarios: Either the address and name are adjacent to each other or they are separate. If they are separate, identifying which is the address is extremely easy. If they are not, check if the zip is at the end. If it is not, then whatever comes after the zip is the name. If the name and address are combined, and the zip is at the end (like the example above), then we will take a guess that will be correct most of the time by stripping the first two words and identifying them as the name. 

A more complete solution would be to verify the street name using some API. Fedex or USPS almost definitely have something for this.

Another caveat of the solution is that it assumes only 4 things are on each line. This seems acceptable given that the question prompt promises us that, however in any real production scenario this code either shouldn't rely on that or otherwise be designed to easily be extended (for example if each line of incoming data now included DOB, it should be easier to work from the code to add that).

And lastly, the phone number regex could be made more robust. With more data I would play around with what the results look like and tweak it a bit. There are at least a couple of known formats it would miss, but how relevant those are is hard to say with the limited test set I've been asked to work from. 

SOLUTION:
1) Identify email address
2) Identify phone number. 
3) Use reasoning from (**) to determine where the name and address are in the remainder of the string.
-->
<?php
class Parser {
	//=============== REGEX CONSTANTS ==============//
	const PHONE_REGEX = "/\(?[0-9]{3}\)?(\s|\.|\-)*[0-9]{3}(\s|\.|\-)*[0-9]{4}/";
	const EMAIL_REGEX = "/\b[a-zA-Z0-9_\-\.+]+@[a-zA-Z0-9_\-\.+]+\b/";
	const STATE_AND_ZIP_REGEX = "/\,\s*[a-zA-Z]{2}\s*[0-9]{5}/";

	//=============== INDEX CONSTANTS ===============//
	const START = 'start';
	const LENGTH = 'length';

	//=============== DETAIL CONSTANTS ===============//
	const NAME = 'name';
	const ADDRESS = 'address';
	const PHONE = 'phone';
	const EMAIL = 'email';

	/**
	* From the string for a line in the text, and a regex, get the index of the first match and the length
	**/
	function getIndexAndLength($regex, $string) {
		$containsRegex = preg_match_all($regex, $string, $matches, PREG_OFFSET_CAPTURE);
		if (!$containsRegex) {
			return [];
		}

		$start_index = $matches[0][0][1];
		$length = strlen($matches[0][0][0]);

		return [self::START => $start_index, self::LENGTH => $length];
	}

	/**
	* Once we've identified where in the string a given detail is, use this to update the detail and val arrays
	*/
	function processVals($detail_name, $detail_vals, &$vals, &$details, $string) {
		$details[$detail_name] = substr($string, $detail_vals[self::START], $detail_vals[self::LENGTH]);
		$vals[] = $detail_vals;
	}

	/**
	* Return an array that tells us if any of the members of $vals occur at the start or end of the string
	*/
	function getStartAndEnd($vals, $string) {
		$start = null;
		$end = null;
		$other = [];

		foreach ($vals as $val) {
			if ($val[self::START] == 0) {
				$start = $val;
			} elseif ($val[self::START]+$val[self::LENGTH] == strlen($string)) {
				$end = $val;
			} else {
				$other[] = $val;
			}
		}

		return [self::START => $start, 'end' => $end, 'other' => $other];
	}

	/**
	* Based on where the email and phone are in the string, we can determine where the name and address must be.
	* It is not guaranteed that they'll be in separate, non-consecutive locations so we will still have to 
	* split them later.
	*/
	function getNameAndAddress($startAndEnd, $string) {
		$start = $startAndEnd[self::START];
		$end = $startAndEnd['end'];
		$other = $startAndEnd['other'];

		$nameAndAddr = [];
		if ($start && !$end) {
			//========= one at the start (check between, check after the other) ==========
			$between = trim(substr($string, 
				$start[self::START]+$start[self::LENGTH], 
				$other[0][self::START]-($start[self::START]+$start[self::LENGTH])
			));
			
			//If the phone number and email occur consecutively, the between will be empty and not valuable
			if (!empty($between)) {
				$nameAndAddr[] = $between;
			}
			
			$nameAndAddr[] = trim(substr(
				$string, 
				$other[0][self::START]+$other[0][self::LENGTH]
			));
		} elseif (!$start && $end) {
			//========== one at the end (check between, check before the other) ==========
			$between = trim(substr(
				$string, 
				$other[0][self::START]+$other[0][self::LENGTH], 
				$end[self::START]-($other[0][self::START]+$other[0][self::LENGTH])
			));
			
			//If the phone number and email occur consecutively, the between will be empty and not valuable
			if (!empty($between)) {
				$nameAndAddr[] = $between;
			}

			$nameAndAddr[] = trim(substr(
				$string, 
				0, 
				$other[0][self::START]
			));
		} elseif ($start && $end) {
			//========== one at the start and one at the end (check between the two) ==========
			$nameAndAddr[] = trim(substr(
				$string, 
				$start[self::START]+$start[self::LENGTH], 
				$end[self::START]-($start[self::START]+$start[self::LENGTH])
			));
		} else {
			//========== neither at either end (check before the first, check after the second) ==========

			//Determine which came first between phone and email
			$first = $other[0];
			$second = $other[1];
			if (!($first[self::START] < $second[self::START])) {
				$temp = $first;
				$first = $second;
				$second = $temp;
			}

			$nameAndAddr[] = trim(substr($string, 0, $first[self::START]));
			$nameAndAddr[] = trim(substr($string, $second[self::START]+$second[self::LENGTH]));
		}

		return $nameAndAddr;	
	}

	/**
	* If the nameAndAddr array has two members, we are done. They are already split.
	* If the array has just one member, look for state and zip code combo.
	* If the if the state+zip occurs in the middle, then we can split at the point the zip ends. 
	* If the zip occurs at the end, we assume first two words are the name.
	*/
	function splitNameAndAddress($nameAndAddr, &$details) {
		$name = '';
		$address = '';

		if (sizeof($nameAndAddr) == 1) {
			//Split the string into two
			$nameAndAddrString = $nameAndAddr[0];
			preg_match_all(self::STATE_AND_ZIP_REGEX, $nameAndAddrString, $matches, PREG_OFFSET_CAPTURE);
			if ($matches[0][0][1]+strlen($matches[0][0][0]) == strlen($nameAndAddrString)) {
				//First two words = name
				$split = sscanf($nameAndAddrString,"%s %s %[^$]");
				$name = $split[0].' '.$split[1];
				$address = $split[2];
			} else {
				$address = substr($nameAndAddrString, 0, $matches[0][0][1]+strlen($matches[0][0][0]));
				$name = substr($nameAndAddrString, $matches[0][0][1]+strlen($matches[0][0][0]));
			}
		} elseif (sizeof($nameAndAddr) == 2) {
			$containsStateAndZip = preg_match_all(self::STATE_AND_ZIP_REGEX, $nameAndAddr[0]);
			if ($containsStateAndZip) {
				$address = $nameAndAddr[0];
				$name = $nameAndAddr[1];
			} else {
				$address = $nameAndAddr[1];
				$name = $nameAndAddr[0];
			}
		} //TODO: else -> fail

		$details[self::NAME] = $name;
		$details[self::ADDRESS] = $address;
	}

	function fill_db($details, $db) {
		$name_id = self::NAME;
		$address_id = self::ADDRESS;
		$email_id = self::EMAIL;
		$phone_id = self::PHONE;

		$query = "
			INSERT INTO 
			person(
				$name_id,
				$address_id,
				$email_id,
				$phone_id
			) VALUES(
				\"$details[$name_id]\",
				\"$details[$address_id]\",
				\"$details[$email_id]\",
				\"$details[$phone_id]\"
			)
		";
		/*TEST: Verify query is what I expect it to be
		echo $query;
		echo "<br>";
		$db->exec($query);*/
	}

	/**
	* Execute the parser
	*/
	function run($filename, $db) {
		$handle = @fopen($filename, "r");
		if($handle) {
			while(($buffer = fgets($handle)) !== false) {
				$buffer = trim($buffer);

				//========== FIND PHONE AND EMAIL ==========
				$email_vals = self::getIndexAndLength(self::EMAIL_REGEX, $buffer);
				if(empty($email_vals)) {
					continue;
				}
				$phone_vals = self::getIndexAndLength(self::PHONE_REGEX, $buffer);

				////========== EXTRACT PHONE AND EMAIL ==========
				$vals = [];
				$details = [self::NAME=>'', self::ADDRESS=>'', self::EMAIL=>'', self::PHONE=>''];
				self::processVals(self::EMAIL, $email_vals, $vals, $details, $buffer);
				self::processVals(self::PHONE, $phone_vals, $vals, $details, $buffer);

				//========== FIND/EXTRACT NAME AND ADDRESS ==========
				$startAndEnd = self::getStartAndEnd($vals, $buffer);
				$nameAndAddr = self::getNameAndAddress($startAndEnd, $buffer);
				$splitNameAndAddr = self::splitNameAndAddress($nameAndAddr, $details);

				//========== ENTER NEW ROW INTO DB ==========
				self::fill_db($details, $db);

				/*TEST: Verify details are what I expect them to be
				foreach($details as $detail) {
					echo $detail."<br>";
				}
				echo "<br>";
				echo "<br>";*/
			}
		}	
	}
}

//==================== CREATE A DB ====================
//This is purely for demonstration purposes
//I of course would prefer to use migrations in any real production environment
function open_a_db() {
	$db = new SQLite3("persondb.db");
	$db->exec("
		CREATE TABLE IF NOT EXISTS person 
		(ID INT PRIMARY KEY, 
		NAME varchar(50),
		ADDRESS varchar(250),
		EMAIL varchar(100),
		PHONE varchar(25))
	");
	return $db;
}

//==================== EXECUTION STARTS HERE ==============================
$db = open_a_db();
$Parser = new Parser();
$Parser->run("textparse_exercise.txt", $db);
/* TEST: I verified the contents of the DB from an external application */
?>