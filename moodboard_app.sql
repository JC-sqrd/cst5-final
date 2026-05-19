-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 07:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `moodboard_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `moodboards`
--

CREATE TABLE `moodboards` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `moodboards`
--

INSERT INTO `moodboards` (`id`, `title`, `created_at`) VALUES
(1, 'My Moodboard', '2026-05-19 17:21:12'),
(2, 'Pixel Town', '2026-05-19 17:24:13'),
(3, 'Simulation Visuals', '2026-05-19 17:24:54');

-- --------------------------------------------------------

--
-- Table structure for table `moodboard_items`
--

CREATE TABLE `moodboard_items` (
  `id` varchar(50) NOT NULL,
  `moodboard_id` int(11) NOT NULL,
  `src` varchar(255) NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `w` int(11) NOT NULL,
  `h` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `moodboard_items`
--

INSERT INTO `moodboard_items` (`id`, `moodboard_id`, `src`, `x`, `y`, `w`, `h`) VALUES
('item_1779211282_293', 1, 'uploads/1779211282_Frame 3.png', 959, 158, 245, 312),
('item_1779211285_435', 1, 'uploads/1779211285_pixe-city.png', 770, 258, 251, 223),
('item_1779211289_884', 1, 'uploads/1779211289_pixel-town.png', 556, 162, 257, 351),
('item_1779211461_249', 2, 'uploads/1779211461_pixe-city.png', 249, 323, 150, 150),
('item_1779211462_390', 2, 'uploads/1779211462_pixel-town.png', 451, 324, 150, 150),
('item_1779211463_877', 2, 'uploads/1779211463_pixel-town.png', 535, 167, 150, 150),
('item_1779211500_837', 3, 'uploads/1779211500_Directed Graph.drawio.png', 206, 106, 247, 344),
('item_1779211504_239', 3, 'uploads/1779211504_Spatial Grid - Pathfinding Example.drawio.png', 471, 221, 308, 416),
('item_1779211514_191', 3, 'uploads/1779211514_example_pathfinding_visual.png', 1179, 283, 271, 324),
('item_1779211520_444', 3, 'uploads/1779211520_Spatial Grid.drawio.png', 798, 138, 366, 375);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `moodboards`
--
ALTER TABLE `moodboards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `moodboard_items`
--
ALTER TABLE `moodboard_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `moodboard_id` (`moodboard_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `moodboards`
--
ALTER TABLE `moodboards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `moodboard_items`
--
ALTER TABLE `moodboard_items`
  ADD CONSTRAINT `moodboard_items_ibfk_1` FOREIGN KEY (`moodboard_id`) REFERENCES `moodboards` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
