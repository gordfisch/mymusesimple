-- phpMyAdmin SQL Dump
-- version 2.10.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Oct 26, 2009 at 01:26 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.4-2ubuntu5.7

-- /////////SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `mymuse`
-- 


-- --------------------------------------------------------

-- Table structure for table `#__mymuse_country`
-- 


DROP TABLE IF EXISTS `#__mymuse_country`;
CREATE TABLE IF NOT EXISTS `#__mymuse_country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bloc` tinytext NOT NULL,
  `plugin` tinytext NOT NULL,
  `country_name` varchar(64) DEFAULT NULL,
  `country_3_code` char(3) DEFAULT NULL,
  `country_2_code` char(2) DEFAULT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_country_name` (`country_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Country records' AUTO_INCREMENT=245 ;

--
-- Dumping data for table `#__mymuse_country`
--

INSERT INTO `#__mymuse_country` (`id`, `bloc`, `plugin`, `country_name`, `country_3_code`, `country_2_code`, `ordering`) VALUES
(1, '', '1', 'Afghanistan', 'AFG', 'AF', 1),
(2, '', '1', 'Albania', 'ALB', 'AL', 2),
(3, '', '1', 'Algeria', 'DZA', 'DZ', 3),
(4, '', '1', 'American Samoa', 'ASM', 'AS', 4),
(5, '', '1', 'Andorra', 'AND', 'AD', 5),
(6, '', '1', 'Angola', 'AGO', 'AO', 6),
(7, '', '1', 'Anguilla', 'AIA', 'AI', 7),
(8, '', '1', 'Antarctica', 'ATA', 'AQ', 8),
(9, '', '1', 'Antigua and Barbuda', 'ATG', 'AG', 9),
(10, '', '1', 'Argentina', 'ARG', 'AR', 10),
(11, '', '1', 'Armenia', 'ARM', 'AM', 11),
(12, '', '1', 'Aruba', 'ABW', 'AW', 12),
(13, '', 'paypal', 'Australia', 'AUS', 'AU', 13),
(14, 'EU', 'paypal', 'Österreich', 'AUT', 'AT', 164),
(15, '', '1', 'Azerbaijan', 'AZE', 'AZ', 14),
(16, '', '1', 'Bahamas', 'BHS', 'BS', 15),
(17, '', '1', 'Bahrain', 'BHR', 'BH', 16),
(18, '', '1', 'Bangladesh', 'BGD', 'BD', 17),
(19, '', '1', 'Barbados', 'BRB', 'BB', 18),
(20, '', '1', 'Belarus', 'BLR', 'BY', 19),
(21, 'EU', 'paypal', 'Belgium', 'BEL', 'BE', 20),
(22, '', '1', 'Belize', 'BLZ', 'BZ', 21),
(23, '', '1', 'Benin', 'BEN', 'BJ', 22),
(24, '', '1', 'Bermuda', 'BMU', 'BM', 23),
(25, '', '1', 'Bhutan', 'BTN', 'BT', 24),
(26, '', '1', 'Bolivia', 'BOL', 'BO', 25),
(27, '', '1', 'Bosnia and Herzegowina', 'BIH', 'BA', 26),
(28, '', '1', 'Botswana', 'BWA', 'BW', 27),
(29, '', '1', 'Bouvet Island', 'BVT', 'BV', 28),
(30, '', '1', 'Brazil', 'BRA', 'BR', 29),
(31, '', '1', 'British Indian Ocean Territory', 'IOT', 'IO', 30),
(32, '', '1', 'Brunei Darussalam', 'BRN', 'BN', 31),
(33, 'EU', 'paypal', 'Bulgaria', 'BGR', 'BG', 32),
(34, '', '1', 'Burkina Faso', 'BFA', 'BF', 33),
(35, '', '1', 'Burundi', 'BDI', 'BI', 34),
(36, '', '1', 'Cambodia', 'KHM', 'KH', 35),
(37, '', '1', 'Cameroon', 'CMR', 'CM', 36),
(38, '', 'paypal', 'Canada', 'CAN', 'CA', 37),
(39, '', '1', 'Cape Verde', 'CPV', 'CV', 39),
(40, '', '1', 'Cayman Islands', 'CYM', 'KY', 40),
(41, '', '1', 'Central African Republic', 'CAF', 'CF', 41),
(42, '', '1', 'Chad', 'TCD', 'TD', 42),
(43, '', '1', 'Chile', 'CHL', 'CL', 43),
(44, '', '1', 'China', 'CHN', 'CN', 44),
(45, '', '1', 'Christmas Island', 'CXR', 'CX', 45),
(46, '', '1', 'Cocos (Keeling) Islands', 'CCK', 'CC', 46),
(47, '', '1', 'Colombia', 'COL', 'CO', 47),
(48, '', '1', 'Comoros', 'COM', 'KM', 48),
(49, '', '1', 'Congo', 'COG', 'CG', 49),
(50, '', '1', 'Cook Islands', 'COK', 'CK', 50),
(51, '', '1', 'Costa Rica', 'CRI', 'CR', 51),
(52, '', '1', 'Cote D''Ivoire', 'CIV', 'CI', 52),
(53, 'EU', 'paypal', 'Croatia', 'HRV', 'HR', 53),
(54, '', '1', 'Cuba', 'CUB', 'CU', 54),
(55, 'EU', 'paypal', 'Cyprus', 'CYP', 'CY', 55),
(56, 'EU', 'paypal', 'Czech Republic', 'CZE', 'CZ', 56),
(57, 'EU', 'paypal', 'Denmark', 'DNK', 'DK', 57),
(58, '', '1', 'Djibouti', 'DJI', 'DJ', 59),
(59, '', '1', 'Dominica', 'DMA', 'DM', 60),
(60, '', '1', 'Dominican Republic', 'DOM', 'DO', 61),
(61, '', '1', 'East Timor', 'TMP', 'TP', 62),
(62, '', '1', 'Ecuador', 'ECU', 'EC', 64),
(63, '', '1', 'Egypt', 'EGY', 'EG', 65),
(64, '', '1', 'El Salvador', 'SLV', 'SV', 66),
(65, '', '1', 'Equatorial Guinea', 'GNQ', 'GQ', 67),
(66, '', '1', 'Eritrea', 'ERI', 'ER', 68),
(67, 'EU', 'paypal', 'Estonia', 'EST', 'EE', 69),
(68, '', '1', 'Ethiopia', 'ETH', 'ET', 70),
(69, '', '1', 'Falkland Islands (Malvinas)', 'FLK', 'FK', 71),
(70, '', '1', 'Faroe Islands', 'FRO', 'FO', 72),
(71, '', '1', 'Fiji', 'FJI', 'FJ', 73),
(72, 'EU', 'paypal', 'Finland', 'FIN', 'FI', 74),
(73, 'EU', 'paypal', 'France', 'FRA', 'FR', 75),
(74, '', '1', 'France, Metropolitan', 'FXX', 'FX', 76),
(75, '', '1', 'French Guiana', 'GUF', 'GF', 77),
(76, '', '1', 'French Polynesia', 'PYF', 'PF', 78),
(77, '', '1', 'French Southern Territories', 'ATF', 'TF', 79),
(78, '', '1', 'Gabon', 'GAB', 'GA', 80),
(79, '', '1', 'Gambia', 'GMB', 'GM', 81),
(80, '', '1', 'Georgia', 'GEO', 'GE', 82),
(81, 'EU', 'paypal', 'Deutschland', 'DEU', 'DE', 58),
(82, '', '1', 'Ghana', 'GHA', 'GH', 83),
(83, '', '1', 'Gibraltar', 'GIB', 'GI', 84),
(84, 'EU', 'paypal', 'Greece', 'GRC', 'GR', 85),
(85, '', '1', 'Greenland', 'GRL', 'GL', 86),
(86, '', '1', 'Grenada', 'GRD', 'GD', 87),
(87, '', '1', 'Guadeloupe', 'GLP', 'GP', 88),
(88, '', '1', 'Guam', 'GUM', 'GU', 89),
(89, '', '1', 'Guatemala', 'GTM', 'GT', 90),
(90, '', '1', 'Guinea', 'GIN', 'GN', 91),
(91, '', '1', 'Guinea-bissau', 'GNB', 'GW', 92),
(92, '', '1', 'Guyana', 'GUY', 'GY', 93),
(93, '', '1', 'Haiti', 'HTI', 'HT', 94),
(94, '', '1', 'Heard and Mc Donald Islands', 'HMD', 'HM', 95),
(95, '', '1', 'Honduras', 'HND', 'HN', 96),
(96, '', 'paypal', 'Hong Kong', 'HKG', 'HK', 97),
(97, 'EU', 'paypal', 'Hungary', 'HUN', 'HU', 98),
(98, '', '1', 'Iceland', 'ISL', 'IS', 99),
(99, '', '1', 'India', 'IND', 'IN', 100),
(100, '', '1', 'Indonesia', 'IDN', 'ID', 101),
(101, '', '1', 'Iran (Islamic Republic of)', 'IRN', 'IR', 102),
(102, '', '1', 'Iraq', 'IRQ', 'IQ', 103),
(103, 'EU', 'paypal', 'Ireland', 'IRL', 'IE', 104),
(104, '', 'paypal', 'Israel', 'ISR', 'IL', 105),
(105, 'EU', 'paypal', 'Italien', 'ITA', 'IT', 106),
(106, '', '1', 'Jamaica', 'JAM', 'JM', 107),
(107, '', 'paypal', 'Japan', 'JPN', 'JP', 108),
(108, '', '1', 'Jordan', 'JOR', 'JO', 110),
(109, '', '1', 'Kazakhstan', 'KAZ', 'KZ', 111),
(110, '', 'paypal', 'Kenya', 'KEN', 'KE', 112),
(111, '', '1', 'Kiribati', 'KIR', 'KI', 113),
(112, '', '1', 'Korea, DPR', 'PRK', 'KP', 114),
(113, '', '1', 'Korea, Republic of', 'KOR', 'KR', 115),
(114, '', '1', 'Kuwait', 'KWT', 'KW', 116),
(115, '', '1', 'Kyrgyzstan', 'KGZ', 'KG', 117),
(116, '', '1', 'Lao PDR', 'LAO', 'LA', 118),
(117, 'EU', 'paypal', 'Latvia', 'LVA', 'LV', 119),
(118, '', '1', 'Lebanon', 'LBN', 'LB', 120),
(119, '', '1', 'Lesotho', 'LSO', 'LS', 121),
(120, '', '1', 'Liberia', 'LBR', 'LR', 122),
(121, '', '1', 'Libyan Arab Jamahiriya', 'LBY', 'LY', 123),
(122, '', 'paypal', 'Liechtenstein', 'LIE', 'LI', 124),
(123, 'EU', 'paypal', 'Lithuania', 'LTU', 'LT', 125),
(124, 'EU', 'paypal', 'Luxembourg', 'LUX', 'LU', 126),
(125, '', '1', 'Macau', 'MAC', 'MO', 127),
(126, '', '1', 'Macedonia', 'MKD', 'MK', 128),
(127, '', '1', 'Madagascar', 'MDG', 'MG', 129),
(128, '', '1', 'Malawi', 'MWI', 'MW', 130),
(129, '', '1', 'Malaysia', 'MYS', 'MY', 131),
(130, '', '1', 'Maldives', 'MDV', 'MV', 132),
(131, '', '1', 'Mali', 'MLI', 'ML', 133),
(132, 'EU', 'paypal', 'Malta', 'MLT', 'MT', 134),
(133, '', '1', 'Marshall Islands', 'MHL', 'MH', 135),
(134, '', '1', 'Martinique', 'MTQ', 'MQ', 136),
(135, '', '1', 'Mauritania', 'MRT', 'MR', 137),
(136, '', '1', 'Mauritius', 'MUS', 'MU', 138),
(137, '', '1', 'Mayotte', 'MYT', 'YT', 139),
(138, '', 'paypal', 'Mexico', 'MEX', 'MX', 140),
(139, '', '1', 'Micronesia, Federated States of', 'FSM', 'FM', 141),
(140, '', '1', 'Moldova, Republic of', 'MDA', 'MD', 142),
(141, '', '1', 'Monaco', 'MCO', 'MC', 143),
(142, '', '1', 'Mongolia', 'MNG', 'MN', 144),
(143, '', '1', 'Montserrat', 'MSR', 'MS', 145),
(144, '', '1', 'Morocco', 'MAR', 'MA', 146),
(145, '', '1', 'Mozambique', 'MOZ', 'MZ', 147),
(146, '', '1', 'Myanmar', 'MMR', 'MM', 148),
(147, '', '1', 'Namibia', 'NAM', 'NA', 149),
(148, '', '1', 'Nauru', 'NRU', 'NR', 150),
(149, '', '1', 'Nepal', 'NPL', 'NP', 151),
(150, 'EU', 'paypal', 'Netherlands', 'NLD', 'NL', 152),
(151, '', '1', 'Netherlands Antilles', 'ANT', 'AN', 153),
(152, '', '1', 'New Caledonia', 'NCL', 'NC', 154),
(153, '', 'paypal', 'New Zealand', 'NZL', 'NZ', 155),
(154, '', '1', 'Nicaragua', 'NIC', 'NI', 156),
(155, '', '1', 'Niger', 'NER', 'NE', 157),
(156, '', '1', 'Nigeria', 'NGA', 'NG', 158),
(157, '', '1', 'Niue', 'NIU', 'NU', 159),
(158, '', '1', 'Norfolk Island', 'NFK', 'NF', 160),
(159, '', '1', 'Northern Mariana Islands', 'MNP', 'MP', 161),
(160, '', 'paypal', 'Norway', 'NOR', 'NO', 162),
(161, '', '1', 'Oman', 'OMN', 'OM', 163),
(162, '', '1', 'Pakistan', 'PAK', 'PK', 165),
(163, '', '1', 'Palau', 'PLW', 'PW', 166),
(164, '', '1', 'Panama', 'PAN', 'PA', 167),
(165, '', '1', 'Papua New Guinea', 'PNG', 'PG', 168),
(166, '', '1', 'Paraguay', 'PRY', 'PY', 169),
(167, '', '1', 'Peru', 'PER', 'PE', 170),
(168, '', '1', 'Philippines', 'PHL', 'PH', 171),
(169, '', '1', 'Pitcairn', 'PCN', 'PN', 172),
(170, 'EU', '1', 'Poland', 'POL', 'PL', 173),
(171, 'EU', '1', 'Portugal', 'PRT', 'PT', 174),
(172, '', '1', 'Puerto Rico', 'PRI', 'PR', 175),
(173, '', '1', 'Qatar', 'QAT', 'QA', 176),
(174, '', '1', 'Reunion', 'REU', 'RE', 177),
(175, 'EU', '1', 'Romania', 'ROM', 'RO', 178),
(176, '', '1', 'Russian Federation', 'RUS', 'RU', 179),
(177, '', '1', 'Rwanda', 'RWA', 'RW', 180),
(178, '', '1', 'Saint Kitts and Nevis', 'KNA', 'KN', 182),
(179, '', '1', 'Saint Lucia', 'LCA', 'LC', 183),
(180, '', '1', 'Saint Vincent and the Grenadines', 'VCT', 'VC', 184),
(181, '', '1', 'Samoa', 'WSM', 'WS', 185),
(182, '', '1', 'San Marino', 'SMR', 'SM', 186),
(183, '', '1', 'Sao Tome and Principe', 'STP', 'ST', 187),
(184, '', '1', 'Saudi Arabia', 'SAU', 'SA', 188),
(185, '', '1', 'Senegal', 'SEN', 'SN', 190),
(186, '', '1', 'Seychelles', 'SYC', 'SC', 191),
(187, '', '1', 'Sierra Leone', 'SLE', 'SL', 192),
(188, '', '1', 'Singapore', 'SGP', 'SG', 193),
(189, 'EU', '1', 'Slovakia (Slovak Republic)', 'SVK', 'SK', 194),
(190, 'EU', '1', 'Slovenia', 'SVN', 'SI', 195),
(191, '', '1', 'Solomon Islands', 'SLB', 'SB', 196),
(192, '', '1', 'Somalia', 'SOM', 'SO', 197),
(193, '', '1', 'South Africa', 'ZAF', 'ZA', 198),
(194, '', '1', 'S. Georgia and the S. Sandwich Islands', 'SGS', 'GS', 181),
(195, 'EU', '1', 'Spain', 'ESP', 'ES', 199),
(196, '', '1', 'Sri Lanka', 'LKA', 'LK', 200),
(197, '', '1', 'St. Helena', 'SHN', 'SH', 203),
(198, '', '1', 'St. Pierre and Miquelon', 'SPM', 'PM', 204),
(199, '', '1', 'Sudan', 'SDN', 'SD', 205),
(200, '', '1', 'Suriname', 'SUR', 'SR', 206),
(201, '', '1', 'Svalbard and Jan Mayen Islands', 'SJM', 'SJ', 207),
(202, '', '1', 'Swaziland', 'SWZ', 'SZ', 208),
(203, 'EU', '1', 'Sweden', 'SWE', 'SE', 209),
(204, '', '1', 'Schweiz', 'CHE', 'CH', 189),
(205, '', '1', 'Syrian Arab Republic', 'SYR', 'SY', 210),
(206, '', '1', 'Taiwan', 'TWN', 'TW', 211),
(207, '', '1', 'Tajikistan', 'TJK', 'TJ', 212),
(208, '', '1', 'Tanzania, United Republic of', 'TZA', 'TZ', 213),
(209, '', '1', 'Thailand', 'THA', 'TH', 214),
(210, '', '1', 'Togo', 'TGO', 'TG', 216),
(211, '', '1', 'Tokelau', 'TKL', 'TK', 217),
(212, '', '1', 'Tonga', 'TON', 'TO', 218),
(213, '', '1', 'Trinidad and Tobago', 'TTO', 'TT', 219),
(214, '', '1', 'Tunisia', 'TUN', 'TN', 220),
(215, '', '1', 'Turkey', 'TUR', 'TR', 221),
(216, '', '1', 'Turkmenistan', 'TKM', 'TM', 222),
(217, '', '1', 'Turks and Caicos Islands', 'TCA', 'TC', 223),
(218, '', '1', 'Tuvalu', 'TUV', 'TV', 224),
(219, '', '1', 'Uganda', 'UGA', 'UG', 225),
(220, '', '1', 'Ukraine', 'UKR', 'UA', 226),
(221, '', '1', 'United Arab Emirates', 'ARE', 'AE', 227),
(222, 'EU', '1', 'United Kingdom', 'GBR', 'GB', 228),
(223, '', '1', 'United States', 'USA', 'US', 229),
(224, '', '1', 'United States Minor Outlying Islands', 'UMI', 'UM', 230),
(225, '', '1', 'Uruguay', 'URY', 'UY', 231),
(226, '', '1', 'Uzbekistan', 'UZB', 'UZ', 232),
(227, '', '1', 'Vanuatu', 'VUT', 'VU', 233),
(228, '', '1', 'Vatican City State (Holy See)', 'VAT', 'VA', 234),
(229, '', '1', 'Venezuela', 'VEN', 'VE', 235),
(230, '', '1', 'Viet Nam', 'VNM', 'VN', 236),
(231, '', '1', 'Virgin Islands (British)', 'VGB', 'VG', 237),
(232, '', '1', 'Virgin Islands (U.S.)', 'VIR', 'VI', 238),
(233, '', '1', 'Wallis and Futuna Islands', 'WLF', 'WF', 239),
(234, '', '1', 'Western Sahara', 'ESH', 'EH', 240),
(235, '', '1', 'Yemen', 'YEM', 'YE', 241),
(236, '', '1', 'Yugoslavia', 'YUG', 'YU', 242),
(237, '', '1', 'The Democratic Republic of Congo', 'DRC', 'DC', 215),
(238, '', '1', 'Zambia', 'ZMB', 'ZM', 243),
(239, '', '1', 'Zimbabwe', 'ZWE', 'ZW', 244),
(240, '', '1', 'East Timor', 'XET', 'XE', 63),
(241, '', '1', 'Jersey', 'XJE', 'XJ', 109),
(242, '', '1', 'St. Barthelemy', 'XSB', 'XB', 201),
(243, '', '1', 'St. Eustatius', 'XSE', 'XU', 202),
(244, '', '1', 'Canary Islands', 'XCA', 'XC', 38);


--
-- Table structure for table `#_mymuse_coupon`
--

CREATE TABLE IF NOT EXISTS `#__mymuse_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) DEFAULT '',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `code` varchar(64) DEFAULT '',
  `coupon_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=Per Order, 1=Per Product',
  `product_id` int(11) unsigned NOT NULL DEFAULT '0',
  `coupon_value` decimal(12,5) DEFAULT NULL,
  `coupon_value_type` tinyint(1) NOT NULL COMMENT '0=Flat-rate, 1=Percentage',
  `currency_id` int(11) DEFAULT NULL,
  `description` text,
  `params` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',

  `start_date` datetime NOT NULL COMMENT 'GMT Only',
  `expiration_date` datetime DEFAULT NULL COMMENT 'GMT Only',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `coupon_uses` int(11) NOT NULL COMMENT 'Running count of the number of uses of this coupon',
  `coupon_max_uses` int(11) NOT NULL DEFAULT '-1' COMMENT '-1=Infinite',
  `coupon_max_uses_per_user` int(11) NOT NULL DEFAULT '-1' COMMENT '-1=Infinite',

  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



-- 
-- Table structure for table `#__mymuse_currency`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_currency` (
  `id` int(11) NOT NULL auto_increment,
  `currency_name` varchar(64) default NULL,
  `currency_code` char(3) default NULL,
  `symbol` varchar(4) NOT NULL default '$',
  PRIMARY KEY  (`id`),
  KEY `idx_currency_name` (`currency_name`)
) DEFAULT CHARSET=utf8 COMMENT='Used to store currencies' ;

-- 
-- Dumping data for table `#__mymuse_currency`
-- 

INSERT IGNORE INTO `#__mymuse_currency` (`id`, `currency_name`, `currency_code`, `symbol`) VALUES 
(1, 'US Dollar', 'USD', '$'),
(2, 'Canadian Dollar', 'CAD', '$'),
(3, 'British Pound', 'GBP', '£'),
(4, 'Japanese Yen', 'JPY', '¥'),
(5, 'Australian Dollar', 'AUD', '$'),
(6, 'New Zealand Dollar', 'NZD', '$'),
(7, 'Swiss Franc', 'CHF', 'CHF'),
(8, 'Hong Kong Dollar', 'HKD', '$'),
(9, 'Singapore Dollar', 'SGD', '$'),
(10, 'Swedish Krona', 'SEK', 'kr'),
(11, 'Danish Krone', 'DKK', 'kr'),
(12, 'Polish Złoty', 'PLN', 'kr'),
(13, 'Norwegian Kroner', 'NOK', 'kr'),
(14, 'Hungarian Forint', 'HUF', 'Ft'),
(15, 'Czech Koruna', 'CZK', 'Kč'),
(16, 'Israeli Shekel', 'ILS', '₪'),
(17, 'Mexican Peso', 'MXP', '$'),
(18, 'Euro', 'EUR', '€'),
(19, 'Kenyan Shilling', 'KES', 'KSh'),
(20, 'South African Rand', 'ZAR', 'R');



-- --------------------------------------------------------

-- 
-- Table structure for table `#__mymuse_downloads`
--
CREATE TABLE IF NOT EXISTS `#__mymuse_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '1',
  `user_name` varchar(64) DEFAULT NULL,
  `user_email` varchar(255) NOT NULL,
  `order_id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `product_id` int(11) NOT NULL DEFAULT '1',
  `product_filename` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_product_filename` (`product_filename`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Download records';


-- --------------------------------------------------------

-- 
-- Table structure for table `#__mymuse_order`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `order_number` varchar(32) DEFAULT NULL,
  `shopper_id` int(11) DEFAULT NULL,
  `ship_info_id` int(11) NOT NULL,
  `order_subtotal` decimal(10,2) DEFAULT NULL,
  `order_shipping` decimal(10,2) DEFAULT NULL,
  `order_currency` varchar(16) DEFAULT NULL,
  `order_status` char(1) DEFAULT NULL,
  `coupon_name` varchar(124) NOT NULL,
  `coupon_discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `coupon_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  `modified` datetime DEFAULT '0000-00-00 00:00:00',
  `discount` decimal(10,2) DEFAULT NULL,
  `shopper_group_discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notes` text DEFAULT NULL,
  `reservation_fee` float(10,2) NOT NULL DEFAULT '0.00',
  `non_res_total` float(10,2) NOT NULL DEFAULT '0.00',
  `pay_now` float(10,2) NOT NULL DEFAULT '0.00',
  `extra` text NOT NULL,
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `licence` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;

-- 
-- Dumping data for table `#__mymuse_order`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `#__mymuse_order_item`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_order_item` (
  `id` int(11) NOT NULL auto_increment,
  `order_id` int(11) default NULL,
  `product_id` int(11) default NULL,
  `product_quantity` int(11) default NULL,
  `product_item_price` decimal(10,2) default NULL,
  `product_sku` varchar(254) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `end_date` int(20) NOT NULL,
  `downloads` tinyint(2) NOT NULL default '0',
  `created` datetime default '0000-00-00 00:00:00',
  `modified` datetime default '0000-00-00 00:00:00',
  `product_in_stock` int(1) default NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8 ;

-- 
-- Dumping data for table `#__mymuse_order_item`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `#__mymuse_order_payment`
-- 
CREATE TABLE IF NOT EXISTS `#__mymuse_order_payment` (
  `id` int(10) NOT NULL auto_increment,
  `order_id` int(11) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT '',
  `plugin` text NOT NULL,
  `institution` varchar(255) NOT NULL DEFAULT '',
  `date` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `amountin` decimal(10,2) NOT NULL default '0.00',
  `fees` decimal(10,2) NOT NULL default '0.00',
  `amountout` decimal(10,2) NOT NULL default '0.00',
  `rate` decimal(10,5) NOT NULL default '1.00000',
  `transaction_id` text NOT NULL,
  `transaction_status` varchar(255) NOT NULL DEFAULT '',
  `transaction_details` text NOT NULL,
  `refundid` int(10) NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`),
  KEY `date` (`date`),
  KEY `transaction_id` (`transaction_id`(32))
) DEFAULT CHARSET=utf8;


-- --------------------------------------------------------


--  h
-- Table structure for table `#__mymuse_order_shipping`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_order_shipping` (
  `id` int(11) NOT NULL auto_increment,
  `order_id` int(11) NOT NULL,
  `ship_type` varchar(255) default NULL,
  `ship_carrier_code` varchar(11) default NULL,
  `ship_carrier_name` varchar(32) default NULL,
  `ship_method_code` varchar(11) NOT NULL default '',
  `ship_method_name` varchar(32) default NULL,
  `cost` decimal(10,2) NOT NULL default '0.00',
  `tracking_id` mediumtext NOT NULL,
  `created` datetime default '0000-00-00 00:00:00',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8 ;





-- 
-- Table structure for table `#__mymuse_order_status`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_order_status` (
  `id` int(11) NOT NULL auto_increment,
  `code` char(1) NOT NULL,
  `name` varchar(64) default NULL,
  `ordering` int(11) default NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8 ;

-- 
-- Dumping data for table `#__mymuse_order_status`
-- 

INSERT IGNORE INTO `#__mymuse_order_status` (`id`, `code`, `name`, `ordering`) VALUES 
(1, 'P', 'Pending', 1),
(2, 'C', 'Confirmed', 2),
(3, 'X', 'Cancelled', 3),
(4, 'S', 'Shipped', 4),
(5, 'I', 'Invalid', 5);

-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__mymuse_product_file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `file_name` varchar(255) NOT NULL,
  `file_length` varchar(32) NOT NULL,
  `file_time` varchar(32) NOT NULL,
  `file_downloads` int(11) NOT NULL DEFAULT '0',
  `file_type` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`file_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- 
-- Table structure for table `#__mymuse_product`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `parentid` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `product_sku` varchar(64) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `title_alias` varchar(255) NOT NULL DEFAULT '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `catid` int(11) NOT NULL,
  `price` decimal(10,4) default NULL,
  `list_image` varchar(255) NOT NULL,
  `detail_image` varchar(255) NOT NULL,
  `urls` text NOT NULL,
  `attribs` text NOT NULL,
  `version` int(11) unsigned NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` varchar(1024) NOT NULL DEFAULT '',
  `metadesc` varchar(1024) NOT NULL DEFAULT '',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `metadata` varchar(2048) NOT NULL DEFAULT '',
  `product_physical` tinyint(1) NOT NULL DEFAULT '0',
  `product_downloadable` tinyint(1) NOT NULL DEFAULT '0',
  `product_allfiles` tinyint(1) NOT NULL DEFAULT '0',
  `product_made_date` date DEFAULT '0000-00-00 00:00:00',
  `product_in_stock` int(11) NOT NULL DEFAULT '1',
  `product_special` char(1) DEFAULT NULL,
  `product_discount` float(4,2) DEFAULT '0.00',
  `product_full_time` varchar(8) NOT NULL,
  `product_country` char(2) NOT NULL,
  `product_publisher` varchar(255) NOT NULL,
  `product_producer` varchar(255) NOT NULL,
  `product_studio` varchar(255) NOT NULL,
  `reservation_fee` float(10,2) NOT NULL DEFAULT '0.00',
  `product_package_ordering` smallint(2) NOT NULL DEFAULT '0',
  `product_package` tinyint(1) NOT NULL DEFAULT '0',
  `file_length` varchar(32) NOT NULL,
  `file_time` varchar(32) NOT NULL,
  `file_name` text NOT NULL,
  `file_downloads` int(11) NOT NULL DEFAULT '0',
  `file_contents` longblob NOT NULL,
  `file_type` varchar(32) NOT NULL,
  `file_preview` varchar(255) NOT NULL DEFAULT '',
  `file_preview_2` varchar(255) NOT NULL DEFAULT '',
  `file_preview_3` varchar(255) NOT NULL DEFAULT '',
  `file_preview_4` varchar(255) NOT NULL DEFAULT '',
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if product is featured.',
  `language` char(7) NOT NULL COMMENT 'The language code for the article.',
   `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_createdby` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



-- 
-- Dumping data for table `#__mymuse_product`
-- 

-- 
-- Table structure for table `#__mymuse_product_attribute`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_product_attribute` (
  `id` int(11) NOT NULL auto_increment,
  `product_id` int(11) NOT NULL default '0',
  `product_attribute_sku_id` int(11) NOT NULL default '0',
  `attribute_name` varchar(255) NOT NULL,
  `attribute_value` char(255) default NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8  ;

-- 
-- Dumping data for table `#__mymuse_product_attribute`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `#__mymuse_product_attribute_sku`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_product_attribute_sku` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `product_parent_id` int(11) NOT NULL default '0',
  `ordering` int(11) default NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8 ;

-- 
-- Dumping data for table `#__mymuse_product_attribute_sku`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `#__mymuse_product_category_xref`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_product_category_xref` (
  `catid` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `#__mymuse_product_category_xref`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `#__mymuse_product_recommend_xref`
-- 
CREATE TABLE IF NOT EXISTS `#__mymuse_product_recommend_xref` (
  `product_id` int(11) NOT NULL DEFAULT '0',
  `recommend_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `#__mymuse_product_rating`
--

CREATE TABLE IF NOT EXISTS `#__mymuse_product_rating` (
  `product_id` int(11) NOT NULL DEFAULT '0',
  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
  `rating_count` int(10) unsigned NOT NULL DEFAULT '0',
  `lastip` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table structure for table `#__mymuse_shopper_group`
-- 


CREATE TABLE IF NOT EXISTS `#__mymuse_shopper_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopper_group_name` varchar(32) DEFAULT NULL,
  `shopper_group_description` text,
  `discount` tinyint(2) DEFAULT NULL,
  `state` int(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__mymuse_shopper_group`
--

INSERT IGNORE INTO `#__mymuse_shopper_group` (`id`, `shopper_group_name`, `shopper_group_description`, `discount`, `state`, `checked_out`, `checked_out_time`) VALUES
(1, 'default', 'Ordinary Shoppers', 0, 1, 0, '0000-00-00 00:00:00');


-- --------------------------------------------------------

-- 
-- Table structure for table `#__mymuse_state`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_state` (
  `id` int(11) NOT NULL auto_increment,
  `country_id` int(11) NOT NULL default '1',
  `state_name` varchar(64) default NULL,
  `state_3_code` char(3) default NULL,
  `state_2_code` char(2) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_country_id` (`country_id`)
) DEFAULT CHARSET=utf8 COMMENT='States that are assigned to a country' ;

-- 
-- Dumping data for table `#__mymuse_state`
-- 

INSERT IGNORE INTO `#__mymuse_state` (`id`, `country_id`, `state_name`, `state_3_code`, `state_2_code`) VALUES
(1, 223, 'Alabama', 'ALA', 'AL'),
(2, 223, 'Alaska', 'ALK', 'AK'),
(3, 223, 'Arizona', 'ARZ', 'AZ'),
(4, 223, 'Arkansas', 'ARK', 'AR'),
(5, 223, 'California', 'CAL', 'CA'),
(6, 223, 'Colorado', 'COL', 'CO'),
(7, 223, 'Connecticut', 'CCT', 'CT'),
(8, 223, 'Delaware', 'DEL', 'DE'),
(9, 223, 'District Of Columbia', 'DOC', 'DC'),
(10, 223, 'Florida', 'FLO', 'FL'),
(11, 223, 'Georgia', 'GEA', 'GA'),
(12, 223, 'Hawaii', 'HWI', 'HI'),
(13, 223, 'Idaho', 'IDA', 'ID'),
(14, 223, 'Illinois', 'ILL', 'IL'),
(15, 223, 'Indiana', 'IND', 'IN'),
(16, 223, 'Iowa', 'IOA', 'IA'),
(17, 223, 'Kansas', 'KAS', 'KS'),
(18, 223, 'Kentucky', 'KTY', 'KY'),
(19, 223, 'Louisiana', 'LOA', 'LA'),
(20, 223, 'Maine', 'MAI', 'ME'),
(21, 223, 'Maryland', 'MLD', 'MD'),
(22, 223, 'Massachusetts', 'MSA', 'MA'),
(23, 223, 'Michigan', 'MIC', 'MI'),
(24, 223, 'Minnesota', 'MIN', 'MN'),
(25, 223, 'Mississippi', 'MIS', 'MS'),
(26, 223, 'Missouri', 'MIO', 'MO'),
(27, 223, 'Montana', 'MOT', 'MT'),
(28, 223, 'Nebraska', 'NEB', 'NE'),
(29, 223, 'Nevada', 'NEV', 'NV'),
(30, 223, 'New Hampshire', 'NEH', 'NH'),
(31, 223, 'New Jersey', 'NEJ', 'NJ'),
(32, 223, 'New Mexico', 'NEM', 'NM'),
(33, 223, 'New York', 'NEY', 'NY'),
(34, 223, 'North Carolina', 'NOC', 'NC'),
(35, 223, 'North Dakota', 'NOD', 'ND'),
(36, 223, 'Ohio', 'OHI', 'OH'),
(37, 223, 'Oklahoma', 'OKL', 'OK'),
(38, 223, 'Oregon', 'ORN', 'OR'),
(39, 223, 'Pennsylvania', 'PEA', 'PA'),
(40, 223, 'Rhode Island', 'RHI', 'RI'),
(41, 223, 'South Carolina', 'SOC', 'SC'),
(42, 223, 'South Dakota', 'SOD', 'SD'),
(43, 223, 'Tennessee', 'TEN', 'TN'),
(44, 223, 'Texas', 'TXS', 'TX'),
(45, 223, 'Utah', 'UTA', 'UT'),
(46, 223, 'Vermont', 'VMT', 'VT'),
(47, 223, 'Virginia', 'VIA', 'VA'),
(48, 223, 'Washington', 'WAS', 'WA'),
(49, 223, 'West Virginia', 'WEV', 'WV'),
(50, 223, 'Wisconsin', 'WIS', 'WI'),
(51, 223, 'Wyoming', 'WYO', 'WY'),
(52, 38, 'Alberta', 'ALB', 'AB'),
(53, 38, 'British Columbia', 'BRC', 'BC'),
(54, 38, 'Manitoba', 'MAB', 'MB'),
(55, 38, 'New Brunswick', 'NEB', 'NB'),
(56, 38, 'Newfoundland and Labrador', 'NFL', 'NL'),
(57, 38, 'Northwest Territories', 'NWT', 'NT'),
(58, 38, 'Nova Scotia', 'NOS', 'NS'),
(59, 38, 'Nunavut', 'NUT', 'NU'),
(60, 38, 'Ontario', 'ONT', 'ON'),
(61, 38, 'Prince Edward Island', 'PEI', 'PE'),
(62, 38, 'Quebec', 'QEC', 'QC'),
(63, 38, 'Saskatchewan', 'SAK', 'SK'),
(64, 38, 'Yukon', 'YUT', 'YT'),
(65, 222, 'England', 'ENG', 'EN'),
(66, 222, 'Northern Ireland', 'NOI', 'NI'),
(67, 222, 'Scotland', 'SCO', 'SD'),
(68, 222, 'Wales', 'WLS', 'WS'),
(69, 13, 'Australian Capital Territory', 'ACT', 'AT'),
(70, 13, 'New South Wales', 'NSW', 'NW'),
(71, 13, 'Northern Territory', 'NOT', 'NT'),
(72, 13, 'Queensland', 'QLD', 'QL'),
(73, 13, 'South Australia', 'SOA', 'SA'),
(74, 13, 'Tasmania', 'TAS', 'TA'),
(75, 13, 'Victoria', 'VIC', 'VI'),
(76, 13, 'Western Australia', 'WEA', 'WA'),
(77, 138, 'Aguascalientes', 'AGS', 'AG'),
(78, 138, 'Baja California Norte', 'BCN', 'BN'),
(79, 138, 'Baja California Sur', 'BCS', 'BS'),
(80, 138, 'Campeche', 'CAM', 'CA'),
(81, 138, 'Chiapas', 'CHI', 'CS'),
(82, 138, 'Chihuahua', 'CHA', 'CH'),
(83, 138, 'Coahuila', 'COA', 'CO'),
(84, 138, 'Colima', 'COL', 'CM'),
(85, 138, 'Distrito Federal', 'DFM', 'DF'),
(86, 138, 'Durango', 'DGO', 'DO'),
(87, 138, 'Guanajuato', 'GTO', 'GO'),
(88, 138, 'Guerrero', 'GRO', 'GU'),
(89, 138, 'Hidalgo', 'HGO', 'HI'),
(90, 138, 'Jalisco', 'JAL', 'JA'),
(91, 138, 'México (Estado de)', 'EDM', 'EM'),
(92, 138, 'Michoacán', 'MCN', 'MI'),
(93, 138, 'Morelos', 'MOR', 'MO'),
(94, 138, 'Nayarit', 'NAY', 'NY'),
(95, 138, 'Nuevo León', 'NUL', 'NL'),
(96, 138, 'Oaxaca', 'OAX', 'OA'),
(97, 138, 'Puebla', 'PUE', 'PU'),
(98, 138, 'Querétaro', 'QRO', 'QU'),
(99, 138, 'Quintana Roo', 'QUR', 'QR'),
(100, 138, 'San Luis Potosí', 'SLP', 'SP'),
(101, 138, 'Sinaloa', 'SIN', 'SI'),
(102, 138, 'Sonora', 'SON', 'SO'),
(103, 138, 'Tabasco', 'TAB', 'TA'),
(104, 138, 'Tamaulipas', 'TAM', 'TM'),
(105, 138, 'Tlaxcala', 'TLX', 'TX'),
(106, 138, 'Veracruz', 'VER', 'VZ'),
(107, 138, 'Yucatán', 'YUC', 'YU'),
(108, 138, 'Zacatecas', 'ZAC', 'ZA'),
(109, 30, 'Acre', 'ACR', 'AC'),
(110, 30, 'Alagoas', 'ALG', 'AL'),
(111, 30, 'Amapá', 'AMP', 'AP'),
(112, 30, 'Amazonas', 'AMZ', 'AM'),
(113, 30, 'Bahía', 'BAH', 'BA'),
(114, 30, 'Ceará', 'CEA', 'CE'),
(115, 30, 'Distrito Federal', 'DFB', 'DF'),
(116, 30, 'Espirito Santo', 'ESS', 'ES'),
(117, 30, 'Goiás', 'GOI', 'GO'),
(118, 30, 'Maranhão', 'MAR', 'MA'),
(119, 30, 'Mato Grosso', 'MAT', 'MT'),
(120, 30, 'Mato Grosso do Sul', 'MGS', 'MS'),
(121, 30, 'Minas Geraís', 'MIG', 'MG'),
(122, 30, 'Paraná', 'PAR', 'PR'),
(123, 30, 'Paraíba', 'PRB', 'PB'),
(124, 30, 'Pará', 'PAB', 'PA'),
(125, 30, 'Pernambuco', 'PER', 'PE'),
(126, 30, 'Piauí', 'PIA', 'PI'),
(127, 30, 'Rio Grande do Norte', 'RGN', 'RN'),
(128, 30, 'Rio Grande do Sul', 'RGS', 'RS'),
(129, 30, 'Rio de Janeiro', 'RDJ', 'RJ'),
(130, 30, 'Rondônia', 'RON', 'RO'),
(131, 30, 'Roraima', 'ROR', 'RR'),
(132, 30, 'Santa Catarina', 'SAC', 'SC'),
(133, 30, 'Sergipe', 'SER', 'SE'),
(134, 30, 'São Paulo', 'SAP', 'SP'),
(135, 30, 'Tocantins', 'TOC', 'TO'),
(136, 44, 'Anhui', 'ANH', '34'),
(137, 44, 'Beijing', 'BEI', '11'),
(138, 44, 'Chongqing', 'CHO', '50'),
(139, 44, 'Fujian', 'FUJ', '35'),
(140, 44, 'Gansu', 'GAN', '62'),
(141, 44, 'Guangdong', 'GUA', '44'),
(142, 44, 'Guangxi Zhuang', 'GUZ', '45'),
(143, 44, 'Guizhou', 'GUI', '52'),
(144, 44, 'Hainan', 'HAI', '46'),
(145, 44, 'Hebei', 'HEB', '13'),
(146, 44, 'Heilongjiang', 'HEI', '23'),
(147, 44, 'Henan', 'HEN', '41'),
(148, 44, 'Hubei', 'HUB', '42'),
(149, 44, 'Hunan', 'HUN', '43'),
(150, 44, 'Jiangsu', 'JIA', '32'),
(151, 44, 'Jiangxi', 'JIX', '36'),
(152, 44, 'Jilin', 'JIL', '22'),
(153, 44, 'Liaoning', 'LIA', '21'),
(154, 44, 'Nei Mongol', 'NML', '15'),
(155, 44, 'Ningxia Hui', 'NIH', '64'),
(156, 44, 'Qinghai', 'QIN', '63'),
(157, 44, 'Shandong', 'SNG', '37'),
(158, 44, 'Shanghai', 'SHH', '31'),
(159, 44, 'Shaanxi', 'SHX', '61'),
(160, 44, 'Sichuan', 'SIC', '51'),
(161, 44, 'Tianjin', 'TIA', '12'),
(162, 44, 'Xinjiang Uygur', 'XIU', '65'),
(163, 44, 'Xizang', 'XIZ', '54'),
(164, 44, 'Yunnan', 'YUN', '53'),
(165, 44, 'Zhejiang', 'ZHE', '33'),
(166, 104, 'Gaza Strip', 'GZS', 'GZ'),
(167, 104, 'West Bank', 'WBK', 'WB'),
(168, 104, 'Other', 'OTH', 'OT'),
(169, 151, 'St. Maarten', 'STM', 'SM'),
(170, 151, 'Bonaire', 'BNR', 'BN'),
(171, 151, 'Curacao', 'CUR', 'CR'),
(172, 175, 'Alba', 'ABA', 'AB'),
(173, 175, 'Arad', 'ARD', 'AR'),
(174, 175, 'Arges', 'ARG', 'AG'),
(175, 175, 'Bacau', 'BAC', 'BC'),
(176, 175, 'Bihor', 'BIH', 'BH'),
(177, 175, 'Bistrita-Nasaud', 'BIS', 'BN'),
(178, 175, 'Botosani', 'BOT', 'BT'),
(179, 175, 'Braila', 'BRL', 'BR'),
(180, 175, 'Brasov', 'BRA', 'BV'),
(181, 175, 'Bucuresti', 'BUC', 'B'),
(182, 175, 'Buzau', 'BUZ', 'BZ'),
(183, 175, 'Calarasi', 'CAL', 'CL'),
(184, 175, 'Caras Severin', 'CRS', 'CS'),
(185, 175, 'Cluj', 'CLJ', 'CJ'),
(186, 175, 'Constanta', 'CST', 'CT'),
(187, 175, 'Covasna', 'COV', 'CV'),
(188, 175, 'Dambovita', 'DAM', 'DB'),
(189, 175, 'Dolj', 'DLJ', 'DJ'),
(190, 175, 'Galati', 'GAL', 'GL'),
(191, 175, 'Giurgiu', 'GIU', 'GR'),
(192, 175, 'Gorj', 'GOR', 'GJ'),
(193, 175, 'Hargita', 'HRG', 'HR'),
(194, 175, 'Hunedoara', 'HUN', 'HD'),
(195, 175, 'Ialomita', 'IAL', 'IL'),
(196, 175, 'Iasi', 'IAS', 'IS'),
(197, 175, 'Ilfov', 'ILF', 'IF'),
(198, 175, 'Maramures', 'MAR', 'MM'),
(199, 175, 'Mehedinti', 'MEH', 'MH'),
(200, 175, 'Mures', 'MUR', 'MS'),
(201, 175, 'Neamt', 'NEM', 'NT'),
(202, 175, 'Olt', 'OLT', 'OT'),
(203, 175, 'Prahova', 'PRA', 'PH'),
(204, 175, 'Salaj', 'SAL', 'SJ'),
(205, 175, 'Satu Mare', 'SAT', 'SM'),
(206, 175, 'Sibiu', 'SIB', 'SB'),
(207, 175, 'Suceava', 'SUC', 'SV'),
(208, 175, 'Teleorman', 'TEL', 'TR'),
(209, 175, 'Timis', 'TIM', 'TM'),
(210, 175, 'Tulcea', 'TUL', 'TL'),
(211, 175, 'Valcea', 'VAL', 'VL'),
(212, 175, 'Vaslui', 'VAS', 'VS'),
(213, 175, 'Vrancea', 'VRA', 'VN'),
(214, 105, 'Agrigento', 'AGR', 'AG'),
(215, 105, 'Alessandria', 'ALE', 'AL'),
(216, 105, 'Ancona', 'ANC', 'AN'),
(217, 105, 'Aosta', 'AOS', 'AO'),
(218, 105, 'Arezzo', 'ARE', 'AR'),
(219, 105, 'Ascoli Piceno', 'API', 'AP'),
(220, 105, 'Asti', 'AST', 'AT'),
(221, 105, 'Avellino', 'AVE', 'AV'),
(222, 105, 'Bari', 'BAR', 'BA'),
(223, 105, 'Belluno', 'BEL', 'BL'),
(224, 105, 'Benevento', 'BEN', 'BN'),
(225, 105, 'Bergamo', 'BEG', 'BG'),
(226, 105, 'Biella', 'BIE', 'BI'),
(227, 105, 'Bologna', 'BOL', 'BO'),
(228, 105, 'Bolzano', 'BOZ', 'BZ'),
(229, 105, 'Brescia', 'BRE', 'BS'),
(230, 105, 'Brindisi', 'BRI', 'BR'),
(231, 105, 'Cagliari', 'CAG', 'CA'),
(232, 105, 'Caltanissetta', 'CAL', 'CL'),
(233, 105, 'Campobasso', 'CBO', 'CB'),
(234, 105, 'Carbonia-Iglesias', 'CAR', 'CI'),
(235, 105, 'Caserta', 'CAS', 'CE'),
(236, 105, 'Catania', 'CAT', 'CT'),
(237, 105, 'Catanzaro', 'CTZ', 'CZ'),
(238, 105, 'Chieti', 'CHI', 'CH'),
(239, 105, 'Como', 'COM', 'CO'),
(240, 105, 'Cosenza', 'COS', 'CS'),
(241, 105, 'Cremona', 'CRE', 'CR'),
(242, 105, 'Crotone', 'CRO', 'KR'),
(243, 105, 'Cuneo', 'CUN', 'CN'),
(244, 105, 'Enna', 'ENN', 'EN'),
(245, 105, 'Ferrara', 'FER', 'FE'),
(246, 105, 'Firenze', 'FIR', 'FI'),
(247, 105, 'Foggia', 'FOG', 'FG'),
(248, 105, 'Forli-Cesena', 'FOC', 'FC'),
(249, 105, 'Frosinone', 'FRO', 'FR'),
(250, 105, 'Genova', 'GEN', 'GE'),
(251, 105, 'Gorizia', 'GOR', 'GO'),
(252, 105, 'Grosseto', 'GRO', 'GR'),
(253, 105, 'Imperia', 'IMP', 'IM'),
(254, 105, 'Isernia', 'ISE', 'IS'),
(255, 105, 'L''Aquila', 'AQU', 'AQ'),
(256, 105, 'La Spezia', 'LAS', 'SP'),
(257, 105, 'Latina', 'LAT', 'LT'),
(258, 105, 'Lecce', 'LEC', 'LE'),
(259, 105, 'Lecco', 'LCC', 'LC'),
(260, 105, 'Livorno', 'LIV', 'LI'),
(261, 105, 'Lodi', 'LOD', 'LO'),
(262, 105, 'Lucca', 'LUC', 'LU'),
(263, 105, 'Macerata', 'MAC', 'MC'),
(264, 105, 'Mantova', 'MAN', 'MN'),
(265, 105, 'Massa-Carrara', 'MAS', 'MS'),
(266, 105, 'Matera', 'MAA', 'MT'),
(267, 105, 'Medio Campidano', 'MED', 'VS'),
(268, 105, 'Messina', 'MES', 'ME'),
(269, 105, 'Milano', 'MIL', 'MI'),
(270, 105, 'Modena', 'MOD', 'MO'),
(271, 105, 'Napoli', 'NAP', 'NA'),
(272, 105, 'Novara', 'NOV', 'NO'),
(273, 105, 'Nuoro', 'NUR', 'NU'),
(274, 105, 'Ogliastra', 'OGL', 'OG'),
(275, 105, 'Olbia-Tempio', 'OLB', 'OT'),
(276, 105, 'Oristano', 'ORI', 'OR'),
(277, 105, 'Padova', 'PDA', 'PD'),
(278, 105, 'Palermo', 'PAL', 'PA'),
(279, 105, 'Parma', 'PAA', 'PR'),
(280, 105, 'Pavia', 'PAV', 'PV'),
(281, 105, 'Perugia', 'PER', 'PG'),
(282, 105, 'Pesaro e Urbino', 'PES', 'PU'),
(283, 105, 'Pescara', 'PSC', 'PE'),
(284, 105, 'Piacenza', 'PIA', 'PC'),
(285, 105, 'Pisa', 'PIS', 'PI'),
(286, 105, 'Pistoia', 'PIT', 'PT'),
(287, 105, 'Pordenone', 'POR', 'PN'),
(288, 105, 'Potenza', 'PTZ', 'PZ'),
(289, 105, 'Prato', 'PRA', 'PO'),
(290, 105, 'Ragusa', 'RAG', 'RG'),
(291, 105, 'Ravenna', 'RAV', 'RA'),
(292, 105, 'Reggio Calabria', 'REG', 'RC'),
(293, 105, 'Reggio Emilia', 'REE', 'RE'),
(294, 105, 'Rieti', 'RIE', 'RI'),
(295, 105, 'Rimini', 'RIM', 'RN'),
(296, 105, 'Roma', 'ROM', 'RM'),
(297, 105, 'Rovigo', 'ROV', 'RO'),
(298, 105, 'Salerno', 'SAL', 'SA'),
(299, 105, 'Sassari', 'SAS', 'SS'),
(300, 105, 'Savona', 'SAV', 'SV'),
(301, 105, 'Siena', 'SIE', 'SI'),
(302, 105, 'Siracusa', 'SIR', 'SR'),
(303, 105, 'Sondrio', 'SOO', 'SO'),
(304, 105, 'Taranto', 'TAR', 'TA'),
(305, 105, 'Teramo', 'TER', 'TE'),
(306, 105, 'Terni', 'TRN', 'TR'),
(307, 105, 'Torino', 'TOR', 'TO'),
(308, 105, 'Trapani', 'TRA', 'TP'),
(309, 105, 'Trento', 'TRE', 'TN'),
(310, 105, 'Treviso', 'TRV', 'TV'),
(311, 105, 'Trieste', 'TRI', 'TS'),
(312, 105, 'Udine', 'UDI', 'UD'),
(313, 105, 'Varese', 'VAR', 'VA'),
(314, 105, 'Venezia', 'VEN', 'VE'),
(315, 105, 'Verbano Cusio Ossola', 'VCO', 'VB'),
(316, 105, 'Vercelli', 'VER', 'VC'),
(317, 105, 'Verona', 'VRN', 'VR'),
(318, 105, 'Vibo Valenzia', 'VIV', 'VV'),
(319, 105, 'Vicenza', 'VII', 'VI'),
(320, 105, 'Viterbo', 'VIT', 'VT'),
(321, 195, 'A Coruña', 'ACO', '15'),
(322, 195, 'Alava', 'ALA', '01'),
(323, 195, 'Albacete', 'ALB', '02'),
(324, 195, 'Alicante', 'ALI', '03'),
(325, 195, 'Almeria', 'ALM', '04'),
(326, 195, 'Asturias', 'AST', '33'),
(327, 195, 'Avila', 'AVI', '05'),
(328, 195, 'Badajoz', 'BAD', '06'),
(329, 195, 'Baleares', 'BAL', '07'),
(330, 195, 'Barcelona', 'BAR', '08'),
(331, 195, 'Burgos', 'BUR', '09'),
(332, 195, 'Caceres', 'CAC', '10'),
(333, 195, 'Cadiz', 'CAD', '11'),
(334, 195, 'Cantabria', 'CAN', '39'),
(335, 195, 'Castellon', 'CAS', '12'),
(336, 195, 'Ceuta', 'CEU', '51'),
(337, 195, 'Ciudad Real', 'CIU', '13'),
(338, 195, 'Cordoba', 'COR', '14'),
(339, 195, 'Cuenca', 'CUE', '16'),
(340, 195, 'Girona', 'GIR', '17'),
(341, 195, 'Granada', 'GRA', '18'),
(342, 195, 'Guadalajara', 'GUA', '19'),
(343, 195, 'Guipuzcoa', 'GUI', '20'),
(344, 195, 'Huelva', 'HUL', '21'),
(345, 195, 'Huesca', 'HUS', '22'),
(346, 195, 'Jaen', 'JAE', '23'),
(347, 195, 'La Rioja', 'LRI', '26'),
(348, 195, 'Las Palmas', 'LPA', '35'),
(349, 195, 'Leon', 'LEO', '24'),
(350, 195, 'Lleida', 'LLE', '25'),
(351, 195, 'Lugo', 'LUG', '27'),
(352, 195, 'Madrid', 'MAD', '28'),
(353, 195, 'Malaga', 'MAL', '29'),
(354, 195, 'Melilla', 'MEL', '52'),
(355, 195, 'Murcia', 'MUR', '30'),
(356, 195, 'Navarra', 'NAV', '31'),
(357, 195, 'Ourense', 'OUR', '32'),
(358, 195, 'Palencia', 'PAL', '34'),
(359, 195, 'Pontevedra', 'PON', '36'),
(360, 195, 'Salamanca', 'SAL', '37'),
(361, 195, 'Santa Cruz de Tenerife', 'SCT', '38'),
(362, 195, 'Segovia', 'SEG', '40'),
(363, 195, 'Sevilla', 'SEV', '41'),
(364, 195, 'Soria', 'SOR', '42'),
(365, 195, 'Tarragona', 'TAR', '43'),
(366, 195, 'Teruel', 'TER', '44'),
(367, 195, 'Toledo', 'TOL', '45'),
(368, 195, 'Valencia', 'VAL', '46'),
(369, 195, 'Valladolid', 'VLL', '47'),
(370, 195, 'Vizcaya', 'VIZ', '48'),
(371, 195, 'Zamora', 'ZAM', '49'),
(372, 195, 'Zaragoza', 'ZAR', '50'),
(373, 11, 'Aragatsotn', 'ARG', 'AG'),
(374, 11, 'Ararat', 'ARR', 'AR'),
(375, 11, 'Armavir', 'ARM', 'AV'),
(376, 11, 'Gegharkunik', 'GEG', 'GR'),
(377, 11, 'Kotayk', 'KOT', 'KT'),
(378, 11, 'Lori', 'LOR', 'LO'),
(379, 11, 'Shirak', 'SHI', 'SH'),
(380, 11, 'Syunik', 'SYU', 'SU'),
(381, 11, 'Tavush', 'TAV', 'TV'),
(382, 11, 'Vayots-Dzor', 'VAD', 'VD'),
(383, 11, 'Yerevan', 'YER', 'ER'),
(384, 99, 'Andaman & Nicobar Islands', 'ANI', 'AI'),
(385, 99, 'Andhra Pradesh', 'AND', 'AN'),
(386, 99, 'Arunachal Pradesh', 'ARU', 'AR'),
(387, 99, 'Assam', 'ASS', 'AS'),
(388, 99, 'Bihar', 'BIH', 'BI'),
(389, 99, 'Chandigarh', 'CHA', 'CA'),
(390, 99, 'Chhatisgarh', 'CHH', 'CH'),
(391, 99, 'Dadra & Nagar Haveli', 'DAD', 'DD'),
(392, 99, 'Daman & Diu', 'DAM', 'DA'),
(393, 99, 'Delhi', 'DEL', 'DE'),
(394, 99, 'Goa', 'GOA', 'GO'),
(395, 99, 'Gujarat', 'GUJ', 'GU'),
(396, 99, 'Haryana', 'HAR', 'HA'),
(397, 99, 'Himachal Pradesh', 'HIM', 'HI'),
(398, 99, 'Jammu & Kashmir', 'JAM', 'JA'),
(399, 99, 'Jharkhand', 'JHA', 'JH'),
(400, 99, 'Karnataka', 'KAR', 'KA'),
(401, 99, 'Kerala', 'KER', 'KE'),
(402, 99, 'Lakshadweep', 'LAK', 'LA'),
(403, 99, 'Madhya Pradesh', 'MAD', 'MD'),
(404, 99, 'Maharashtra', 'MAH', 'MH'),
(405, 99, 'Manipur', 'MAN', 'MN'),
(406, 99, 'Meghalaya', 'MEG', 'ME'),
(407, 99, 'Mizoram', 'MIZ', 'MI'),
(408, 99, 'Nagaland', 'NAG', 'NA'),
(409, 99, 'Orissa', 'ORI', 'OR'),
(410, 99, 'Pondicherry', 'PON', 'PO'),
(411, 99, 'Punjab', 'PUN', 'PU'),
(412, 99, 'Rajasthan', 'RAJ', 'RA'),
(413, 99, 'Sikkim', 'SIK', 'SI'),
(414, 99, 'Tamil Nadu', 'TAM', 'TA'),
(415, 99, 'Tripura', 'TRI', 'TR'),
(416, 99, 'Uttaranchal', 'UAR', 'UA'),
(417, 99, 'Uttar Pradesh', 'UTT', 'UT'),
(418, 99, 'West Bengal', 'WES', 'WE'),
(419, 101, 'Ahmadi va Kohkiluyeh', 'BOK', 'BO'),
(420, 101, 'Ardabil', 'ARD', 'AR'),
(421, 101, 'Azarbayjan-e Gharbi', 'AZG', 'AG'),
(422, 101, 'Azarbayjan-e Sharqi', 'AZS', 'AS'),
(423, 101, 'Bushehr', 'BUS', 'BU'),
(424, 101, 'Chaharmahal va Bakhtiari', 'CMB', 'CM'),
(425, 101, 'Esfahan', 'ESF', 'ES'),
(426, 101, 'Fars', 'FAR', 'FA'),
(427, 101, 'Gilan', 'GIL', 'GI'),
(428, 101, 'Gorgan', 'GOR', 'GO'),
(429, 101, 'Hamadan', 'HAM', 'HA'),
(430, 101, 'Hormozgan', 'HOR', 'HO'),
(431, 101, 'Ilam', 'ILA', 'IL'),
(432, 101, 'Kerman', 'KER', 'KE'),
(433, 101, 'Kermanshah', 'BAK', 'BA'),
(434, 101, 'Khorasan-e Junoubi', 'KHJ', 'KJ'),
(435, 101, 'Khorasan-e Razavi', 'KHR', 'KR'),
(436, 101, 'Khorasan-e Shomali', 'KHS', 'KS'),
(437, 101, 'Khuzestan', 'KHU', 'KH'),
(438, 101, 'Kordestan', 'KOR', 'KO'),
(439, 101, 'Lorestan', 'LOR', 'LO'),
(440, 101, 'Markazi', 'MAR', 'MR'),
(441, 101, 'Mazandaran', 'MAZ', 'MZ'),
(442, 101, 'Qazvin', 'QAS', 'QA'),
(443, 101, 'Qom', 'QOM', 'QO'),
(444, 101, 'Semnan', 'SEM', 'SE'),
(445, 101, 'Sistan va Baluchestan', 'SBA', 'SB'),
(446, 101, 'Tehran', 'TEH', 'TE'),
(447, 101, 'Yazd', 'YAZ', 'YA'),
(448, 101, 'Zanjan', 'ZAN', 'ZA'),
(449, 193, 'Eastern Cape', '', 'EC'),
(450, 193, 'Free State', '', 'FS'),
(451, 193, 'Gauteng', '', 'GT'),
(452, 193, 'KwaZulu Natal', '', 'NL'),
(453, 193, 'Limpopo', '', 'LP'),
(454, 193, 'Mpumalanga', '', 'MP'),
(455, 193, 'Northern Cape', '', 'NC'),
(456, 193, 'North West', '', 'NW'),
(457, 193, 'Western Cape', '', 'WC'),
(458, 81, 'Bayern', '', 'BY'),
(459, 81, 'Berlin', '', 'BE'),
(460, 81, 'Brandenburg', '', 'BB'),
(461, 81, 'Bremen', '', 'HB'),
(462, 81, 'Hamburg', '', 'HH'),
(463, 81, 'Hessen', '', 'HE'),
(464, 81, 'Mecklenburg-Vorpommern', '', 'MV'),
(465, 81, 'Niedersachsen', '', 'NW'),
(466, 81, 'Nordrhein-Westfalen', '', 'WC'),
(467, 81, 'Rheinland-Pfalz', '', 'RP'),
(468, 81, 'Saarland', '', 'SL'),
(469, 81, 'Sachsen', '', 'SN'),
(470, 81, 'Sachsen-Anhalt', '', 'ST'),
(471, 81, 'Schleswig-Holstein', '', 'SH'),
(472, 81, 'Thüringen', '', 'TH'),
(473, 82, 'Baden-Württemberg', NULL, 'BW'),
(474, 21, 'Antwerp', 'VAN', NULL),
(475, 21, 'Limburg', 'VLI', NULL),
(476, 21, 'Flemish Brabant', 'VBR', NULL),
(477, 21, 'East Flanders', 'VOV', NULL),
(478, 21, 'West Flanders', 'VWV', NULL),
(479, 21, 'Hainaut', 'WHT', NULL),
(480, 21, 'Walloon Brabant', 'WBR', NULL),
(481, 21, 'Namur', 'WNA', NULL),
(482, 21, 'Liège', 'WLG', NULL),
(483, 21, 'Luxembourg', 'WLX', NULL),
(484, 21, 'Brussels', 'BRU', NULL);

-- --------------------------------------------------------
-- 
-- Table structure for table `#__mymuse_store`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `scope` varchar(50) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `count` int(11) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `currency` varchar(16) DEFAULT NULL,
  `version` varchar(64) NOT NULL,
  `metadesc` varchar(1024) NOT NULL DEFAULT '',
  `metakey` varchar(1024) NOT NULL DEFAULT '',
  `metadata` varchar(2048) NOT NULL DEFAULT '',
  `my_catid` smallint(11) NOT NULL,
  `state` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `#__mymuse_store`
-- 


INSERT IGNORE INTO `#__mymuse_store` (`id`, `title`, `name`, `alias`, `scope`, `description`, `published`, `checked_out`, `checked_out_time`, `ordering`, `access`, `count`, `params`, `currency`, `version`, `metadesc`, `metakey`, `metadata`, `my_catid`, `state`) VALUES
(1, 'MyMuse Store', '', 'mymuse', 'store', '<p>MyMuse Store Description</p>', 0, 0, '0000-00-00 00:00:00', 1, 0, 0, '{"contact_first_name":"Gord","contact_last_name":"Fisch","contact_title":"Mister","contact_email":"gord@arboreta.ca","phone":"514-481-8524","fax":"514-481-3333","address_1":"5382 King Edward","address_2":"","city":"Montreal","province":"Quebec","country":"CA","zip":"H4A 2K1","twitter_handle":"@MyMuseforJoomla","currency":"CAD","store_thumb_image":"images\\/logo150sq.jpg","my_downloads_enable":"1","my_formats":["mp3"],"my_download_max":"3","my_download_expire":"432000","my_download_enable_status":"C","my_download_dir":"\\/var\\/www\\/html\\/mymusetest35\\/images\\/A_MyMuseDownloads","my_preview_dir":"images\\/A_MyMusePreviews","my_encode_filenames":"0","my_free_downloads":"0","my_play_downloads":"0","my_use_shipping":"0","my_use_stock":"0","my_check_stock":"0","my_add_stock_zero":"0","my_saveorder":"before","my_use_coupons":"0","my_currency_separator":",","my_currency_dec_point":".","my_currency_position":"0","my_registration_redirect":"registration","my_registration":"joomla","my_checkout":"regular","my_profile_key":"mymuse","my_plugin_email":"0","my_cc_webmaster":"1","my_webmaster":"info@joomlamymuse.com","my_webmaster_name":"Joe Strummer","my_continue_shopping":"index.php?option=com_mymuse","my_date_format":"d M Y","my_email_msg":"","my_max_recommended":"4","my_show_original_price":"0","my_add_taxes":"0","my_default_shopper_group_id":"1","my_ownergid":"3","my_owner_percent":"100","my_shop_test":"0","my_debug":"0"}', 'CAD', '3.3.0', '', '', '{"robots":"","author":"","rights":"","xreference":""}', 49, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `#__mymuse_tax_rate`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_tax_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) DEFAULT '0',
  `province` varchar(3) DEFAULT NULL,
  `country` varchar(3) DEFAULT NULL,
  `tax_rate` decimal(10,4) DEFAULT NULL,
  `tax_applies_to` char(1) NOT NULL DEFAULT 'S',
  `tax_name` varchar(32) NOT NULL DEFAULT 'Tax',
  `tax_format` set('RATE','AMOUNT') NOT NULL DEFAULT 'RATE',
  `compounded` char(1) NOT NULL DEFAULT '0',
  `ordering` tinyint(2) DEFAULT '99',
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
-- 
-- Dumping data for table `#__mymuse_tax_rate`
-- 


