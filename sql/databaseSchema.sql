-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2016 at 04:14 PM
-- Server version: 5.7.9
-- PHP Version: 5.5.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `embondsworking`
--

-- --------------------------------------------------------

--
-- Table structure for table `bonds`
--

CREATE TABLE `bonds` (
  `id` int(11) NOT NULL,
  `isin` text NOT NULL,
  `bondname` text NOT NULL,
  `currency` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `currency` text NOT NULL,
  `rate` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `endofday`
--

CREATE TABLE `endofday` (
  `id` int(11) NOT NULL,
  `isin` text NOT NULL,
  `tradingday` date NOT NULL,
  `max_sz_livebid` double NOT NULL,
  `max_sz_liveask` double NOT NULL,
  `max_sz_indicativebid` double NOT NULL,
  `max_sz_indicativeask` double NOT NULL,
  `px_last_live_bid` double NOT NULL,
  `px_last_live_ask` double NOT NULL,
  `px_last_indicative_bid` double NOT NULL,
  `px_last_indicative_ask` double NOT NULL,
  `ts_last_live_bid` double NOT NULL,
  `ts_last_live_ask` double NOT NULL,
  `ts_last_indicative_bid` double NOT NULL,
  `ts_last_indicative_ask` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `orderid` int(11) NOT NULL,
  `username` text,
  `counterparty` text,
  `isin` text,
  `quotetype` text,
  `size` decimal(20,5) DEFAULT NULL,
  `price` decimal(20,10) DEFAULT NULL,
  `side` text,
  `ordertime` datetime DEFAULT NULL,
  `reason` text,
  `endtime` datetime DEFAULT NULL,
  `ordertype` text,
  `timeinforce` text,
  `filltype` text,
  `anonymity` text,
  `logid` int(11) DEFAULT NULL,
  `action` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `orderstemp`
--

CREATE TABLE `orderstemp` (
  `id` int(11) NOT NULL,
  `orderid` int(11) NOT NULL,
  `username` text,
  `counterparty` text,
  `isin` text,
  `quotetype` text,
  `size` decimal(20,5) DEFAULT NULL,
  `price` decimal(20,10) DEFAULT NULL,
  `side` text,
  `ordertime` datetime DEFAULT NULL,
  `reason` text,
  `endtime` datetime DEFAULT NULL,
  `ordertype` text,
  `timeinforce` text,
  `filltype` text,
  `anonymity` text,
  `logid` int(11) DEFAULT NULL,
  `action` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `processed`
--

CREATE TABLE `processed` (
  `id` int(11) NOT NULL,
  `filename` text NOT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT '0',
  `datetime_from` datetime DEFAULT NULL,
  `datetime_to` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `quotesummary`
--

CREATE TABLE `quotesummary` (
  `id` int(11) NOT NULL,
  `isin` text NOT NULL,
  `users` int(11) DEFAULT NULL,
  `sizelivebid` float DEFAULT NULL,
  `sizeliveask` float DEFAULT NULL,
  `sizeindicativebid` float DEFAULT NULL,
  `sizeindicativeask` float DEFAULT NULL,
  `trades` int(11) DEFAULT NULL,
  `lastpxlivebid` float DEFAULT NULL,
  `lastpxliveask` float DEFAULT NULL,
  `lastpxindicativebid` float DEFAULT NULL,
  `lastpxindicativeask` float DEFAULT NULL,
  `tradingday` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `raw`
--

CREATE TABLE `raw` (
  `id` int(11) NOT NULL,
  `line` text NOT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rejects`
--

CREATE TABLE `rejects` (
  `id` int(11) NOT NULL,
  `logid` int(11) DEFAULT NULL,
  `fileid` int(11) DEFAULT NULL,
  `line` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rfq`
--

CREATE TABLE `rfq` (
  `id` int(11) NOT NULL,
  `rfqid` int(11) DEFAULT NULL,
  `action` text NOT NULL,
  `actiontime` datetime NOT NULL,
  `user` text,
  `counterparty` text,
  `size` double DEFAULT NULL,
  `bidprice` double DEFAULT NULL,
  `askprice` double DEFAULT NULL,
  `tradeprice` double DEFAULT NULL,
  `rfqtype` text,
  `content` text,
  `logid` int(11) DEFAULT NULL,
  `isin` text,
  `responders` text,
  `responderuser` text,
  `respondercounterparty` text,
  `tradedirection` text,
  `giveruser` text,
  `givercounterparty` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `rfqsec`
--

CREATE TABLE `rfqsec` (
  `id` int(11) NOT NULL,
  `rfqid` int(11) DEFAULT NULL,
  `action` text NOT NULL,
  `actiontime` datetime NOT NULL,
  `user` text,
  `counterparty` text,
  `size` double DEFAULT NULL,
  `bidprice` double DEFAULT NULL,
  `askprice` double DEFAULT NULL,
  `tradeprice` double DEFAULT NULL,
  `rfqtype` text,
  `content` text,
  `logid` int(11) DEFAULT NULL,
  `isin` text,
  `responders` text,
  `responderuser` text,
  `respondercounterparty` text,
  `tradedirection` text,
  `giveruser` text,
  `givercounterparty` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `rfqu`
--

CREATE TABLE `rfqu` (
  `id` int(11) NOT NULL,
  `rfqid` int(11) DEFAULT NULL,
  `action` text NOT NULL,
  `actiontime` datetime NOT NULL,
  `user` text,
  `counterparty` text,
  `size` double DEFAULT NULL,
  `bidprice` double DEFAULT NULL,
  `askprice` double DEFAULT NULL,
  `tradeprice` double DEFAULT NULL,
  `rfqtype` text,
  `content` text,
  `logid` int(11) DEFAULT NULL,
  `isin` text,
  `responders` text,
  `responderuser` text,
  `respondercounterparty` text,
  `tradedirection` text,
  `giveruser` text,
  `givercounterparty` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `temp`
--

CREATE TABLE `temp` (
  `logid` int(11) DEFAULT NULL,
  `logtime` datetime DEFAULT NULL,
  `logothertime` datetime DEFAULT NULL,
  `type` text,
  `user` text,
  `cpty` text,
  `action` text,
  `content` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `trades`
--

CREATE TABLE `trades` (
  `id` int(11) NOT NULL,
  `tradeid` int(11) NOT NULL,
  `buyer` text,
  `seller` text,
  `giver` text,
  `taker` text,
  `isin` text NOT NULL,
  `price` decimal(20,10) NOT NULL,
  `quotetype` text NOT NULL,
  `size` text NOT NULL,
  `currency` text NOT NULL,
  `tradetime` datetime NOT NULL,
  `logid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bonds`
--
ALTER TABLE `bonds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `endofday`
--
ALTER TABLE `endofday`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orderstemp`
--
ALTER TABLE `orderstemp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `processed`
--
ALTER TABLE `processed`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quotesummary`
--
ALTER TABLE `quotesummary`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `raw`
--
ALTER TABLE `raw`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rejects`
--
ALTER TABLE `rejects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rfq`
--
ALTER TABLE `rfq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rfqsec`
--
ALTER TABLE `rfqsec`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rfqu`
--
ALTER TABLE `rfqu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trades`
--
ALTER TABLE `trades`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bonds`
--
ALTER TABLE `bonds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=727;
--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `endofday`
--
ALTER TABLE `endofday`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32768;
--
-- AUTO_INCREMENT for table `orderstemp`
--
ALTER TABLE `orderstemp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `processed`
--
ALTER TABLE `processed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `quotesummary`
--
ALTER TABLE `quotesummary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `raw`
--
ALTER TABLE `raw`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rejects`
--
ALTER TABLE `rejects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rfq`
--
ALTER TABLE `rfq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `rfqsec`
--
ALTER TABLE `rfqsec`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rfqu`
--
ALTER TABLE `rfqu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `trades`
--
ALTER TABLE `trades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
