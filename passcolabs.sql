-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 06, 2019 at 09:19 PM
-- Server version: 5.7.27-0ubuntu0.19.04.1
-- PHP Version: 7.2.19-0ubuntu0.19.04.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `passcolabs`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `admin_user` int(11) DEFAULT NULL,
  `auth_token` varchar(128) DEFAULT NULL,
  `level` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `admin_user`, `auth_token`, `level`) VALUES
(1, 'super', '28f20a02bf8a021fab4fcec48afb584e', 2, NULL, '1'),
(2, 'admin', '5f4dcc3b5aa765d61d8327deb882cf99', 4, NULL, '0'),
(3, 'bobby', '28f20a02bf8a021fab4fcec48afb584e', 1, '$2y$10$k38b9fVgTEUkLTyxDr6mIeU3bt9UsmFdgoNCcO2q5nco5r5TZLNmG', '0');

-- --------------------------------------------------------

--
-- Table structure for table `albumentry`
--

CREATE TABLE IF NOT EXISTS `albumentry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album` int(11) NOT NULL,
  `track` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `album` (`album`) USING BTREE,
  KEY `track` (`track`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE IF NOT EXISTS `albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `by` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `pline` varchar(128) DEFAULT NULL,
  `cline` varchar(128) DEFAULT NULL,
  `label` varchar(128) DEFAULT NULL,
  `art` varchar(128) DEFAULT NULL,
  `release_id` varchar(128) DEFAULT NULL,
  `release_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `safe_link` varchar(128) DEFAULT NULL,
  `tags` text,
  `genre` varchar(128) DEFAULT NULL,
  `public` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `by` (`by`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `allowed_config`
--

CREATE TABLE IF NOT EXISTS `allowed_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `allowed_config`
--

INSERT INTO `allowed_config` (`id`, `name`) VALUES
(1, 'logo'),
(2, 'intro_logo'),
(3, 'banner'),
(4, 'site_name'),
(19, 'intro_banner'),
(20, 'slug'),
(21, 'site_phone'),
(22, 'site_office'),
(23, 'twitter'),
(24, 'instagram'),
(25, 'whatsapp'),
(26, 'facebook'),
(27, 'email'),
(28, 'ads_1'),
(29, 'ads_off'),
(30, 'allow_login');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_users`
--

CREATE TABLE IF NOT EXISTS `blocked_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`id`,`user_id`),
  KEY `by` (`id`,`by`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `value` varchar(128) DEFAULT NULL,
  `info` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `collaborators`
--

CREATE TABLE IF NOT EXISTS `collaborators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project` (`project`) USING BTREE,
  KEY `user` (`user`) USING BTREE,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `collabrequests`
--

CREATE TABLE IF NOT EXISTS `collabrequests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user` (`user`) USING BTREE,
  KEY `project` (`project`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `configuration`
--

CREATE TABLE IF NOT EXISTS `configuration` (
  `site_name` varchar(128) NOT NULL DEFAULT 'Passcontest',
  `logo` varchar(128) DEFAULT NULL,
  `intro_logo` varchar(128) DEFAULT NULL,
  `banner` varchar(128) DEFAULT NULL,
  `intro_banner` varchar(128) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `site_phone` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `site_office` text,
  `facebook` varchar(128) DEFAULT NULL,
  `twitter` varchar(128) DEFAULT NULL,
  `instagram` varchar(128) DEFAULT NULL,
  `whatsapp` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `company` varchar(128) DEFAULT NULL,
  `company_url` varchar(128) DEFAULT NULL,
  `blog_url` varchar(128) DEFAULT NULL,
  `template` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `curated_keyword` varchar(128) DEFAULT NULL,
  `skin` varchar(128) NOT NULL DEFAULT 'mdb-skin',
  `tracking` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'english',
  `cleanurl` enum('0','1') NOT NULL DEFAULT '0',
  `mode` enum('live','offline','debug') NOT NULL DEFAULT 'live',
  `per_page` int(11) NOT NULL DEFAULT '5',
  `per_featured` int(11) NOT NULL DEFAULT '5',
  `per_messenger` int(11) NOT NULL DEFAULT '5',
  `notification_limit` int(11) NOT NULL DEFAULT '6',
  `page_limits` int(11) NOT NULL DEFAULT '5',
  `sidebar_limit` int(11) NOT NULL DEFAULT '5',
  `related_limit` int(11) NOT NULL DEFAULT '5',
  `releases_limit` int(11) NOT NULL DEFAULT '5',
  `img_upload_limit` int(11) NOT NULL DEFAULT '2500000',
  `audio_upload_limit` int(11) NOT NULL DEFAULT '2500000',
  `file_upload_limit` int(11) NOT NULL DEFAULT '2500000',
  `map_embed_url` varchar(255) DEFAULT NULL,
  `fbacc` enum('0','1') NOT NULL DEFAULT '1',
  `fb_appid` varchar(128) DEFAULT NULL,
  `fb_secret` varchar(128) DEFAULT NULL,
  `twilio_sid` varchar(128) DEFAULT NULL,
  `twilio_token` varchar(128) DEFAULT NULL,
  `twilio_phone` varchar(128) DEFAULT NULL,
  `captcha` enum('0','1') NOT NULL DEFAULT '0',
  `invite_only` enum('0','1') NOT NULL DEFAULT '0',
  `activation` enum('0','email','phone') NOT NULL DEFAULT '0',
  `smtp` enum('0','1') NOT NULL DEFAULT '0',
  `sms` enum('0','1') NOT NULL DEFAULT '0',
  `smtp_server` varchar(128) NOT NULL,
  `smtp_debug` enum('0','1','2') NOT NULL DEFAULT '2',
  `smtp_port` int(6) NOT NULL,
  `smtp_secure` enum('0','ssl','tls') NOT NULL DEFAULT '0',
  `smtp_auth` enum('0','1') NOT NULL DEFAULT '0',
  `smtp_username` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `smtp_password` varchar(128) NOT NULL,
  `pay_mode` enum('0','1') NOT NULL,
  `pay_public_key` varchar(128) NOT NULL,
  `pay_private_key` varchar(128) NOT NULL,
  `pay_encryption_key` varchar(128) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `ads_1` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ads_2` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ads_3` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ads_4` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ads_off` enum('0','1') NOT NULL DEFAULT '1',
  `allow_login` enum('0','1') NOT NULL DEFAULT '0',
  `online_time` int(6) NOT NULL DEFAULT '600',
  `timeout_intval` varchar(128) NOT NULL DEFAULT '5000,300'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `configuration`
--

INSERT INTO `configuration` (`site_name`, `logo`, `intro_logo`, `banner`, `intro_banner`, `slug`, `site_phone`, `site_office`, `facebook`, `twitter`, `instagram`, `whatsapp`, `email`, `company`, `company_url`, `blog_url`, `template`, `curated_keyword`, `skin`, `tracking`, `language`, `cleanurl`, `mode`, `per_page`, `per_featured`, `per_messenger`, `notification_limit`, `page_limits`, `sidebar_limit`, `related_limit`, `releases_limit`, `img_upload_limit`, `audio_upload_limit`, `file_upload_limit`, `map_embed_url`, `fbacc`, `fb_appid`, `fb_secret`, `twilio_sid`, `twilio_token`, `twilio_phone`, `captcha`, `invite_only`, `activation`, `smtp`, `sms`, `smtp_server`, `smtp_debug`, `smtp_port`, `smtp_secure`, `smtp_auth`, `smtp_username`, `smtp_password`, `pay_mode`, `pay_public_key`, `pay_private_key`, `pay_encryption_key`, `currency`, `ads_1`, `ads_2`, `ads_3`, `ads_4`, `ads_off`, `allow_login`, `online_time`, `timeout_intval`) VALUES
('Passcolabs', '48991288_225073657_n.jpg', '1299104938_1862595620_n.png', '2136239575_189717598_n.jpg', '993590724_794965505_n.jpg', 'NIGERIA\'S NO.1 ART BLOG', '09031983482', 'No. 31 Your street Address, Somewhere in, One State, Nigeria', 'collageduceemos', 'cceemos', 'collageduceemos_', '+ 2347089593153', 'collageduce@gmail.com', 'Hoolicontech Limited', NULL, 'https://blog.passcolabs.com', 'default', 'Curated', 'mdb-skin', '\r\n    <!-- Global site tag (gtag.js) - Google Analytics -->\r\n    <script async src=\"https://www.googletagmanager.com/gtag/js?id=UA-112185838-1\"></script>\r\n    <script> \r\n      window.dataLayer = window.dataLayer || [];\r\n      function gtag(){dataLayer.push(arguments);}\r\n      gtag(\'js\', new Date());\r\n\r\n      gtag(\'config\', \'UA-112185838-1\');\r\n    </script>\r\n', 'english', '1', 'live', 10, 21, 5, 6, 5, 5, 5, 5, 3500000, 3500000, 3500000, 'https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d7847.589886224702!2d7.452054075764595!3d10.437840449809023!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sng!4v1573320251807!5m2!1sen!2sng', '0', '283872735659168', 'e2c51f61d42a9074fc61e2d9a208d300', 'AC5cf08b88620d4b3ea0672ff6bcf0aa00', '634140af0f8319a503809b1b9ce88276', '18327304145', '0', '0', '0', '0', '0', 'passcontest.cf', '2', 25, 'ssl', '1', 'support@passcontest.cf', 'friendship1A@', '0', 'pk_test_36a1170f08ef500b7c55315ea6792864953b4ca0', 'sk_test_bbc81f998f1151d1d34f8ff0e4ae4ef1fa3a9784', 'a95e1e4267f556ed1651603d', 'NGN', '<!-- Content -->\r\n  <div class=\"text-white text-center d-flex align-items-center rgba-black-strong py-5 px-4\">\r\n    <div>\r\n      <h5 class=\"pink-text\"><i class=\"fa fa-pie-chart\"></i> Marketings</h5>\r\n      <h3 class=\"card-title pt-2\"><strong>This is card title</strong></h3>\r\n      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repellat fugiat, laboriosam, voluptatem,\r\n        optio vero odio nam sit officia accusamus minus error nisi architecto nulla ipsum dignissimos.\r\n        Odit sed qui, dolorum!.</p>\r\n      <a class=\"btn btn-pink\"><i class=\"fa fa-clone left\"></i> View project</a>\r\n    </div>\r\n  </div>', '<!-- Card content -->\n  <div class=\"card-body card-body-cascade text-center\">\n\n    <!-- Title -->\n    <h4 class=\"card-title\"><strong>My adventure</strong></h4>\n    <!-- Subtitle -->\n    <h6 class=\"font-weight-bold indigo-text py-2\">This is not Photography</h6>\n    <!-- Text -->\n    <p class=\"card-text\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Exercitationem perspiciatis voluptatum a, quo nobis, non commodi quia repellendus sequi nulla voluptatem dicta reprehenderit, placeat laborum ut beatae ullam suscipit veniam.\n    </p>\n\n    <!-- Linkedin -->\n    <a class=\"px-2 fa-lg li-ic\"><i class=\"fa fa-linkedin\"></i></a>\n    <!-- Twitter -->\n    <a class=\"px-2 fa-lg tw-ic\"><i class=\"fa fa-twitter\"></i></a>\n    <!-- Dribbble -->\n    <a class=\"px-2 fa-lg fb-ic\"><i class=\"fa fa-facebook\"></i></a>\n\n  </div>', '<!-- Content -->\n  <div class=\"text-white text-center d-flex align-items-center rgba-black-strong py-5 px-4\">\n    <div>\n      <h5 class=\"pink-text\"><i class=\"fa fa-pie-chart\"></i> Marketing</h5>\n      <h3 class=\"card-title pt-2\"><strong>This is another card title</strong></h3>\n      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repellat fugiat, laboriosam, voluptatem,\n        optio vero odio nam sit officia accusamus minus error nisi architecto nulla ipsum dignissimos.\n        Odit sed qui, dolorum!.</p>\n      <a class=\"btn btn-pink\"><i class=\"fa fa-clone left\"></i> Viewadv</a>\n    </div>\n  </div>', '<!-- Content -->\n  <div class=\"text-white text-center d-flex align-items-center rgba-black-strong py-5 px-4\">\n    <div>\n      <h5 class=\"pink-text\"><i class=\"fa fa-pie-chart\"></i> Marketing</h5>\n      <h3 class=\"card-title pt-2\"><strong>This is another card title for ad </strong></h3>\n      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repellat fugiat, laboriosam, voluptatem,\n        optio vero odio nam sit officia accusamus minus error nisi architecto nulla ipsum dignissimos.\n        Odit sed qui, dolorum!.</p>\n      <a class=\"btn btn-pink\"><i class=\"fa fa-clone left\"></i> View this project</a>\n    </div>\n  </div>', '1', '0', 600, '{interval:5000,suspend:300}');

-- --------------------------------------------------------

--
-- Table structure for table `genre`
--

CREATE TABLE IF NOT EXISTS `genre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`id`, `name`, `title`) VALUES
(1, 'hip-hop', 'Hip Hop & Rap'),
(2, 'trap', 'Trap'),
(3, 'dance', 'Dance and EDM'),
(4, 'afro', 'Afro'),
(5, 'alternative-rock', 'Altenative Rock'),
(6, 'ambient', 'Ambient'),
(7, 'classical', 'Classical'),
(8, 'country', 'Country'),
(9, 'naija', 'Naija'),
(10, 'dancehal', 'Dance Hall'),
(11, 'deep-house', 'Deep House'),
(12, 'disco', 'Disco'),
(13, 'dubstep', 'Dubstep'),
(14, 'electronic', 'Electronic'),
(15, 'jazz', 'Jazz'),
(16, 'rnb', 'RNB & Soul'),
(17, 'regae', 'Regae'),
(18, 'rock', 'Rock');

-- --------------------------------------------------------

--
-- Table structure for table `instrumentals`
--

CREATE TABLE IF NOT EXISTS `instrumentals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `genre` varchar(128) DEFAULT NULL,
  `file` varchar(128) NOT NULL,
  `tags` varchar(128) NOT NULL,
  `public` enum('0','1') NOT NULL DEFAULT '0',
  `hidden` enum('0','1') NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tags` (`tags`) USING BTREE,
  KEY `title` (`title`) USING BTREE,
  KEY `user` (`user`) USING BTREE,
  KEY `project` (`project`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('1','2') NOT NULL DEFAULT '1' COMMENT '1: Album likes, 2: Track Likes',
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `item_id` (`item_id`) USING BTREE,
  KEY `time` (`time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `message_status`
--

CREATE TABLE IF NOT EXISTS `message_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `messenger`
--

CREATE TABLE IF NOT EXISTS `messenger` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) NOT NULL,
  `receiver` int(11) NOT NULL,
  `message` text NOT NULL,
  `thread` varchar(128) DEFAULT NULL,
  `seen` enum('0','1') NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cid`,`sender`) USING BTREE,
  KEY `from` (`sender`,`receiver`,`seen`),
  KEY `reciever` (`receiver`,`seen`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `new_release`
--

CREATE TABLE IF NOT EXISTS `new_release` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `release_id` varchar(128) DEFAULT NULL,
  `upc` varchar(128) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `description` text,
  `art` varchar(128) DEFAULT NULL,
  `by` int(11) NOT NULL,
  `p_genre` varchar(128) DEFAULT NULL,
  `s_genre` varchar(128) DEFAULT NULL,
  `c_line` varchar(128) DEFAULT NULL,
  `p_line` varchar(128) DEFAULT NULL,
  `c_line_year` varchar(4) DEFAULT NULL,
  `p_line_year` varchar(4) DEFAULT NULL,
  `label` varchar(128) DEFAULT NULL,
  `tags` varchar(128) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `status` enum('0','1','2','3') NOT NULL DEFAULT '1' COMMENT '3: Aproved, 2: In review, 1: Action needed, 0: Removed',
  `explicit` enum('0','1') NOT NULL DEFAULT '1',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `release_id` (`release_id`),
  KEY `title` (`title`),
  KEY `release_date` (`release_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `new_release_artists`
--

CREATE TABLE IF NOT EXISTS `new_release_artists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `release_id` varchar(128) DEFAULT NULL,
  `role` varchar(128) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `username` varchar(128) DEFAULT NULL,
  `intro` text,
  `photo` varchar(128) DEFAULT NULL,
  `by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `new_release_tracks`
--

CREATE TABLE IF NOT EXISTS `new_release_tracks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `release_id` varchar(128) DEFAULT NULL,
  `isrc` int(11) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `audio` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE IF NOT EXISTS `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  `type` enum('0','1','2','3','4','5') NOT NULL COMMENT '0: Follow, 1: Track Likes, 2: Album Likes, 3: Track Comments, 4: Album Comments, 5: Admin',
  `object` int(11) DEFAULT NULL,
  `content` text,
  `status` enum('0','1') NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1; 

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `rid` bigint(20) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `fname` varchar(128) DEFAULT NULL,
  `lname` varchar(128) DEFAULT NULL,
  `amount` decimal(9,2) DEFAULT NULL,
  `reference` varchar(128) DEFAULT NULL,
  `details` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `playlist`
--

CREATE TABLE IF NOT EXISTS `playlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `by` int(11) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `public` enum('0','1') NOT NULL DEFAULT '0',
  `featured` enum('0','1') NOT NULL DEFAULT '0',
  `plid` bigint(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `by` (`by`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `playlistentry`
--

CREATE TABLE IF NOT EXISTS `playlistentry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist` int(11) NOT NULL,
  `track` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `playlist` (`playlist`) USING BTREE,
  KEY `track` (`track`) USING BTREE,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `playlistfollows`
--

CREATE TABLE IF NOT EXISTS `playlistfollows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist` int(11) NOT NULL,
  `subscriber` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `time` (`time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `projectfollows`
--

CREATE TABLE IF NOT EXISTS `projectfollows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `follower` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project` (`project`) USING BTREE,
  KEY `follower` (`follower`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator_id` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `cover` varchar(128) DEFAULT NULL,
  `details` text,
  `instrumental` varchar(128) DEFAULT NULL,
  `datafile` varchar(128) DEFAULT NULL,
  `genre` varchar(128) DEFAULT NULL,
  `tags` varchar(128) DEFAULT NULL,
  `max_users` int(11) DEFAULT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '0',
  `published` enum('0','1','2') NOT NULL DEFAULT '0',
  `recommended` enum('0','1') NOT NULL DEFAULT '0',
  `safe_link` varchar(128) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`) USING BTREE,
  KEY `id` (`id`) USING BTREE,
  KEY `title_1` (`title`) USING BTREE,
  KEY `genre_1` (`genre`),
  KEY `tags_1` (`tags`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `relationship`
--

CREATE TABLE IF NOT EXISTS `relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leader_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `leader_id` (`leader_id`) USING BTREE,
  KEY `follower_id` (`follower_id`) USING BTREE,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `static_pages`
--

CREATE TABLE IF NOT EXISTS `static_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `banner` varchar(128) DEFAULT NULL,
  `button_links` varchar(128) DEFAULT NULL,
  `icon` varchar(128) DEFAULT NULL,
  `content` text,
  `parent` varchar(128) DEFAULT NULL,
  `safelink` varchar(128) DEFAULT NULL,
  `footer` enum('0','1') NOT NULL DEFAULT '0',
  `header` enum('0','1') NOT NULL DEFAULT '0',
  `priority` enum('0','1','2','3','4','5') NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `static_pages`
--

INSERT INTO `static_pages` (`id`, `title`, `banner`, `button_links`, `icon`, `content`, `parent`, `safelink`, `footer`, `header`, `priority`, `date`) VALUES
(1, 'But, It&#39;s mostly about you!', 'rhapsody2.JPG', 'https://www.facebook.com/guru:title:More Info:type:2,www.example.com/guru:title:Register Now:type:1', 'fa-bug', '<p>COLLAGE DU CEEMOS is a platform designed to showcase Elegant, Exquisite artworks of diverse genres and specifications (Paintings, Sculptures, and Photographs) from talented Artists to the general art community and enthusiasts of arts and culture through a well grounded platform designed to promote the awareness, popularity and appreciation of Arts and the likes in the Nigerian public and beyond. Art is universal and COLLAGE DU CEEMOS aims to provide Art for collectors of art, Exhibitors, home settings, and any kind of workspace be it offices, receptions, waiting rooms, lounges, bars and the likes.</p>', 'about', NULL, '0', '0', '3', '2019-11-09 16:27:53'),
(3, 'Creative', NULL, NULL, 'fa-paint-brush', 'We are creative, we know what you want, we know how to create what you want and we go the extra mile, do the dirty job just to get you that clean plate you\'ve been longing to see.', 'about', NULL, '0', '0', '2', '2019-10-09 12:26:16'),
(4, 'Exhibit', NULL, NULL, 'fa-magic', 'We create, but don\'t know if you create too; well, if you do, we could help you get your creations out there for the world to see and help you get those prospects you seek.', 'about', NULL, '0', '0', '2', '2019-10-09 12:26:16'),
(5, 'Submissions and advertising', NULL, NULL, NULL, 'Submissions are welcome from artists who wish to get their works featured on the platform. Simply contact us through the information on the contact page to discuss further terms and conditions. Art Exhibitions and events organizers can also publish details of their upcoming events on the platform through the same process. We look forward to hearing from you!', 'about', NULL, '0', '0', '1', '2019-10-09 12:26:16'),
(6, 'About The Founder', '1210817745_1901812764_n.png', '', 'fa-bug', '<p>Suleiman Olaide Semiu is a 23 year old graduate of the university of Ilorin with a First degree in Geology and Mineral Sciences with a Professional certification in Project management from the Project Management Academy after which he obtained a Masters degree in Energy Security Management from the Nigerian Defense Academy post graduate school. He has a drive for problem solving, entrepreneurship, personal career development and an eye for the arts, especially contemporary African art.&nbsp;</p><p>&nbsp;{$texp-&gt;marxemi}</p>', 'about', NULL, '0', '0', '1', '2019-11-09 15:59:12'),
(7, 'Contact us', '', '', 'fa-bug', '<p>Do you want to talk doing business with us or you have a suggestion or an idea that could make us better, or you simply need answers to questions you think we have answers to, please do not hesitate to contact us! You can instantly send us a message using the contact form below or use our contact info.&nbsp;</p>', 'contact', NULL, '0', '0', '3', '2019-11-09 18:13:33'),
(9, 'Welcome to africa', '', NULL, 'fa-500px', '<p>button on the editor to add more images to this pubutton on the editor to add more images to this pubutton on the editor to add more images to this pubutton on the editor to add more images to this pu</p>', 'about', 'welcome-to-africa', '0', '0', '1', '2019-10-09 12:26:16'),
(12, 'How do you like yours', NULL, '', 'fa-bug', '<p>Ex sunt eu ullamco ullamco nostrud cillum aliquip officia dolor fugiat elit officia reprehenderit adipisicing reprehenderit excepteur sit cillum sit culpa elit cupidatat aute mollit do deserunt velit consequat sed aliquip dolore duis laboris aliqua est enim eu proident sed consequat amet amet in incididunt nisi deserunt pariatur duis esse minim velit labore duis aute et reprehenderit tempor esse magna quis reprehenderit sed dolore sunt aute aliqua eiusmod id.</p><figure class=\"image image-style-align-left\"><img src=\"http://passcolabs.te/uploads/photos/davidson_2/1583839945_262764459_n.png\"><figcaption>Swell Again</figcaption></figure><p>Ullamco ex mollit occaecat officia fugiat laboris commodo sunt est in veniam reprehenderit laboris est occaecat ut laborum non fugiat cillum occaecat cupidatat est excepteur pariatur ea in mollit aliquip dolor culpa eu cupidatat ullamco ad culpa dolor minim dolor laborum qui aute deserunt ut magna ad do tempor nulla et nisi in adipisicing fugiat magna incididunt deserunt eiusmod irure elit id occaecat elit minim ad quis do cupidatat fugiat dolore laboris irure.</p>', 'static', 'like-minim', '1', '1', '3', '2019-11-28 01:01:01'),
(13, 'What a friend we have in jesus', '', NULL, NULL, 'Ex sunt eu ullamco ullamco nostrud cillum aliquip officia dolor fugiat elit officia reprehenderit adipisicing reprehenderit excepteur sit cillum sit culpa elit cupidatat aute mollit do deserunt velit consequat sed aliquip dolore duis laboris aliqua est enim eu proident sed consequat amet amet in incididunt nisi deserunt pariatur duis esse minim velit labore duis aute et reprehenderit tempor esse magna quis reprehenderit sed dolore sunt aute aliqua eiusmod id ullamco ex mollit occaecat officia fugiat laboris commodo sunt est in veniam reprehenderit laboris est occaecat ut laborum non fugiat cillum occaecat cupidatat est excepteur pariatur ea in mollit aliquip dolor culpa eu cupidatat ullamco ad culpa dolor minim dolor laborum qui aute deserunt ut magna ad do tempor nulla et nisi in adipisicing fugiat magna incididunt deserunt eiusmod irure elit id occaecat elit minim ad quis do cupidatat fugiat dolore laboris irure.', 'static', 'like-salama', '1', '0', '3', '2019-10-08 12:26:16'),
(14, 'Saalaam Aleikum', '555740678_1598032979_n.jpeg', NULL, NULL, 'Ex sunt eu ullamco ullamco nostrud cillum aliquip officia dolor fugiat elit officia reprehenderit adipisicing reprehenderit excepteur sit cillum sit culpa elit cupidatat aute mollit do deserunt velit consequat sed aliquip dolore duis laboris aliqua est enim eu proident sed consequat amet amet in incididunt nisi deserunt pariatur duis esse minim velit labore duis aute et reprehenderit tempor esse magna quis reprehenderit sed dolore sunt aute aliqua eiusmod id ullamco ex mollit occaecat officia fugiat laboris commodo sunt est in veniam reprehenderit laboris est occaecat ut laborum non fugiat cillum occaecat cupidatat est excepteur pariatur ea in mollit aliquip dolor culpa eu cupidatat ullamco ad culpa dolor minim dolor laborum qui aute deserunt ut magna ad do tempor nulla et nisi in adipisicing fugiat magna incididunt deserunt eiusmod irure elit id occaecat elit minim ad quis do cupidatat fugiat dolore laboris irure.', 'static', 'like-salam-aleikum', '1', '0', '3', '2019-10-01 12:26:16'),
(15, 'Saalaam Aleikum Is back', '555740678_1598032979_n.jpeg', NULL, NULL, 'Ex sunt eu ullamco ullamco nostrud cillum aliquip officia dolor fugiat elit officia reprehenderit adipisicing reprehenderit excepteur sit cillum sit culpa elit cupidatat aute mollit do deserunt velit consequat sed aliquip dolore duis laboris aliqua est enim eu proident sed consequat amet amet in incididunt nisi deserunt pariatur duis esse minim velit labore duis aute et reprehenderit tempor esse magna quis reprehenderit sed dolore sunt aute aliqua eiusmod id ullamco ex mollit occaecat officia fugiat laboris commodo sunt est in veniam reprehenderit laboris est occaecat ut laborum non fugiat cillum occaecat cupidatat est excepteur pariatur ea in mollit aliquip dolor culpa eu cupidatat ullamco ad culpa dolor minim dolor laborum qui aute deserunt ut magna ad do tempor nulla et nisi in adipisicing fugiat magna incididunt deserunt eiusmod irure elit id occaecat elit minim ad quis do cupidatat fugiat dolore laboris irure.', 'static', 'back-salam-aleikum', '1', '0', '3', '2019-10-09 12:26:16'),
(16, 'Scillum sit culpa elit cupidatat', '555740678_1598032979_n.jpeg', NULL, NULL, 'Ex sunt eu ullamco ullamco nostrud cillum aliquip officia dolor fugiat elit officia reprehenderit adipisicing reprehenderit excepteur sit cillum sit culpa elit cupidatat aute mollit do deserunt velit consequat sed aliquip dolore duis laboris aliqua est enim eu proident sed consequat amet amet in incididunt nisi deserunt pariatur duis esse minim velit labore duis aute et reprehenderit tempor esse magna quis reprehenderit sed dolore sunt aute aliqua eiusmod id ullamco ex mollit occaecat officia fugiat laboris commodo sunt est in veniam reprehenderit laboris est occaecat ut laborum non fugiat cillum occaecat cupidatat est excepteur pariatur ea in mollit aliquip dolor culpa eu cupidatat ullamco ad culpa dolor minim dolor laborum qui aute deserunt ut magna ad do tempor nulla et nisi in adipisicing fugiat magna incididunt deserunt eiusmod irure elit id occaecat elit minim ad quis do cupidatat fugiat dolore laboris irure.', 'static', 'aute-deserunt', '1', '0', '3', '2019-10-09 12:26:16'),
(19, 'How to do great things', '', NULL, 'fa-bug', '<p>We create, but dont know if you create too; well, if you do, we could help you get your creations out there for the world to see and help you get those prospects you seek.We create, but dont know if you create too; well, if you do, we could help you get your creations out there for the world to see and help you get those prospects you seek.We create, but dont know if you create too; well, if you do, we could help you get your creations out there for the world to see and help you get those prospects you seek.We create, but dont know if you create too; well, if you do, we could help you get your creations out there for the world to see and help you get those prospects you seek.</p>', 'about', 'how-to-do-great-things', '0', '0', '1', '2019-10-11 15:26:17'),
(20, 'Art events and Exhibitions', '', NULL, 'fa-bug', '<p>Get ahead of the latest gatherings and meetups made possible by people who appreciate art for the appreciation of art.</p>', 'events', 'art-events-and-exhibitions', '0', '0', '3', '2019-10-25 07:38:56'),
(21, 'Collage du ceemos', '', NULL, 'fa-bug', '<p>Submissions are requested from creative persons any where in the world, for more information please visit the about section for details.</p>', 'footer', 'collage-du-ceemos', '0', '0', '3', '2019-10-28 14:19:27'),
(30, 'Collage du ceemos', '105472042_235854472_n.jpg', NULL, 'fa-bug', '<p>Submissions are requested from creative persons any where in the world, for more information please visit the about section for details.</p>', 'footer', 'collage-du-ceemos', '0', '0', '3', '2019-11-07 14:04:08'),
(31, 'Today is a new day', NULL, '', 'fa-bug', '<p>Ex sunt eu ullamco ullamco nostrud cillum aliquip officia dolor fugiat elit officia reprehenderit adipisicing reprehenderit excepteur sit cillum sit culpa elit cupidatat aute mollit do deserunt velit consequat sed aliquip dolore duis laboris aliqua est enim eu proident sed consequat amet amet in incididunt nisi deserunt pariatur duis esse minim velit labore duis aute et reprehenderit tempor esse magna quis reprehenderit sed dolore sunt aute aliqua eiusmod id ullamco ex mollit occaecat officia fugiat laboris commodo sunt est in veniam reprehenderit laboris est occaecat ut laborum non fugiat cillum occaecat cupidatat est excepteur pariatur ea in mollit aliquip dolor culpa eu cupidatat ullamco ad culpa dolor minim dolor laborum qui aute deserunt ut magna ad do tempor nulla et nisi in adipisicing fugiat magna incididunt deserunt eiusmod irure elit id occaecat elit minim ad quis do cupidatat fugiat dolore laboris irure.&nbsp;</p>', 'static', 'today-is-a-new-day', '1', '0', '3', '2019-11-10 00:31:03'),
(32, 'Second line', '1675903440_2005855823_n.png', '', 'fa-bug', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>', 'about', 'second-line', '0', '0', '4', '2019-11-09 16:22:51'),
(33, 'We do beautiful things', '', '', 'fa-bug', '<p>Use the image button on the editor to add more images to this publication.<br>You can fetch and append a users profile by adding {$texp-&gt;username} to the main content (username should be the username of the profile you wish to append).</p>', 'about', 'we-do-beautiful-things', '0', '0', '5', '2019-11-09 17:19:21'),
(34, 'We create music', '', '', 'fa-bug', '<p>Provide only the urls for the buttons, and specify the title by appending :title:Title to the end of the link. To specify whether the button will be filled or bordered append</p>', 'about', 'we-create-music', '0', '0', '5', '2019-11-09 17:19:46'),
(35, 'LISTEN TO MUSIC BUZZ ANYWHERE.', '775883856_468412126_n.jpg', ' https://www.passcolab.com/guru:title:Read More:type:2', 'fa-bug', '<p>Vivamus suscipit tortor eget felis porttitor volutpat. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus</p>', 'home', 'listen-to-music-buzz-anywhere', '0', '0', '5', '2019-11-23 17:33:41'),
(36, 'MUSIC BUZZ GET ADDICTED TO MUSIC', '709399823_448023028_n.jpg', '#choose:title:Know More:type:2', 'fa-bug', '<p>Vestibulum volutpat non eros ut vulputate. Nunc id risus accumsan Donec mi nulla, auctor nec sem a, ornare auctor mi. Sed mi tortor, commodo a felis in, fringilla tincidunt nulla</p><p>Donec mi nulla, auctor nec sem a, ornare auctor mi. Sed mi tortor, commodo a felis in, fringilla tincidunt nulla. Vestibulum volutpat non eros ut &nbsp;vulpuuctor nec sem a, ornare auctor mi. Sed mi tortor, commodo a felis in, fringilla tincidunt nulla. Vestibulum volutpat non eros uttate.</p>', 'home', 'music-buzz-get-addicted-to-music', '0', '0', '4', '2019-11-23 18:31:20'),
(37, 'WHY CHOOSE US?', '1117023181_1838608154_n.png', '#choose:title:Know More:type:2', 'fa-bug', '<p>MUSIC BUZZ</p>', 'home', 'why-choose-us', '0', '0', '2', '2019-11-23 21:24:21'),
(38, 'MUSIC BUZZ GET ADDICTED TO HOT MUSIC', '', '#choose:title:Know More:type:2', 'fa-bug', '<p>Donec mi nulla, auctor nec sem a, ornare auctor mi. Sed mi tortor, commodo a felis in, fringilla tincidunt nulla. Vestibulum volutpat non eros ut vulpuuctor nec sem a, ornare auctor mi. Sed mi tortor, commodo a felis in, fringilla tincidunt nulla. Vestibulum volutpat non eros uttate.</p><p>Donec mi nulla, auctor nec sem a, ornare auctor mi. Sed mi tortor, commodo a felis in, fringilla tincidunt nulla. Vestibulum volutpat non eros ut vulpuuctor nec sem a, ornare auctor mi. Sed mi tortor, commodo a felis in, fringilla tincidunt nulla. Vestibulum volutpat non eros uttate.</p>', 'home', 'music-buzz-get-addicted-to-hot-music', '0', '0', '1', '2019-11-23 21:37:40'),
(39, 'FOR MORE INFORMATION', '1918751587_1013958009_n.jpg', 'STAY IN TOUCH', 'fa-bug', '<p>Donec mi nullDonec mi nulla, auctor nec sem a, ornare auctor mi. Sed mi tortor, commodo a felis in, fringilla tincidunt nulla. Vestibulum volutpat non eros ut vulpuuctor nec sem a, a auctor nec sem a ornare auctor mi.</p>', 'home', 'for-more-information', '0', '0', '0', '2019-11-23 21:43:40'),
(40, 'FAQ', '', '', 'fa-bug', '<ol><li>Use the image button on the editor to add more images to this publication.<br>You can fetch and append a users profile by adding {$texp-&gt;username} to the main content (username should be the username of the profile you wish to append)</li><li>Provide only the urls for the buttons, and specify the title by appending :title:Title to the end of the link. To specify whether the button will be filled or bordered append a :type:1 or :type:2, to specify multiple buttons separate them with a comma(,) for example: <strong>https://www.facebook.com/guru:title:Guru:type:2,www.example.com/guru:title:Guru:type:1</strong></li><li>For content that appear at the top most of the about page, set the priority to 3, for the second row set the priority to 2, and for the others set priority to 1. Priority set at 0 wont be used. Be careful when creating or modifying content for the about and contact pages, certain features are unused and you may be required to remove excesses. Icons are only applicable to about page content with a priority of 2.</li></ol>', 'static', 'faq', '0', '1', '1', '2019-11-27 23:27:33');

-- --------------------------------------------------------

--
-- Table structure for table `stems`
--

CREATE TABLE IF NOT EXISTS `stems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `file` varchar(128) DEFAULT NULL,
  `tag` varchar(128) NOT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project` (`project`) USING BTREE,
  KEY `user` (`user`) USING BTREE,
  KEY `tag` (`tag`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `title` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `title`) VALUES
(1, 'intro', 'Intro'),
(2, 'hook', 'Hook'),
(3, 'pre_hook', 'Pre Hook'),
(4, 'Instruments', 'Instruments'),
(5, 'chorus', 'Chorus'),
(6, 'refrain', 'Refrain'),
(7, 'bridge', 'Bridge'),
(8, 'verse_one', 'Verse One'),
(9, 'verse_two', 'Verse Two'),
(10, 'verse_three', 'Verse Three'),
(11, 'verse_four', 'Verse Four'),
(12, 'verse_five', 'Verse Five'),
(13, 'verse_six', 'Verse Six'),
(14, 'adlibs', 'Adlibs'),
(15, 'talk', 'Talk'),
(16, 'outro', 'Outro');

-- --------------------------------------------------------

--
-- Table structure for table `tracks`
--

CREATE TABLE IF NOT EXISTS `tracks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `description` text,
  `art` varchar(128) DEFAULT NULL,
  `audio` varchar(128) DEFAULT NULL,
  `artist_id` int(11) DEFAULT NULL,
  `aggregator_id` int(11) DEFAULT NULL,
  `explicit` enum('0','1') NOT NULL DEFAULT '0',
  `label` varchar(128) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `likes` int(11) NOT NULL DEFAULT '0',
  `public` int(11) NOT NULL DEFAULT '0',
  `release` date DEFAULT NULL,
  `upload_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pline` varchar(128) DEFAULT NULL,
  `cline` varchar(128) DEFAULT NULL,
  `featured` enum('0','1') NOT NULL DEFAULT '0',
  `genre` varchar(128) DEFAULT NULL,
  `s_genre` varchar(128) DEFAULT NULL,
  `tags` text,
  `safe_link` varchar(128) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `release_id` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `title` (`title`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) DEFAULT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `fname` varchar(128) DEFAULT NULL,
  `lname` varchar(128) DEFAULT NULL,
  `country` varchar(128) DEFAULT NULL,
  `state` varchar(128) DEFAULT NULL,
  `city` varchar(128) DEFAULT NULL,
  `newsletter` enum('1','0') NOT NULL DEFAULT '0',
  `photo` varchar(128) DEFAULT NULL,
  `cover` varchar(128) DEFAULT NULL,
  `intro` text,
  `facebook` varchar(128) DEFAULT NULL,
  `twitter` varchar(128) DEFAULT NULL,
  `instagram` varchar(128) DEFAULT NULL,
  `label` varchar(128) DEFAULT NULL,
  `verified` enum('0','1') NOT NULL DEFAULT '0',
  `role` enum('1','2','3','4','5') NOT NULL DEFAULT '1',
  `online` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(255) DEFAULT NULL,
  `token_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `username`, `password`, `email`, `fname`, `lname`, `country`, `state`, `city`, `newsletter`, `photo`, `cover`, `intro`, `facebook`, `twitter`, `instagram`, `label`, `verified`, `role`, `online`, `reg_date`, `token`, `token_date`) VALUES
(2, 'davidson', '28f20a02bf8a021fab4fcec48afb584e', 'mygame@gmail.com', 'Davidson', 'Olowu', 'Bahrain', 'Manama', 'Manama', '1', '1229181009_2087010590_210015907_n.png', '1690584172_68244057_1646527174_n.png', 'Short text is here to help', 'bobby_', '_bobby', 'bobby_ig', 'newnify', '1', '4', '2019-11-06 13:56:58', '2019-08-10 04:42:04', NULL, '2019-08-14 04:42:04');
-- --------------------------------------------------------

--
-- Table structure for table `views`
--

CREATE TABLE IF NOT EXISTS `views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `by` int(11) NOT NULL,
  `track` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `by` (`by`) USING BTREE,
  KEY `time` (`time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `new_release`
--
ALTER TABLE `new_release` ADD FULLTEXT KEY `title_2` (`title`);

--
-- Indexes for table `playlist`
--
ALTER TABLE `playlist` ADD FULLTEXT KEY `title` (`title`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects` ADD FULLTEXT KEY `tags` (`tags`);
ALTER TABLE `projects` ADD FULLTEXT KEY `genre` (`genre`);
ALTER TABLE `projects` ADD FULLTEXT KEY `title` (`title`);
