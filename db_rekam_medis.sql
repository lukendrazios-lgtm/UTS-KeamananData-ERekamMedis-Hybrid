-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 17, 2026 at 03:26 AM
-- Server version: 8.0.30
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_rekam_medis`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_kunci_hybrid`
--

CREATE TABLE `tb_kunci_hybrid` (
  `id` int NOT NULL,
  `public_key` text NOT NULL,
  `encrypted_aes_key` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tb_kunci_hybrid`
--

INSERT INTO `tb_kunci_hybrid` (`id`, `public_key`, `encrypted_aes_key`, `created_at`) VALUES
(1, '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoelyfquwP+vRskneAYCp\ny0shBYOhC4ip3Ms3nKOHumQcY80R9btEbEzN3Zb4idwZY28Kd4Q2Sc40sSFhakeT\nrDsVZoXNmYRYlsKSui2GMkffL1xk2/8u/syvLSusKsXwxJsCYiJUQU4AzTYuF9dk\nybIVUMHMF2SzxNl1GVQeaN0BZtc7rZg4SjHBXW40M4x+dmvkjhsIfHUKKI1UOsPh\nQgu+2iAyBUMq13YpWybLV/41wFof4LJxoRTHjSIYA7z9xVKGYT6yS7ZDElXQNmle\n4tt2QHLe9sNSUoIqQpArdkyf4vmdOyDg437tZPJhGbG7c/uB9mf8JhqGgII+Hs8v\nIwIDAQAB\n-----END PUBLIC KEY-----\n', 'dwLFRAldrqNTiKuDVieK5s/kq5iEveBUqiH0qXxY4Xc/yW0GgHWsGDieA8psE9Sq06pJcKI91XPiwPPrcv26wyvmDmKKs/ZUHj8tiTlI1wIMAFhvenzP8c76H+qtdoEmZyWF1bd6sdpIexk4flzjIqXP0Gs9laqcgwbcGeT2YTRh3FUNmjUW2kBZ9mQjflTVs4Dl1WM7bQNk5Un77ZH8ZmVycYgVlYg+0IrQG88jVwV6FShghXFKpQhd2pJk0rVB6RzI+69Hx/Anbx5mz30fZp3mSwEPkVKEYpm9r+oXvKx/HVOml6TZnre1VKR4UIsfvHH81p6yZIlM299Pp9hqJQ==', '2026-11-17 01:26:36');

-- --------------------------------------------------------

--
-- Table structure for table `tb_rekam_medis`
--

CREATE TABLE `tb_rekam_medis` (
  `id` int NOT NULL,
  `no_rm` varchar(30) NOT NULL,
  `nik_pasien` text NOT NULL,
  `nama_pasien` text NOT NULL,
  `keluhan` text NOT NULL,
  `diagnosa` text NOT NULL,
  `resep_obat` text NOT NULL,
  `tanggal_periksa` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tb_rekam_medis`
--

INSERT INTO `tb_rekam_medis` (`id`, `no_rm`, `nik_pasien`, `nama_pasien`, `keluhan`, `diagnosa`, `resep_obat`, `tanggal_periksa`) VALUES
(1, 'RM-2026-001', 'EBASQ50wuLfQY/0pFtaH9clKKmzD5wNKZf3G5M8uCPFSt1SXeo5wwn4M6YIjQfAL', 'TW/ClQm0yMFBhg/4xWwhRhvbAfjfU8MYvKNNsmnbH+A=', 's+x2Hi5X46gmixXItAI0s5K6G8YQISUuirLRXMvsrm5VotkY4JYghrYDxPWENQWaWjDR9le6eHD9AzyM1+Cwm5lboijsimC5Rz2J/zyUY94=', '9/IDdGISqw3iLuDV4YdQUav26Fj+gnS3CQSo9gGPq/N9ceOKMUrg7j/Kp+vHhrYL2pX8JZqHb0iaUIcjL9YZLQ==', '+ruF68o0LawFcjxzU+HcgZTe+aBLdIKFGba/yu8f58J8X7FYBtA+Q19w1biWQO+wktrEeV74vH09Tp/btkPpPFqvDzMjMAI5aXviuxaNAoM=', '2026-11-17 08:26:36'),
(2, 'RM-2026-002', 'vzGNjIrFP7kguS4doXQsPN7uNOX1rc6wQJuXHqrVznUqU4XKpAogXsS40mMqo5dC', 'oxQbitk0zbeCLAlw0r1irZKUknpX+Uq6M9fbuPi1Ru8=', '7Yzs7WBeHgNtGMsqg7J1NyatUeASLqOkUvXHdilKoN6jg+08y42arg5BI6pWdx9ezv6TCVl7KV0zerC/zb/Jlqf33d5E45UMvP1btyViaro=', 'N5yb+sDLPrtm8Sx1JUPnCaBJgTpxXlsuewIAF7P1kiVai2ZP9M+TZCVZT2RhktPjbMlvgmFNacHRHOA3FDPvVQ==', 'FFkjc9MD7DIkAiVWRFuIhk7DpUAw8syaQ9kFF5eKVxFl48pd6Jdq91cL0rc25lYAHlNDfT+Fipn9TxtulC0COOjmC0liqiLcipySituQNb4=', '2026-11-17 08:26:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_kunci_hybrid`
--
ALTER TABLE `tb_kunci_hybrid`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_rekam_medis`
--
ALTER TABLE `tb_rekam_medis`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_rekam_medis`
--
ALTER TABLE `tb_rekam_medis`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
