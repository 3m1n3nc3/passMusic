<?php
//======================================================================\\
// Passengine 1.0 - Php templating engine and framework                 \\
// Copyright © Passcontest. All rights reserved.                        \\
//----------------------------------------------------------------------\\
// http://www.passcontest.com/                                          \\
//======================================================================\\

use Gumlet\ImageResize;

$framework = new framework;
$recovery = new doRecovery;
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
class framework {
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
        } elseif (isset($this->limit)) {
            $limit = sprintf(' ORDER BY date DESC LIMIT %s, %s', $this->start, $this->limit);
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
            $auth = $this->userData($username);

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
            }
            return $username;

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

    // Registeration function
    function registrationCall() {
        // Prevents bypassing the FILTER_VALIDATE_EMAIL
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');

        $token = $this->generateToken();
        $password = hash('md5', $_POST['password']);
        $sql = sprintf("INSERT INTO " . TABLE_USERS . " (`email`, `username`, `password`, `f_name`, `l_name`,
		 `country`, `state`, `city`, `token`) VALUES 
	        ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $this->email, $this->username,
            $this->password, $this->firstname, $this->lastname, $this->country, $this->state, $this->city, $token);
        $response = $this->dbProcessor($sql, 0, 1);

        if ($response == 1) {
            $_SESSION['username'] = $this->username;
            $_SESSION['password'] = $password;
            $process = 1;
        }
        return $response;
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
        if ($firstname == '' || $lastname == '' || $email == '' || $intro == '') {
            $msg = messageNotice($LANG['_all_required']);
        } elseif ($var_email && $var_email['email'] !== $user['email']) {
        	$msg = messageNotice($LANG['email_used']);
        } elseif ($var_user && $var_user['username'] !== $user['username']) {
        	$msg = messageNotice($LANG['username_used']);
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

		$user_data = $this->userData($this->user_id, 1);
		$message = $this->message;

		// show the message details if test_mode is on
		$return_response = null;
		$echo =
		'<small class="p-1"><div class="text-warning text-justify"
			Sender: '.$sender.'<br>
			Receiver: '.$receiver.'<br>
			Subject: '.$subject.'<br>
			Message: '.$message.'<br></div>
		</small>';
		if ($this->trueAjax() && $configuration['mode'] == 0) {
			echo $echo;
		} 

	    // Send a test email message
	    if (isset($this->test)) {
	    	$sender = $SETT['email'];
	    	$receiver = $SETT['email'];
	    	$subject = 'Test EMAIL Message from '.$configuration['site_name'];
	    	$message = 'Test EMAIL Message from '.$configuration['site_name'];
	    	$return_response = successMessage('Test Email Sent');
	    }

		if ($user_data && $user_data['allow_emails'] == 0 && !isset($this->activation)) {
			return false;
		} else {
			// If the SMTP emails option is enabled in the Admin Panel
			if($configuration['smtp']) { 
 
				require_once(__DIR__ . '/vendor/autoload.php');
				
				//Tell PHPMailer to use SMTP
				$mail->isSMTP();

				//Enable SMTP debugging
				// 0 = off 
				// 1 = client messages
				// 2 = client and server messages
				$mail->SMTPDebug = $configuration['mode'] == 0 ? 2 : 0;
				
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
		}
		return $return_response;
	}

    function captchaVal($captcha) {
        global $configuration;
        if ($configuration['captcha']) {
            if ($captcha == "{$_SESSION['captcha']}" && !empty($captcha)) {
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
		
		if ($role) {
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
		} else {
			return $user['role'];
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
		global $LANG, $SETT, $configuration, $contact_;
		$username = $this->username;
		$content = $this->content;
		$template = '
		<div style="background: #f7fff5; padding: 35px;">
			<div style="width: 200px;">'.$contact_['address'].'</div><hr>
			<div style="font: green; border: solid 1px lightgray; border-radius: 7px; background: white; margin: 50px; ">
				<div style="padding: 10px;background: lightgray;display: flex; width: 100%;">
				<img src="'.getImage('logo-full.png', 2).'" width="50px" height="auto" alt="'.ucfirst($configuration['site_name']).'Logo">
				<h3>'.ucfirst($configuration['site_name']).'</h3>
				</div>
				<div style="margin: 25px;">
					<p style="font-weight: bolder;">Hello '.$username.',</p>
					<p style="color: black;">
						'.$content.'
					</p>
				</div>
			</div>
			<div style="margin-left: 35px; margin-right: 35px; padding-bottom: 35px;">This message was sent from <a href="'.$SETT['url'].'" target="_blank">'.$SETT['url'].'</a>, because you have requested one of our services. Please ignore this message if you are not aware of this action. You can also make inquiries to <a href="mailto:'.$contact_['email'].'">'.$contact_['email'].'</a></div>
		</div>
		<div style="text-align: center; padding: 15px; background: #fff;">
			<div>'.ucfirst($configuration['site_name']).'</div>
			<div style="color: teal;">
				&copy; ' . ucfirst($LANG['copyright']) . ' ' . date('Y') . ' ' . $contact_['c_line'].'
			</div>
		</div>';
		return $template;
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
	* Create url referer to safely redirect users
	*/
	function urlReferrer($url, $type) {
	    if ($type == 0) {
	        $url = str_replace('/', '@', $url); 
	    } else {
	        $url = str_replace('@', '/', $url); 
	    }
	 
	    return $url;
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


	function urlRequery($query) {
	    global $SETT;
		$set = '';
		if (isset($_GET['view'])) {
			$set .= '&view='.$_GET['view'];
		} 
		if (isset($_GET['set'])) {
			$set .= '&set='.$_GET['set'];
		}
		return cleanUrls($SETT['url'] . '/index.php?page=' . $_GET['page'].$set.$query);
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

	function autoComplete($_type = null, $preset = null) {
		global $SETT, $configuration, $databaseCL, $marxTime;

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
				$tag_array[] = '"'.ucfirst($value).'"';
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
		return '['.$tag_list.']';
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
		// Response 1 = Debug

		$data = null; 
		if ($type == 2) {
			$data = $response;
		} else {
			try {
				$stmt = $DB->prepare($sql);	 	
				$stmt->execute();
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
						} else {
							$data = $response;
						}
					} else {
						if ($response == 2) {
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
		return $data;
	}

	public function pagination($type = null) {
		global $SETT, $LANG, $configuration, $databaseCL;

		$page = $SETT['url'].$_SERVER['REQUEST_URI'];
		if (isset($_GET['pagination'])) {
			$page = str_replace('&pagination='.$_GET['pagination'], '', $page);
		}

		// Pagination Navigation settings
		if ($type == 1) {
			$perpage = $configuration['per_featured'];
		} else {
			$perpage = $configuration['per_page'];
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
		if ($_GET['page'] == 'homepage' && !isset($_GET['archive'])) {
			$count = $count - 1;
		}
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
				$pager = cleanUrls($page.'&pagination='.$startpage);
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
				$pager = cleanUrls($page.'&pagination='.$previouspage);
			    $navigation .= '
					<li class="page-item">
						<a class="page-link" href="'.$pager.'">Prev</a>
					</li>
			    ';
			}

			$pager = cleanUrls($page.'&pagination='.$curpage);
		    $navigation .= '
				<li class="page-item active">
					<a class="page-link" href="'.$pager.'">'.$curpage.'</a>
				</li>
		    '; 

			if($curpage != $endpage){
				$pager = cleanUrls($page.'&pagination='.$nextpage);
			    $navigation .= '
					<li class="page-item">
						<a class="page-link" href="'.$pager.'">Next</a>
					</li>
			    ';  
 
				$pager = cleanUrls($page.'&pagination='.$endpage);
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
		return $navigation;	 
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
	 * @param  variable $fb is used as fallback when an ajax xhr request type is not possible for a ajax request
	 * @return Boolean     returns true if a user was deleted else it returns false
	 */
	function deleteUser($id, $fb = null) {
		$id = $this->db_prepare_input($id);

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
		if ($type == NUll || $type == 0) {
			// This completes Type: 0 (Delete Files) Without actually making any changes
			$update = 1;
		} else {
			$set_date = $type == 1 ? '\''.$date.'\'' : 'NULL';
			$sql = sprintf("UPDATE new_release SET `status` = '%s', `approved_date` = %s WHERE `release_id` = '%s'", $status, $set_date, $id);
			$update = $this->dbProcessor($sql, 0, 1);
		}

		if ($update == 1 && ($type == NUll || $type == 0)) { 

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
		    $this->dbProcessor("DELETE FROM new_release WHERE `release_id` = '{$id}'", 0);

			return true;
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
					$cu = $this->dbProcessor($sql, 0, 1);
					if ($cu != 1) {
						echo bigNotice($cu, null, 'mt-5');
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
		// 5: just select all tracks by this artist and return an array of id as list
		// 4: Count the views on all tracks by this artist
		// 3: Get all tracks by this artist
		// 2: Get a particular track
		// 1: Get the most popular track
		// 0: Get all tracks not in an album
		if ($type == 1) {
			$sql = sprintf("SELECT * FROM users,tracks WHERE tracks.artist_id = '%s' AND users.uid = tracks.artist_id AND tracks.id = (SELECT MAX(`track`) FROM `views` WHERE tracks.id = views.track)", $this->db_prepare_input($artist_id));
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM tracks,users WHERE tracks.id = '%s' AND users.uid = tracks.artist_id OR tracks.safe_link = '%s' AND users.uid = tracks.artist_id", $this->db_prepare_input($this->track), $this->db_prepare_input($this->track));
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
		} else {
			$sql = sprintf("SELECT * FROM users,tracks WHERE tracks.artist_id = '%s' AND users.uid = tracks.artist_id AND tracks.id NOT IN (SELECT track FROM albumentry WHERE 1)", $this->db_prepare_input($artist_id));
		}
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
			$sql = sprintf("SELECT follower_id FROM relationship WHERE `follower_id` = '%s' AND leader_id` = '%s'", $this->leader_id, $this->follower_id);
		} if ($type == 1) {
			$sql = sprintf("SELECT uid,username,fname,lname,label,photo, relationship.id AS order_id, (SELECT COUNT(`follower_id`) FROM relationship WHERE `leader_id` = '%s') AS counter FROM relationship LEFT JOIN users ON `relationship`.`follower_id` = `users`.`uid` WHERE `leader_id` = '%s'%s ORDER BY order_id%s", $user_id, $user_id, $next, $limit);
		} else {
			$sql = sprintf("SELECT uid,username,fname,lname,label,photo, relationship.id AS order_id, (SELECT COUNT(`leader_id`) FROM relationship WHERE `follower_id` = '%s') AS counter  FROM relationship LEFT JOIN users ON `relationship`.`leader_id` = `users`.`uid` WHERE `follower_id` = '%s'%s ORDER BY order_id%s", $user_id, $user_id, $next, $limit);
		}
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
		$extra = '';
		$limit = isset($this->limit) ? ' LIMIT '.$this->limit : ' LIMIT '.$configuration['page_limits'];
		if (isset($this->extra)) {
			$extra = $this->extra == true ? ' `playlist`.`by` = \''.$user['uid'].'\' AND ' : $this->extra;
		}
		if ($type == 1) {
			$sql = sprintf("SELECT * FROM users,playlist WHERE `playlist`.`by` = '%s' AND `users`.`uid` = `playlist`.`by`", $this->db_prepare_input($id));
		} elseif ($type == 2) {
			$sql = "SELECT * FROM users,playlist WHERE `users`.`uid` = `playlist`.`by` AND `playlist`.`public` = 1";
		} elseif ($type == 3) {
			$sql = sprintf("SELECT * FROM playlist WHERE `playlist`.`public` = '1' AND `playlist`.`title` LIKE '%s' ORDER BY views DESC%s", '%'.$this->title.'%', $limit);
		} else {
			if ($plid) {
				$sql = sprintf("SELECT * FROM users,playlist WHERE `users`.`uid` = `playlist`.`by` AND `playlist`.`plid` = '%s'", $this->db_prepare_input($id));
			} else {
				$sql = sprintf("SELECT * FROM users,playlist WHERE `users`.`uid` = `playlist`.`by` AND %s(`playlist`.`plid` = '%s') OR (`playlist`.`id` = '%s') OR (`playlist`.`title` = '%s')", $extra, $this->db_prepare_input($id), $this->db_prepare_input($id), $this->db_prepare_input($id));
			}
		}
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
		if ($type == 1) {
			$sql = sprintf("SELECT * FROM `projects` WHERE `id` = '%s'", $this->db_prepare_input($id));
		} elseif ($type == 2) {
			$next = isset($this->last_id) ? " AND id > ".$this->last_id : '';
			$creator = isset($this->creator) ? sprintf(" AND `creator_id` = '%s'", $this->creator) : '';

			if (isset($this->counter)) {
				// Count the projects
				$sql = sprintf("SELECT COUNT(id) AS counter FROM projects WHERE 1%s%s", $next, $creator);
			} else {
				$sql = sprintf("SELECT *, id AS pid, (SELECT count(id) FROM stems WHERE `project` = pid AND stems.status = '1') AS count_stems, (SELECT count(id) FROM instrumentals WHERE `project` = pid AND instrumentals.hidden = '0') AS count_instrumentals FROM projects WHERE 1%s%s LIMIT %s", $next, $creator, $configuration['page_limits']);
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

	function fetchNotifications($id = null) {
		global $user, $configuration, $user_role, $admin;	
		
		if (isset($this->limiter)) {
			$limit = sprintf(' ORDER BY `date` DESC LIMIT %s, %s', $this->start, $this->limiter);
		} else {
			$limit = ' ORDER BY `date` DESC';
		}

		$status = isset($this->status) ? $this->status : 0;
		$uid = $id ? $id : $user['uid'];

		$sql = sprintf("SELECT * FROM notification WHERE `uid` = '%s' AND `status` = '%s' %s", $uid, $status, $limit);
		return $this->dbProcessor($sql, 1);
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
		global $SETT;

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

				$edit_link = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&set=details&rel_id='.$releases['release_id']); 
				$creator = $this->userData($releases['by'], 1);
				$set_status = $this->releaseStatus($releases['release_id']); 
				$upc = $releases['upc'] ? $releases['upc'] : 'N/L';
				$copyright = $releases['c_line_year'].' '.$releases['c_line'];
				$recording = $releases['p_line_year'].' '.$releases['p_line'];
				$this->type = 3;
				$views = $this->releaseStats($releases['release_id'])[0]; 

				if (isset($_GET['action'])) {
					if ($set_status[1] == 3) {
						$set_state_link = cleanUrls(str_replace('&action='.$_GET['action'].'&rel_id='.$_GET['rel_id'], '', $page).'&action=remove&rel_id='.$releases['release_id']);
					} else {
						$set_state_link = cleanUrls(str_replace('&action='.$_GET['action'].'&rel_id='.$_GET['rel_id'], '', $page).'&action=approve&rel_id='.$releases['release_id']);
					}
					$state_class = $set_status[1] == 3 ? 'fa-times-circle text-warning' : 'fa-check-circle text-success';
					$pager = str_replace('&action='.$_GET['action'].'&rel_id='.$_GET['rel_id'], '', $page);
				} else {
					if ($set_status[1] !== 3) {
						$set_state_link = cleanUrls($page.'&action=approve&rel_id='.$releases['release_id']);
						$state_class = 'fa-check-circle text-success';
					} else {
						$set_state_link = cleanUrls($page.'&action=remove&rel_id='.$releases['release_id']);
						$state_class = 'fa-times-circle text-warning';
					}
					$pager = $page;
				}
				if (isset($_GET['delete'])) {
					$delete_link = cleanUrls(str_replace('&action=delete&rel_id='.$_GET['delete'], '', $pager).'&action=delete&rel_id='.$releases['release_id']);
				} else {
					$delete_link = cleanUrls($pager.'&action=delete&rel_id='.$releases['release_id']);
				} 
				$table_row .= '
				<tr>
					<th scope="row">'.$i.'</th>
					<td><a href="'.$edit_link.'" title="View Content">'.$releases['title'].'</a></td>
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
			<tr><td colspan="9">'.notAvailable('You have not created any posts', '', 1).'</td></tr>';
		}		
			return $table_row;
	}

	function categoryOptions($get_post = null) {
		global $SETT, $framework;

		// Set category select options for new posts
		$option = '';
		$category = $this->dbProcessor("SELECT id, title, value FROM categories", 1);
		foreach ($category as $row) { 
			$sel = (isset($_POST['category']) && $_POST['category'] == $row['value']) || ($get_post['category'] == $row['value']) ? ' selected="selected"' : ''; 
			$option .= '<option value="'.$row['value'].'"'.$sel.'>'.$row['title'].'</option>';
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
