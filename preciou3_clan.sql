-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 10, 2025 at 09:11 PM
-- Server version: 8.0.41-cll-lve
-- PHP Version: 8.1.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `preciou3_clan`
--

-- --------------------------------------------------------

--
-- Table structure for table `devs`
--

CREATE TABLE `devs` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `devs`
--

INSERT INTO `devs` (`id`, `name`, `image_url`) VALUES
(1, 'Honour', 'https://avatars.githubusercontent.com/u/117399956?v=4'),
(2, 'Precious', 'https://avatars.githubusercontent.com/u/193069706?v=4'),
(3, 'Whakee', 'https://avatars.githubusercontent.com/u/106148374?v=4'),
(4, 'P-tech', 'https://pbs.twimg.com/profile_images/1923704192369086464/wGOK9rWA_400x400.jpg'),
(5, 'Coolerputt', 'https://wakatime.com/photo/1f435ed2-39b5-4c9e-a1f5-4cbd67baaac6?s=420'),
(6, 'Treasure', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQF68sjaHTS4kWmUdqjvH0KuJ1wlykBA7Tuwg&usqp=CAU'),
(7, 'Tomiwa', 'https://pbs.twimg.com/profile_images/1895248217027002368/HB0tKzua_400x400.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `event_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `image_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `created_at`, `image_url`, `details`) VALUES
(1, 'CodeClan HackJam', 'A weekend hackathon to build something wild.', '2025-06-08', '2025-06-02 07:00:14', 'data:image/png;base64,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', '\n## 🔥 What\'s Happening?\n\n- 24 hours of coding madness  \n- Free pizza 🍕, energy drinks ☕, and late-night music 🎶  \n- Teams of up to 4 or go solo  \n- Win awesome prizes 🏆  \n\n---\n\n## 🛠️ Sample Code\n\n```html\n<div class=\"team\">\n  <h2>Team Innovators</h2>\n  <p>Building a real-time voting app using Firebase and React.</p>\n</div>\n```\n\n```php\n<?php\necho \"Hack the planet!\";\n?>\n```\n\n---\n\n## ✍️ How to Join\n\n1. Register at [codeclan.ng/hackjam](https://codeclan.ng/hackjam)  \n2. Bring your laptop and charger 🔌  \n3. Show up and build something awesome!\n\n---\n\n## 🧠 Judging Criteria\n\n- Creativity 💡  \n- Functionality ⚙️  \n- Design ✨  \n- Teamwork 🤝  \n\n---\n\n## 📍 Location\n\nCodeClan HQ, Lagos  \n**Date:** June 8, 2025  \n**Time:** 10:00 AM – 10:00 AM (24-hour hackathon)\n\n---\n\nSee you at **#CodeClanHackJam** 💥'),
(2, 'Build and Learn', 'Collab session + debugging tips with seniors.', '2025-06-12', '2025-06-02 07:00:14', 'data:image/png;base64,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', '\n## 🤝 What’s Inside?\n\n- Live debugging sessions with seniors  \n- Open Q&A and career advice  \n- Group coding with real-world mini projects  \n- Code reviews and refactoring best practices\n\n---\n\n## 💬 Example Tip\n\n```js\n// Instead of doing this:\nlet result = data ? data.value : null;\n\n// Use optional chaining:\nlet result = data?.value;\n```\n\n```bash\n# Or when debugging in PHP\nini_set(\'display_errors\', 1);\nerror_reporting(E_ALL);\n```\n\n---\n\n## 🎯 Goals\n\n- Build small but real web apps  \n- Learn common debugging techniques  \n- Understand how seniors approach code  \n- Grow through peer feedback\n\n---\n\n## 🧑‍💻 Who Can Join?\n\n- Juniors (0–2 years experience)  \n- Anyone eager to learn  \n- Students, self-taught devs, bootcampers\n\n---\n\n## 📍 Location & Time\n\n**CodeClan Online Discord Server**  \n🗓️ **June 12, 2025**  \n🕒 **5:00 PM – 8:00 PM WAT**\n\n---\n\nJoin the #BuildAndLearn vibe 🚀'),
(3, 'Clan After Dark', 'Late-night coding stream with surprises.', '2025-05-20', '2025-06-02 07:00:14', 'data:image/png;base64,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', 'lorem isplur');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `devs`
--
ALTER TABLE `devs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `devs`
--
ALTER TABLE `devs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
