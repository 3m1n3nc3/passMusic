<?php
//======================================================================\\
// Passengine 1.0 - Php templating engine and framework                 \\
// Copyright © Passcontest. All rights reserved.                        \\
//----------------------------------------------------------------------\\
// http://www.passcontest.com/                                          \\
//======================================================================\\

use Gumlet\ImageResize;

$framework = new framework; 
$databaseCL = new databaseCL;   

//Fetch settings from database
function configuration() {
	global $framework;
	$sql = "SELECT * FROM ".TABLE_CONFIG; 
    return $framework->dbProcessor($sql, 1)[0];
}

/**
 * This class holds all major functions of this framework
 */
class framework extends Not_CIClass {
	public $username; 
	public $email;
    public $password;
	public $remember;
	public $firstname;
	public $lastname;
	public $city;
	public $state;
	public $country;
	public $phone;
    public $user;

	function userData($user = NULL, $type = NULL) {
        // if type = 0 fetch all users, and use filter to add custom query
        // if type = 1 users by their user ids or fetch users by their usernames
        // if type = 10 fetch users for datatables

	    global $configuration;

	    // Limit clause to enable pagination
        if (isset($this->limited)) {
            $limit = sprintf(' LIMIT %s', $this->limited);
        } elseif (isset($this->limit) || isset($this->limiter)) {
        	$limiter = isset($this->limiter) ? $this->limiter : $this->limit;
            $limit = sprintf(' ORDER BY uid DESC LIMIT %s, %s', $this->start, $limiter);
        } else {
            $limit = '';
        }
        $filter = isset($this->filter) ? $this->filter : '';

        if (isset($this->search)) {            //Search instance
	    	$search = $this->search; 	
	    	$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE username LIKE '%s' OR concat_ws(' ', `f_name`, `l_name`) LIKE '%s' OR country LIKE '%s' OR role LIKE '%s' LIMIT %s", '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', $configuration['data_limit']);
        } elseif ($type === 0) {
            $sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE 1%s%s", $filter, $limit);
        } elseif ($type === 1) {
	    	$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE uid = '%s' OR `username` = '%s'", $user, $user); 
	    }  elseif ($type === 3) {
	    	$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE 1%s", $limit);
	    } else {
	    	// if the username is an email address
	    	if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
                $sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE email = '%s'", $user);
	    	} else {
                $sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE username = '%s'", $user);
	    	}
	    }
	    $this->filter = $this->limited = $this->limiter = $this->limit = null;
        // Process the information
        $results = $this->dbProcessor($sql, 1);
        if ($type !== 0) {
            return $results[0];
        } else {
            return $results;
        }
    }

    function authenticateUser($type = null) {
        global $LANG;
        if (isset($_COOKIE['username']) && isset($_COOKIE['usertoken'])) {
            $this->username = $_COOKIE['username'];
            $auth = $this->userData($this->username, 2);

            if ($auth['username']) {
                $logged = true;
            } else {
                $logged = false;
            }
        } elseif (isset($this->username)) {
            $username = $this->username;
            $auth = $this->checkUser();

            if ($auth['username']) {
                if ($this->remember == 1) {
                    setcookie("username", $auth['username'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
                    setcookie("usertoken", $auth['token'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);

                    $_SESSION['username'] = $auth['username'];

                    $logged = true;
                    session_regenerate_id();
                } else {
                    $_SESSION['username'] = $auth['username'];
                    $_SESSION['password'] = $auth['password'];
                    $logged = true;
                } 
            } else {
				$logged = false;
			}

        } elseif ($type) {
            $auth = $this->userData($this->username);

            if ($this->remember == 1) {
                setcookie("username", $auth['username'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
                setcookie("usertoken", $auth['token'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);

                $_SESSION['username'] = $auth['username'];

                $logged = true;
                session_regenerate_id();
            } else {
                return $LANG['data_unmatch'];
            }
        }

        if (isset($logged) && $logged == true) {
            return $auth;
        } elseif (isset($logged) && $logged == false) {
            $this->sign_out();
            return $LANG['data_unmatch'];
        }

        return false;
    }

	function checkUser() {
		$username = $this->username;
		$password = $this->password;
		$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE `username` = '%s' AND `password` = '%s'", $username, $password);
       	return $this->dbProcessor($sql, 1)[0];
	}

    // Registeration function
    function registrationCall() {
        // Prevents bypassing the FILTER_VALIDATE_EMAIL
        
        $email = htmlspecialchars($this->email, ENT_QUOTES, 'UTF-8');
        $username = $this->username;
        $newsletter = $this->newsletter;
        $password = $this->password;
        $token = $this->generateToken();

        $sql = sprintf(
        	"INSERT INTO " . TABLE_USERS . " (`email`, `username`, `newsletter`, `password`, `token`) 
        	VALUES ('%s', '%s', '%s', '%s', '%s')", $email, $username, $newsletter, $password, $token);
        $response = $this->dbProcessor($sql, 0, 1);

        if ($response == 1) {
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;
        	return $username;
        } else {
        	return $response;
        }
    }

    function updateProfile() {
        global $user, $LANG;
        $firstname = $this->db_prepare_input($this->firstname);
        $lastname = $this->db_prepare_input($this->lastname);
        $email = $this->db_prepare_input($this->email);
        $username = $this->db_prepare_input($this->username);
        $label = $this->db_prepare_input($this->label);
        $intro = $this->db_prepare_input($this->intro);
        $newsletter = $this->db_prepare_input($this->newsletter);

        $facebook = $this->facebook;
        $twitter = $this->twitter;
        $instagram = $this->instagram;
        $social = sprintf(", `facebook` = '%s', `twitter` = '%s', `instagram` = '%s'", $facebook, $twitter, $instagram);

        $country = $this->db_prepare_input($this->country);
        $state = isset($this->state) ? $this->db_prepare_input($this->state) : '';
        $city = isset($this->city) ? $this->db_prepare_input($this->city) : '';

        $var_email = $this->checkEmail($email, 1);
		$var_user = $this->userData($username, 2); 
        if (mb_strlen($username) < 5) {
            $msg = messageNotice($LANG['username_short'], 0, 2);
        } elseif ($var_user && $var_user['username'] !== $user['username']) {
        	$msg = messageNotice($LANG['username_used'], 0, 2);
        } elseif ($firstname == '' || $lastname == '' || $email == '') {
            $msg = messageNotice($LANG['_all_required'], 0, 2);
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = messageNotice($LANG['invalid_email'], 0, 2);   
        } elseif ($var_email && $var_email['email'] !== $user['email']) {
        	$msg = messageNotice($LANG['email_used'], 0, 2);
        } elseif ($var_user && $var_user['username'] !== $user['username']) {
        	$msg = messageNotice($LANG['username_used'], 0, 2);
        } else {
            $sql = sprintf("UPDATE " . TABLE_USERS . " SET `username` = '%s', `fname` = '%s', `lname` = '%s', " .
                "`email` = '%s', `country` = '%s', `state` = '%s', `city` = '%s', `intro` = '%s', `newsletter` = '%s'%s WHERE " .
                "`uid` = '%s'", $username, $firstname, $lastname, $email, $country, $state, $city, $intro, $newsletter, $social, $user['uid']);
            $query = $this->dbProcessor($sql, 0, 1);
            if ($query == 1) {
            	$msg = messageNotice($LANG['profile_updated'], 1);
            } else {
            	$msg = messageNotice($query);
            }
            // $header = cleanUrls($SETT['url'] . '/index.php?page=account&profile=home');
        }
        return $msg;
    }

	// Fetch and authenticate Administrator
	function administrator($type = null, $username = null) {
		global $LANG, $framework;
		if ($type == 1) {
			if (isset($_COOKIE['admin']) && isset($_COOKIE['admintoken'])) {
	            $this->username = $_COOKIE['admin'];
	            $auth = $this->administrator();

	            if ($auth['username']) {
	                $logged = true;
	            } else {
	                $logged = false;
	            }
	        } elseif (isset($this->username)) { 
				$username = $this->username;
	            $auth = $this->administrator();
	            if ($auth['username']) {
	                if ($this->remember == 1) {
	                    setcookie("admin", $auth['username'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
	                    setcookie("admintoken", $auth['auth_token'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);

	                    $_SESSION['admin'] = $auth['username'];

	                    $logged = true;
	                    session_regenerate_id();
	                } else {
	                    $_SESSION['admin'] = $auth['username'];
	                    $_SESSION['adminpassword'] = $auth['password'];
	                    $logged = true;
	                } 
	            } else {
	                $logged = false;
	            }
			}
 
	        if (isset($logged) && $logged == true) {
	            return $auth;
	        } elseif (isset($logged) && $logged == false) {
	            $this->sign_out(null, 1);
	            return $LANG['data_unmatch'];
	        }

	        return false;
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM admin WHERE `username` = '%s'", $username); 
		    return $framework->dbProcessor($sql, 1)[0];
		} else {
			$sql = sprintf("SELECT * FROM admin WHERE `username` = '%s' AND `password` = '%s'", $this->username, $this->password); 
		    return $framework->dbProcessor($sql, 1)[0];
		}
	} 

    function sign_out($reset = null, $type = null) {
		if ($type) {
			$set = 'admin';
			$extra = $add = $set;
		} else {
			$set = 'username';
			$extra = 'user';
			$add = '';
		}

        if ($reset == true) {
            $this->resetToken();
        }
        setcookie($extra."token", '', time() - 3600, COOKIE_PATH);
        setcookie($set, '', time() - 3600, COOKIE_PATH);
        unset($_SESSION[$set]);
        unset($_SESSION[$add.'password']);
        return 1;
    } 

	function resetToken($type = null) {
		if ($type) {
			$table = 'admin';
		} else {
			$table = 'users';
		}
		$this->dbProcessor(sprintf("UPDATE `%s` SET `auth_token` = '%s' WHERE `username` = '%s'", $table, $this->generateToken(null, 1), $this->db_prepare_input($this->username)));
	}

	function account_activation($token, $username) {
		global $SETT, $LANG, $configuration, $user, $framework;
		if($token == 'resend') { 
			// Check if a token has been sent before, and is not expired
			$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE username = '%s' AND status = '0'", $this->db_prepare_input($username));
			$data = $this->dbProcessor($sql, 1)[0];
 
			if($user['token'] && date("Y-m-d", strtotime($data['date'])) < date("Y-m-d")) {
				$date = date("Y-m-d H:i:s");
				$token = $this->generateToken(null, 2);
				$sql = sprintf("UPDATE " . TABLE_USERS . " SET `token` = '%s', `date` = '%s'"
				." WHERE `username` = '%s'", $token, $date, $this->db_prepare_input($username));
				$return = $this->dbProcessor($sql, 0, 1);
				if($configuration['activation'] == 'email') {
					$link = cleanUrls($SETT['url'].'/index.php?a=account&unverified=true&activation='.$token.'&username='.$username);
					$msg = sprintf($LANG['welcome_msg_otp'], $configuration['site_name'], $token);	
					$subject = ucfirst(sprintf($LANG['activation_subject'], $username, $configuration['site_name']));
					
					$this->username = $username;
					$this->content = $msg;
					$this->message = $this->emailTemplate();
					$this->user_id = $data['id'];  
					$this->activation = 1;
	    			$this->mailerDaemon($SETT['email'], $data['email'], $subject);
	    			return messageNotice($LANG['activation_sent'], 1);
				}			
			} else {
				return messageNotice($LANG['activation_already_sent']);
			}
		} else {
			$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE username = '%s' AND token = '%s' AND status = '0'", 
				$this->db_prepare_input($username), $this->db_prepare_input($token)); 
			$return = $this->dbProcessor($sql, 0, 1);
			if ($return == 1) {
				$sql = sprintf("UPDATE " . TABLE_USERS . " SET `status` = '1', `token` = ''"
				." WHERE `username` = '%s'", $this->db_prepare_input($username));
				$return = $this->dbProcessor($sql, 0, 1);
				return $return == 1 ? messageNotice('Congratulations, your account was activated successfully', 1) : '';
			} else {
				return messageNotice('Invalid OTP', 3);
			}
		}
	}

    function checkEmail($email = NULL, $type = 0) {
        $sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE 1 AND email = '%s'", mb_strtolower($email));
        // Process the information
        $results = $this->dbProcessor($sql, 1);
        if ($type == 1) {
            return $results[0];
        } else {
            return $results[0]['email'];
        }
    }

	function mailerDaemon($sender, $receiver, $subject) {
		// Load up the site settings
		global $SETT, $configuration, $user, $mail; 

		$message = $this->message;

		// show the message details if test_mode is on
		$return_response = null;

		if ($this->trueAjax() && $configuration['smtp_debug'] != 0) {
			// echo '
			// <small class="p-1"><div class="text-warning text-justify"
			// 	Sender: '.$sender.'<br>
			// 	Receiver: '.$receiver.'<br>
			// 	Subject: '.$subject.'<br>
			// 	Message: '.$message.'<br></div>
			// </small>';
		}

	    // Send a test email message
	    if (isset($this->test)) {
	    	$sender = $SETT['email'];
	    	$receiver = $SETT['email'];
	    	$subject = 'Test EMAIL Message from '.$configuration['site_name'];
	    	$message = 'Test EMAIL Message from '.$configuration['site_name'];
	    	$return_response = successMessage('Test Email Sent');
	    }   

		// If the SMTP emails option is enabled in the Admin Panel
		if($configuration['smtp']) { 

			require_once(__DIR__ . '/vendor/autoload.php');
			
			//Tell PHPMailer to use SMTP
			$mail->isSMTP();

			//Enable SMTP debugging
			// 0 = off 
			// 1 = client messages
			// 2 = client and server messages
			$mail->SMTPDebug = $configuration['smtp_debug'];
			
			$mail->CharSet = 'UTF-8';	//Set the CharSet encoding
			
			$mail->Debugoutput = 'html'; //Ask for HTML-friendly debug output
			
			$mail->Host = $configuration['smtp_server'];	//Set the hostname of the mail server
			
			$mail->Port = $configuration['smtp_port'];	//Set the SMTP port number - likely to be 25, 465 or 587
			
			$mail->SMTPAuth = $configuration['smtp_auth'] ? true : false;	//Whether to use SMTP authentication
			
			$mail->Username = $configuration['smtp_username'];	//Username to use for SMTP authentication
			
			$mail->Password = $configuration['smtp_password'];	//Password to use for SMTP authentication
			
			$mail->setFrom($sender, $configuration['site_name']);	//Set who the message is to be sent from
			
			$mail->addReplyTo($sender, $configuration['site_name']);	//Set an alternative reply-to address
			if($configuration['smtp_secure'] !=0) {
				$mail->SMTPSecure = $configuration['smtp_secure'];
			} else {
				$mail->SMTPSecure = false;
			}
			//Set who the message is to be sent to
			if(is_array($receiver)) {
				foreach($receiver as $address) { 
					$mail->addAddress($address); 
				}
			} else {
				$mail->addAddress($receiver);
			}
			//Set the message subject 
			$mail->Subject = $subject;
			//convert HTML into a basic plain-text alternative body,
			//Read an HTML message body from an external file, convert referenced images to embedded
			$mail->msgHTML($message);

			//send the message, check for errors
			if(!$mail->send()) {
				// Return the error in the Browser's console
				#echo $mail->ErrorInfo;
			}
		} else {
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8' . PHP_EOL;
			$headers .= 'From: '.$configuration['site_name'].' <'.$sender.'>' . PHP_EOL .
				'Reply-To: '.$configuration['site_name'].' <'.$sender.'>' . PHP_EOL .
				'X-Mailer: PHP/' . phpversion();
			if(is_array($receiver)) {
				foreach($receiver as $address) {
					@mail($address, $subject, $message, $headers);
				}
			} else {
				@mail($receiver, $subject, $message, $headers);
			}
		}	 
		return $return_response;
	}

    function captchaVal($captcha) {
        global $configuration;
        if ($configuration['captcha']) {
            if ($captcha === "{$_SESSION['captcha']}") {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    function phoneVal($phone, $type = 0) {
        global $configuration;
        $phone = $this->db_prepare_input($phone);

        if ($type) {
            $sql = sprintf("SELECT phone FROM " . TABLE_USERS . " WHERE phone = '%s'", $phone);
            $rs = $this->dbProcessor($sql, 1)[0];
            return $rs ? false : true;
        } else {
            if (mb_strlen($phone) < 9 OR !preg_match('/^[0-9]+$/', $phone)) {
                return false;
            } else {
                return true;
            }
        }
    }

    // Show the user roles
	function userRoles($role = 0, $set_user = null) {
		global $framework, $user;
		if ($set_user) {
			$user = $this->userData($set_user, 1);
			$role = $user['role'];
		} 
		
		if ($role == 0) {
			return $user['role'];
		} else {
			if ($role == 1) {
				$role = 'User';
			} elseif ($role == 2) {
				$role = 'Artist';
			} elseif ($role == 3) {
				$role = 'Music Aggregator';
			} elseif ($role == 4) {
				$role = 'Administrator';
			} elseif ($role == 5) {
				$role = 'Super Administrator';
			}
			return $role;
		}

	}

    // Show the user roles
	function releaseStatus($rel = null) {
		global $databaseCL;
		if ($rel ) {
			$release = $databaseCL->fetchReleases(1, $rel)[0];
			$status = $release['status']; 
		
			if ($status == 1) {
				$status = 'Action Needed';
			} elseif ($status == 2) {
				$status = 'In Review';
			} elseif ($status == 3) {
				$status = 'Approved';
			} else {
				$status = 'Removed';
			}
			return array($status, $release['status']);
		}
		return false;
	}

	/*
	Email template
	 */
	function emailTemplate() {
		global $LANG, $SETT, $configuration, $framework;
		$site_copy = '&copy; Copyright '.date('Y').' <strong><a href="'.$SETT['url'].'">'.$configuration['site_name'].'</a><strong>. All Rights Reserved';   

	    $fullname = isset($this->recievername) ? $this->recievername : '{$eml->fullname}';

		$content = isset($this->content) ? $this->content : '';
		$template = '
		<div style="background: #afc4d0; padding: 35px;">
			<div style="width: 200px;">'.$configuration['site_office'].'</div><hr style="border-top: 1px solid rgb(221, 226, 230);">
			<div style="text-align: center; margin-top: 50px;">
				<img src="'.getImage($configuration['intro_logo'], 1).'" width="100px" height="100px" alt="'.ucfirst($configuration['site_name']).'Logo" style="border-radius: 50%; box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12);">
			</div>
			<div style="font: message-box; border: solid 1px lightgray; border-radius: 7px; background: white; margin: 15px; box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12);">
				<div style="padding: 10px; background: #f2f2f2; display: flex; width: 100%; border-top-left-radius: 7px; border-top-right-radius: 7px;">
				<h3>'.ucfirst($configuration['site_name']).'</h3>
				</div>
				<div style="margin: 15px;">
					<p style="font-weight: bolder;">Hello '.$fullname.',</p>
					<p style="color: black;">
						'.$content.'
					</p>
				</div>
			</div>
			<div style="margin-left: 35px; margin-right: 35px; padding-bottom: 35px;">This message was sent from <a href="'.$SETT['url'].'" target="_blank">'.$SETT['url'].'</a>, because you granted us permission to do so. Please ignore this message if you are not aware of this action. You can also send us a message to remove yourself <a href="mailto:'.$configuration['email'].'">'.$configuration['email'].'</a></div>
		</div>
		<div style="text-align: center; padding: 15px; background: #fff;">
			<div>'.ucfirst($configuration['site_name']).'</div>
			<div style="color: teal;">
				'.$site_copy.'
			</div>
		</div>';
		return $template;
	}	

	function message_template($template) {
	    global $LANG, $PTMPL, $SETT, $user, $configuration, $EML, $framework;  

	    // Message receivers details 
	    $EML['username'] = $username = isset($this->username) ? $this->username : '';
	    $EML['password'] = isset($this->password) ? $this->password : '';
	    $EML['firstname'] = $firstname = isset($this->firstname) ? $this->firstname : '';
	    $EML['lastname'] = $lastname = isset($this->lastname) ? $this->lastname : '';
	    $EML['fullname'] = $framework->realName($username, $firstname, $lastname);
	    $EML['key'] = isset($this->key) ? $this->key : '';
	    $EML['email'] = isset($this->email) ? $this->email : '';

	    $EML['sitename'] = $configuration['site_name'];

	    // Details of who is performing an action
	    $EML['sender_username'] = $sender_username = isset($this->sender_username) ? $this->sender_username : '';
	    $EML['sender_firstname'] = $sender_firstname = isset($this->sender_firstname) ? $this->sender_firstname : '';
	    $EML['sender_lastname'] = $sender_lastname = isset($this->sender_lastname) ? $this->sender_lastname : '';
	    $EML['sender_fullname'] = $framework->realName($sender_username, $sender_firstname, $sender_lastname);

	    // The action triggering this Message
	    $EML['action'] = isset($this->action) ? $this->action : '';

	    // action on poll ot post
	    $EML['action_on'] = isset($this->action_on) ? $this->action_on : '';

		$msg = preg_replace_callback('/{\$eml->(.+?)}/i', function($matches) {
			global $EML;
			return (isset($EML[$matches[1]])?$EML[$matches[1]]:"");
		}, $template);

	    return $msg; 
	} 

	/**
	* Manage the payments
	*/
	function updatePayments($type = null) {
	  global $framework, $user;

	  $user_id = $framework->payer_id;    
	  $paymentid = $framework->payment_id;                       
	  $amount = $framework->amount;
	  $currency = $framework->currency;
	  $course = $framework->course;
	  $fname = $framework->payer_fn;
	  $lname = $framework->payer_ln;
	  $email = $framework->email;  
	  $country = $framework->country;
	  $orderref = $framework->order_ref;

	  if (!$type) {
	    $sql = sprintf("INSERT INTO " . TABLE_PAYMENTS . " (`user_id`, `payment_id`, `amount`, `currency`, `course`, `pf_name`, "
	      . "`pl_name`, `email`, `country`, `order_ref`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
	      $user_id, $paymentid, $amount, $currency, $course, $fname, $lname, $email, $country, $orderref);
	    $update = $framework->dbProcessor($sql, 0, 1);
	  }
	  return $update;
	}
 
	/**
	* List all available languages
	*/
	function list_languages($type) {
		global $SETT, $LANG, $configuration;
		
		if ($type == 0) {
			$languages = scandir('./languages/');
			
			$LANGS = $LANG;
			$by = $LANG['writter'];
			$default = $LANG['default'];
			$make = $LANG['make_default'];

			$sort = '';
			foreach($languages as $language) {
				if($language != '.' && $language != '..' && substr($language, -4, 4) == '.php') {
					$language = substr($language, 0, -4);
					$system_languages[] = $language;
					
					include('./languages/'.$language.'.php');
					
					if($configuration['language'] == $language) {
						$state = '<a class="pass-btn">'.$default.'</a>';
					} else {
						$state = '<a class="pass-btn" href="'.$SETT['url'].'/index.php?a=settings&b=languages&language='.$language.'">'.$make.'</a>';
					}

                    $sort .= '
                    <div class="padding-5">
						' . $state . '
						<div>
							<div>
								<strong><a href="' . $url . '" target="_blank">' . $name . '</a></strong>
							</div>
							<div>
								' . $by . ': <a href="' . $url . '" target="_blank">' . $author . '</a>
							</div>
						</div>
					</div>';
				}
			}
			
			$LANG = $LANGS;
			return array($system_languages, $sort);
		} else {
			$sql = sprintf("UPDATE " . TABLE_CONFIG . " SET `language` = '%s'", $this->language); 
        	return dbProcessor($sql, 0, 1);
		}
	}

	/**
	* Manage language settings for your website
	* Type 1: Show available languages
	* Type 2: Update the language settings
	*/
	function getLanguage($url, $ln = null, $type = null) {
		global $configuration; 
		
		// Define the languages folder
		$lang_folder = __DIR__ .'/../languages/';
		
		// Open the languages folder
		if($handle = opendir($lang_folder)) {
			// Read the files (this is the correct way of reading the folder)
			while(false !== ($entry = readdir($handle))) {
				// Excluse the . and .. paths and select only .php files
				if($entry != '.' && $entry != '..' && substr($entry, -4, 4) == '.php') {
					$name = pathinfo($entry);
					$languages[] = $name['filename'];
				}
			}
			closedir($handle);
		}
		
		if($type == 1) {
			// Add to array the available languages
	        $available = '';
			foreach($languages as $lang) {
				// The path to be parsed
				$path = pathinfo($lang);
				
				// Add the filename into $available array
				$available .= '<span><a href="'.$url.'/index.php?lang='.$path['filename'].'">'.ucfirst(mb_strtolower($path['filename'])).'</a></span>';
			}
			return $available;  
		} else {
			// If get is set, set the cookie and stuff
			$lang = $configuration['language']; // Default Language
			
			if(isset($_GET['lang'])) {
				if(in_array($_GET['lang'], $languages)) {
					$lang = $_GET['lang'];
					// Set to expire in one month
					setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); 
				} else {
					// Set to expire in one month
					setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); 
				}
			} elseif(isset($_COOKIE['lang'])) {
				if(in_array($_COOKIE['lang'], $languages)) {
					$lang = $_COOKIE['lang'];
				}
			} else {
				// Set to expire in one month
				setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH);
			}

			// If the language file doens't exist, fall back to an existent language file
			if(!file_exists($lang_folder.$lang.'.php')) {
				$lang = $languages[0];
			}
			return $lang_folder.$lang.'.php';
		}
	} 

	/**
	/* generate safelinks from strings E.g: Where is Tommy (where-is-tommy)
	**/
	function safeLinks($string, $type=null) { 
		// Replace spaces and special characters with a -
		$separator = $type ? '_' : '-';
	    $return = strtolower(trim(preg_replace('~[^0-9a-z]+~i', $separator, html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), $separator));
	 
	    // If the link is not safe add a random string
	    $safelink = ($string == $return) ? $return.$separator.rand(100,900) : $return; 
	    
	    return $safelink;
	}

	/**
	 * This function will change the provided to lower case, replace spaces with an underscore
	 * and check for match to the newly generated string in user database
	 * @param  variable $string is the string to convert to a username
	 * @param  variable $type if == 1 will return the username 
	 * match else if it is null or 0 will add a random number to the username
	 * @return string         will return the newly generated username
	 */
	function generateUserName($string, $type = null) {
		$new_string = $this->safeLinks($string, 1);
		$username = $this->userData($string, 2)['username'];

		if ($type == 1) {
			if ($new_string == $username) {
				$set_username = $username;
			} else {
				$set_username = $new_string;
			}
		} else {
			if ($new_string == $this->userData($new_string, 2)['username']) {
				$set_username = $new_string.rand(100,997);
			} else {
				$set_username = $new_string;
			}
		}
		return $set_username;
	}

	/**
	/* generate clean urls, this is similar to safeLinks()
	**/
	function cleanUrl($url) {
		$url = str_replace(' ', '-', $url);
		$url = preg_replace('/[^\w-+]/u', '', $url);
		$url = preg_replace('/\-+/u', '-', $url);
		return mb_strtolower($url);
	}

	/**
	/* Encryption function
	**/
	function easy_crypt($string, $type = 0) {
	    if ($type == 0) {
	        return base64_encode($string . "_@#!@/");
	    } else {
	        $str = base64_decode($string);
	        return str_replace("_@#!@/", "", $str);        
	    }
	    
	} 

	/**
	/*  Sanitize text input function
	**/
    function db_prepare_input($string, $x = null)
    {
        $string = trim(addslashes($string));
        if ($x) {
            return $string;
        }
        return filter_var($string, FILTER_SANITIZE_STRING);
	}

	/**
	/*  Generate a random token (MD5 or password_hash)
	**/
    function generateToken($length = 10, $type = 0)
    {
	    $str = ''; 
	    $characters = array_merge(range('A','Z'), range('a','z'), range(0,9));
 
	    for($i=0; $i < $length; $i++) {
	        $str .= $characters[array_rand($characters)];
	    }
	    if ($type == 1) {
	        return password_hash($str.time(), PASSWORD_DEFAULT);
	    } if ($type == 2) {
	    	return rand(400000,900000);
	    } elseif ($type == 3) {
	    	$rand_letter = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
	    	$rand_sm = substr(str_shuffle("DEFGHOPQRSTUVWXYZ"), 0, 3);
	    	return 'PCAUD-'.$rand_letter.$this->generateToken(10, 2).'-'.$rand_sm;
	    } elseif ($type == 5) {
	    	$key_one = substr(10000000000000000, 0, $length);
	    	$key_two = substr(90000000000000000, 0, $length);
	    	return rand($key_one,$key_two);
	    } else {
	        return hash('md5', $str.time());
	    }
	}

	/**
	/* Generate a 13 digit random coupon code
	**/
	function token_generator($length = 10, $type = null) {
		global $configuration, $user, $databaseCL;
		// Type 1: Token for playlist id
		
		// Set the type of token to generate
	  	if ($type == null) {
		  	$t = 5;
	  	}

	  	// Generate a new key
	  	$key = $this->generateToken($length, $t);

	  	// Fetch already created tokens
	  	if ($type == null) {
		  	$check_token = $databaseCL->fetchPlaylist($key, null, $key)[0]['plid'];
	  	}

	  	// Generate a new key if it has already been used
	  	if ($check_token == $key) {
	  		$token = $this->generateToken($length, $t);
	  	} else {
	  		$token = $key;
	  	}
	  	return $token;
	}

	/** 
	/* Generate a random secure password 
	**/
	function securePassword($length) {
		// Allowed characters
		$chars = str_split("abcdefghijklmnopqrstuvwxyz0123456789");
		
		// Generate password
	    $password = '';
		for($i = 1; $i <= $length; $i++) {
			// Get a random character
			$n = array_rand($chars, 1);
			
			// Store random char
			$password .= $chars[$n];
		}
		return $password;
	}

	/**
	/*  Fetch url content via curl
	**/
	function fetch($url) {
	    if(function_exists('curl_exec')) {
	        $ch = curl_init($url);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
	        $response = curl_exec($ch);
	    }
	    if(empty($response)) {
	        $response = file_get_contents($url);
	    }
	    return $response;
	}

	/* 
	* Find tags in a string
	*/
	function tag_finder($str, $x=0) {
	    if ($x == 1) {
	        // find an @
	        if (preg_match('/(^|[^a-z0-9_\/])@([a-z0-9_]+)/i', $str)) {
	           return 2;
	        } 
	    } else {
	        // find a #
	        if (preg_match('/(^|[^a-z0-9_\/])#(\w+)/u', $str)) {
	           return 1;
	        }
	    }
	    return false;
	}

	/* 
	* Truncate text
	*/
	function myTruncate($str, $limit, $break=" ", $pad="...") {

	    // return with no effect if string is shorter than $limit
	    if(strlen($str) <= $limit) return $str;

	    // is $break is present between $limit and the strings end?
	    if(false !== ($break_pos = strpos($str, $break, $limit))) {
	        if($break_pos < strlen($str) - 1) {
	            $str = substr($str, 0, $break_pos) . $pad;
	        }
	    } 
	    return $str;
	}

	/* 
	* Remove special html tags from string
	*/
	function rip_tags($string) { 
	    // ----- remove HTML TAGs ----- 
	    $string = preg_replace ('/<[^>]*>/', ' ', $string); 
	    // $string = filter_var($string, FILTER_SANITIZE_STRING);
	    
	    // ----- remove control characters ----- 
	    $string = str_replace("\r", '', $string);    // --- replace with empty space
	    $string = str_replace("\n", ' ', $string);   // --- replace with space
	    $string = str_replace("\t", ' ', $string);   // --- replace with space
	    
	    // ----- remove multiple spaces ----- 
	    $string = trim(preg_replace('/ {2,}/', ' ', $string));
	    
	    return $string; 
	} 

	/* 
	* Try to create the title of the url from link
	* This assumes that there is a predefined :title:title on the link
	*/
	function urlTitle($str, $type = null) { 
	    $tit = str_ireplace('www.', '', $str);
	    $tit = str_ireplace('http', '', $tit);
	    $tit = str_ireplace('https', '', $tit);
	    $tit = str_ireplace('://', '', $tit);  
	    $tit = str_ireplace(':type:1', '', $tit);
	    $tit = str_ireplace(':type:2', '', $tit);
	    $tit = substr($tit, strripos($tit, ':title:'));
	    $tit = ucfirst(str_ireplace(':title:', '', $tit));
	    if ($type == 1) {
		    $titr = str_ireplace(':type:1', '', $str);
		    $titr = str_ireplace(':type:2', '', $titr);
	    	return str_ireplace(':title:'.$tit, '', $titr);
	    } elseif ($type == 2) {
	    	$str = substr($str, strripos($str, ':type:'));
	    	return str_ireplace(':type:', '', $str);
	    } else {

	    }
	    return $tit;	
	}

	/**
	 * generateButton this function will try to parse parameters 
	 * available in the provided link and generate a button
	 * @param  string $link this is the link to try to parse
	 * @param  integer $type this is to determine the type of button to generate
	 * @return string       this contains formated html representing the requested button
	 */
	function generateButton($button_link = '', $type = 0, $disabled = 0) {
		global $framework;

		$role = '';
	    if ($button_link) {
	        $button_link = explode(',', $button_link);
	        $button_linked = '';
	        if (is_array($button_link)) {
	            foreach ($button_link as $link) {
	            	$btn_type = $class = $framework->urlTitle($link, 2);
	            	if ($type == 1) {
	            		$scroll = strpos($link, '#') !== false ? ' scroll' : '';
	            		$class_append = ($btn_type == 1 ? 'mt-4'.$scroll : ($btn_type == 3 ? 'btn-block w-50' : 'abt_card_btn'.$scroll));
	            		$class = 'btn btn-agile '.$class_append;
	            		$role = ' role="button"';
	            		$disabled = $disabled ? ' disabled' : '';
	            	} else {;
	                	$class = $btn_type == 1 ? 'btn-get-started' : 'btn-services';
	            	}
	                $link_title = $framework->urlTitle($link);
	                $linked = str_ireplace('-', ' ', $link);
	                $linked = str_ireplace('_', ' ', $linked);
	                $link = $framework->urlTitle($link, 1);
	                $button_linked .= '<a href="'.$link.'" class="'.$class.'"'.$role.$disabled.'>'.$link_title.'</a>';
	            }
	        }
	        return $button_linked;
	    } 
	}

	function urlRequery($query = '') {
	    global $SETT; 
		$set = $rel = $page = '';
		if (strripos($query, 'rel=search')) {
			$_GET['page'] = 'search';
		}
		if (isset($_GET['q']) && isset($_GET['rel'])) {
			$rel = $_GET['rel'];
			$_GET['page'] = $_GET['rel'];
		}
		if (isset($_GET['view']) && $rel == '') {
			$set .= '&view='.$_GET['view'];
		} 
		if (isset($_GET['set'])) {
			$set .= '&set='.$_GET['set'];
		}  
		if (isset($_GET['q']) && $_GET['page'] == 'search') {
			$_GET['page'] = 'search';
		}
		return cleanUrls($SETT['url'] . '/index.php?page=' . $_GET['page'].$set.$query.$page);
	}

	/* 
	* redirect page
	*/
	function redirect($location = '', $type = 0) {
	    global $SETT;
	    if ($type) {
	        header('Location: '.$location);
	    } else {
	        if($location) {
                header('Location: ' . cleanUrls($SETT['url'] . '/index.php?page=' . $location));
	        } else {
                header('Location: ' . cleanUrls($SETT['url'] . '/index.php'));
	        }        
	    }

	    exit;
	}

	function autoComplete($_type = null, $preset = null, $gjson = 0) {
		global $SETT, $configuration, $databaseCL, $marxTime;

		$i = 0;
		if ($_type == 1) {
			$tag_array = [];
			$tags = $databaseCL->fetchGenre();
			if ($tags) {
			  foreach ($tags as $value) {
			    $tag_array[] = '"'.$value['name'].'"';
			  }
			} 
		} elseif ($_type == 2) {
		    $marxTime->explode = ',';
		    $marxTime->get_array = true;
		    $tags = $marxTime->reconstructString($preset); 
			$tag_array = []; 	
			if ($tags) {
			  foreach ($tags as $key => $value) {
			  	$i++;
			  	if ($gjson == 1) {
			  		// get json formated like {var_1:400}
			  		$tag_array[] = 'var_'.$i.':'.ucfirst($value);
			  	} elseif ($gjson == 2) {
			  		// get json formated like {"var_1":"400"}
			  		$tag_array[] = '"var_'.$i.'":"'.ucfirst($value).'"';
			  	} elseif ($gjson == 3) {
			  		// get json formated like {'var_1':'400'}
			  		$tag_array[] = '\'var_'.$i.'\':\''.ucfirst($value).'\'';
			  	} else {
			  		// get a numeric keyed array
					$tag_array[] = '"'.ucfirst($value).'"';
				}
			  }
			}
		} else {
			$tag_array = [];
			$tags = $this->userData(null, 0); 	
			if ($tags) {
			  foreach ($tags as $value) {
				$tag_array[] = '"'.ucfirst($value['username']).'"';
			  }
			}
		}
		$tag_list = implode(', ', $tag_array);
	  	if ($gjson) {
			return '{'.$tag_list.'}';
	  	} else {
			return '['.$tag_list.']';
		}
	}

	/**
	 * Create click-able links from texts
	 */	
	function decodeText($message, $x=0) { 
		global $LANG, $SETT;

		// Decode the links
		$extractUrl = preg_replace_callback('/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))/', "decodeLink", $message);
		
		$y = $x==1 ? 'secondary' : 'primary';
		// Decode link from #hashtags and @mentions
		$extractMessage = preg_replace(array('/(^|[^a-z0-9_\/])@([a-z0-9_]+)/i', '/(^|[^a-z0-9_\/])#(\w+)/u'), array('$1<a class="'.$y.'-color" href="/$2" rel="loadpage">@$2</a>', '$1<a class="'.$y.'-color" href="/$2" rel="loadpage">#$2</a>'), $extractUrl);

		return $extractMessage;
	} 

	/**
	/* Determine if the text is a link or a file
	**/

	function determineLink($string) {
		if(substr($string, 0, 4) == 'www.' || substr($string, 0, 5) == 'https' || 
			substr($string, 0, 4) == 'http') {
			if (substr($string, 0, 4) == 'www.') {
				return 'http://'.$string;
			} else {
				return $string;
			}
		} else {
			return false;
		}	
	}

	//Get users real name
	function realName($username, $first = null, $last = null) { 
		if($first && $last) {
			$name = ucwords($first).' '.ucwords($last);
		} elseif($first) {
			$name = ucwords($first);
		} elseif($last) {
			$name = ucwords($last);
		} else {
			$name = ucwords($username);
		}
		return $name;
	}

	function mdbColors($key, $type = null) { 
		$colors = array(
			0 	=>	'light-text',
			1 	=> 	'pink-text',
			2 	=> 	'cyan-text',
			3 	=> 	'blue-text',
			4 	=> 	'yellow-text',
			5 	=> 	'green-text',
			6 	=> 	'red-text',
			7 	=> 	'fb-ic',
			9 	=> 	'tw-ic',
			8 	=> 	'ins-ic',
			10 	=>	'gplus-ic',
			12 	=> 	'text-danger',
			13 	=> 	'text-warning',
			14 	=> 	'text-info',
			15 	=> 	'text-primary',
			16 	=> 	'text-success',
			17 	=> 	'text-default'
		);
		$buttons = array(
			'btn-light',
			'btn-pink',
			'btn-cyan',
			'btn-yellow',
			'btn-dark-green',
			'btn-link',
			'btn-unique',
			'btn-elegant',
			'btn-purple',
			'btn-indigo',
			'btn-amber',
			'btn-brown',
			'btn-blue-grey',
			'btn-light-green',
			'btn-light-blue',
			'btn-deep-purple',
			'btn-deep-orange',
			'btn-mdb-color',
			'btn-primary',
			'btn-secondary',
			'btn-warning',
			'btn-success',
			'btn-danger',
			'btn-info',
			'btn-dark',
			'btn-default'
		);

		if ($type == 1) {
			if (!array_key_exists($key, $buttons)) { 
				$new_key = rand(18, 25);
				return $this->mdbColors($new_key, $type);
			}
			if (isset($buttons[$key])) {
				$color = $buttons[$key];
			} else {
				$color = $buttons[0];
			}
			return $buttons[$key];
		} else {
			if (!array_key_exists($key, $colors)) { 
				$new_key = rand(12, 17);
				return $this->mdbColors($new_key, $type);
			}
			if (isset($colors[$key])) {
				$color = $colors[$key];
			} else {
				$color = $colors[0];
			}
		}
		
		return $color;
	}

	/**
	/* this function controls file uploads	
	/* Type 1: Upload images to templates folder
	/* Type 2: Upload images to upload folder (Square)
	/* Type 3: Upload images to upload folder (Long)
	 **/	
	function imageUploader($file = null, $type = null, $eck = null) {
		global $PTMPL, $LANG, $SETT, $user, $framework, $configuration, $databaseCL, $marxTime; 
		// File arguments
		$errors = array();
		$uploade_type = ''; 

		// Generate the image properties  
		if (isset($file['name'])) { 
			$_FILE = $file;
			$allowed = array('jpeg','jpg','png');
			$size = $configuration['img_upload_limit']; 
			$error = $file['name'] == '' ? "Please select a file to upload." : null;

		    if (isset($this->resolution)) {
		    	$resolution = explode(',', $this->resolution);
		    	if (count($resolution) > 1) {
			    	$w = $resolution[0];
			    	$h = $resolution[1];
		    	} else {
			    	$w = $h = $resolution[0]; 
		    	}
		    } else {
			    if ($type == 1) { // Uploads to template folder
			    	$w = 620; $h = 310;
			    } if ($type == 2) { // Upload Artworks
			    	$w = 3000; $h = 3000;
			    } else { // Upload covers
					$w = 1200; $h = 800; 
			    }		    	
		    }
    		$size_format = $marxTime->swissConverter($size);

			$file_name = $_FILE['name'];
			$file_size = $_FILE['size'];
			$file_tmp = $_FILE['tmp_name'];
			$file_type= $_FILE['type'];  
			$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			  
		    $new_image = mt_rand().'_'.mt_rand().'_n.'.$file_ext; 

		    // Check if file is allowed for upload type
			if(in_array($file_ext,$allowed)=== false){
			    $errors[] = 'File type not allowed, use a JPEG, JPG or PNG file.';
			}
			if($file_size > $size){
	    		$errors[].= 'Upload should not be more than '.$size_format;
			}

			/*
			// Check for errors in the file upload before uploading, 
			// To avoid multiple waste of storage
			 */
			if ($eck) {
				if (empty($errors)==true) { 
					return 0;
				} else {
					return $errors[0];
				}
			} else {
			    $cd = $SETT['working_dir'];
			    if ($type == 1) { // Uploads to template folder
			    	$dir = $cd.'/'.$SETT['template_url'].'/img/';
			    } else {
			    	$dir = $cd.'/uploads/photos/';
			    }
				// Crop and compress the image
				if (empty($errors)==true) {
					// Check for file permissions
					if(is_writable($dir)) {
						// Create a new ImageResize object
		                $image = new ImageResize($file_tmp);
			        	// Manipulate the image
			        	if ($type == 1 || $type == 2) {
			        		$image->resizeToBestFit($w, $h);
			        		$image->crop($w, $h);
			        	} else {
			        		$image->crop($w, $h);
			        	}
			        	$image->save($dir.$new_image);    
						return array($new_image, 1);
					} else  {
						// chmod($dir.'/default.jpg', 0755);
						return 'You do not have enough permissions to write this file';
					}
				} else {
					return $errors[0];
				}
			}	
		} else {
			return 'Please select a file to upload';
		}	
	}

    /**
	* Check if this request is being made from ajax
	*/
	function trueAjax() {
	    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	        return true;
	    } else {
	        return false;
	    }
	}

	/**
	 * send sms text message with twillio
	 */
	function sendSMS($text, $phone, $test=0) {
	    global $configuration;
	    $success = true;
	    $fail = false;
	    if ($test==1) {
	    	$phone = $configuration['site_phone'];
	    	$text = 'Test SMS from '.$configuration['site_name'];
	    	$return = 'Test SMS successfully sent';
	    	$fail = 'Failed to send Test SMS';
	    }
	    $client = new Twilio\Rest\Client($configuration['twilio_sid'], $configuration['twilio_token']);
	    $message = $client->account->messages->create(
	        $phone,
	        array(
	            'from' => $configuration['twilio_phone'],
	            'body' => $text
	        )
	    );
	    if($message->sid) {
	    	return $success;
	    }
	    return $fail;
	}

	/**
	/* this function controls file uploads	
	 **/	
	function uploader($file = null, $type = 0, $eck = null) {
		// File arguments
		$errors = array();
		$uploade_type = '';

		// Generate the image properties  
		if ($type == 0 && isset($file['name'])) { 
			$_FILE = $file;
			$allowed = array("jpeg","jpg","png");
			$size = 1097152;
			$uploade_type .= 'Cover Photo';
			$error = $file['name'] == '' ? "Please select an image for cover photo. " : 
			$error = "File type not allowed for Cover Photo, use a JPEG, JPG or PNG file. ";
			$w = 3200; $h = 2000;
    		$dir = 'img'; 
		} elseif ($type == 1 && isset($file['name'])) {
			$_FILE = $file;
			$allowed = array("png");
			$size = 1097152;
			$uploade_type .= 'Badge';
			$error = $file['name'] == '' ? "Please select an image for badge. " : 
			$error = "File type not allowed for Badge, use a PNG file. ";
			$w = 3200; $h = 2000;
    		$dir = 'img'; 
		} 
		$file_name = $_FILE['name'];
		$file_size = $_FILE['size'];
		$file_tmp = $_FILE['tmp_name'];
		$file_type= $_FILE['type'];
		$lower = explode('.',$_FILE['name']);
		$file_ext = strtolower(end($lower));
		  
	    $new_image = mt_rand().'_'.mt_rand().'_'.mt_rand().'_n.'.$file_ext; 

	    // Check if file is allowed for upload type
		if(in_array($file_ext,$allowed)=== false){
		    $errors[] = $error;
		}
		if($file_size > $size){
    		$errors[].= $uploade_type.' should not be more than '.$size.'MB';
		}

		/*
		// Check for errors in the file upload before uploading, 
		// To avoid multiple waste of storage
		 */
		if ($eck) {
			if (in_array($file_ext,$allowed) && empty($errors)==true) { 
				return 1;
			} else {
				return $errors[0];
			}
		} else {
			// Crop and compress the image
			if (in_array($file_ext,$allowed) && empty($errors)==true) {
				// Create a new ImageResize object
                $image = new ImageResize($file_tmp);
	      		$cd = getcwd();
	        	// Manipulate the image
	        	$image->crop($w, $h);
	        	$image->save($cd.'/uploads/'.$dir.'/'.$new_image);    
				return $new_image;
			} else {
				return $errors[0];
			}
		}		
	}

	/**
	 * Rave Payment processing and validation class 
	 */ 
	function raveValidate() {
		$ravemode = $this->ravemode;
		$query = $this->query;

		$data_string = json_encode($query);

	    $ch = curl_init('https://'.$ravemode.'/flwv3-pug/getpaidx/api/v2/verify');                                                                      
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                              
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	    $response = curl_exec($ch);

	    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	    $header = substr($response, 0, $header_size);
	    $body = substr($response, $header_size);

	    if (curl_error($ch)) {
			$error_msg = curl_error($ch);
		}
		if(isset($error_msg)) {
	    	return $error_msg;
		}
	    curl_close($ch);

	    return json_decode($response, true);	
	}

	function paymentData($location = '') {
		global $SETT, $PTMPL, $user, $configuration;
		$payer = $this->payer;

        if ($configuration['pay_public_key'] == "" || $configuration['pay_private_key'] == "") {
            return bigNotice('<b>error:</b> Paystack settings not available', 3, 'bg-white shadow');
            // redirect($_SERVER['HTTP_REFERER']);
        } else {
			$reference = 'PREMIUM-'.$this->generateToken(10, 5);
            $params = array(
                'public_key' => $configuration['pay_public_key'],
                'private_key' => $configuration['pay_private_key'], 
                'currency' => $configuration['currency'],
                'email' => $payer['email'],
                'firstname' => $payer['fname'],
                'lastname' => $payer['lname'],
                'user_id' => $payer['uid'],
                'total' => $this->amount, 
                'reference' => $reference,
                'release_id' => $this->release_id, 
                'payment_detail' => $this->payment_details
            );

            $_SESSION['params'] = $params;
            $this->redirect($location, 1);
        }
	}

	function auto_template($string, $type = null) {
    	global $SETT, $PTMPL, $user, $framework, $collage, $marxTime, $TEXP;  

    	if ($type == 1) {
				$msg = preg_replace_callback('/{\$texp->(.+?)}/i', function($matches) {
				global $TEXP, $framework;
				$texp_user = $framework->userData($matches[1], 1);
				$_user = smallUser_Card($texp_user['uid'], null, 1);
				return (
					isset($texp_user)?$_user:""
				);
			}, $string);
		} else {
			$msg = preg_replace_callback('/{\$texp->(.+?)}/i', function($matches) {
				global $TEXP; 
				return (isset($TEXP[$matches[1]])?$TEXP[$matches[1]]:"");
			}, $string);
		}

	    return $msg; 
	}

	/**
	/* Function to process all database calls
	**/
	function dbProcessor($sql = 0, $type = 0, $response='') {
		global $DB;
		// Type 0 = Insert, Update, Delete
		// Type 1 = Select 
		// Type 2 = Just return the response
		// Response 5 = Debug
		// Response 1 = Return 1 on success or error notice on fail
		// Response 2 = Return 0 on fail or 1 on success
		// Response 3 = Return last insert id on success or 0 on fail

		$data = null; 
		if ($type == 2) {
			$data = $response;
		} else {
			try {
				$stmt = $DB->prepare($sql);	 	
				$stmt->execute();
				$last_id = $DB->lastInsertId();
			} catch (Exception $ex) {
			   $error = messageNotice($ex->getMessage(), 3);
			}
			if (isset($error)) {
			    $data = $error;
			} else {
				if ($type == 0) {
					if ($stmt->rowCount() > 0) {  
						if ($response == 2) {
							$data = 1;
						} elseif ($response == 3) {
							$data = $last_id;
						} else {
							$data = $response;
						}
					} else {
						if ($response == 2 || $response == 3) {
							$data = 0;
						} else {
							$data = 'No changes were made';
						}
					}		 
				} elseif ($type == 1) {
					$results = $stmt->fetchAll();
				    if (count($results)>0) { 
				    	$data = $results; 
				    }
				}
			}		
		} 
		if ($response == 5) {
			$data .= messageNotice('Debug is on, response is set to : '.$data, 2);
			$data .= messageNotice('Query String: '.$sql);
		}
		$data = (isset($error) ? $error : $data);
		return $data;
	}

	function pagination($type = null) {
		global $SETT, $LANG, $configuration, $databaseCL;

		$page = $SETT['url'].$_SERVER['REQUEST_URI'];
		if (isset($_GET['pagination'])) {
			$page = str_replace('&pagination='.$_GET['pagination'], '', $page);
		}
		$page = $this->urlRequery();
		if (isset($this->limit_records)) {
			$perpage = $this->limit_records;
		} else {
			// Pagination Navigation settings
			if ($type == 1) {
				$perpage = $configuration['per_featured'];
			} else {
				$perpage = $configuration['per_page'];
			}
		} 
		if(isset($_GET['pagination']) && $_GET['pagination'] !== ''){
		    $curpage = $_GET['pagination'];
		} else{
		    $curpage = 1;
		}

		$start = ($curpage * $perpage) - $perpage;
		if ($this->all_rows) {
			$all_rows = $this->all_rows;
		} else {
			$all_rows = [];
		}
		$count = count($all_rows);
		// if (isset($_GET['page']) && $_GET['page'] == 'homepage' && !isset($_GET['archive'])) {
		// 	$count = $count - 1;
		// }
		$this->limiter = $databaseCL->limiter = $perpage; 
		$this->start = $databaseCL->start = $start;	

		// Pagination Logic
		$endpage = ceil($count/$perpage);
		$startpage = 1;
		$nextpage = $curpage + 1;
		$previouspage = $curpage - 1;
 
		$navigation = '';
		if ($endpage > 1) {
			if ($curpage != $startpage) {
				$pager = $this->urlRequery('&pagination='.$startpage);
				$navigation .= '
					<li class="page-item">
						<a class="page-link" aria-label="Previous" href="'.$pager.'">
							<span aria-hidden="true">&laquo;</span>
							<span class="sr-only">Previous</span>
						</a>
					</li>
			    ';
			}

			if ($curpage >= 2) {
				$pager = $this->urlRequery('&pagination='.$previouspage);
			    $navigation .= '
					<li class="page-item">
						<a class="page-link" href="'.$pager.'">Prev</a>
					</li>
			    ';
			}

			$pager = $this->urlRequery('&pagination='.$curpage);
		    $navigation .= '
				<li class="page-item active">
					<a class="page-link" href="'.$pager.'">'.$curpage.'</a>
				</li>
		    '; 

			if($curpage != $endpage){
				$pager = $this->urlRequery('&pagination='.$nextpage);
			    $navigation .= '
					<li class="page-item">
						<a class="page-link" href="'.$pager.'">Next</a>
					</li>
			    ';  
 
				$pager = $this->urlRequery('&pagination='.$endpage);
			    $navigation .= '                
					<li class="page-item">
						<a class="page-link" aria-label="Next" href="'.$pager.'">
							<span aria-hidden="true">&raquo;</span>
							<span class="sr-only">Next</span>
						</a>
					</li> 
			    ';   
			}

		  	$navigation = '
				<nav class="mb-5 pb-2">
					<ul class="pagination pg-darkgrey flex-center">
						'.$navigation.'
					</ul>
				</nav>
				';
		} else {
		  	$navigation = '';
		}
		$this->all_rows = $this->limit_records = null;
		return $navigation;	 
	}
}

/**
 * Manage viewing and sending of messages
 */
class social extends framework {


	/**
	* Check if the user is online
	*/
	function online_state($user, $gettime=null, $type=0) {
		global $LANG, $configuration, $marxTime;

		$timeNow = time();
		$data = $this->userData($user, 1);
		$online_time = $gettime ? $gettime : $configuration['online_time']; 
		$data_online_time = (isset($this->data_online) ? $this->data_online : $data['online']);
		$title = (isset($this->data_online) ? $LANG['last_msg'] : $LANG['last_seen']);

		// Set icon to show online status
		if(($timeNow - strtotime($data_online_time)) > $online_time) {
			$info = $LANG['offline'];
			$icon = '<i class="small-icon fa fa-circle text-warning" data-toggle="tooltip" data-placement="right" data-title="'.$info.'"></i>';
			$last = $title.' '.$marxTime->timeAgo($data_online_time, 1);
			$status = 0;
		} else {
			$info = $LANG['online'];
			$icon = '<i class="small-icon fa fa-circle text-success" data-toggle="tooltip" data-placement="right" data-title="'.$info.'"></i>';
			$last = $title.' '.$LANG['just_now'];
			$status = 1;
		}
		// Reset the online time
		$this->data_online = null;
		if ($type == 0) {
			return array('icon' => $icon, 'text' => $info, 'last_seen' => $last);
		} else {
			if ($status) {
				return $status;
			} else { 
				$this->dbProcessor(sprintf("UPDATE " .TABLE_USERS. " SET `online` = '%s' WHERE `id` = '%s'", $timeNow, $this->db_prepare_input($user)), 0, 1);				
			}			
		}
	}

	/**
	* Write or read messages from db for group chat
	*/
	function fetchGroupMessages($type, $receiver = null, $chat_id = null, $start = null) {
		// type 0: Read
		// type 1: Check for new messages 
		// type 2: Fetch newly posted message
		// type 3: delete the message status so set message as read
		// type 4: fetch the last message in the row
		// type 5: fetch the new unread message
		// type 6: fetch thread for the group
		//---------------------- 
		global $user, $configuration;

		if($start == 0) {
			$start = '';
		} else { 
			$start = 'AND `messenger`.`cid` < \''.$this->db_prepare_input($chat_id).'\'';
		}

		// Check for group thread
		$thread = '';
		if (isset($_SESSION['group_thread'])) {
            $thread = $_SESSION['group_thread'];
        } elseif (isset($this->thread)) {
			$thread = $this->thread;
		}

		if ($type == 0) { 
			$sql = sprintf("SELECT * FROM  messenger, users WHERE (
				`messenger`.`thread` = '%s' AND `messenger`.`sender` = `users`.`uid` AND `messenger`.`thread` IN (SELECT `thread` FROM messenger WHERE `sender` = '%s')) %s ORDER BY `messenger`.`cid` DESC LIMIT %s", 
				$thread, $user['uid'], $start, ($configuration['per_messenger'] + 1));
			return $this->dbProcessor($sql, 1); 
		} elseif ($type == 1) { 
			$sql = sprintf("SELECT * FROM messenger, users WHERE `thread` = '%s' AND `cid` IN (SELECT `cid` FROM message_status WHERE `uid` = '%s') AND `messenger`.`sender` != '%s' AND `messenger`.`sender` = `users`.`uid` ORDER BY `messenger`.`cid` DESC", $thread, $user['uid'], $user['uid']); 
			return $this->dbProcessor($sql, 1); 
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM messenger, users WHERE `thread` = '%s' AND `cid` = (SELECT `cid` FROM message_status WHERE `uid` = '%s' ORDER BY `id` DESC LIMIT 1) AND `messenger`.`sender` = `users`.`uid` ORDER BY `messenger`.`cid` DESC LIMIT 1", $thread, $user['uid']);  
			return $this->dbProcessor($sql, 1);
		} elseif($type == 3) { 
			// Check for the cid from session delete it and unset the session 
			if ($chat_id) {
				$chat_id = $chat_id;
			} elseif (isset($_SESSION['delete_cid'])) {
				$chat_id = $_SESSION['delete_cid'];
			}
			$sql = sprintf("DELETE FROM message_status WHERE `uid` = '%s' AND `cid` = '%s'", $user['uid'], $chat_id);
			$this->dbProcessor($sql, 0);
			if (isset($_SESSION['delete_cid'])) {
				unset($_SESSION['delete_cid']);
			}
		} elseif ($type == 4) { 
			$sender = $receiver;
			$sql = sprintf("SELECT * FROM messenger, users WHERE (`messenger`.`sender` = '%s' AND `messenger`.`thread` IN (SELECT `thread` FROM messenger WHERE `sender` = '%s') AND `messenger`.`sender` = `users`.`uid`) ORDER BY `messenger`.`cid` DESC LIMIT 1", $sender, $sender);
			return $this->dbProcessor($sql, 1); 
		} elseif ($type == 5) { 
			$limit = isset($this->limit) ? ' LIMIT '.$this->limit : '';
			$status = isset($this->seen) ? ' AND `seen` = \''.$this->seen.'\'' : '';
			$sql = sprintf("
				SELECT thread, sender FROM messenger WHERE `receiver` = '%s'%s
				GROUP BY thread, sender
				ORDER BY sender ASC%s", $user['uid'], $status, $limit); 
			return $this->dbProcessor($sql, 1); 	//
		} elseif ($type == 6) {
			// Fetch group thread
			return $this->dbProcessor(sprintf("SELECT thread FROM messenger WHERE `thread` LIKE '%s' AND `receiver` = '$receiver'", '%grpc_%'), 1)[0];	
		}	
	}

	/**
	* Write or read messages from db
	*/
	function fetchMessages($type, $receiver = null, $chat_id = null, $start = null) {
		// type 0: Read
		// type 1: Check for new messages
		// type 2: Fetch new message
		// type 3: Fetch newly posted message
		// type 4: set the message status as read
		// type 5: fetch the last message
		// type 6: fetch the new unread message
		//---------------------- 
		global $user, $configuration;

		if($start == 0) {
			$start = '';
		} else { 
			$start = 'AND `messenger`.`cid` < \''.$this->db_prepare_input($chat_id).'\'';
		}

		if ($type == 0) { 
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger, " . TABLE_USERS . " AS users WHERE  (`messenger`.`sender` = '%s' AND `messenger`.`receiver` = '%s' AND `messenger`.`sender` = `users`.`uid`) %s OR (`messenger`.`sender` = '%s' AND `messenger`.`receiver` = '%s' AND `messenger`.`sender` = `users`.`uid`) %s ORDER BY `messenger`.`cid` DESC LIMIT %s", $user['uid'], $this->db_prepare_input($receiver), $start, $this->db_prepare_input($receiver), $user['uid'], $start, ($configuration['per_messenger'] + 1));
			return $this->dbProcessor($sql, 1); 
		} elseif ($type == 1) {
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger WHERE `sender` = '%s' AND `receiver` = '%s' AND `seen` = '0'", $this->db_prepare_input($receiver), $user['uid']);
			$process = $this->dbProcessor($sql, 1);
			if ($process) {
				return $this->fetchMessages(2, $receiver);
			}
			return false;
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger, " . TABLE_USERS . " AS users WHERE `sender` = '%s' AND `receiver` = '%s' AND `seen` = '0' AND `messenger`.`sender` = `users`.`uid` ORDER BY `messenger`.`cid` DESC", $this->db_prepare_input($receiver), $user['uid']); 
			return $this->dbProcessor($sql, 1); 
		} elseif ($type == 3) {
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger, " . TABLE_USERS . " AS users WHERE (`messenger`.`sender` = '%s' AND `messenger`.`receiver` = '%s' AND `messenger`.`sender` = `users`.`uid`) ORDER BY `messenger`.`cid` DESC LIMIT 1", $user['uid'], $this->db_prepare_input($receiver));
			return $this->dbProcessor($sql, 1);
		} elseif($type == 4) { 
			$sql = sprintf("UPDATE " . TABLE_MESSAGE . " SET `seen` = '1', `date` = `date` WHERE `sender` = '%s' AND `receiver` = '%s' AND `seen` = '0'", $this->db_prepare_input($receiver), $user['uid']);
			$this->dbProcessor($sql, 0);
		} elseif ($type == 5) { 
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger, " . TABLE_USERS . " AS users WHERE (`messenger`.`sender` = '%s' AND `messenger`.`receiver` = '%s' AND `messenger`.`sender` = `users`.`uid`) OR (`messenger`.`sender` = '%s' AND `messenger`.`receiver` = '%s' AND `messenger`.`sender` = `users`.`uid`) ORDER BY `messenger`.`cid` DESC LIMIT 1", $user['uid'], $this->db_prepare_input($receiver), $this->db_prepare_input($receiver), $user['uid']);
			return $this->dbProcessor($sql, 1); 
		} elseif ($type == 6) { 
			$limit = isset($this->limit) ? ' LIMIT '.$this->limit : '';
			$status = isset($this->seen) ? ' AND `seen` = \''.$this->seen.'\'' : '';
			$sql = sprintf("
				SELECT thread, sender FROM messenger WHERE `receiver` = '%s'%s
				GROUP BY thread, sender
				ORDER BY sender ASC%s", $user['uid'], $status, $limit); 
			return $this->dbProcessor($sql, 1); 			
		}		
	}

	/**
	* Display the messages
	*/
	function messenger($type, $receiver) {
		global $SETT, $LANG, $user, $configuration;
		// Type 0: all messages
		// Type 1: Fetch just sent message
		// Type 2: Fetch new received message
		// Type 3: Fetch more messages

		// $action = new actions;
		$marxTime = new marxTime;

		$more = $readmore = ''; 
		$read_msg = '';

		// Check if this is a group chat 
		$thread = $this->thread = '';
        if (isset($_SESSION['group_thread'])) {
            $thread = $this->thread = $_SESSION['group_thread'];
        } elseif (isset($this->thread)) {
            $thread = $this->thread = $this->thread;
        }
        $preserve = isset($this->preserve) ? $this->preserve : 0;
		str_ireplace('grpc', '', $thread, $is_group);

		if ($is_group) {
			if ($type == 0) {
				$messages = $this->fetchGroupMessages(0, $receiver); 
			} elseif ($type == 1) {
				$messages = $this->fetchGroupMessages(2, $receiver);
			} elseif ($type == 2) {
				$messages = $this->fetchGroupMessages(1, $receiver);
			} elseif ($type == 3) {
				$messages = $this->fetchGroupMessages(0, $receiver, $this->chat_id, $this->start);
			}  			
		} else {
			if ($type == 0) {
				$messages = $this->fetchMessages(0, $receiver); 
			} elseif ($type == 1) {
				$messages = $this->fetchMessages(3, $receiver);
			} elseif ($type == 2) {
				$messages = $this->fetchMessages(1, $receiver);
			} elseif ($type == 3) {
				$messages = $this->fetchMessages(0, $receiver, $this->chat_id, $this->start);
			} 
		}
		
		if (empty($messages)) {
			return false;
		}
		// Update the message status to seen
		if($type !== 1) {
			if ($is_group && $preserve === 0) {
				// if this is a group chat delete all the listed notifications
				foreach ($messages as $_cid) {
					$this->fetchGroupMessages(3, $receiver, $_cid['cid']);
				}
			} else {
				// Else just update the message status
				$this->fetchMessages(4, $receiver);
			}
		}

		$messages = array_reverse($messages);

		if(array_key_exists($configuration['per_messenger'], $messages)) {
			$readmore = 1;
			
			// Unset the first array element used to predict if the Load More Messages should be shown
			unset($messages[0]);
		}
  
		foreach ($messages as $cmsg) {
			// Get the user's profile data
			$profile_name = $this->realName($cmsg['username'], $cmsg['fname'], $cmsg['lname']);
			$profile_link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$cmsg['username']);
			$clean_message = $this->rip_tags($cmsg['message']);
			$clean_message = $this->decodeText($clean_message); 

			$time = $marxTime->timeAgo($cmsg['date'], 1);

			if ($cmsg['username'] == $user['username']) {
				$delete = '<a onclick="firstDelete({action:\'message\', type:1, id:'.$cmsg['cid'].'})" data-toggle="tooltip" data-placement="left" title="'.$LANG['delete'].'" class="delete-msg" id="delete_'.$cmsg['cid'].'"><i class="fa fa-trash text-danger px-1 hoverable rounded-circle"></i></a>';
				$seen = $cmsg['seen'] == 1 ? '<i class="fa fa-check text-success"></i>' : '';
				$read_msg .= '
			    <div class="outgoing_msg" id="message_'.$cmsg['cid'].'">
			      <div class="sent_msg">
			        <p>'.$clean_message.'</p>
			        <span class="time_date">
			        	'.$time.'
			        	<span class="teal-text">'.$seen.'</span>
			        	'.$delete.'
			        </span>
			      </div>
			    </div>';				
			} else { 
				$read_msg .= '
			    <div class="incoming_msg">
			      <a href="'.$profile_link.'" data-toggle="tooltip" data-placement="bottom" title="@'.$profile_name.'">
			        <div class="incoming_msg_img"> 
			      	  <img class="rounded-circle" src="'.getImage($cmsg['photo'], 1).'" alt="'.$cmsg['username'].'"> 
			        </div>
			      </a>
			      <div class="received_msg">
			        <div class="received_withd_msg">
			          <p>'.$clean_message.'</p>
			          <span class="time_date">
			          	'.$time.'
			          </span>
			        </div>
			      </div>
			    </div>';				
			}
		}
		if($readmore) {
			$more = '<div class="more-messages text-center grey-text"><a onclick="loadMessages('.htmlentities($receiver, ENT_QUOTES).', \'\', '.$messages[1]['cid'].', 1)" style="cursor: pointer;">'.$LANG['show_more'].'... </a><span class="more_loader"><span></div>';
		}	 

		return $more.$read_msg;
	}

	/**
	* Display the messages and show send new message input
	*/
	function messenger_master($sender, $receiver) {
		global $SETT, $LANG, $user, $databaseCL;  

		$fetch_msg = $this->messenger(0, $receiver);

		// Collect profile data
		str_ireplace('grpc', '', $this->thread, $is_group);
		$follow = '';
		if (isset($_SESSION['group_thread'])) {
			$project = $databaseCL->fetchProject($receiver)[0];
			$profile_link = cleanUrls($SETT['url'] . '/index.php?page=project&project='.$project['safe_link']);
			$profile_name = 'Project '.ucfirst($project['safe_link']);
			$profile_photo = $project['cover'];
			if ($project['status']) {
		        $follow = clickApprove($project['id'], $user['uid'], 1); 
		    }
		} else {
			$profile = $this->userData($receiver, 1);
			$profile_link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$profile['username']);
			$profile_name = $this->realName($profile['username'], $profile['fname'], $profile['lname']);
			$profile_photo = $profile['photo'];
			// Show the follow link 
			$follow = clickFollow($receiver, $user['uid']);
		}
		// Check and block chat follower 
		$blocked = $databaseCL->manageBlock($receiver);
		// Check if logged user is blocked 
		// Fetch messages
		$fetch_messages = $this->messenger(0, $receiver);
		$fetch_messages = $fetch_messages ? $fetch_messages : $this->messageError($LANG['too_quiet']);

		// Show the user's online status 
		if (isset($_SESSION['group_thread'])) {
			$this->data_online = $this->fetchGroupMessages(0, $receiver)[0]['date'];
		}
		$online = $this->online_state($receiver);

		// Show the input if the user is not blocked
		if ($blocked['status'] && $blocked['user'] == $user['uid']) {
			$input = '
			<h5 class="border blue-grey-border rounded m-2 p-3 blue-grey-text text-center grey lighten-5"> 
			  '.$LANG['cant_reply'].'
			</h5>';
		} else {
			$input = '
			<div class="form-group"> 
			  <textarea class="form-control" id="write_msg" rows="3" placeholder="'.$LANG['type_message'].'..."></textarea>
			</div>
			<div class="text-right"> 
				<button type="button" class="btn btn-light text send-message">Send</button>
			</div>';
		}

		// To prevent checking for incoming chats, set $is_group ? 'receiver' to $is_group ? 'group'
		$msg_receiver = $is_group ? 'receiver' : 'receiver'; 
        $messages = '
        <div class="msg_history p-2" id="messages_read">
          '.$fetch_messages.'
        </div>
        <div id="loader"></div>
        <div class="my-2 chat-profile">
        	<img class="rounded" src="'.getImage($profile_photo, 1).'" alt="'.$profile_name.' Photo">
        	'.$online['icon'].'
        	<a href="'.$profile_link.'" class="px-1">'.$profile_name.'</a>
        	'.$follow.'
        	'.$blocked['link_icon'].'
        	<span class="float-right">'.$online['last_seen'].'</span>
        </div>
        <div class="type_msg">
          <div class="input_msg_write">
          	<input type="hidden" value="'.$receiver.'" id="message-'.$msg_receiver.'" />
          	'.$input.'
          </div>
        </div>';
        return $messages;
	}

	/**
	* Add the message to DB
	*/
	function send_message($receiver, $message) {
		global $LANG, $user, $databaseCL;

		$gms = null;
		$rec_data = $this->userData($receiver, 1);
		$sender = $user['uid'];

		if(strlen($message) > 300) {
			return messageNotice($LANG['message_too_long'], 3);
		} else {
			if (isset($_SESSION['group_thread'])) {
				// check for group members
			    $group_members = $databaseCL->fetch_projectCollaborators($receiver);
			    // check the group info
    			$project = $databaseCL->fetchProject($receiver)[0];
    			if (!$project) {
    				return messageNotice($LANG['group_not_exist'], 3);
    			} elseif (!$group_members) {
    				return messageNotice('This group has no members', 3);
    			}
    			$thread_prepend = $_SESSION['group_thread']; 
			} else {
				if($receiver == $user['uid']) {
					return messageNotice($LANG['message_self'], 3);
				} elseif(!$rec_data['username']) {
					return messageNotice($LANG['user_not_exist'], 3);
				}
    			$thread_prepend = $this->generateToken(10);
			}
		}

		$check_msgs = $this->fetchMessages(0, $receiver)[0];
		if ($check_msgs) {
			$thread = $check_msgs['thread'];
		} else {
			$thread = $thread_prepend;
			if (!$check_msgs['thread']) {
				$this->dbProcessor("UPDATE messenger SET `thread` = '$thread' WHERE (`sender` = '$sender' AND `receiver` = '$receiver') OR (`sender` = '$receiver' AND `receiver` = '$sender')", 0, 1);
			}
		}

		// Send the message immediately
		$message = $this->rip_tags($message);
		$sql = sprintf("INSERT INTO " . TABLE_MESSAGE . " (`sender`, `receiver`, `message`, `thread`, `seen`, `date`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)", $sender, $this->db_prepare_input($receiver), $this->db_prepare_input($message), $thread, 0);
		$send = $this->dbProcessor($sql, 0, 1);
		if ($send == 1) {
			// If this is a group chat, send notification
	        if (isset($_SESSION['group_thread'])) { 
	        	$cid = $this->dbProcessor(sprintf("SELECT cid FROM messenger WHERE `thread` = '%s' AND `sender` = '%s' ORDER BY `cid` DESC LIMIT 1", $thread, $user['uid']), 1)[0]['cid'];  
	        	// Notify all the group members
	        	// First delete the last notification for this user
	        	$this->fetchGroupMessages(3); 
	        	$_SESSION['delete_cid'] = $cid;
	        	foreach ($group_members as $mb) {
					$this->dbProcessor(sprintf("INSERT INTO message_status (`status`, `cid`, `uid`) VALUES ('1', '%s', '%s')", $cid, $mb['user']), 0);
	        	} 
	        }
	        // Return the message you just sent
			return $this->messenger(1, $receiver);
		}
	}	 

	function sendNotification($type = 0, $datatype = null, $data_id = null) {
		global $SETT, $configuration, $admin, $user, $framework;

		// Get the sender
		if (isset($this->sender)) {
			$sender = $this->sender;
		} elseif ($admin) {
			$sender = $admin['admin_user'];
		} else {
			$sender = $user['uid'];
		}

		// Get the receiver
		if (isset($this->receiver)) {
			$receiver = $this->receiver;
		} else {
			$receiver = $user['uid'];
		}

		// Get the receiver
		if (isset($this->notification)) {
			$notification = $this->notification;
		} else {
			$notification = '';
		}

		$i = 0; $count = 0;
		if (is_array($receiver)) { 
			foreach ($receiver as $uid) {
				$receiver = $uid; $i++;
				if ($type == 1) {
				   // Set the notification for following
				    $status = $framework->dbProcessor(sprintf("INSERT INTO notification (`uid`, `by`, `type`) VALUES ('%s', '%s', '0')", $receiver, $sender), 0, 1);
				} elseif ($type == 2) {
			      // Set the notification for likes
		        	$notification_type = $datatype == 2 ? 1 : 2;
		        	$status = $framework->dbProcessor(sprintf("INSERT INTO notification (`uid`, `by`, `object`, `type`) VALUES ('%s', '%s', '%s', '%s')", $receiver, $sender, $data_id, $notification_type), 0, 1); 
				} else {
					// Set the notification for admin (Custom)
					$status = $framework->dbProcessor(sprintf("INSERT INTO notification (`uid`, `by`, `content`, `type`) VALUES ('%s', '%s', '%s', '5')", $receiver, $sender, $notification), 0, 1);
				}				
			}
			$count = ($i-1);
		} else {
			if ($type == 1) {
			   // Set the notification for following
			    $status = $framework->dbProcessor(sprintf("INSERT INTO notification (`uid`, `by`, `type`) VALUES ('%s', '%s', '0')", $receiver, $sender), 0, 1);
			} elseif ($type == 2) {
		      // Set the notification for likes
	        	$notification_type = $datatype == 2 ? 1 : 2;
	        	$status = $framework->dbProcessor(sprintf("INSERT INTO notification (`uid`, `by`, `object`, `type`) VALUES ('%s', '%s', '%s', '%s')", $receiver, $sender, $data_id, $notification_type), 0, 1); 
			} else {
				// Set the notification for admin (Custom)
				$status = $framework->dbProcessor(sprintf("INSERT INTO notification (`uid`, `by`, `content`, `type`) VALUES ('%s', '%s', '%s', '5')", $receiver, $sender, $notification), 0, 1);
			}
		}
		return array($status, $count);
	}
	
	/**
	* Fetch friends
	*/
	function friendship($user_id = null, $string = null) {
		global $SETT, $LANG, $configuration, $user, $marxTime, $framework, $databaseCL; 

		$process = $databaseCL->fetchFollowers($user_id, 1);
		$person = $chat_btn = '';
		if ($process) {
			foreach ($process as $followers) {
				$_link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$followers['username']);
			    $chat_link = showMessageLink($followers['uid'], 'mx-5 pc-font-1_5'); 

				$person .= '
				<div class="friend">
					<a href="'.$_link.'">
						<i class="ion-ios-person"></i>
						'.$framework->realName($followers['username'], $followers['fname'], $followers['lname']).'
					</a> 
					'.$chat_link.'
				</div>';
			}
		}
		return $person;
	}

	/**
	* Fetch active chats
	*/
	function activeChats($user_id = null, $type = null, $string = null) {
		global $SETT, $LANG, $configuration, $user, $marxTime, $databaseCL;
		$messaging = new social;
		// Type 0: All Followers
		// Type 1: Search Followers
		$timeNow = time();
		$user_id = $user_id ? $user_id : $user['uid'];
 
		if ($type == 0) {
			$sql = sprintf("
				SELECT thread FROM messenger WHERE `thread` != '' AND (`sender` = '%s' OR `receiver` = '%s') OR
				(`sender` != '%s' AND `sender` IN (SELECT `sender` FROM messenger WHERE `thread` LIKE '%s'))
				GROUP BY thread
				ORDER BY `thread` DESC", $this->db_prepare_input($user_id), $this->db_prepare_input($user_id), $this->db_prepare_input($user_id), '%grpc_%');
		} elseif ($type == 1) {
			if ($string) {
				$sql = sprintf("
				SELECT * FROM users WHERE (`username` LIKE '%s' OR concat_ws(' ', `fname`, `lname`) LIKE '%s') AND `uid` IN (
					SELECT follower_id FROM relationship WHERE `leader_id` = '%s'
				) ORDER BY `online` DESC", '%'.$this->db_prepare_input($string).'%', '%'.$this->db_prepare_input($string).'%', $this->db_prepare_input($user_id));		
			} else {
				$sql = sprintf("
				SELECT * FROM users WHERE `uid` IN (
					SELECT follower_id FROM relationship WHERE `leader_id` = '%s'
				) ORDER BY `online` DESC", $this->db_prepare_input($user_id));
			} 
		} else {
			// Show online followers
			$sql = sprintf("SELECT * FROM users WHERE `id` IN (%s) AND `online` > '%s'-'%s' ORDER BY `online` DESC", db_prepare_input($subs), $timeNow, $this->online_time);
		}
		
		$process = $this->dbProcessor($sql, 1);

		$follow_list = '';
		if ($type == 0 || $type == 1) {
			if (isset($process)) {
				foreach ($process as $thread) {
					if ($type == 1) {
						$user_profile_id = $thread['uid'];  
					} else {
						$thread = $thread['thread'];
						$message = $this->dbProcessor("SELECT * FROM messenger WHERE `thread` = '$thread' ORDER BY `date` DESC", 1)[0];
						$user_profile_id = $message['sender'] == $user_id ? $message['receiver'] : $message['sender'];
					}

					str_ireplace('grpc', '', $thread, $is_group);
					if ( $is_group) {
						$projc = $databaseCL->fetchProject($message['receiver'])[0];
						$profile = $this->userData($message['sender'], 1);
						$last_msg = $messaging->fetchGroupMessages(4, $message['sender'])[0];
						$last_msg['message'] = $message['message'];
						$user_profile_id = ($projc['id'] ? $projc['id'] : $last_msg['receiver']);
						$prjt = ' on Project '.$projc['title'];
						$last_msg_thread = $message['thread'];
					} else {
						$profile = $this->userData($user_profile_id, 1);
 						$last_msg = $messaging->fetchMessages(5, $user_profile_id)[0];
						$last_msg_thread = $last_msg['thread'];
 						$prjt = '';
					}
					$profile_name = $this->realName($profile['username'], $profile['fname'], $profile['lname']);
					$active = isset($this->active) && $this->active == $key['id'] ? 'active_chat' : '';
 					
 					$last_msg_date = $last_msg['thread'] ? $marxTime->timeAgo($last_msg['date'], 2) : '';
					$msg_query = $last_msg['thread'] ? '&cid='.$last_msg['cid'].'&r_id='.$user_profile_id.'&thread='.$last_msg_thread : '&r_id='.$user_profile_id;
					$messaging_link = cleanUrls($SETT['url'] . '/index.php?page=account&view=messages'.$msg_query);

					// Set icon to show online status
					if(($timeNow - strtotime($profile['online'])) > $configuration['online_time']) {
						$icon = 'warning';
					} else {
						$icon = 'success';
					}
					$online = $this->online_state($profile['uid']);

					$bold = $last_msg['seen'] ? '' : ' class="font-weight-bold"';
					$last_msg_text = $last_msg['message'] ? $last_msg['message'] : $LANG['start_a_message'];

					$follow_list .= '
					<a href="'.$messaging_link.'" id="hoverable">
						<div class="chat_list '.$active.'">
							<div class="chat_people">
								<div class="chat_img">
									<img class="rounded-circle" src="'.getImage($profile['photo'], 1).'" alt="'.$profile_name.'_photo">
								</div>
								<div class="chat_ib">
									<h5>'.$profile_name.$prjt.'
									'.$online['icon'].'
									<span class="chat_date">'.$last_msg_date.'</span></h5>
									<p'.$bold.'>'.$this->myTruncate($last_msg_text, 80, ' ', '').'</p>
								</div>
							</div>
						</div>
					</a>';
				} 				
			} else {
				$follow_list = '
				<div class="chat_list">
					<div class="chat_people">
						<div class="chat_ib"><h5>'.$LANG['nobody_here'].'</h5></div>
					</div>
				</div>';
			}

		}
		return $follow_list;
	}

	function messageError($error) {
		return '<div class="message-error">'.$error.'</div>';
	}	
}

/**
 * Class to handle all recovery operations
 */
class doRecovery extends framework { 
	public $LANG, $username;	// The username to recover
	
	function verify_user() {
		global $LANG;
		// Query the database and check if the username exists
		$result = $this->userData($this->email_address);  
		
		// If user is verified or found
		if ($result) {

			// Generate the recovery key
			$data = $result;
			$this->list = array($data['id'], $data['username'], $data['email']);
			$sentToken = $this->setToken($data['username']);
			
			// If the recovery key has been generated
			if($sentToken) {
				// Return the username, email and recovery key
				return $sentToken;
			}
		} else {
			return messageNotice($LANG['not_found_email'], 2);
		}
	}
	
	function setToken($username) {
		global $SETT, $LANG, $configuration;
		// Generate the token
		$key = $this->generateToken(5, 1);
				
		// Prepare to update the database with the token
		$date = date("Y-m-d H:i:s");
		$sql = sprintf("UPDATE ".TABLE_USERS." SET `token` = '%s', `date` = '%s' WHERE `username` = '%s'", $this->db_prepare_input($key), $date, $this->db_prepare_input(mb_strtolower($username))); 
		 
		$result = $this->dbProcessor($sql, 0, 1); 

		$link = cleanUrls($SETT['url'].'/index.php?page=account&password_reset=true&username='.$username.'&token='.$key);
		$msg = sprintf($LANG['recovery_msg'], $configuration['site_name'], $link, $link);	
		$subject = ucfirst(sprintf($LANG['recovery_subject'], $username, $configuration['site_name']));
		
		list($uid, $username, $email) = $this->list;

		$this->username = $username;
		$this->content = $msg;
		$this->message = $this->emailTemplate();
		$this->user_id = $uid;  
		$this->activation = 1;
		$this->mailerDaemon($SETT['email'], $email, $subject);

		// If token was updated return token
		if($result == 1) {
			return messageNotice($LANG['recovery_sent'], 1);
		} else {
			return false;
		}
	}
	
	function changePassword($username, $password, $token) {
		global $framework;
		// Check if the username and the token exists
		$sql = sprintf("SELECT `username` FROM ".TABLE_USERS." WHERE `username` = '%s' AND `token` = '%s'", $this->db_prepare_input(mb_strtolower($username)), $this->db_prepare_input($token));
		$result = $this->dbProcessor($sql, 1);
		
		// If a valid match was found
		if ($result) {
			$password = hash('md5', $framework->db_prepare_input($password));
			
			// Change the password
			$sql = sprintf("UPDATE ".TABLE_USERS." SET `password` = '%s', `token` = '' WHERE `username` = '%s'", $password, $this->db_prepare_input(mb_strtolower($username)));  

			$result = $this->dbProcessor($sql, 0, 1);

			if($result == 1) {
				return true;
			} else {
				return false;
			}
		}
	}
}

/**
 * Class to manage all database entries
 */
class databaseCL extends framework {
	
	/**
	 * This function is very powerful as it will delete the user and all his associated records from the program
	 * @param  variable $id is the identifier of the user to delete
	 * @param  variable $fb is used as fallback when an ajax xhr request type is not possible for an ajax request
	 * @return Boolean     returns true if a user was deleted else it returns false
	 */
	function deleteUser($id, $fb = null, $test = null) {
		$id = $this->db_prepare_input($id);

		// Test if you can get to this function
		if (isset($test)) {
			return messageNotice('You have reached the delete user function, removing the $test argument will execute this function. <b>UID: '.$id.'</b>');
		}

		// Try to delete the user from the db
		$destroy = $this->dbProcessor("DELETE FROM `users` WHERE `uid` = '{$id}'", 0, 1);

		// If the user was deleted successfully
		if ($destroy == 1) {

			// Delete the profile and cover photos
			$trs = $this->dbProcessor("SELECT cover,photo FROM users WHERE `uid` = '{$id}'", 1);
			if ($trs) {
				foreach($trs as $rows) {
					deleteFile($rows['cover'], 1, $fb);
					deleteFile($rows['photo'], 1, $fb);
				}
			}

			// Delete project stem files
			$trs1 = $this->dbProcessor("SELECT file FROM stems WHERE `user` = '{$id}'", 1);
			if ($trs1) {
				foreach($trs1 as $rows) {
					deleteFile($rows['file'], null, $fb); 
				}
			}

			// Delete Project
			$trs2 = $this->dbProcessor("SELECT cover,instrumental,datafile FROM projects WHERE `creator_id` = '{$id}'", 1);
			if ($trs2) {
				foreach($trs2 as $rows) {
					deleteFile($rows['cover'], 1, $fb);
					deleteFile($rows['instrumental'], null, $fb); 
					deleteFile($rows['datafile'], 2, $fb);
				}
			}

			// Delete project instrumentals
			$trs3 = $this->dbProcessor("SELECT file FROM instrumentals WHERE `user` = '{$id}'", 1);
			if ($trs3) {
				foreach($trs3 as $rows) {
					deleteFile($rows['file'], null, $fb); 
				}
			}

			// Delete tracks
			$trs4 = $this->dbProcessor("SELECT art,audio FROM tracks WHERE `uid` = '{$id}'", 1);
			if ($trs4) {
				foreach($trs4 as $rows) {
					deleteFile($rows['art'], 1, $fb);
					deleteFile($rows['audio'], null, $fb);
				}	
			}		

			// Delete associated records
		    $this->dbProcessor("DELETE FROM albumentry WHERE album IN (SELECT id FROM albums WHERE `by` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM albumentry WHERE track IN (SELECT id FROM tracks WHERE `artist_id` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM collaborators WHERE project IN (SELECT id FROM projects WHERE `creator_id` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM collabrequests WHERE project IN (SELECT id FROM projects WHERE `creator_id` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM instrumentals WHERE project IN (SELECT id FROM projects WHERE `creator_id` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM likes WHERE `type` = '2' AND item_id IN (SELECT id FROM tracks WHERE `artist_id` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM likes WHERE `type` = '1' AND item_id IN (SELECT id FROM albums WHERE `by` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM playlistfollows WHERE playlist IN (SELECT id FROM playlist WHERE `by` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM playlistentry WHERE track IN (SELECT id FROM tracks WHERE `artist_id` = '{$id}')", 0); 
		    $this->dbProcessor("DELETE FROM stems WHERE project IN (SELECT id FROM projects WHERE `creator_id` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM playlistentry WHERE playlist IN (SELECT id FROM playlist WHERE `by` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM views WHERE track IN (SELECT id FROM tracks WHERE `uid` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM playlistfollows WHERE `subscriber` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM projects WHERE `creator_id` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM stems WHERE `user` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM collaborators WHERE `user` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM collabrequests WHERE `user` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM instrumentals WHERE `user` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM likes WHERE `user_id` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM playlist WHERE `by` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM relationship WHERE `leader_id` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM relationship WHERE `follower_id` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM views WHERE `by` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM tracks WHERE `uid` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM albums WHERE `by` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM users WHERE `uid` = '{$id}'", 0);
			return true;
		} else {
			return false;
		}
	}

	function deleteTrack($id, $test = null) {

		// Test if you can get to this function
		if (isset($test)) {
			return messageNotice('You have reached the delete track function, removing the $test argument will execute this function. <b>ID: '.$id.'</b>');
		}

		// if the user id was passed, start deleting
		$destroy = $this->dbProcessor("DELETE FROM tracks WHERE `id` = '{$id}'", 0, 1);

		// If the user was deleted successfully
		if ($destroy == 1) { 
		    // Delete all related records of this track 
		    // Remove from sale
		    $this->dbProcessor("DELETE FROM playlistentry WHERE track = '{$id}'", 0); 
		    $this->dbProcessor("DELETE FROM likes WHERE `type` = '2' AND item_id  = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM likes WHERE `type` = '1' AND item_id  = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM views WHERE track = '{$id}'", 0); 
			return true;
		} else {
			return $destroy;
		}
	}

	function deleteProject($id, $test = null) {
		// Test if you can get to this function
		if (isset($test)) {
			return messageNotice('You have reached the delete project function, removing the $test argument will execute this function. <b>ID: '.$id.'</b>');
		}

		// if the id was passed, start deleting
		$destroy = $this->dbProcessor("DELETE FROM projects WHERE `id` = '{$id}'", 0, 1);

		// If the project was deleted successfully
		if ($destroy == 1) { 
		    // Delete all related records of this tproject 
		    $this->dbProcessor("DELETE FROM projects WHERE `id` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM stems WHERE `project` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM collaborators WHERE `project` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM collabrequests WHERE `project` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM instrumentals WHERE `project` = '{$id}'", 0);
		    return TRUE;
		} else {
			return $destroy;
		}
	}

	/**
	 * This function is another powerful function that will delete the user in the new release scope and all his associated 
	 * records from the program
	 * @param  variable $id is the identifier of the user to delete
	 * @param  variable $fb is used as fallback when an ajax xhr request type is not possible for a ajax request
	 * @return Boolean     returns true if a user was deleted else it returns false
	 */
	function deleteReleaseArtist($id, $fb = null) {
		$id = $this->db_prepare_input($id);

		// if the user id was passed, start deleting
		$destroy = $id ? 1 : 0;

		// If the user was deleted successfully
		if ($destroy == 1) { 
			// Delete the user if created
			$userdata = $this->userData($id, 1);
			if ($userdata) {
				$this->deleteUser($userdata['uid'], $fb);
			}

			// Delete release profile photos
			$trs = $this->dbProcessor("SELECT photo FROM new_release_artists WHERE `username` = '{$id}'", 1);
			if ($trs) { 
				deleteFile($trs[0]['photo'], 1, $fb);  
			}

			// Delete release track files
			$trs1 = $this->dbProcessor("SELECT audio FROM new_release_tracks WHERE `release_id` IN (SELECT release_id FROM new_release_artists WHERE `username` = '{$id}')", 1); 
			if ($trs1) {
				foreach($trs1 as $rows) { 
					deleteFile($rows['audio'], null, $fb); 
				} 	
			}

			// Delete release files
			$trs2 = $this->dbProcessor("SELECT art FROM new_release WHERE `release_id` IN (SELECT release_id FROM new_release_artists WHERE `username` = '{$id}')", 1); 
			if ($trs2) {
				foreach($trs2 as $rows) { 
					deleteFile($rows['art'], 1, $fb); 
				} 
			}		

			// Delete associated records 
		    $this->dbProcessor("DELETE FROM new_release_tracks WHERE `release_id` IN (SELECT release_id FROM new_release_artists WHERE `username` = '{$id}')", 0); 
		    $this->dbProcessor("DELETE FROM new_release WHERE `release_id` IN (SELECT release_id FROM new_release_artists WHERE `username` = '{$id}')", 0); 
		    $this->dbProcessor("DELETE FROM `new_release_artists` WHERE `username` = '{$id}'", 0, 1);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Either delete or approve a release
	 * @param  variable $id is the identifier of the release to act upon
	 * @param  variable $type is used to determine if this is an approve release or delete release request 
	 * Type (
	 * 	1: approve,
	 * 	2: delete without removing fies, (Remove from sale) 
	 * 	0/null: delete)
	 * @return Boolean     returns true if an action was successful
	 */
	function salesAction($id, $type = null) {
		global $SETT, $LANG, $configuration, $user, $marxTime;

		$id = $this->db_prepare_input($id);
		// If type == 1: Approve (Set status to 3)
		// If type == 2: Remove from sale (Set status to 0)
		$status = $type == 1 ? 3 : 0; 
		$date = date('Y-m-d', strtotime('today')); 

		// Check if this is a premium release then set the premium variable to 1
		$invoice = $this->fetchPayments(1, $id);
		$premium = ($invoice['rid'] === $id ? 1 : 0);

		if ($type == NUll || $type == 0) {
			// This completes Type: 0 (Delete Files) Without actually making any changes
			$update = 1;
		} else {
			$set_date = $type == 1 ? '\''.$date.'\'' : 'NULL';
			$sql = sprintf("UPDATE new_release SET `status` = '%s', `approved_date` = %s WHERE `release_id` = '%s'", $status, $set_date, $id);
			$update = $this->dbProcessor($sql, 0, 1);
		}

		if ($update == 1 && !$type) { 

			// Delete tracks
			$trs4 = $this->dbProcessor("SELECT art,audio FROM tracks WHERE `release_id` = '{$id}'", 1);
			if ($trs4) {
				foreach($trs4 as $rows) {
					deleteFile($rows['art'], 1);
					deleteFile($rows['audio'], null);
				}	
			}
			// Delete Albums
			$trs4 = $this->dbProcessor("SELECT art,id FROM albums WHERE `release_id` = '{$id}'", 1);
			if ($trs4) {
				foreach($trs4 as $rows) {
					deleteFile($rows['art'], 1);
				}
			}	

			// Delete associated records 
		    $this->dbProcessor("DELETE FROM new_release_tracks WHERE `release_id` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM `new_release_artists` WHERE `release_id` = '{$id}'", 0);
		    $delete_release = $this->dbProcessor("DELETE FROM new_release WHERE `release_id` = '{$id}'", 0, 2);

		    if ($delete_release) {
				return true;
		    }
		    return false;
		} elseif ($type == 2 && $update == 1) {
		    // Delete all related records of this track 
		    // Remove from sale
		    $this->dbProcessor("DELETE FROM playlistentry WHERE track IN (SELECT id FROM tracks WHERE `release_id` = '{$id}')", 0); 
		    $this->dbProcessor("DELETE FROM likes WHERE `type` = '2' AND item_id IN (SELECT id FROM tracks WHERE `release_id` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM likes WHERE `type` = '1' AND item_id IN (SELECT id FROM albums WHERE `release_id` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM views WHERE track IN (SELECT id FROM tracks WHERE `release_id` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM albumentry WHERE album IN (SELECT id FROM albums WHERE `release_id` = '{$id}')", 0);
		    $this->dbProcessor("DELETE FROM tracks WHERE `release_id` = '{$id}'", 0);
		    $this->dbProcessor("DELETE FROM albums WHERE `release_id` = '{$id}'", 0);
			return true;
		} elseif ($type == 1 && $update == 1) {
			// Approve the release
			$release_data = $this->fetchReleases(1, $id);
			$release_tracks = $this->fetchRelease_Audio(1, $id);

			$release_artist = $this->fetchRelease_Artists(1, $id)[0];
			$userdata = $this->userData($release_artist['username'], 1); 
			if (!$userdata) {	
				$named = explode(' ', $release_artist['name']);							
				if (count($named)>1) {
					$fname = $named[0];
					$lname = $named[1];
				} else {
					$fname = $named[0];
					$lname = '';
				}
				if ($release_artist['photo']) {
					$artwork = $release_artist['photo'];
				} else {
					$artwork = $release_data[0]['art'];
				}
				// Create the new artist
				$sql = sprintf(
					"INSERT INTO users (`username`, `password`, `intro`, `fname`, `lname`, `photo`, `label`, `role`) 
					VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', 2)", $release_artist['username'], md5(rand(10000,90000)),
					$release_artist['intro'], $fname, $lname, $artwork, $release_data[0]['label']
				);
				$cu = $this->dbProcessor($sql, 0, 1);
				if ($cu != 1) {
					echo bigNotice($cu, null, 'mt-5');
				}
				$userdata = $this->userData($release_artist['username'], 1); 
			}

			// Add the tracks to the database
			$ver_tracks = $this->dbProcessor("SELECT release_id, id FROM tracks WHERE `release_id` = '{$id}'", 1);
			$r_data = $release_data[0];
			if (!$ver_tracks) {
				foreach ($release_tracks as $rt => $r_tracks) {  
					$safelink = $this->safeLinks($r_tracks['title']);
					$sql = sprintf(
						"INSERT INTO tracks (`uid`, `aggregator_id`, `artist_id`, `title`, `description`, `label`, `genre`, 
							`s_genre`, `pline`, `cline`, `release`, `release_date`, `art`, `audio`, `release_id`, 
							`safe_link`, `tags`, `public`) 
						VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 
							'%s', '%s', '%s')", $r_data['by'], $r_data['by'], $userdata['uid'], $r_tracks['title'], 
							$r_data['description'], $r_data['label'], $r_data['p_genre'], $r_data['s_genre'], 
							$r_data['p_line'], $r_data['c_line'], $date, $date, $r_data['art'], $r_tracks['audio'], 
							$r_data['release_id'], $safelink, $r_data['tags'], 1
					);
					$new_track = $this->dbProcessor($sql, 0, 3);

					if (strpos($this->rip_tags($new_track), 'SQLSTATE') == null) {
						// If the release is premium add to the curated playlists
						if ($premium) {
							$_playlists = $this->dbProcessor("SELECT * FROM playlist WHERE `featured` = '1' AND MATCH (title) AGAINST ('{$r_data['p_genre']}, {$r_data['tags']}, {$configuration['curated_keyword']}, {$r_tracks['title']}, {$r_data['s_genre']}' IN NATURAL LANGUAGE MODE)", 1);
							$this->dbProcessor("UPDATE tracks SET `featured` = '1' WHERE `id` = '$new_track'", 0);
							foreach ($_playlists as $playlist) {
								$this->dbProcessor("INSERT INTO playlistentry (`playlist`, `track`) VALUES ('{$playlist['id']}', '$new_track')", 0, 3); 
							}
						}
					} else {
						echo bigNotice($new_track, null, 'mt-5');
					}
				}
			}

			// If the tracks are more than 1, add the tracks to an album
			$track_count = count($release_tracks);
			if ($track_count > 1) {
				$safelink = $this->safeLinks($r_data['title']);
				$ver_album = $this->dbProcessor("SELECT id FROM albums WHERE `release_id` = '{$id}' AND `safe_link` = '{$safelink}'", 1); 
				if (!$ver_album) {
					$v_a = $this->fetchAlbum($safelink);
					if ($v_a) {
						$safelink = $this->safeLinks($r_data['title'].' '.rand(1000,9000));
					}
					$sql = sprintf(
						"INSERT INTO albums (`by`, `title`, `description`, `label`, `pline`, `cline`, `release_date`, 
							`art`, `release_id`, `safe_link`, `tags`, `public`) 
						VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
							$userdata['uid'], $r_data['title'], $r_data['description'], $r_data['label'], $r_data['p_line'], 
							$r_data['c_line'], $date, $r_data['art'], $r_data['release_id'], $safelink, $r_data['tags'], 1
					);

					// If you have created the album, link the track files
					$create = $this->dbProcessor($sql, 0, 1); 
					if ($create == 1) {
						$fetch_tracks = $this->dbProcessor("SELECT id FROM tracks WHERE `release_id` = '{$id}'", 1); 
						$fetch_album = $this->dbProcessor("SELECT id FROM albums WHERE `release_id` = '{$id}'", 1)[0]; 
						if ($fetch_tracks) {
							foreach ($fetch_tracks as $ft => $tracks) {   
								$sql = sprintf("INSERT INTO albumentry (`album`, `track`) VALUES ('%s', '%s')", 
									$fetch_album['id'], $tracks['id']);
								$this->dbProcessor($sql, 0, 1);
							}
						}	
					} else {
						echo bigNotice($create, null, 'mt-5');
					}
				}			
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * This function would fetch tracks from the album entry table enabling users to view those tracks as an album
	 * @param  [variable] $id [id of the album to fetch tracks from]
	 * @param  [variable] $t  [if this is specified as 1 it will limit the results to just one else it will return all records]
	 * @return [array]     [this is an array containing the album records]
	 */
	function albumEntry($id, $t=null) {
		$order_limit = $t == 1 ? 'ASC LIMIT 1' : 'ASC';

		if (isset($this->type) && $this->type == 1) {
			// Count all relevant album records
			$sql = sprintf("SELECT count(tracks.id) AS track_count FROM albumentry,users,tracks WHERE (`albumentry`.`album` = '%s' AND `albumentry`.`track` = `tracks`.`id` AND `tracks`.`uid` = `users`.`uid` AND `tracks`.`public` = 1) OR (`albumentry`.`album` = '%s' AND `albumentry`.`track` = `tracks`.`id` AND `tracks`.`uid` = `users`.`uid` AND `tracks`.`uid` = '%s') ORDER BY `albumentry`.`id` %s", $this->db_prepare_input($id), $this->db_prepare_input($id), $this->user_id, $order_limit);
		} else {
			// Fetch all relevant album records
			$sql = sprintf("SELECT * FROM albumentry,users,tracks WHERE (`albumentry`.`album` = '%s' AND `albumentry`.`track` = `tracks`.`id` AND `tracks`.`uid` = `users`.`uid` AND `tracks`.`public` = 1) OR (`albumentry`.`album` = '%s' AND `albumentry`.`track` = `tracks`.`id` AND `tracks`.`uid` = `users`.`uid` AND `tracks`.`uid` = '%s') ORDER BY `albumentry`.`id` %s", $this->db_prepare_input($id), $this->db_prepare_input($id), $this->user_id, $order_limit);
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetchAlbum($id, $type=null) {
		// 2: Count all of this artists albums
		// 1: Fetch all of this artists albums
		// 0 or Null: Fetch one albums
		if ($type == 1) {
			$sql = sprintf("SELECT * FROM users,albums WHERE 1 AND `albums`.`by` = '%s' AND `users`.`uid` = `albums`.`by`", $this->db_prepare_input($id));
		} elseif ($type == 2) {
			$sql = sprintf("SELECT COUNT(id) AS counter FROM albums WHERE `by` = '%s'", $this->db_prepare_input($id));
		} else {
			$sql = sprintf("SELECT * FROM users,albums WHERE `users`.`uid` = `albums`.`by` AND (`albums`.`id` = '%s') OR (`albums`.`safe_link` = '%s')", $this->db_prepare_input($id), $this->db_prepare_input($id));
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetchTracks($artist_id, $type=null) {
		global $user, $configuration;
		// 6: Get all tracks on the site
		// 5: just select all tracks by this artist and return an array of id as list
		// 4: Count the views on all tracks by this artist
		// 3: Get all tracks by this artist
		// 2: Get a particular track
		// 1: Get the most popular track
		// 0: Get all tracks not in an album
		// 
        $limit = isset($this->limiter) ? sprintf(' ORDER BY id DESC LIMIT %s, %s', $this->start, $this->limiter) : '';
        $filter = isset($this->filter) ? $this->filter : '';

		if ($type == 1) {
			$sql = sprintf("SELECT * FROM users,tracks WHERE tracks.artist_id = '%s' AND users.uid = tracks.artist_id AND tracks.id = (SELECT MAX(`track`) FROM `views` WHERE tracks.id = views.track)", $this->db_prepare_input($artist_id));
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM tracks,users WHERE users.uid = tracks.artist_id AND tracks.id = '%s' OR tracks.safe_link = '%s'", $this->db_prepare_input($this->track), $this->db_prepare_input($this->track));
		} elseif ($type == 3) {
			$next = "";
			if (isset($this->last_id)) { 
				$next = " AND id > ".$this->last_id;
			} 
			if (isset($this->counter)) {
				// Count the tracks
				$sql = sprintf("SELECT COUNT(id) AS counter FROM tracks WHERE artist_id = '%s'%s", $this->db_prepare_input($artist_id), $next);
			} elseif (isset($this->personal_id)) {
				$artist_id = $this->personal_id;
				$sql = sprintf("SELECT *, (SELECT COUNT(`id`) FROM tracks WHERE artist_id = '%s') AS counter FROM users,tracks WHERE tracks.artist_id = '%s' AND users.uid = tracks.artist_id%s ORDER BY id LIMIT %s", $this->db_prepare_input($artist_id), $this->db_prepare_input($artist_id), $next, $configuration['page_limits']);
			} else {
				$sql = sprintf("SELECT *, (SELECT COUNT(`id`) FROM tracks WHERE artist_id = '%s') AS counter FROM users,tracks WHERE tracks.artist_id = '%s' AND users.uid = tracks.artist_id AND tracks.public = '1'%s ORDER BY id LIMIT %s", $this->db_prepare_input($artist_id), $this->db_prepare_input($artist_id), $next, $configuration['page_limits']);
			}
		} elseif ($type == 4) {
			$sql = sprintf("SELECT count(`track`) AS counter FROM views WHERE `track` IN (SELECT id FROM tracks WHERE `artist_id` = '%s')", $this->db_prepare_input($artist_id));
		} elseif ($type == 5) {
			$sql = sprintf("SELECT `id` FROM `tracks` WHERE `artist_id` = '%s'", $this->db_prepare_input($artist_id));
			$list = $this->dbProcessor($sql, 1);
			$rows = [];
			if ($list) {
				foreach ($list as $key => $value) {
					$rows[] = $value['id'];
				}
			}
			return $rows;
		} elseif ($type == 6) {
			$sql = sprintf("SELECT * FROM users, tracks WHERE users.uid = tracks.artist_id%s%s", $filter, $limit);
		} else {
			$sql = sprintf("SELECT * FROM users,tracks WHERE tracks.artist_id = '%s' AND users.uid = tracks.artist_id AND tracks.id NOT IN (SELECT track FROM albumentry WHERE 1)", $this->db_prepare_input($artist_id));
		}
		$this->limiter = $this->filter = null;
		return $this->dbProcessor($sql, 1);
	}

	function fetchStats($type = null, $id = null) {
		// $type == 1: Return stats for a particular track
		// $type == 0 or null: Return stats for a list of tracks (!important => Track and explore page)
		if ($type == 1) {
			$sql = sprintf("SELECT (SELECT count(`track`) FROM `views` WHERE `track` = '%s') as total, (SELECT count(`track`) FROM `views` WHERE `track` = '%s' AND CURDATE() = date(`time`)) as today, (SELECT count(`track`) FROM `views` WHERE `track` = '%s' AND CURDATE()-1 = date(`time`)) as yesterday, (SELECT count(`track`) FROM `views` WHERE `track` = '%s' AND `time` BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 14 DAY ) AND DATE_SUB( CURDATE( ) ,INTERVAL 7 DAY )) as last_week", $this->db_prepare_input($id), $this->db_prepare_input($id), $this->db_prepare_input($id), $this->db_prepare_input($id));
		} else {
			if(!$this->track_list) {
				return;
			}
			$sql = sprintf("SELECT (SELECT count(`track`) FROM `views` WHERE `track` IN (%s)) as total, (SELECT count(`track`) FROM `views` WHERE `track` IN (%s) AND CURDATE() = date(`time`)) as today, (SELECT count(`track`) FROM `views` WHERE `track` IN (%s) AND CURDATE()-1 = date(`time`)) as yesterday, (SELECT count(`track`) FROM `views` WHERE `track` IN (%s) AND `time` BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 14 DAY ) AND DATE_SUB( CURDATE( ) ,INTERVAL 7 DAY )) as last_week, (SELECT count(`track`) FROM `views` WHERE `track` IN (%s) AND `time` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) as last_month", $this->track_list, $this->track_list, $this->track_list, $this->track_list, $this->track_list);
		}
		return $this->dbProcessor($sql, 1);
	}

	function addViews($id, $type = null) {
		global $user;
		return $this->dbProcessor(sprintf("INSERT INTO views (`track`, `by`) VALUES ('%s', '%s')", $id, $user['uid']), 0, 1);
	}

	function releaseStats($id, $dt = null) {
		global $user;

		$id = $this->db_prepare_input($id);
		$date = $dt ? '\''.$dt.'\'' : 'CURDATE()'; 

		if (isset($this->type)) {
			if ($this->type == 1) {	
				$sql = sprintf("
					SELECT count(`track`) AS quarterly_views, QUARTER(`time`) AS qt 
					FROM `views` 
					WHERE `track` IN (SELECT `id` FROM tracks WHERE `release_id` IN (SELECT `release_id` FROM new_release WHERE `by` = '%s'))
					GROUP BY qt", $id);
			} elseif ($this->type == 2) {
				$limit = isset($this->limit) ? ' LIMIT '.$this->limit : '';
				$sql = sprintf("
					SELECT track, count(`track`) AS views, (SELECT title FROM tracks WHERE `id` = track) AS title  
					FROM `views`
					WHERE `track` IN (SELECT `id` FROM tracks WHERE `release_id` IN (SELECT `release_id` FROM new_release WHERE `by` = '%s'))
					GROUP BY track ORDER BY views DESC%s", $id, $limit);
			} elseif ($this->type == 3) { 
				$sql = sprintf("
					SELECT track, count(`track`) AS views, (SELECT title FROM tracks WHERE `id` = track) AS title  
					FROM `views`
					WHERE `track` IN (SELECT `id` FROM tracks WHERE `release_id` = '%s') GROUP BY track", $id);
			}
		} else {
			$sql = sprintf("SELECT 
				(SELECT count(`id`) FROM new_release WHERE `status` = '0' AND `by` = '%s') AS removed, 
				(SELECT count(`id`) FROM new_release WHERE `status` = '1' AND `by` = '%s') AS incomplete, 
				(SELECT count(`id`) FROM new_release WHERE `status` = '2' AND `by` = '%s') AS pending, 
				(SELECT count(`id`) FROM new_release WHERE `status` = '3' AND `by` = '%s') AS approved,
				(SELECT count(`id`) FROM new_release WHERE `by` = '%s') AS total,
				(SELECT count(`track`) FROM `views` WHERE `track` IN (SELECT `id` FROM tracks WHERE `release_id` IN (SELECT `release_id` FROM new_release WHERE `by` = '%s'))) AS total_views,
				(SELECT count(`track`) FROM `views` WHERE `track` IN (SELECT `id` FROM tracks WHERE `release_id` IN (SELECT `release_id` FROM new_release WHERE `by` = '%s')) AND CURDATE() = date(`time`)) AS today_views,
				(SELECT count(`track`) FROM `views` WHERE `track` IN (SELECT `id` FROM tracks WHERE `release_id` IN (SELECT `release_id` FROM new_release WHERE `by` = '%s')) AND CURDATE()-1 = date(`time`)) AS yesterday_views,
				(SELECT count(`track`) FROM `views` WHERE `track` IN (SELECT `id` FROM tracks WHERE `release_id` IN (SELECT `release_id` FROM new_release WHERE `by` = '%s')) AND `time` BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 14 DAY ) AND DATE_SUB( CURDATE( ) ,INTERVAL 7 DAY )) AS lastweek_views,
				(SELECT count(`track`) FROM `views` WHERE `track` IN (SELECT `id` FROM tracks WHERE `release_id` IN (SELECT `release_id` FROM new_release WHERE `by` = '%s')) AND `time` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AS thismonth_views,
				(SELECT count(`track`) FROM `views` WHERE `track` IN (SELECT `id` FROM tracks WHERE `release_id` IN (SELECT `release_id` FROM new_release WHERE `by` = '%s')) AND `time` >= DATE_SUB({$date}, INTERVAL 1 QUARTER)) AS quarterly"
				, $id, $id, $id, $id, $id, $id, $id, $id, $id, $id, $id);
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetchViewers($type = null, $id = null) {
		// $type == 1: Track monthly viewers
		// $type == 0/NULL: Artist monthly viewers
		if ($type == 1) {
			$sql = sprintf("SELECT uid,username,fname,lname,photo,role FROM `views` AS v LEFT JOIN `users` AS u ON v.by = u.uid WHERE v.track = '%s' AND `time` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) GROUP BY u.uid ORDER BY `uid` DESC", $id);
		} else {
			$sql = sprintf("SELECT uid,username,fname,lname,photo,role FROM `views` AS v LEFT JOIN `users` AS u ON v.by = u.uid WHERE `track` IN (%s) AND `time` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) GROUP BY u.uid ORDER BY `uid` DESC", $this->track_list);
		}
		return $this->dbProcessor($sql, 1);
	}

	function userLikes($user_id, $item_id, $type) {
		global $user, $configuration;
		// $type: 4 = Select this users tracks that have been liked by others
		// $type: 3 = Liked Tracks Artist (Set $item_id to 0 or Null)
		// $type: 2 = Track Likes
		// $type: 1 = Album Likes
		
		$limit = (isset($this->limit) && $this->limit === true) ? " LIMIT ".$configuration['page_limits'] : '';
		if ($type == 3) {
			$next = isset($this->last_id) ? " AND artist_id > ".$this->last_id : '';
			$sql = sprintf("SELECT DISTINCT artist_id FROM tracks LEFT JOIN likes ON `tracks`.`id` = `likes`.`item_id` WHERE `tracks`.`uid` != '%s' AND `likes`.`user_id` = '%s'%s ORDER BY artist_id%s", $user_id, $user_id, $next, $limit);
		} elseif ($type == 4) {
    		$sql = sprintf("SELECT *, (SELECT COUNT(`id`) FROM `tracks` WHERE `id` IN (%s)) AS likes FROM `tracks` WHERE `id` IN (%s)", $this->track_list, $this->track_list);
		} elseif ($type == 5) {
			$next = isset($this->last_time) ? " AND time < date('".$this->last_time."')" : '';
    		$sql = sprintf("SELECT `time`,user_id AS artist_id FROM likes WHERE `item_id` = '%s' AND `type` = '%s'%s ORDER BY `time` DESC%s", $item_id, $this->type, $next, $limit);
		} else {
			$sql = sprintf("SELECT user_id, item_id FROM likes WHERE `user_id` = '%s' AND `item_id` = '%s' AND `type` = '%s'", $user_id, $item_id, $type);
		}

  		$check = $this->dbProcessor($sql, 1);
  		return $check;
	}

	function LikesCount($type, $id) {
		// $type: 3 = Count likes for a provided track or album and sort by time
		// $type: 2 = Track Likes
		// $type: 1 = Album Likes
		//
		global $user, $configuration;
		if ($type == 3) {
			$type = isset($this->like_type) ? $this->like_type : 2;
			$sql = sprintf("SELECT (SELECT count(`item_id`) FROM `likes` WHERE `item_id` = '%s' AND `type` = '%s') as total, (SELECT count(`item_id`) FROM `likes` WHERE `item_id` = '%s' AND CURDATE() = date(`time`) AND `type` = '%s') as today, (SELECT count(`item_id`) FROM `likes` WHERE `item_id` = '%s' AND CURDATE()-1 = date(`time`) AND `type` = '%s') as yesterday, (SELECT count(`item_id`) FROM `likes` WHERE `item_id` = '%s' AND `time` BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 14 DAY ) AND DATE_SUB( CURDATE( ) ,INTERVAL 7 DAY ) AND `type` = '%s') as last_week", $this->db_prepare_input($id), $type, $this->db_prepare_input($id), $type, $this->db_prepare_input($id), $type, $this->db_prepare_input($id), $type);
			$user = null;
			return $this->dbProcessor($sql, 1);
		} else {
			$sql = sprintf("SELECT COUNT(item_id) AS likes_count,item_id FROM likes WHERE `item_id` = '%s' AND `type` = '%s'", $id, $type);

	  		$check = $this->dbProcessor($sql, 1); 

	  		$likes_count = number_format($check[0]['likes_count']);
	  		$likes_count = '
	  			<span style="font-size: 18px;">
	  				<i class="ion-ios-heart text-danger"></i> 
	  				<span id="likes-count-'.$id.'">'.$likes_count.'</span>
	  			</span>';
			$user = null;
	  		return $likes_count;
	  	}
	}

	function listLikedItems($user_id, $type) {
		// $type: 2 = Track Likes
		// $type: 1 = Album Likes
		// 
		global $user, $configuration;

		if (isset($this->last_id)) { 
			$next = " AND likes.id > ".$this->last_id;
		} else {
			$next = "";
		}
		if ($type == 1) {
			if (isset($this->counter)) {
				// Count the albums
				$sql = sprintf("SELECT COUNT(likes.id) AS counter FROM likes,albums,users WHERE likes.user_id = '%s' AND albums.id = likes.item_id AND users.uid = albums.by AND likes.type = '%s'%s ORDER BY likes.id", $this->db_prepare_input($user_id), $type, $next);
			} else {
				$sql = sprintf("SELECT albums.id AS aid,art,fname,lname,safe_link,title,username,release_date,albums.by,likes.id AS like_id FROM likes,albums,users WHERE likes.user_id = '%s' AND albums.id = likes.item_id AND users.uid = albums.by AND likes.type = '%s'%s ORDER BY likes.id LIMIT %s", $this->db_prepare_input($user_id), $type, $next, $configuration['page_limits']);
			}
		} elseif ($type == 2) {
			$limit = isset($this->limit) ? $this->limit : $configuration['page_limits'];
			if (isset($this->counter)) {
				// Count the tracks
				$sql = sprintf("SELECT COUNT(likes.id) AS counter FROM likes,tracks,users WHERE likes.user_id = '%s' AND tracks.id = likes.item_id AND users.uid = tracks.artist_id AND likes.type = '%s'%s ORDER BY likes.id", $this->db_prepare_input($user_id), $type, $next);
			} else {
				$sql = sprintf("SELECT (SELECT COUNT(item_id) FROM likes WHERE likes.user_id = '%s' AND likes.type = '%s') AS likes, tracks.id AS id,art,fname,lname,audio,safe_link,title,views,username,artist_id,explicit,likes.id AS like_id FROM likes,tracks,users WHERE likes.user_id = '%s' AND tracks.id = likes.item_id AND users.uid = tracks.artist_id AND likes.type = '%s'%s ORDER BY likes.id LIMIT %s", $this->db_prepare_input($user_id), $type, $this->db_prepare_input($user_id), $type, $next, $limit);
			}
		}

  		$check = $this->dbProcessor($sql, 1);
  		return $check;
	}

	function fetchRelated($id, $type = null) {
		global $user, $configuration;
		// 3: Related Playlists
		// 2: Related tracks
		// 1: Related artists
		// 0: Similar albums
		$limit = isset($this->limit) ? ($this->limit !== true ? " LIMIT ".$this->limit : " LIMIT ".$configuration['page_limits']) : '';
		if ($type == 1) {
			$sql = sprintf("SELECT username,fname,lname,photo,cover,verified,uid,label FROM users WHERE `uid` != '%s' AND `uid` != '%s' AND (`username` LIKE '%s' OR `fname` LIKE '%s' OR `lname` LIKE '%s' OR `label` LIKE '%s') ORDER BY RAND()%s", $user['uid'], $id, '%'.$this->username.'%', '%'.$this->fname.'%', '%'.$this->lname.'%', '%'.$this->label.'%', $limit);
		} elseif ($type == 2) {
			$sql = sprintf("SELECT id AS track_id,art,artist_id,title,safe_link FROM tracks WHERE id != '%s' AND `public` = '1' AND (`title` LIKE '%s' OR `artist_id` LIKE '%s' OR `tracks`.`label` LIKE '%s' OR `pline` LIKE '%s' OR `cline` LIKE '%s' OR `genre` LIKE '%s' OR `tags` LIKE '%s') ORDER BY RAND() %s", $id, '%'.$this->title.'%', '%'.$this->artist_id.'%', '%'.$this->label.'%', '%'.$this->pline.'%', '%'.$this->cline.'%', '%'.$this->genre.'%', '%'.$this->tags.'%', $limit);
		} elseif ($type == 3) {
			$sql = sprintf("SELECT * FROM playlist WHERE id != '%s' AND `public` = '1' AND (`title` LIKE '%s') ORDER BY RAND() %s", $id, '%'.$this->title.'%', $limit);
		} else {
			$sql = sprintf("SELECT * FROM albums WHERE `id` != '%s' AND `public` = '1' AND (`title` LIKE '%s' OR `pline` LIKE '%s' OR `cline` LIKE '%s' OR `tags` LIKE '%s') ORDER BY RAND() %s", $id, '%'.$this->title.'%', '%'.$this->pline.'%', '%'.$this->cline.'%', '%'.$this->tags.'%', $limit);
		}
		$this->limit = null;
  		return $this->dbProcessor($sql, 1);
	}

	function fetchTopTracks($type=null, $user_id=null) {
		global $user, $configuration, $framework;
		// $type == 2: Select records from users not older than N date
		// $type == 1: Select records by genre not older than N date
		// $type == 0 or NULL: Select records by genre or tags
		//  

		$limit = $configuration['page_limits']; //$configuration['top_limits'];
		$tags = isset($this->tags) ? ' OR `tags` LIKE \'%'.$this->tags.'%\'' : '';
		if ($type == 1) {
			$sql = sprintf("SELECT id,audio,art,artist_id,title,safe_link,upload_time,genre,tags,explicit,views FROM tracks WHERE `public` = '1' AND `upload_time` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND (`genre` LIKE '%s'%s) ORDER BY views DESC LIMIT %s", '%'.$this->genre.'%', $tags, $limit);
		} elseif ($type == 2 || $type == 3 || $type == 4) {
			$sql = sprintf("SELECT id,audio,art,artist_id,title,safe_link,upload_time,genre,tags,explicit,views FROM tracks WHERE `public` = '1' AND (`artist_id` = '%s') ORDER BY views DESC LIMIT %s", $this->genre, $limit);
		} else {
			$sql = sprintf("SELECT id,audio,art,artist_id,title,safe_link,upload_time,genre,tags,explicit,views FROM tracks WHERE `public` = '1' AND (`genre` LIKE '%s'%s) ORDER BY views DESC LIMIT %s", '%'.$this->genre.'%', $tags, $limit);
		}
  		return $this->dbProcessor($sql, 1);
	}

	// Fetch the default genres for the site
	function fetchGenre($string=null) {
		// $string = isset: get a requested genre or search for it
		// $string = null: return all available genre's
		// 
		if ($string) {
			$sql = sprintf("SELECT * FROM genre WHERE `name` = '%s' OR `title` LIKE '%s' OR `name` LIKE '%s' ORDER BY id DESC");
		} else {
			$sql = sprintf("SELECT * FROM genre WHERE 1 ORDER BY id DESC");
		} 
  		return $this->dbProcessor($sql, 1);
	}

	// Fetch the default tags for the site
	function fetchTags($string=null) {
		// $string = isset: get a requested tag or search for it
		// $string = null: return all available tags
		// 
		if ($string) {
			$sql = sprintf("SELECT * FROM tags WHERE `name` = '%s' OR `title` LIKE '%s' OR `name` LIKE '%s' ORDER BY id ASC", $string, $string, $string);
		} else {
			$sql = "SELECT * FROM tags WHERE 1 ORDER BY id ASC";
		} 
  		return $this->dbProcessor($sql, 1);
	}

	function fetchFollowers($user_id, $type=null) {
		global $user, $configuration;
		// 3: Check if a user is following another ($user_id Can be null)
		// 1: get Followers
		// 0 or null: get Following
		//
		$next = isset($this->last_id) ? " AND relationship.id > ".$this->last_id : '';
		$limit = isset($this->limit) ? ($this->limit !== true ? " LIMIT ".$this->limit : " LIMIT ".$configuration['page_limits']) : ''; 
		if ($type == 3) {
			$sql = sprintf("SELECT follower_id FROM relationship WHERE `follower_id` = '%s' AND `leader_id` = '%s'", $this->follower_id, $this->leader_id);
		} elseif ($type == 1) {
			$sql = sprintf("SELECT uid,username,fname,lname,label,photo, relationship.id AS order_id, (SELECT COUNT(`follower_id`) FROM relationship WHERE `leader_id` = '%s') AS counter FROM relationship LEFT JOIN users ON `relationship`.`follower_id` = `users`.`uid` WHERE `leader_id` = '%s'%s ORDER BY order_id%s", $user_id, $user_id, $next, $limit);
		} else {
			$sql = sprintf("SELECT uid,username,fname,lname,label,photo, relationship.id AS order_id, (SELECT COUNT(`leader_id`) FROM relationship WHERE `follower_id` = '%s') AS counter  FROM relationship LEFT JOIN users ON `relationship`.`leader_id` = `users`.`uid` WHERE `follower_id` = '%s'%s ORDER BY order_id%s", $user_id, $user_id, $next, $limit);
		}
		$this->limit = $this->last_id = null;
  		return $this->dbProcessor($sql, 1);
	}

	function fetchPlaylist($id=null, $type=null, $plid = null) {
		global $user, $configuration;
		// $id can be playlist_id or artist_id
		// 4: Fetch this users playlist or this users public playlist
		// 3: Fetch filtered or search playlist
		// 2: Fetch all playlist
		// 1: Fetch all of this artists playlist
		// 0 or Null: Fetch one playlist by id, plid, or title
		// 
		$extra = $limit = '';
		if (isset($this->limiter)) {
        	$limit = sprintf(' ORDER BY id DESC LIMIT %s, %s', $this->start, $this->limiter);
		} elseif(!isset($this->all)) {
			$limit = isset($this->limit) ? ' LIMIT '.$this->limit : ' LIMIT '.$configuration['page_limits'];
		}
        $filter = isset($this->filter) ? $this->filter : '';

		if (isset($this->extra)) {
			$extra = $this->extra == true ? ' `playlist`.`by` = \''.$user['uid'].'\' AND ' : $this->extra;
		}
		if ($type == 1) {
			$plby = $this->db_prepare_input($id);
			$order = isset($this->order) ? $this->order : '';
			$sql = sprintf("SELECT * FROM users,playlist WHERE `users`.`uid` = `playlist`.`by` AND (`playlist`.`by` = '$plby'%s)%s", $filter, $order);
		} elseif ($type == 2) {
			$private = !isset($this->get_all) ? ' AND `playlist`.`public` = 1': '';
			$sql = sprintf("SELECT *, (SELECT count(track) FROM playlistentry WHERE `playlist` = playlist.id) AS track_count , (SELECT count(subscriber) FROM playlistfollows WHERE `playlist` = playlist.id) AS subscribers  FROM users,playlist WHERE `users`.`uid` = `playlist`.`by`%s%s%s", $private, $filter, $limit);
		} elseif ($type == 3) {
			$sql = sprintf("SELECT * FROM playlist WHERE `playlist`.`public` = '1' AND `playlist`.`title` LIKE '%s' ORDER BY views DESC%s", '%'.$this->title.'%', $limit);
		} else {
			if ($plid) {
				$sql = sprintf("SELECT * FROM users,playlist WHERE `users`.`uid` = `playlist`.`by` AND `playlist`.`plid` = '%s'", $this->db_prepare_input($id));
			} else {
				$sql = sprintf("SELECT * FROM users,playlist WHERE `users`.`uid` = `playlist`.`by` AND %s(`playlist`.`plid` = '%s') OR (`playlist`.`id` = '%s') OR (`playlist`.`title` = '%s')", $extra, $this->db_prepare_input($id), $this->db_prepare_input($id), $this->db_prepare_input($id));
			}
		}
		$this->limiter = $this->limit = $this->filter = $this->get_all = $this->order = $this->extra = null;
		return $this->dbProcessor($sql, 1);
	}
	
	function playlistEntry($id, $t=null, $track = null) {
		$order_limit = $t == 1 ? 'ASC LIMIT 1' : 'ASC';

		if (isset($this->type) && $this->type == 1 || (isset($this->count) && $this->count == 1)) {
			// Count all relevant playlist records
			$sql = sprintf("SELECT count(tracks.id) AS track_count FROM playlistentry,users,tracks WHERE (`playlistentry`.`playlist` = '%s' AND `playlistentry`.`track` = `tracks`.`id` AND `tracks`.`uid` = `users`.`uid` AND `tracks`.`public` = 1) OR (`playlistentry`.`playlist` = '%s' AND `playlistentry`.`track` = `tracks`.`id` AND `tracks`.`uid` = `users`.`uid` AND `tracks`.`uid` = '%s') ORDER BY `playlistentry`.`id` %s", $this->db_prepare_input($id), $this->db_prepare_input($id), $this->user_id, $order_limit);
		} elseif ($track) {
			// Select a particular playlist track
			$sql = sprintf("SELECT * FROM playlistentry WHERE `playlist` = '%s' AND `track` = '%s'", $this->db_prepare_input($id), $this->db_prepare_input($track));
		} else {
			// Fetch all relevant playlist records
			$sql = sprintf("SELECT * FROM playlistentry,users,tracks WHERE `playlistentry`.`playlist` = '%s' AND `playlistentry`.`track` = `tracks`.`id` AND `tracks`.`uid` = `users`.`uid` AND (`tracks`.`public` = 1 OR `tracks`.`uid` = '%s') ORDER BY `playlistentry`.`id` %s", $this->db_prepare_input($id), $this->user_id, $order_limit);
		}
		return $this->dbProcessor($sql, 1);
	}

	function playlistSubscribers($id, $type = null) {
		global $user, $configuration;				
		// 1: Fetch all of the records for this playlist
		// 2: Fetch fetch a users records for this playlist
		// 0 or Null: Fetch count records
		if ($type == 1) {
			$sql = sprintf("SELECT * FROM `playlistfollows` WHERE `playlist` = '%s'", $this->db_prepare_input($id));
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM `playlistfollows` WHERE `playlist` = '%s' AND `subscriber` = '%s'", $this->db_prepare_input($id), $this->db_prepare_input($this->user_id));
		} else {
			$sql = sprintf("SELECT (SELECT count(`playlist`) FROM `playlistfollows` WHERE `playlist` = '%s') as total, (SELECT count(`playlist`) FROM `playlistfollows` WHERE `playlist` = '%s' AND CURDATE() = date(`time`)) as today, (SELECT count(`playlist`) FROM `playlistfollows` WHERE `playlist` = '%s' AND CURDATE()-1 = date(`time`)) as yesterday, (SELECT count(`playlist`) FROM `playlistfollows` WHERE `playlist` = '%s' AND `time` BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 14 DAY ) AND DATE_SUB( CURDATE( ) ,INTERVAL 7 DAY )) as last_week", $this->db_prepare_input($id), $this->db_prepare_input($id), $this->db_prepare_input($id), $this->db_prepare_input($id));
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetchProject($id, $type = null) { 
		global $user, $configuration;	 
		// $type == 1: Fetch a single project by just id
		// $type == 2: Fetch all projects - id could be null or 0 or anything, doesn't matter
		// $type == 0, null: Fetch a single project by id, safelink or public, creator
		// 
		$limit = '';
		if (isset($this->limiter)) {
			$limit = sprintf(' ORDER BY id DESC LIMIT %s, %s', $this->start, $this->limiter);
		} elseif (!isset($this->get_all)) {
			$limit = sprintf(' LIMIT %s', $configuration['page_limits']);
		}
        
        $filter = isset($this->filter) ? $this->filter : '';

		if ($type == 1) {
			$sql = sprintf("SELECT * FROM `projects` WHERE `id` = '%s'", $this->db_prepare_input($id));
		} elseif ($type == 2) {
			$next = isset($this->last_id) ? " AND id > ".$this->last_id : '';
			$creator = isset($this->creator) ? sprintf(" AND `creator_id` = '%s'", $this->creator) : '';

			if (isset($this->counter)) {
				// Count the projects
				$sql = sprintf("SELECT COUNT(id) AS counter FROM projects WHERE 1%s%s", $next, $creator);
			} else {
				$sql = sprintf("SELECT *, id AS pid, (SELECT count(id) FROM stems WHERE `project` = pid AND stems.status = '1') AS count_stems, (SELECT count(id) FROM instrumentals WHERE `project` = pid AND instrumentals.hidden = '0') AS count_instrumentals FROM projects,users WHERE users.uid = projects.creator_id%s%s%s%s", $next, $creator, $filter, $limit);
			}
		} else {
			$sql = sprintf("SELECT * FROM `projects` WHERE `id` = '%s' OR `safe_link` = '%s' AND (status = '1' OR `creator_id` = '%s')", $this->db_prepare_input($id), $this->db_prepare_input($id), $user['uid']);
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetch_projectCollaborators($id, $type = null) {
		// 2: check if a collaborator is part of a project
		// 1: get data of collaborators
		// 0, Null: get list of collaborators
		if ($type == 1) {
			$sql = sprintf("SELECT *, c.id AS cid FROM `collaborators` AS c LEFT JOIN `users` AS u ON c.user = u.uid WHERE `user` = '%s'", $this->db_prepare_input($id));
		} elseif ($type == 2) {
			$sql = sprintf("SELECT user FROM `collaborators` WHERE `user` = '%s' AND `project` = '%s'", $this->db_prepare_input($this->user_id), $this->db_prepare_input($id));
		} else {
			$sql = sprintf("SELECT *, (SELECT COUNT(user) FROM `collaborators` WHERE `project` = '%s') AS counter FROM `collaborators` WHERE `project` = '%s'", $this->db_prepare_input($id), $this->db_prepare_input($id));
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetchInstrumental($id, $type = null) {
		global $user, $configuration;
		// 2: Get a particular instrumental
		// 1: Get all instrumentals by project
		if ($type == 1) {
			$sql = sprintf("SELECT * FROM `instrumentals` WHERE `id` = '%s'", $this->db_prepare_input($id));
		} else {
			$sql = sprintf("SELECT * FROM `instrumentals` WHERE `project` = '%s' AND (`hidden` = '0' OR `user` = '%s')", $this->db_prepare_input($id), $this->user_id);
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetchStems($id, $type = null) {
		global $user, $configuration;
		// 2: Get a particular stem
		// 1: Get unique stem uploaders
		// 0, Null: Get stem for unique users
		if ($type == 1) {
			$sql = sprintf("SELECT DISTINCT user,creator_id FROM stems LEFT JOIN projects ON `stems`.`project` = `projects`.`id` WHERE `stems`.`project` = '%s' AND `projects`.`id` = `stems`.`project` AND (`stems`.`status` = '1' OR `stems`.`user` = '%s' OR `projects`.`creator_id` = '%s')", $this->db_prepare_input($id), $user['uid'], $this->creator_id);
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM `stems` WHERE `id` = '%s'", $this->db_prepare_input($id));
		}  elseif ($type == 3) {
			$sql = sprintf("SELECT * FROM `stems` WHERE `id` = '%s'", $this->db_prepare_input($id));
		} else {
			$filter = $this->creator_id == $user['uid'] ? '' : sprintf(' AND (`status` = "1" OR `user` = "%s")', $user['uid']);

			$sql = sprintf("SELECT * FROM `stems` AS s LEFT JOIN `users` AS u ON s.user = u.uid WHERE `user` = '%s' AND `project` = '%s'%s ORDER BY `time` DESC", $this->db_prepare_input($id), $this->project_id, $filter);
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetch_colabRequests($id, $type = null) {
		global $user, $configuration;	
		// 1: Get all colab request
		// 0, Null: Verify user state
		if ($type == 1) {
			$sql = sprintf("SELECT * FROM `collabrequests` WHERE `project` = '%s' AND `user` = '%s'", $this->db_prepare_input($id), $this->db_prepare_input($this->user_id));
		} else {
			$sql = sprintf("SELECT * FROM `collabrequests` WHERE `project` = '%s'", $this->db_prepare_input($id));
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetchsimilarProjects($id) {
		global $user, $configuration;
		$sql = sprintf("SELECT * FROM projects WHERE `id` != '%s' AND `status` = '1' AND (MATCH (title) AGAINST ('%s' IN NATURAL LANGUAGE MODE) OR MATCH (tags) AGAINST ('%s' IN NATURAL LANGUAGE MODE) OR MATCH (genre) AGAINST ('%s' IN NATURAL LANGUAGE MODE))", $this->db_prepare_input($id), $this->db_prepare_input($this->project_title), $this->db_prepare_input($this->project_tags), $this->db_prepare_input($this->project_genre));
		return $this->dbProcessor($sql, 1);

	}

	function fetchNotifications($id = 0, $type = 0) {
		global $user, $configuration, $user_role, $admin;	
		
		if (isset($this->limiter)) {
			$limit = sprintf(' ORDER BY `date` DESC  LIMIT %s, %s', $this->start, $this->limiter);
		} else {
			$limit = ' ORDER BY `date` DESC';
		}

		$status = isset($this->seen) ? ' AND `status` = \''.$this->seen.'\'' : '';
		$uid = $id ? $id : $user['uid'];

		if ($type == 1) {
			return $this->dbProcessor("UPDATE notification SET `status` = '1' WHERE `uid` = '$uid'", 0, 1);
		} else {
			$sql = sprintf("SELECT * FROM notification WHERE `uid` = '%s'%s%s", $uid, $status, $limit);
			return $this->dbProcessor($sql, 1);
		}
	}

	/**
	 * get payment history and details
	 * @param  integer $type if 1: return payment for id or release_id, if 2: return the users payment history
	 *                       if 0: return all payment history
	 * @param  integer of numeric string  $id   set the id of the payment to retrieve
	 * @return array       	An array containing the requested payment data
	 */
	function fetchPayments($type = 0, $id = null) {
		global $user, $configuration, $user_role, $admin;	
		$id = $this->db_prepare_input($id);
		
		if (isset($this->limiter)) {
			$limit = sprintf(' ORDER BY `date` DESC  LIMIT %s, %s', $this->start, $this->limiter);
		} else {
			$limit = ' ORDER BY `date` DESC';
		}

		if ($type == 2) {
			return $this->dbProcessor(sprintf("SELECT * FROM payments WHERE `uid` = '$id' OR `email` = '$id'%s", $limit), 1);
		} else if ($type == 1) {
			return $this->dbProcessor("SELECT * FROM payments WHERE `id` = '$id' OR `rid` = '$id'", 1)[0];
		} else {
			return $this->dbProcessor(sprintf("SELECT * FROM payments WHERE 1%s", $limit), 1);
		}
	}

	function fetchReleases($type = null, $id = null) {
		global $user, $configuration, $user_role, $admin;	
		
		if (isset($this->limiter)) {
			$limit = sprintf(' ORDER BY `date` DESC LIMIT %s, %s', $this->start, $this->limiter);
		} else {
			$limit = isset($this->limit) ? " LIMIT ".$configuration['releases_limit'] : '';
		}
		$restrict = $user_role < 4 && !$admin ? ' AND `by` = \''.$user['uid'].'\'' : '';

		if ($type == 1) {
			$sql = sprintf("SELECT * FROM `new_release` WHERE `release_id` = '%s'%s", $this->db_prepare_input($id), $restrict);
		} else {
			$search = '';
			if (isset($this->search_query)){
				$searchValue = $this->search_query;
	     		$search = " AND 
			    title like '%".$searchValue."%' OR  
			    release_id like '%".$searchValue."%' OR  
			    p_line like '%".$searchValue."%' OR  
			    p_genre like '%".$searchValue."%' OR  
			    label like '%".$searchValue."%'";		
			} 
 
			$filter_query = isset($this->filter_query) ? ' AND `status` = \''.$this->filter_query.'\'' : '';
			$next = isset($this->last_id) ? ' AND `id` < \''.$this->last_id.'\'' : '';
			$status = isset($this->status) ? ' AND `status` = \''.$this->status.'\'' : '';
			if (isset($this->counter)) {
				// Count the projects
				$sql = sprintf("SELECT COUNT(id) AS counter FROM new_release WHERE `by` = '%s'%s%s", $user['uid'], $status, $next);
			} else {
				$sql = sprintf("SELECT * FROM new_release WHERE 1 %s%s%s%s%s%s", $restrict, $status, $search, $filter_query, $next, $limit);
			}
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetchRelease_Artists($type = null, $id = null) {
		global $user, $configuration;	
		if ($type == 1) {
			$sql = sprintf("SELECT * FROM `new_release_artists` WHERE `release_id` = '%s' AND `role` = 'primary'", $this->db_prepare_input($id)); 
		} elseif ($type == 2) {
			$sql = sprintf("SELECT DISTINCT username FROM `new_release_artists` WHERE `by` = '%s'", $this->db_prepare_input($id)); 
		} elseif ($type == 3) {
			$sql = sprintf("SELECT * FROM `new_release_artists` WHERE 1 AND `id` = '%s' OR `username` = '%s'", $this->db_prepare_input($id), $this->db_prepare_input($id)); 
		} else {
			$sql = sprintf("SELECT * FROM `new_release_artists` WHERE `release_id` = '%s' AND `role` != 'primary'", $this->db_prepare_input($id));
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetchRelease_Audio($type = null, $id = null) {
		global $user, $configuration;	
		if ($type) {
			$sql = sprintf("SELECT * FROM `new_release_tracks` WHERE `release_id` = '%s'", $this->db_prepare_input($id));
		} else {
			$sql = sprintf("SELECT * FROM `new_release_tracks` WHERE `release_id` = '%s'", $this->db_prepare_input($id));
		}
		return $this->dbProcessor($sql, 1);
	}

	function fetchStatic($id = null, $type = null) {
		$search = '';
		if (isset($this->search_query)){
			$searchValue = $this->search_query;
     		$search = " AND 
		    title like '%".$searchValue."%'";		
		} 

		$filter_query = isset($this->filter_query) ? ' AND `parent` = \''.$this->filter_query.'\'' : '';

		$linked = isset($this->linked) ? ' AND `linked` = \''.$this->linked.'\'' : '';
		$priority = isset($this->priority) ? ' AND `priority` = \''.$this->priority.'\'' : '';
		$parent = isset($this->parent) ? ' AND `parent` = \''.$this->parent.'\'' : '';
		$limit = isset($this->limiter) ? sprintf(' LIMIT %s, %s', $this->start, $this->limiter) : '';

		$dasc = $limit ? 'DESC' : 'ASC';
		$order = isset($this->reverse) ? ' ORDER BY `date` '.$dasc : ' ORDER BY `date` '.$dasc;

		if ($type == 1) {
			$sql = sprintf("SELECT * FROM static_pages WHERE 1%s%s%s%s%s%s%s", $priority, $parent, $search, $filter_query, $linked, $order, $limit);
		} else {
			$sql = sprintf("SELECT * FROM static_pages WHERE `id` = '%s' OR `safelink` = '%s'", $this->db_prepare_input($id), $this->db_prepare_input($id));
		} 
		return $this->dbProcessor($sql, 1);
	}

	function createStaticContent() {
		global $PTMPL, $LANG, $SETT, $user, $framework, $marxTime; 

		$static_ids = isset($_GET['static_id']) ? $framework->db_prepare_input($_GET['static_id']) : null;
		$get_statics = $this->fetchStatic($static_ids)[0];

		$parent = $framework->db_prepare_input($this->parent); 
		$priority = $framework->db_prepare_input($this->priority); 
		$icon = $framework->db_prepare_input($this->icon); 
		$title = $framework->db_prepare_input($this->title); 
		$main_content = $this->main_content; 
		$footer = $this->footer;
		$header = $this->header;
		$framework->resolution = '750,600';
		$image = $framework->imageUploader($this->image);
		$buttons = $this->banner_buttons;

		$safelink = $framework->safeLinks($title);

		if (is_array($image)) { 
			if ($static_ids) {
				deleteFile($get_statics['banner'], 1);
			}
			$banner = $image[0];
		} else {
			if (isset($get_statics['banner'])) {
				$banner = $get_statics['banner'];
			} else {
				$banner = NULL;
			}
		}

		if ($static_ids) {
			$sql = sprintf("UPDATE static_pages SET `parent` = '%s', `banner` = '%s', `button_links` = '%s', `title` = '%s', 
				`content` = '%s', `priority` = '%s', `icon` = '%s', `footer` = '%s', `header` = '%s' WHERE `id` = '%s'", 
				$parent, $banner, $buttons, $title, $main_content, $priority, $icon, $footer, $header, $static_ids);

			$post = $this->dbProcessor($sql, 0, 1);
			$post = $post == 1 ? $post : messageNotice($post, 2);
		} else {
			if (empty($this->image['name']) || $banner) { 
				$sql = sprintf("INSERT INTO static_pages (`parent`, `banner`, `button_links`, `title`, `content`, `priority`, 
					`icon`, `footer`, `header`, `safelink`) VALUES  ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
					$parent, $banner, $buttons, $title, $main_content, $priority, $icon, $footer, $header, $safelink);

				$post = $this->dbProcessor($sql, 0, 1);
				$post = $post == 1 ? $post : messageNotice($post, 2);
			} else {
				$post = messageNotice($image, 3);
			}
		}
		if ($post == 1) {
			$msg = messageNotice('Your content has been saved', 1);
		} else {
			$msg = $post;
		}	
		return $msg;	
	}

	/**
	 * Do a Search query
	 */
	function searchEngine($q=null, $x=0) {
	    // Search users and contests
	    $filter = isset($this->filters) ? $this->filters : null;
	    $results = array(); 
	    // Show the regular results

	    $limit = isset($this->limiter) ? sprintf(" ORDER BY id DESC LIMIT %s, %s", $this->start, $this->limiter) : '';
	    $uqr = '';
        if (isset($this->tags)) {
            $tags = sprintf("(`tags` LIKE '%s')", '%'.$this->db_prepare_input($q).'%');
        } else { 
        	$uqr = sprintf(" OR `by` IN (SELECT uid FROM users WHERE `username` LIKE '%s')", '%'.$this->db_prepare_input($q).'%');
            $tags = sprintf("(`title` LIKE '%s' OR `genre` LIKE '%s')", '%'.$this->db_prepare_input($q).'%', '%'.$this->db_prepare_input($q).'%');
        }
        $title_only = sprintf("(`title` LIKE '%s')", '%'.$this->db_prepare_input($q).'%');

	    if (empty($filter)) {
	 
	        if ($x == 0) { 
	            $users = sprintf("SELECT uid AS id, concat_ws(' ',fname,lname) AS title, username AS safe_link, photo AS art, intro AS description, label AS `by`, 1 AS type FROM users WHERE `username` LIKE '%s' OR concat_ws(' ', `fname`, `lname`) LIKE '%s'", '%'.$q.'%', '%'.$q.'%');

	            $tracks = sprintf("SELECT id, title, safe_link, art, description, artist_id AS `by`, 2 AS type FROM tracks WHERE `public` = '1' AND %s", $tags); 

	            $albums = sprintf("SELECT id, title, safe_link, art, description, `by`, 3 AS type FROM albums WHERE `public` = '1' AND  %s%s", $tags, $uqr); 

	            $projects = sprintf("SELECT id, title, safe_link, cover AS art, details AS description, creator_id AS `by`, 4 AS type FROM projects WHERE `status` = '1' AND %s", $tags); 

	            $instrumentals = sprintf("SELECT id, title, file AS safe_links, 'music.png' AS art, tags AS description, user AS `by`, 5 AS type FROM instrumentals WHERE `hidden` = '0' AND %s", $tags);  

	            $playlist = sprintf("SELECT id, title, plid AS safe_links, 'playlist.png' AS art, title AS description, `by`, 6 AS type FROM playlist WHERE `public` = '1' AND %s%s", $title_only, $uqr);  

	            $select = sprintf("%s UNION ALL %s UNION ALL %s UNION ALL %s UNION ALL %s UNION ALL %s%s", $users, $tracks, $albums, $projects, $instrumentals, $playlist, $limit);
	        }
	    } elseif ($filter === 'find') {
	    	// Fetch users for who to follow
	    	$limit = isset($this->limit) ? sprintf(" ORDER BY RAND() LIMIT %s", $this->limit) : ' ORDER BY RAND()';
	        $select = sprintf("SELECT uid AS id, concat_ws(' ',fname,lname) AS title, username AS safe_link, photo AS art, intro AS description, label AS `by`, 1 AS type FROM users WHERE `role` >= '2'%s", $limit);
	    } else {
	    	$blimit = $limit ? $limit : sprintf(" ORDER BY RAND() LIMIT %s", 4);

			$tracks = "SELECT id, title, safe_link, art, description, 2 AS type FROM tracks WHERE `public` = '1' AND (`featured` = '1' OR `upload_time` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH))"; 
	    	
	    	$projects = "SELECT id, title, safe_link, cover AS art, details AS description, 4 AS type FROM projects WHERE `status` = '1' AND  `recommended` = '1'"; 

	        $playlist = "SELECT id, title, plid AS safe_links, 'playlist.png' AS art, title AS description, 6 AS type FROM playlist WHERE `public` = '1' AND `featured` = '1'";  

	        $select = sprintf("%s UNION ALL %s UNION ALL %s %s", $tracks, $projects, $playlist, $blimit);
	    }

	    return $this->dbProcessor($select,1);
	}

	/**
	 * Delete content
	 * @param  variable $id   is the id of the item to be deleted
	 * @param  variable $type is the type of item to delete
	 * @return integer       0 for a failure 1 for success
	 */
	function deleteContent($id, $type = null) {
		global $PTMPL, $LANG, $SETT, $user, $framework, $collage; 
		
		if ($type == 1) {
			$content = $this->fetchStatic($id)[0]; 
			deleteFile($content['banner'], 1);
			$delete = $this->dbProcessor("DELETE FROM static_pages WHERE `id` = '$id'", 0, 2);
		} else {
 
		}
		return $delete;
	}

	function manageRealeases() {
		global $SETT, $framework;

		if (isset($_GET['q'])) {
			$this->search_query = $_GET['q'];
		} 
		if (isset($_POST['filter'])) {
			$this->filter_query = $_POST['f'];		
		}

		$this->manage = true;
	    $this->all_rows = $this->fetchReleases();
	    $PTMPL['pagination'] = $this->pagination();
		$list_releases = $this->fetchReleases();
		
		$table_row = ''; $i=0;
		if ($list_releases) {
			foreach ($list_releases as $releases) {
				$i++;
				$page = $SETT['url'].$_SERVER['REQUEST_URI'];

				$edit_link = base_url('distribution&action=manage&set=details&rel_id='.$releases['release_id']); 
				$creator = $this->userData($releases['by'], 1);
				$set_status = $this->releaseStatus($releases['release_id']);
				$upc = $releases['upc'] ? $releases['upc'] : 'N/L';
				$copyright = $releases['c_line_year'].' '.$releases['c_line'];
				$recording = $releases['p_line_year'].' '.$releases['p_line'];
				$this->type = 3;
				$views = $this->releaseStats($releases['release_id'])[0]; 

				// Check if this is a premium release then set the premium class
				$invoice = $this->fetchPayments(1, $releases['release_id']);
				$premium_class = ($invoice['rid'] === $releases['release_id'] ? 'text-success' : 'text-danger');
				$premium_title = ($invoice['rid'] === $releases['release_id'] ? ' (PREMIUM RELEASE)' : '');

				if ($set_status[1] == 3) { 
					$set_state_link = $framework->urlRequery('&action=remove&rel_id='.$releases['release_id']);
					$state_class = 'fa-times-circle text-warning';
				} else { 
					$set_state_link = $framework->urlRequery('&action=approve&rel_id='.$releases['release_id']);
					$state_class = 'fa-check-circle text-success';
				}
				$pager = $page;

				$delete_link = $framework->urlRequery('&action=delete&rel_id='.$releases['release_id']);

				$table_row .= '
				<tr>
					<th scope="row">'.$i.'</th>
					<td><a href="'.$edit_link.'" title="View Content'.$premium_title.'" class="'.$premium_class.'">'.$releases['title'].'</a></td>
					<td>'.ucfirst($creator['username']).'</td>
					<td>'.$set_status[0].'</td>
					<td>'.$upc.'</td>
					<td>'.$copyright.'</td>
					<td>'.$recording.'</td>
					<td>'.$views['views'].'</td>
					<td class="d-flex justify-content-around">
						<a href="'.$set_state_link.'" title="Approve"><i class="fa fa-2x '.$state_class.' hoverable mr-1"></i></a>
						<a href="'.$edit_link.'" title="Edit Content"><i class="fa fa-2x fa-edit text-info hoverable mr-1"></i></a>
						<a href="'.$delete_link.'" title="Delete Content"><i class="fa fa-2x fa-trash text-danger hoverable"></i></a> 
					</td>
				</tr>';
			}
		} else {
			$table_row .= '
			<tr><td colspan="9">'.notAvailable('No release data to show', '', 1).'</td></tr>';
		}		
			return $table_row;
	}

	function categoryOptions($type = 0, $get_post = null) {
		global $SETT, $framework;

		// Set category select options for new posts
		$category = $this->dbProcessor("SELECT id, title, value, info FROM categories", 1);
		if ($type == 0) {
			$option = '';
			if ($category) { 
				foreach ($category as $row) { 
					$sel = (isset($_POST['category']) && $_POST['category'] == $row['value']) || ($get_post['category'] == $row['value']) ? ' selected="selected"' : ''; 
					$option .= '<option value="'.$row['value'].'"'.$sel.'>'.$row['title'].'</option>';
				}
			}
		} else {
			return $category;
		}
		return $option;
	}

	/**
	 * Fetch data from the categories table
	 * @param  integer $type determines the type of category to repeat: 1 = Exclude Events, 2 = All Categories
	 * @return array       containing all available categories
	 */
	function fetchCategories($type = null, $id = null) {
		if ($id) {
			return $this->dbProcessor(sprintf("SELECT id, title, value, info FROM categories WHERE `value` = '%s'", $id), 1); 
		} else {
			$event = $type == 1 ? ' WHERE (`value` != \'event\' AND `value` != \'exhibition\')' : '';
			return $this->dbProcessor(sprintf("SELECT id, title, value, info FROM categories%s", $event), 1); 
		}
	}
	
	/**
	 * [manageBlock sets the users to user access 
	 * to blocked or unblocked on the block user table]
	 * @param  [type]  	$uid    	this is the id of the user to check up or block
	 * @param  integer  $type   	if this is set to 1 the user block will be toggled 
	 * @param  integer  $swap    	setting this to 1 will check if the logged in user is blocked
	 *                            	else it will check if the user with the provided $uid is blocked
	 * @param  integer  $feedback 	decide what style to return for the live block button
	 * @return array          		an array containing the status of the block, as an icon, link or link with icon
	 */
	function manageBlock($uid, $type = 0, $swap = 0, $feedback = 0) {
		// Type 0: Show the block button
		// Type 1: Block or Unblock a user
		global $LANG, $user;
 
		$u = $this->userData($uid, 1);
		$fullname = $this->realName($u['username'], $u['fname'], $u['lname']);
		
		// If the username does not exist, return nothing
		if(empty($u)) {
			return false;
		} else {
			// Check if this user was blocked
			$sql = sprintf("SELECT * FROM blocked_users WHERE (`user_id` = '%s' AND `by` = '%s') OR (`user_id` = '%s' AND `by` = '%s')", $this->db_prepare_input($u['uid']), $user['uid'], $user['uid'], $this->db_prepare_input($u['uid']));

			$state = $this->dbProcessor($sql, 1);
			$blocked = $state[0];
			$state = $state && count($state) > 0 ? 1 : 0;
			
			// Block or unblock
			if($type) {
				// If there is a block, unblock
				if($state) {
					// Remove the block
					$sql = sprintf("DELETE FROM blocked_users WHERE `user_id` = '%s' AND `by` = '%s'", $this->db_prepare_input($u['uid']), $user['uid']); 
					$status = 0; 
				} else {
					// unblock
					$sql = sprintf("INSERT INTO blocked_users (`user_id`, `by`) VALUES ('%s', '%s')", $this->db_prepare_input($u['uid']), $user['uid']); 
					$status = 1;
				}
				$action = $this->dbProcessor($sql, 0, 1); 
			} else {
				$status = $state; 
			}
		} 

		// Set the icon
		$ban_icon = '<i class="fa fa-ban text-danger px-1 hoverable rounded-circle"></i>';
		$unban_icon = '<i class="fa fa-check-circle-o text-success px-1 hoverable rounded-circle"></i>'; 

		$block_link = '<a onclick="blockAction('.$uid.', 1, '.$feedback.')" data-toggle="tooltip" data-placement="right" title="'.$LANG['block'].' '.$fullname.'" id="block_" style="cursor: pointer;">%s</a>';

		$unblock_link = '<a onclick="blockAction('.$uid.', 1, '.$feedback.')" data-toggle="tooltip" data-placement="right" title="'.$LANG['unblock'].' '.$fullname.'" id="unblock_" style="cursor: pointer;">%s</a>';

		if($status && $blocked['by'] == $user['uid']) {
			// Show only icon
			$icon = sprintf($unblock_link, $unban_icon); 
			// Show only the link
			$link = sprintf($unblock_link, $LANG['unblock']); 
			// Show link and icon
			$link_icon = sprintf($unblock_link, $unban_icon.$LANG['unblock']);  
		} else {
			// Show only icon
			$icon = sprintf($block_link, $ban_icon); 
			// Show only the link
			$link = sprintf($block_link, $LANG['block']); 
			// Show link and icon
			$link_icon = sprintf($block_link, $ban_icon.$LANG['block']); 
		}
		return array('icon' => $icon, 'link' => $link, 'link_icon' => $link_icon, 'status' => $status, 'user' => $blocked['user_id']);		
	}
}

/* 
* Callback for decodeText()
*/
function decodeLink($text, $x=0) { 
    // If www. is found at the beginning add http in front of it to make it a valid html link
    $y = $x==1 ? 'primary-color' : 'secondary-color';

    if(substr($text[1], 0, 4) == 'www.') {
        $link = 'http://'.$text[1];
    } else {
        $link = $text[1];
    }
    return '<a class="'.$y.'" href="'.$link.'" target="_blank" rel="nofollow">'.$link.'</a>'; 
}
