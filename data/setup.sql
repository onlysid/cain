/* DB Setup */
CREATE DATABASE IF NOT EXISTS `patientresult_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `patientresult_db`;

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `sender` varchar(256) NOT NULL DEFAULT '""',
  `sequenceNumber` varchar(256) NOT NULL DEFAULT '""',
  `version` varchar(256) NOT NULL DEFAULT '""',
  `assayType` varchar(256) NOT NULL DEFAULT '',
  `assaySubType` varchar(256) NOT NULL DEFAULT '',
  `site` varchar(256) NOT NULL DEFAULT '""',
  `firstName` varchar(256) NOT NULL DEFAULT '""',
  `lastName` varchar(256) NOT NULL DEFAULT '""',
  `dob` varchar(256) NOT NULL DEFAULT '""',
  `hospitalId` varchar(256) NOT NULL DEFAULT '""',
  `nhsNumber` varchar(256) NOT NULL,
  `timestamp` varchar(256) NOT NULL DEFAULT '""',
  `testcompletetimestamp` varchar(256) NOT NULL DEFAULT '""',
  `clinicId` varchar(256) NOT NULL DEFAULT '""',
  `operatorId` varchar(256) NOT NULL DEFAULT '""',
  `moduleSerialNumber` varchar(256) NOT NULL DEFAULT '""',
  `patientId` varchar(256) NOT NULL DEFAULT '""',
  `patientAge` varchar(256) NOT NULL DEFAULT '""',
  `patientSex` varchar(256) NOT NULL DEFAULT '""',
  `sampleid` varchar(256) NOT NULL DEFAULT '""',
  `trackingCode` varchar(256) NOT NULL DEFAULT '""',
  `product` varchar(256) NOT NULL DEFAULT '""',
  `result` varchar(256) NOT NULL DEFAULT '""',
  `testPurpose` varchar(256) NOT NULL DEFAULT '""',
  `abortErrorCode` varchar(255) NOT NULL DEFAULT '""',
  `patientLocation` varchar(255) NOT NULL DEFAULT '""',
  `reserve1` varchar(255) NOT NULL DEFAULT '""',
  `reserve2` varchar(255) NOT NULL DEFAULT '""',
  `sampleCollected` varchar(255) NOT NULL DEFAULT '""',
  `sampleReceived` varchar(255) NOT NULL DEFAULT '""',
  `flag` int(11) DEFAULT NULL,
  `post_timestamp` bigint(20) DEFAULT NULL,
  `assayStepNumber` varchar(256) NOT NULL DEFAULT '""',
  `cameraReadings` varchar(256) NOT NULL DEFAULT '""',
  `bits` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `flags` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `operator_id` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `user_type` tinyint(4) DEFAULT 1,
  `last_active` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `operator_id` (`operator_id`);

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


COMMIT;