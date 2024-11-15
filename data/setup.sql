/* DB Setup */
CREATE DATABASE IF NOT EXISTS `patientresult_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `patientresult_db`;

--
-- Table structure for table `instruments`
--

CREATE TABLE `instruments` (
  `id` int(11) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `module_version` varchar(100) DEFAULT NULL,
  `front_panel_id` varchar(100) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `current_assay` varchar(255) DEFAULT NULL,
  `assay_start_time` bigint(20) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `device_error` varchar(255) DEFAULT NULL,
  `tablet_version` varchar(100) DEFAULT NULL,
  `enforcement` tinyint(4) DEFAULT NULL,
  `last_connected` bigint(20) DEFAULT NULL,
  `locked` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instrument_qc_results`
--

CREATE TABLE `instrument_qc_results` (
  `id` int(11) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `result` tinyint(4) NOT NULL,
  `instrument` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `result_counter` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instrument_test_types`
--

CREATE TABLE `instrument_test_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `time_intervals` int(11) DEFAULT NULL,
  `result_intervals` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lots`
--

CREATE TABLE `lots` (
  `id` int(11) NOT NULL,
  `lot_number` varchar(100) DEFAULT NULL,
  `sub_lot_number` varchar(100) DEFAULT NULL,
  `assay_type` varchar(256) DEFAULT NULL,
  `assay_sub_type` varchar(256) DEFAULT NULL,
  `delivery_date` timestamp NULL DEFAULT NULL,
  `expiration_date` timestamp NULL DEFAULT NULL,
  `qc_pass` tinyint(4) DEFAULT 0,
  `last_updated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lots_qc_results`
--

CREATE TABLE `lots_qc_results` (
  `id` int(11) NOT NULL,
  `lot` int(11) DEFAULT NULL,
  `timestamp` bigint(20) DEFAULT NULL,
  `operator_id` varchar(50) DEFAULT NULL,
  `qc_result` tinyint(4) DEFAULT NULL,
  `reference` text DEFAULT NULL,
  `test_result` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `process_data`
--

CREATE TABLE `process_data` (
  `id` int(11) NOT NULL,
  `key` varchar(50) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `process_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `process_queue`
--

CREATE TABLE `process_queue` (
  `id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

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
  `abortErrorCode` varchar(255) NOT NULL,
  `patientLocation` varchar(255) NOT NULL,
  `reserve1` varchar(255) NOT NULL,
  `reserve2` varchar(255) NOT NULL,
  `sampleCollected` varchar(255) NOT NULL,
  `sampleReceived` varchar(255) NOT NULL,
  `flag` int(11) DEFAULT NULL,
  `post_timestamp` bigint(20) DEFAULT NULL,
  `assayStepNumber` varchar(256) NOT NULL DEFAULT '""',
  `cameraReadings` varchar(256) NOT NULL DEFAULT '""',
  `bits` int(11) NOT NULL DEFAULT 0,
  `lot_number` varchar(100) DEFAULT NULL,
  `summary` varchar(100) DEFAULT NULL
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
-- Table structure for table `tablets`
--

CREATE TABLE `tablets` (
  `id` int(11) NOT NULL,
  `tablet_id` varchar(100) DEFAULT NULL,
  `app_version` varchar(100) DEFAULT NULL
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

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `instruments`
--
ALTER TABLE `instruments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`);

--
-- Indexes for table `instrument_qc_results`
--
ALTER TABLE `instrument_qc_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instrument` (`instrument`),
  ADD KEY `user` (`user`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `instrument_test_types`
--
ALTER TABLE `instrument_test_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `lots`
--
ALTER TABLE `lots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lot_number` (`lot_number`);

--
-- Indexes for table `lots_qc_results`
--
ALTER TABLE `lots_qc_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lot` (`lot`),
  ADD KEY `test_result` (`test_result`);

--
-- Indexes for table `process_data`
--
ALTER TABLE `process_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `process_id` (`process_id`);

--
-- Indexes for table `process_queue`
--
ALTER TABLE `process_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lot_number` (`lot_number`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `tablets`
--
ALTER TABLE `tablets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tablet_id` (`tablet_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `operator_id` (`operator_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `instruments`
--
ALTER TABLE `instruments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instrument_qc_results`
--
ALTER TABLE `instrument_qc_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instrument_test_types`
--
ALTER TABLE `instrument_test_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lots`
--
ALTER TABLE `lots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lots_qc_results`
--
ALTER TABLE `lots_qc_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `process_data`
--
ALTER TABLE `process_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `process_queue`
--
ALTER TABLE `process_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `tablets`
--
ALTER TABLE `tablets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `instrument_qc_results`
--
ALTER TABLE `instrument_qc_results`
  ADD CONSTRAINT `instrument_qc_results_ibfk_1` FOREIGN KEY (`instrument`) REFERENCES `instruments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `instrument_qc_results_ibfk_2` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `instrument_qc_results_ibfk_3` FOREIGN KEY (`type`) REFERENCES `instrument_test_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lots_qc_results`
--
ALTER TABLE `lots_qc_results`
  ADD CONSTRAINT `lots_qc_results_ibfk_1` FOREIGN KEY (`lot`) REFERENCES `lots` (`id`),
  ADD CONSTRAINT `lots_qc_results_ibfk_2` FOREIGN KEY (`test_result`) REFERENCES `results` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `process_data`
--
ALTER TABLE `process_data`
  ADD CONSTRAINT `process_data_ibfk_1` FOREIGN KEY (`process_id`) REFERENCES `process_queue` (`id`);

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`lot_number`) REFERENCES `lots` (`lot_number`);

COMMIT;