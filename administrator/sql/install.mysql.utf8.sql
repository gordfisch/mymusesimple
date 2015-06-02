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
  `plugin` tinytext NOT NULL,
  `country_name` varchar(64) DEFAULT NULL,
  `country_3_code` char(3) DEFAULT NULL,
  `country_2_code` char(2) DEFAULT NULL,
  `bloc` varchar(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_country_name` (`country_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Country records' AUTO_INCREMENT=245 ;

--
-- Dumping data for table `vl6xc_mymuse_country`
--

INSERT INTO `#__mymuse_country` (`id`, `plugin`, `country_name`, `country_3_code`, `country_2_code`, `bloc`) VALUES
(1, '', 'Afghanistan', 'AFG', 'AF', ''),
(2, '', 'Albania', 'ALB', 'AL', ''),
(3, '', 'Algeria', 'DZA', 'DZ', ''),
(4, '', 'American Samoa', 'ASM', 'AS', ''),
(5, '', 'Andorra', 'AND', 'AD', ''),
(6, '', 'Angola', 'AGO', 'AO', ''),
(7, '', 'Anguilla', 'AIA', 'AI', ''),
(8, '', 'Antarctica', 'ATA', 'AQ', ''),
(9, '', 'Antigua and Barbuda', 'ATG', 'AG', ''),
(10, '', 'Argentina', 'ARG', 'AR', ''),
(11, '', 'Armenia', 'ARM', 'AM', ''),
(12, '', 'Aruba', 'ABW', 'AW', ''),
(13, 'paypal', 'Australia', 'AUS', 'AU', ''),
(14, 'paypal', 'Austria', 'AUT', 'AT', 'EU'),
(15, '', 'Azerbaijan', 'AZE', 'AZ', ''),
(16, '', 'Bahamas', 'BHS', 'BS', ''),
(17, '', 'Bahrain', 'BHR', 'BH', ''),
(18, '', 'Bangladesh', 'BGD', 'BD', ''),
(19, '', 'Barbados', 'BRB', 'BB', ''),
(20, '', 'Belarus', 'BLR', 'BY', ''),
(21, 'paypal', 'Belgium', 'BEL', 'BE', 'EU'),
(22, '', 'Belize', 'BLZ', 'BZ', ''),
(23, '', 'Benin', 'BEN', 'BJ', ''),
(24, '', 'Bermuda', 'BMU', 'BM', ''),
(25, '', 'Bhutan', 'BTN', 'BT', ''),
(26, '', 'Bolivia', 'BOL', 'BO', ''),
(27, '', 'Bosnia and Herzegowina', 'BIH', 'BA', ''),
(28, '', 'Botswana', 'BWA', 'BW', ''),
(29, '', 'Bouvet Island', 'BVT', 'BV', ''),
(30, '', 'Brazil', 'BRA', 'BR', ''),
(31, '', 'British Indian Ocean Territory', 'IOT', 'IO', ''),
(32, '', 'Brunei Darussalam', 'BRN', 'BN', ''),
(33, 'paypal', 'Bulgaria', 'BGR', 'BG', 'EU'),
(34, '', 'Burkina Faso', 'BFA', 'BF', ''),
(35, '', 'Burundi', 'BDI', 'BI', ''),
(36, '', 'Cambodia', 'KHM', 'KH', ''),
(37, '', 'Cameroon', 'CMR', 'CM', ''),
(38, 'paypal', 'Canada', 'CAN', 'CA', ''),
(39, '', 'Cape Verde', 'CPV', 'CV', ''),
(40, '', 'Cayman Islands', 'CYM', 'KY', ''),
(41, '', 'Central African Republic', 'CAF', 'CF', ''),
(42, '', 'Chad', 'TCD', 'TD', ''),
(43, '', 'Chile', 'CHL', 'CL', ''),
(44, '', 'China', 'CHN', 'CN', ''),
(45, '', 'Christmas Island', 'CXR', 'CX', ''),
(46, '', 'Cocos (Keeling) Islands', 'CCK', 'CC', ''),
(47, '', 'Colombia', 'COL', 'CO', ''),
(48, '', 'Comoros', 'COM', 'KM', ''),
(49, '', 'Congo', 'COG', 'CG', ''),
(50, '', 'Cook Islands', 'COK', 'CK', ''),
(51, '', 'Costa Rica', 'CRI', 'CR', ''),
(52, '', 'Cote D''Ivoire', 'CIV', 'CI', ''),
(53, 'paypal', 'Croatia', 'HRV', 'HR', 'EU'),
(54, '', 'Cuba', 'CUB', 'CU', ''),
(55, 'paypal', 'Cyprus', 'CYP', 'CY', 'EU'),
(56, 'paypal', 'Czech Republic', 'CZE', 'CZ', 'EU'),
(57, 'paypal', 'Denmark', 'DNK', 'DK', 'EU'),
(58, '', 'Djibouti', 'DJI', 'DJ', ''),
(59, '', 'Dominica', 'DMA', 'DM', ''),
(60, '', 'Dominican Republic', 'DOM', 'DO', ''),
(61, '', 'East Timor', 'TMP', 'TP', ''),
(62, '', 'Ecuador', 'ECU', 'EC', ''),
(63, '', 'Egypt', 'EGY', 'EG', ''),
(64, '', 'El Salvador', 'SLV', 'SV', ''),
(65, '', 'Equatorial Guinea', 'GNQ', 'GQ', ''),
(66, '', 'Eritrea', 'ERI', 'ER', ''),
(67, 'paypal', 'Estonia', 'EST', 'EE', 'EU'),
(68, '', 'Ethiopia', 'ETH', 'ET', ''),
(69, '', 'Falkland Islands (Malvinas)', 'FLK', 'FK', ''),
(70, '', 'Faroe Islands', 'FRO', 'FO', ''),
(71, '', 'Fiji', 'FJI', 'FJ', ''),
(72, 'paypal', 'Finland', 'FIN', 'FI', 'EU'),
(73, 'paypal', 'France', 'FRA', 'FR', 'EU'),
(74, '', 'France, Metropolitan', 'FXX', 'FX', ''),
(75, '', 'French Guiana', 'GUF', 'GF', ''),
(76, '', 'French Polynesia', 'PYF', 'PF', ''),
(77, '', 'French Southern Territories', 'ATF', 'TF', ''),
(78, '', 'Gabon', 'GAB', 'GA', ''),
(79, '', 'Gambia', 'GMB', 'GM', ''),
(80, '', 'Georgia', 'GEO', 'GE', ''),
(81, 'paypal', 'Germany', 'DEU', 'DE', 'EU'),
(82, '', 'Ghana', 'GHA', 'GH', ''),
(83, '', 'Gibraltar', 'GIB', 'GI', ''),
(84, 'paypal', 'Greece', 'GRC', 'GR', 'EU'),
(85, '', 'Greenland', 'GRL', 'GL', ''),
(86, '', 'Grenada', 'GRD', 'GD', ''),
(87, '', 'Guadeloupe', 'GLP', 'GP', ''),
(88, '', 'Guam', 'GUM', 'GU', ''),
(89, '', 'Guatemala', 'GTM', 'GT', ''),
(90, '', 'Guinea', 'GIN', 'GN', ''),
(91, '', 'Guinea-bissau', 'GNB', 'GW', ''),
(92, '', 'Guyana', 'GUY', 'GY', ''),
(93, '', 'Haiti', 'HTI', 'HT', ''),
(94, '', 'Heard and Mc Donald Islands', 'HMD', 'HM', ''),
(95, '', 'Honduras', 'HND', 'HN', ''),
(96, 'paypal', 'Hong Kong', 'HKG', 'HK', ''),
(97, 'paypal', 'Hungary', 'HUN', 'HU', 'EU'),
(98, '', 'Iceland', 'ISL', 'IS', ''),
(99, '', 'India', 'IND', 'IN', ''),
(100, '', 'Indonesia', 'IDN', 'ID', ''),
(101, '', 'Iran (Islamic Republic of)', 'IRN', 'IR', ''),
(102, '', 'Iraq', 'IRQ', 'IQ', ''),
(103, 'paypal', 'Ireland', 'IRL', 'IE', 'EU'),
(104, 'paypal', 'Israel', 'ISR', 'IL', ''),
(105, 'paypal', 'Italy', 'ITA', 'IT', 'EU'),
(106, '', 'Jamaica', 'JAM', 'JM', ''),
(107, 'paypal', 'Japan', 'JPN', 'JP', ''),
(108, '', 'Jordan', 'JOR', 'JO', ''),
(109, '', 'Kazakhstan', 'KAZ', 'KZ', ''),
(110, 'paypal', 'Kenya', 'KEN', 'KE', ''),
(111, '', 'Kiribati', 'KIR', 'KI', ''),
(112, '', 'Korea, DPR', 'PRK', 'KP', ''),
(113, '', 'Korea, Republic of', 'KOR', 'KR', ''),
(114, '', 'Kuwait', 'KWT', 'KW', ''),
(115, '', 'Kyrgyzstan', 'KGZ', 'KG', ''),
(116, '', 'Lao PDR', 'LAO', 'LA', ''),
(117, 'paypal', 'Latvia', 'LVA', 'LV', 'EU'),
(118, '', 'Lebanon', 'LBN', 'LB', ''),
(119, '', 'Lesotho', 'LSO', 'LS', ''),
(120, '', 'Liberia', 'LBR', 'LR', ''),
(121, '', 'Libyan Arab Jamahiriya', 'LBY', 'LY', ''),
(122, 'paypal', 'Liechtenstein', 'LIE', 'LI', ''),
(123, 'paypal', 'Lithuania', 'LTU', 'LT', 'EU'),
(124, 'paypal', 'Luxembourg', 'LUX', 'LU', 'EU'),
(125, '', 'Macau', 'MAC', 'MO', ''),
(126, '', 'Macedonia', 'MKD', 'MK', ''),
(127, '', 'Madagascar', 'MDG', 'MG', ''),
(128, '', 'Malawi', 'MWI', 'MW', ''),
(129, '', 'Malaysia', 'MYS', 'MY', ''),
(130, '', 'Maldives', 'MDV', 'MV', ''),
(131, '', 'Mali', 'MLI', 'ML', ''),
(132, 'paypal', 'Malta', 'MLT', 'MT', 'EU'),
(133, '', 'Marshall Islands', 'MHL', 'MH', ''),
(134, '', 'Martinique', 'MTQ', 'MQ', ''),
(135, '', 'Mauritania', 'MRT', 'MR', ''),
(136, '', 'Mauritius', 'MUS', 'MU', ''),
(137, '', 'Mayotte', 'MYT', 'YT', ''),
(138, 'paypal', 'Mexico', 'MEX', 'MX', ''),
(139, '', 'Micronesia, Federated States of', 'FSM', 'FM', ''),
(140, '', 'Moldova, Republic of', 'MDA', 'MD', ''),
(141, '', 'Monaco', 'MCO', 'MC', ''),
(142, '', 'Mongolia', 'MNG', 'MN', ''),
(143, '', 'Montserrat', 'MSR', 'MS', ''),
(144, '', 'Morocco', 'MAR', 'MA', ''),
(145, '', 'Mozambique', 'MOZ', 'MZ', ''),
(146, '', 'Myanmar', 'MMR', 'MM', ''),
(147, '', 'Namibia', 'NAM', 'NA', ''),
(148, '', 'Nauru', 'NRU', 'NR', ''),
(149, '', 'Nepal', 'NPL', 'NP', ''),
(150, 'paypal', 'Netherlands', 'NLD', 'NL', 'EU'),
(151, '', 'Netherlands Antilles', 'ANT', 'AN', ''),
(152, '', 'New Caledonia', 'NCL', 'NC', ''),
(153, 'paypal', 'New Zealand', 'NZL', 'NZ', ''),
(154, '', 'Nicaragua', 'NIC', 'NI', ''),
(155, '', 'Niger', 'NER', 'NE', ''),
(156, '', 'Nigeria', 'NGA', 'NG', ''),
(157, '', 'Niue', 'NIU', 'NU', ''),
(158, '', 'Norfolk Island', 'NFK', 'NF', ''),
(159, '', 'Northern Mariana Islands', 'MNP', 'MP', ''),
(160, 'paypal', 'Norway', 'NOR', 'NO', ''),
(161, '', 'Oman', 'OMN', 'OM', ''),
(162, '', 'Pakistan', 'PAK', 'PK', ''),
(163, '', 'Palau', 'PLW', 'PW', ''),
(164, '', 'Panama', 'PAN', 'PA', ''),
(165, '', 'Papua New Guinea', 'PNG', 'PG', ''),
(166, '', 'Paraguay', 'PRY', 'PY', ''),
(167, '', 'Peru', 'PER', 'PE', ''),
(168, '', 'Philippines', 'PHL', 'PH', ''),
(169, '', 'Pitcairn', 'PCN', 'PN', ''),
(170, 'paypal', 'Poland', 'POL', 'PL', 'EU'),
(171, 'paypal', 'Portugal', 'PRT', 'PT', 'EU'),
(172, '', 'Puerto Rico', 'PRI', 'PR', ''),
(173, '', 'Qatar', 'QAT', 'QA', ''),
(174, '', 'Reunion', 'REU', 'RE', ''),
(175, 'paypal', 'Romania', 'ROM', 'RO', 'EU'),
(176, '', 'Russian Federation', 'RUS', 'RU', ''),
(177, '', 'Rwanda', 'RWA', 'RW', ''),
(178, '', 'Saint Kitts and Nevis', 'KNA', 'KN', ''),
(179, '', 'Saint Lucia', 'LCA', 'LC', ''),
(180, '', 'Saint Vincent and the Grenadines', 'VCT', 'VC', ''),
(181, '', 'Samoa', 'WSM', 'WS', ''),
(182, '', 'San Marino', 'SMR', 'SM', ''),
(183, '', 'Sao Tome and Principe', 'STP', 'ST', ''),
(184, '', 'Saudi Arabia', 'SAU', 'SA', ''),
(185, '', 'Senegal', 'SEN', 'SN', ''),
(186, '', 'Seychelles', 'SYC', 'SC', ''),
(187, '', 'Sierra Leone', 'SLE', 'SL', ''),
(188, 'paypal', 'Singapore', 'SGP', 'SG', ''),
(189, 'paypal', 'Slovakia (Slovak Republic)', 'SVK', 'SK', 'EU'),
(190, 'paypal', 'Slovenia', 'SVN', 'SI', 'EU'),
(191, '', 'Solomon Islands', 'SLB', 'SB', ''),
(192, '', 'Somalia', 'SOM', 'SO', ''),
(193, 'paypal', 'South Africa', 'ZAF', 'ZA', ''),
(194, '', 'S. Georgia and the S. Sandwich Islands', 'SGS', 'GS', ''),
(195, 'paypal', 'Spain', 'ESP', 'ES', 'EU'),
(196, '', 'Sri Lanka', 'LKA', 'LK', ''),
(197, '', 'St. Helena', 'SHN', 'SH', ''),
(198, '', 'St. Pierre and Miquelon', 'SPM', 'PM', ''),
(199, '', 'Sudan', 'SDN', 'SD', ''),
(200, '', 'Suriname', 'SUR', 'SR', ''),
(201, '', 'Svalbard and Jan Mayen Islands', 'SJM', 'SJ', ''),
(202, '', 'Swaziland', 'SWZ', 'SZ', ''),
(203, 'paypal', 'Sweden', 'SWE', 'SE', 'EU'),
(204, 'paypal', 'Switzerland', 'CHE', 'CH', ''),
(205, '', 'Syrian Arab Republic', 'SYR', 'SY', ''),
(206, '', 'Taiwan', 'TWN', 'TW', ''),
(207, '', 'Tajikistan', 'TJK', 'TJ', ''),
(208, '', 'Tanzania, United Republic of', 'TZA', 'TZ', ''),
(209, '', 'Thailand', 'THA', 'TH', ''),
(210, '', 'Togo', 'TGO', 'TG', ''),
(211, '', 'Tokelau', 'TKL', 'TK', ''),
(212, '', 'Tonga', 'TON', 'TO', ''),
(213, '', 'Trinidad and Tobago', 'TTO', 'TT', ''),
(214, '', 'Tunisia', 'TUN', 'TN', ''),
(215, '', 'Turkey', 'TUR', 'TR', ''),
(216, '', 'Turkmenistan', 'TKM', 'TM', ''),
(217, '', 'Turks and Caicos Islands', 'TCA', 'TC', ''),
(218, '', 'Tuvalu', 'TUV', 'TV', ''),
(219, '', 'Uganda', 'UGA', 'UG', ''),
(220, '', 'Ukraine', 'UKR', 'UA', ''),
(221, '', 'United Arab Emirates', 'ARE', 'AE', ''),
(222, 'paypal', 'United Kingdom', 'GBR', 'GB', 'EU'),
(223, 'paypal', 'United States', 'USA', 'US', ''),
(224, '', 'United States Minor Outlying Islands', 'UMI', 'UM', ''),
(225, '', 'Uruguay', 'URY', 'UY', ''),
(226, '', 'Uzbekistan', 'UZB', 'UZ', ''),
(227, '', 'Vanuatu', 'VUT', 'VU', ''),
(228, '', 'Vatican City State (Holy See)', 'VAT', 'VA', ''),
(229, '', 'Venezuela', 'VEN', 'VE', ''),
(230, '', 'Viet Nam', 'VNM', 'VN', ''),
(231, '', 'Virgin Islands (British)', 'VGB', 'VG', ''),
(232, '', 'Virgin Islands (U.S.)', 'VIR', 'VI', ''),
(233, '', 'Wallis and Futuna Islands', 'WLF', 'WF', ''),
(234, '', 'Western Sahara', 'ESH', 'EH', ''),
(235, '', 'Yemen', 'YEM', 'YE', ''),
(236, '', 'Yugoslavia', 'YUG', 'YU', ''),
(237, '', 'The Democratic Republic of Congo', 'DRC', 'DC', ''),
(238, '', 'Zambia', 'ZMB', 'ZM', ''),
(239, '', 'Zimbabwe', 'ZWE', 'ZW', ''),
(240, '', 'East Timor', 'XET', 'XE', ''),
(241, '', 'Jersey', 'XJE', 'XJ', ''),
(242, '', 'St. Barthelemy', 'XSB', 'XB', ''),
(243, '', 'St. Eustatius', 'XSE', 'XU', ''),
(244, '', 'Canary Islands', 'XCA', 'XC', '');
-- --------------------------------------------------------

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
-- Table structure for table `#__mymuse_dowmloads`
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
  `notes` varchar(255) DEFAULT NULL,
  `reservation_fee` float(10,2) NOT NULL DEFAULT '0.00',
  `non_res_total` float(10,2) NOT NULL DEFAULT '0.00',
  `pay_now` float(10,2) NOT NULL DEFAULT '0.00',
  `extra` text NOT NULL,
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
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

-- 
-- Table structure for table `#__mymuse_product`
-- 

CREATE TABLE IF NOT EXISTS `#__mymuse_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `title_alias` varchar(255) NOT NULL DEFAULT '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `catid` int(11) NOT NULL,
  `price` decimal(10,4) default NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `list_image` varchar(255) NOT NULL,
  `detail_image` varchar(255) NOT NULL,
  `urls` text NOT NULL,
  `attribs` text NOT NULL,
  `version` int(11) unsigned NOT NULL DEFAULT '1',
  `parentid` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` varchar(1024) NOT NULL DEFAULT '',
  `metadesc` varchar(1024) NOT NULL DEFAULT '',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `metadata` varchar(2048) NOT NULL DEFAULT '',
  `product_physical` tinyint(1) NOT NULL DEFAULT '0',
  `product_downloadable` tinyint(1) NOT NULL DEFAULT '0',
  `product_allfiles` tinyint(1) NOT NULL DEFAULT '0',
  `product_sku` varchar(64) NOT NULL DEFAULT '',
  `product_made_date` date DEFAULT '0000-00-00',
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
  `file_name` varchar(255) NOT NULL,
  `file_downloads` int(11) NOT NULL DEFAULT '0',
  `file_contents` longblob NOT NULL,
  `file_type` varchar(32) NOT NULL,
  `file_preview` varchar(255) NOT NULL DEFAULT '',
  `file_preview_2` varchar(255) NOT NULL DEFAULT '',
  `file_preview_3` varchar(255) NOT NULL DEFAULT '',
  `file_preview_4` varchar(255) NOT NULL DEFAULT '',
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if product is featured.',
  `language` char(7) NOT NULL COMMENT 'The language code for the article.',
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
(1, 'MyMuse Store', '', 'mymuse', 'store', '<p>MyMuse Store Description</p>', 0, 0, '0000-00-00 00:00:00', 1, 0, 0, '{"contact_first_name":"Gord","contact_last_name":"Fisch","contact_title":"Mister","contact_email":"gord@arboreta.ca","phone":"514-481-8524","fax":"514-481-3333","address_1":"5380 King Edward","address_2":"","city":"Montreal","province":"Quebec","country":"CA","zip":"H4A 2K1","currency":"CAD","store_thumb_image":"images\\/logo150sq.jpg","my_downloads_enable":"1","my_download_max":"3","my_download_expire":"432000","my_download_enable_status":"C","my_download_dir":"\\/var\\/www\\/html\\/mymusetest35\\/images\\/A_MyMuseDownloads","my_preview_dir":"images\\/A_MyMusePreviews","my_encode_filenames":"0","my_free_downloads":"0","my_play_downloads":"0","my_use_shipping":"0","my_use_stock":"0","my_check_stock":"0","my_add_stock_zero":"0","my_saveorder":"before","my_use_coupons":"0","my_currency_separator":",","my_currency_dec_point":".","my_currency_position":"0","my_registration_redirect":"registration","my_registration":"joomla","my_checkout":"regular","my_profile_key":"mymuse","my_plugin_email":"0","my_cc_webmaster":"1","my_webmaster":"info@joomlamymuse.com","my_webmaster_name":"Joe Strummer","my_continue_shopping":"index.php?option=com_mymuse","my_date_format":"d M Y","my_email_msg":"","my_show_original_price":"0","my_add_taxes":"0","my_default_shopper_group_id":"1","my_ownergid":"3","my_owner_percent":"100","my_shop_test":"0","my_debug":"0"}', 'CAD', '3.2.7', '', '', '{"robots":"","author":"","rights":"","xreference":""}', 49, 1);

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


