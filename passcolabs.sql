-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 04, 2019 at 09:18 PM
-- Server version: 5.7.27-0ubuntu0.19.04.1
-- PHP Version: 7.2.19-0ubuntu0.19.04.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `passcolabs`
--

-- --------------------------------------------------------

--
-- Table structure for table `albumentry`
--

CREATE TABLE `albumentry` (
  `id` int(11) NOT NULL,
  `album` int(11) NOT NULL,
  `track` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
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
  `public` enum('0','1') NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `collaborators`
--

CREATE TABLE `collaborators` (
  `id` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `collabrequests`
--

CREATE TABLE `collabrequests` (
  `id` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `genre`
--

CREATE TABLE `genre` (
  `id` int(11) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL
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

CREATE TABLE `instrumentals` (
  `id` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `file` varchar(128) NOT NULL,
  `tags` varchar(128) NOT NULL,
  `public` enum('0','1') NOT NULL DEFAULT '0',
  `hidden` enum('0','1') NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `instrumentals`
--

INSERT INTO `instrumentals` (`id`, `project`, `user`, `title`, `file`, `tags`, `public`, `hidden`, `time`) VALUES
(13, 3, 2, 'Imagine Me', 'PCAUD-MUERQAB659437-WGX_INST.mp3', 'franklin,release,solo,tracks', '0', '0', '2019-09-01 22:40:06');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `type` enum('1','2') NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `new_release`
--

CREATE TABLE `new_release` (
  `id` int(11) NOT NULL,
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
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `new_release`
--

INSERT INTO `new_release` (`id`, `release_id`, `upc`, `title`, `description`, `art`, `by`, `p_genre`, `s_genre`, `c_line`, `p_line`, `c_line_year`, `p_line_year`, `label`, `tags`, `release_date`, `approved_date`, `status`, `explicit`, `date`) VALUES
(1, '3277979445956', '', 'How stars do cover', 'If you don&#39;t have a formally agreed label your artist name or band name will be sufficient, Descriptions such as &#34;Indie&#34;, &#34;Independent&#34;, &#34;Non&#34; will not be accepted.', '1629481705_671746119_85174190_n.jpg', 2, 'rock', 'jazz', 'Passit', 'Newnify', '2019', '2019', 'Newnify Music', 'deep-house,alternative-rock,dubstep,afro,regae,rock,dancehal,classical,dance,disco,rnb,ambient,naija,country,electronic', '2019-10-09', NULL, '2', '1', '2019-09-30 23:06:22'),
(2, '5340427757970', '', 'Welcome Again', 'If you don&#39;t have a formally agreed label your artist name or band name will be sufficient', '777262550_2002105107_2037566856_n.jpg', 2, 'rock', 'electronic', 'Xper1mentall Music', 'Xper1mentall Music', '2019', '2019', 'Xper1mentall Music', 'rock,rnb,regae', '2019-10-09', NULL, '2', '0', '2019-10-01 06:31:14'),
(3, '3587808661344', '', 'Beautiful Babe', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '1', '2019-09-29 13:50:22'),
(4, '1878299053653', '', 'Grateful', 'd a new artist, there must always be a primary artist.\r\n(If you no longer need and artist you can remove them from the artist services section).', '1645448544_752966858_1498885540_n.JPG', 2, 'rock', 'jazz', 'Xper1mentall Music', 'Newnify', '2019', '2019', 'Xper1mentall Music', 'rock,afro,wew,west', '2019-09-25', NULL, '0', '1', '2019-09-30 22:17:58'),
(5, '2423799986134', '', 'Daz How Star Do (TheUnikVersion)', 'o generate useful suggestions of tracks to users based on their preference, tracks with span tags are going to be deleted. M', '1155646781_1801632142_727901068_n.jpg', 2, 'rock', 'rock', 'Newnify', 'Newnify', '2019', '2019', 'Newnify Music', 'deep-house,alternative-rock', '2019-10-09', '2019-10-01', '0', '0', '2019-10-01 12:45:27');

-- --------------------------------------------------------

--
-- Table structure for table `new_release_artists`
--

CREATE TABLE `new_release_artists` (
  `id` int(11) NOT NULL,
  `release_id` varchar(128) DEFAULT NULL,
  `role` varchar(128) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `username` varchar(128) DEFAULT NULL,
  `intro` text,
  `photo` varchar(128) DEFAULT NULL,
  `by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `new_release_artists`
--

INSERT INTO `new_release_artists` (`id`, `release_id`, `role`, `name`, `username`, `intro`, `photo`, `by`) VALUES
(1, '1878299053653', 'primary', 'Western', 'western', NULL, '2000988763_368205464_1673384145_n.jpg', 2),
(2, '1878299053653', 'primary', 'Western', 'western', NULL, NULL, 2),
(3, '1878299053653', 'primary', 'Western', 'western', NULL, NULL, 2),
(4, '1878299053653', 'primary', 'Western', 'western', NULL, NULL, 2),
(5, '3277979445956', 'primary', 'Element', 'element', NULL, NULL, 2),
(6, '5340427757970', 'primary', 'Marxemi', 'marxemi', NULL, NULL, 2),
(7, '2423799986134', 'primary', 'David Olowu', 'davidson', NULL, NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `new_release_tracks`
--

CREATE TABLE `new_release_tracks` (
  `id` int(11) NOT NULL,
  `release_id` varchar(128) DEFAULT NULL,
  `isrc` int(11) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `audio` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `new_release_tracks`
--

INSERT INTO `new_release_tracks` (`id`, `release_id`, `isrc`, `title`, `audio`) VALUES
(1, '1878299053653', NULL, 'Feel Good Ice Prince ft  Phyno x Falz Naijapals', 'PCAUD-FDZXC892731-WER.mp3'),
(2, '1878299053653', NULL, 'Deal With It Phyno  Naijapals', 'PCAUD-IVLWZ459212-XYD.mp3'),
(3, '1878299053653', NULL, 'Ride For You Phyno ft  Davido Naijapals', 'PCAUD-GOQJP628507-PYT.mp3'),
(4, '2423799986134', NULL, ' Blow My Mind (Audio)', 'PCAUD-DFAOM660975-DUZ.mp3'),
(5, '3277979445956', NULL, 'Phyno SFSG So Far So Good 9jaflaver.com ', 'PCAUD-MTEQL828667-OEG.mp3'),
(6, '5340427757970', NULL, 'Ride For You Phyno ft  Davido Naijapals', 'pcaud-zqysa410513-ytz.mp3'),
(7, '5340427757970', NULL, 'davido noni radio active', 'pcaud-bnxtm889067-tqo.mp3');

-- --------------------------------------------------------

--
-- Table structure for table `playlist`
--

CREATE TABLE `playlist` (
  `id` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `public` enum('0','1') NOT NULL DEFAULT '0',
  `featured` enum('0','1') NOT NULL DEFAULT '0',
  `plid` bigint(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `playlist`
--

INSERT INTO `playlist` (`id`, `by`, `title`, `public`, `featured`, `plid`, `views`) VALUES
(2, 2, 'Food Line', '1', '0', 1235, 5);

-- --------------------------------------------------------

--
-- Table structure for table `playlistentry`
--

CREATE TABLE `playlistentry` (
  `id` int(11) NOT NULL,
  `playlist` int(11) NOT NULL,
  `track` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `playlistfollows`
--

CREATE TABLE `playlistfollows` (
  `id` int(11) NOT NULL,
  `playlist` int(11) NOT NULL,
  `subscriber` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `playlistfollows`
--

INSERT INTO `playlistfollows` (`id`, `playlist`, `subscriber`, `time`) VALUES
(23, 2, 2, '2019-10-01 12:50:49');

-- --------------------------------------------------------

--
-- Table structure for table `projectfollows`
--

CREATE TABLE `projectfollows` (
  `id` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `follower` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
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
  `recomended` enum('0','1') NOT NULL DEFAULT '0',
  `safe_link` varchar(128) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `creator_id`, `title`, `cover`, `details`, `instrumental`, `datafile`, `genre`, `tags`, `max_users`, `status`, `published`, `recomended`, `safe_link`, `time`) VALUES
(1, 1, 'Grateful', 'mon.jpg', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vitae dignissimos temporibus, animi soluta neque assumenda dolorum inventore numquam eveniet alias distinctio, sed iure obcaecati error esse dolores excepturi totam, ut. ipsum dolor sit amet, consectetur adipisicing elit. Quam minus optio repellat provident nam. Nobis porro obcaecati odit, ipsa vitae recusandae, repellat eaque, quas aliquid explicabo a magni harum tenetur? ipsum dolor sit amet, consectetur adipisicing elit. Earum doloribus optio ut iste deserunt est dolor suscipit expedita pariatur eos dolores saepe ratione eaque distinctio totam, tempore voluptatem laboriosam explicabo.', 'PCAUD-GZECLPS865527-VWR.mp3', 'grateful.zip', 'trap', 'hip-hop, rap, trap', 12, '1', '1', '0', 'greatful', '2019-09-03 22:20:56'),
(2, 1, 'Rebranding', '5.jpg', 'Nobis porro obcaecati odit, ipsa vitae recusandae, repellat eaque, quas aliquid explicabo a magni harum tenetur? ipsum dolor sit amet, consectetur adipisicing elit. Earum doloribus optio ut iste deserunt est dolor suscipit expedita pariatur eos dolores saepe ratione eaque distinctio totam, tempore voluptatem laboriosam explicabo. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vitae dignissimos temporibus, animi soluta neque assumenda dolorum inventore numquam eveniet alias distinctio, sed iure obcaecati error esse dolores excepturi totam, ut. ipsum dolor sit amet, consectetur adipisicing elit. Quam minus optio repellat provident nam. ', 'PCAUD-IBAUCNZ869688-SHO.mp3', 'rebrand.zip', 'gospel', 'blues,western,vast', 2, '1', '0', '0', 'rebranding', '2019-09-01 13:50:50'),
(3, 2, 'Definition', 'jcole.jpg', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vitae dignissimos temporibus, animi soluta neque assumenda dolorum inventore numquam eveniet alias distinctio, sed iure obcaecati error esse dolores excepturi totam, ut. ipsum dolor sit amet, consectetur adipisicing elit. Quam minus optio repellat provident nam. ', 'PCAUD-IBAUCNZ869688-SHO.mp3', 'PCAUD-RGBOEKJ446689-RUO.zip', 'trap', 'trap,western,vast', 12, '1', '0', '0', 'definition', '2019-10-01 12:53:21');

-- --------------------------------------------------------

--
-- Table structure for table `relationship`
--

CREATE TABLE `relationship` (
  `id` int(11) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `relationship`
--

INSERT INTO `relationship` (`id`, `leader_id`, `follower_id`, `date`) VALUES
(1, 7, 2, '2019-10-01 12:22:06');

-- --------------------------------------------------------

--
-- Table structure for table `stems`
--

CREATE TABLE `stems` (
  `id` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `file` varchar(128) DEFAULT NULL,
  `tag` varchar(128) NOT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `title` varchar(128) NOT NULL
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

CREATE TABLE `tracks` (
  `id` int(11) NOT NULL,
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
  `release_id` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` int(11) NOT NULL,
  `username` varchar(128) DEFAULT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `fname` varchar(128) DEFAULT NULL,
  `lname` varchar(128) DEFAULT NULL,
  `photo` varchar(128) DEFAULT NULL,
  `cover` varchar(128) DEFAULT NULL,
  `intro` text,
  `label` varchar(128) DEFAULT NULL,
  `verified` enum('0','1') NOT NULL DEFAULT '0',
  `role` enum('1','2','3','4','5') NOT NULL DEFAULT '1',
  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `username`, `password`, `email`, `fname`, `lname`, `photo`, `cover`, `intro`, `label`, `verified`, `role`, `reg_date`, `token_date`) VALUES
(2, 'davidson', 'a1fa59e79bba1a38bb0684d3298c9ddd', 'mygame@gmail.com', 'David', 'Olowu', 'yonas.jpg', NULL, NULL, 'newnify', '0', '4', '2019-08-10 04:42:04', '2019-08-14 04:42:04'),
(6, 'wilson', 'a1fa59e79bba1a38bb0684d3298c9ddd', 'mygame@gmail.com', 'Wilson', 'Good', '', NULL, NULL, 'pass', '1', '1', '2019-09-28 00:00:00', '2019-09-28 19:09:24'),
(7, 'western', '42bf85196c63fadb97cc4123d7ecf834', NULL, 'Western', '', '2000988763_368205464_1673384145_n.jpg', NULL, '', 'Xper1mentall Music', '0', '2', '2019-09-30 15:13:19', '2019-09-30 15:13:19');

-- --------------------------------------------------------

--
-- Table structure for table `views`
--

CREATE TABLE `views` (
  `id` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  `track` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `views`
--

INSERT INTO `views` (`id`, `by`, `track`, `time`) VALUES
(1, 1, 3, '2019-09-29 14:02:27'),
(2, 1, 4, '2019-05-14 14:02:27'),
(3, 1, 4, '2019-01-15 14:02:27'),
(4, 3, 4, '2019-01-15 14:02:27'),
(5, 3, 5, '2019-01-15 14:02:27'),
(6, 3, 6, '2019-01-15 14:02:27'),
(7, 3, 7, '2019-01-15 14:02:27'),
(8, 4, 7, '2019-01-15 14:02:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albumentry`
--
ALTER TABLE `albumentry`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `album` (`album`) USING BTREE,
  ADD KEY `track` (`track`) USING BTREE;

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `by` (`by`) USING BTREE;

--
-- Indexes for table `collaborators`
--
ALTER TABLE `collaborators`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project` (`project`) USING BTREE,
  ADD KEY `user` (`user`) USING BTREE,
  ADD KEY `id` (`id`) USING BTREE;

--
-- Indexes for table `collabrequests`
--
ALTER TABLE `collabrequests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`) USING BTREE,
  ADD KEY `project` (`project`) USING BTREE;

--
-- Indexes for table `genre`
--
ALTER TABLE `genre`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `instrumentals`
--
ALTER TABLE `instrumentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tags` (`tags`) USING BTREE,
  ADD KEY `title` (`title`) USING BTREE,
  ADD KEY `user` (`user`) USING BTREE,
  ADD KEY `project` (`project`) USING BTREE;

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`) USING BTREE,
  ADD KEY `item_id` (`item_id`) USING BTREE,
  ADD KEY `time` (`time`) USING BTREE;

--
-- Indexes for table `new_release`
--
ALTER TABLE `new_release`
  ADD PRIMARY KEY (`id`),
  ADD KEY `release_id` (`release_id`),
  ADD KEY `title` (`title`),
  ADD KEY `release_date` (`release_date`);
ALTER TABLE `new_release` ADD FULLTEXT KEY `title_2` (`title`);

--
-- Indexes for table `new_release_artists`
--
ALTER TABLE `new_release_artists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `new_release_tracks`
--
ALTER TABLE `new_release_tracks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `playlist`
--
ALTER TABLE `playlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `by` (`by`) USING BTREE;
ALTER TABLE `playlist` ADD FULLTEXT KEY `title` (`title`);

--
-- Indexes for table `playlistentry`
--
ALTER TABLE `playlistentry`
  ADD PRIMARY KEY (`id`),
  ADD KEY `playlist` (`playlist`) USING BTREE,
  ADD KEY `track` (`track`) USING BTREE,
  ADD KEY `id` (`id`) USING BTREE;

--
-- Indexes for table `playlistfollows`
--
ALTER TABLE `playlistfollows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `time` (`time`) USING BTREE;

--
-- Indexes for table `projectfollows`
--
ALTER TABLE `projectfollows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project` (`project`) USING BTREE,
  ADD KEY `follower` (`follower`) USING BTREE;

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creator_id` (`creator_id`) USING BTREE,
  ADD KEY `id` (`id`) USING BTREE,
  ADD KEY `title_1` (`title`) USING BTREE,
  ADD KEY `genre_1` (`genre`),
  ADD KEY `tags_1` (`tags`) USING BTREE;
ALTER TABLE `projects` ADD FULLTEXT KEY `tags` (`tags`);
ALTER TABLE `projects` ADD FULLTEXT KEY `genre` (`genre`);
ALTER TABLE `projects` ADD FULLTEXT KEY `title` (`title`);

--
-- Indexes for table `relationship`
--
ALTER TABLE `relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leader_id` (`leader_id`) USING BTREE,
  ADD KEY `follower_id` (`follower_id`) USING BTREE,
  ADD KEY `id` (`id`) USING BTREE;

--
-- Indexes for table `stems`
--
ALTER TABLE `stems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project` (`project`) USING BTREE,
  ADD KEY `user` (`user`) USING BTREE,
  ADD KEY `tag` (`tag`) USING BTREE;

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tracks`
--
ALTER TABLE `tracks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`) USING BTREE,
  ADD KEY `uid` (`uid`) USING BTREE,
  ADD KEY `title` (`title`) USING BTREE;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `uid` (`uid`) USING BTREE;

--
-- Indexes for table `views`
--
ALTER TABLE `views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `by` (`by`) USING BTREE,
  ADD KEY `time` (`time`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albumentry`
--
ALTER TABLE `albumentry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `collaborators`
--
ALTER TABLE `collaborators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `collabrequests`
--
ALTER TABLE `collabrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `genre`
--
ALTER TABLE `genre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table `instrumentals`
--
ALTER TABLE `instrumentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `new_release`
--
ALTER TABLE `new_release`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `new_release_artists`
--
ALTER TABLE `new_release_artists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `new_release_tracks`
--
ALTER TABLE `new_release_tracks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `playlist`
--
ALTER TABLE `playlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `playlistentry`
--
ALTER TABLE `playlistentry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `playlistfollows`
--
ALTER TABLE `playlistfollows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `projectfollows`
--
ALTER TABLE `projectfollows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `relationship`
--
ALTER TABLE `relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `stems`
--
ALTER TABLE `stems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `tracks`
--
ALTER TABLE `tracks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `views`
--
ALTER TABLE `views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
