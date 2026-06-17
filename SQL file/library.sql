-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2026 at 09:45 PM
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
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `AdminEmail` varchar(120) DEFAULT NULL,
  `UserName` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `updationDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `FullName`, `AdminEmail`, `UserName`, `Password`, `updationDate`) VALUES
(1, 'liangyea', 'admin@gmail.com', 'admin', '0192023a7bbd73250516f069df18b500', '2026-05-08 18:29:31'),
(2, 'liangyea', 'liangyeal227@gmail.com', 'admin1', '0192023a7bbd73250516f069df18b500', '2026-05-08 18:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `tblauthors`
--

CREATE TABLE `tblauthors` (
  `id` int(11) NOT NULL,
  `AuthorName` varchar(159) DEFAULT NULL,
  `creationDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblauthors`
--

INSERT INTO `tblauthors` (`id`, `AuthorName`, `creationDate`, `UpdationDate`) VALUES
(1, 'Anuj kumar', '2023-12-31 21:23:03', '2025-01-07 06:18:43'),
(2, 'Chetan Bhagatt', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(3, 'Anita Desai', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(4, 'HC Verma', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(5, 'R.D. Sharma ', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(9, 'fwdfrwer', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(10, 'Dr. Andy Williams', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(11, 'Kyle Hill', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(12, 'Robert T. Kiyosak', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(13, 'Kelly Barnhill', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(14, 'Herbert Schildt', '2023-12-31 21:23:03', '2025-01-07 06:18:50'),
(16, ' Tiffany Timbers', '2025-01-07 06:55:54', NULL),
(17, 'Atsushi Ohkubo', '2026-06-14 08:15:53', NULL),
(18, 'Jane Austen', '2026-06-15 13:36:24', NULL),
(20, 'Charlotte Brontë', '2026-06-15 13:44:32', NULL),
(21, 'Diana Gabaldon', '2026-06-15 13:46:59', NULL),
(22, 'Nicholas Sparks', '2026-06-15 13:54:21', NULL),
(23, 'Emily Henry', '2026-06-15 14:01:36', NULL),
(24, 'Casey McQuiston', '2026-06-15 14:06:25', NULL),
(25, 'Tia Williams', '2026-06-15 14:38:05', NULL),
(27, 'Helen Hoang', '2026-06-15 14:42:45', NULL),
(28, 'Walter Isaacson', '2026-06-15 14:46:33', NULL),
(29, 'Martin Kleppmann', '2026-06-15 14:52:26', NULL),
(30, 'Dafydd Stuttard', '2026-06-15 14:54:52', NULL),
(31, 'Robert C. Martin', '2026-06-15 14:57:32', NULL),
(32, 'Louis-François Bouchard', '2026-06-15 14:59:09', NULL),
(33, 'Stephen Hawking', '2026-06-15 15:02:12', NULL),
(34, 'Carl Sagan', '2026-06-15 15:04:18', NULL),
(35, 'Richard Dawkins', '2026-06-15 15:06:24', NULL),
(36, 'Siddhartha Mukherjee', '2026-06-15 15:09:41', NULL),
(37, 'Brian Greene', '2026-06-15 15:11:34', NULL),
(38, 'Jim C. Collins', '2026-06-15 15:14:33', NULL),
(39, 'Eric Ries', '2026-06-15 15:16:20', NULL),
(40, 'Kim Malone Scott.', '2026-06-15 15:19:26', NULL),
(41, 'Patrick Lencioni', '2026-06-15 15:21:07', NULL),
(42, 'Harper Lee', '2026-06-15 15:23:12', NULL),
(43, 'George Orwell', '2026-06-15 15:24:35', NULL),
(44, 'Paulo Coelho', '2026-06-15 15:26:36', NULL),
(45, 'Khaled Hosseini', '2026-06-15 15:28:30', NULL),
(46, 'Markus Zusak', '2026-06-15 15:29:45', NULL),
(47, 'Robert Cecil Martin', '2026-06-15 15:33:11', NULL),
(48, 'Andrew Hunt', '2026-06-15 15:36:41', NULL),
(49, 'Steve McConnell', '2026-06-15 15:39:26', NULL),
(50, 'O\'Reilly Media', '2026-06-15 15:41:11', NULL),
(51, 'Thomas H. Cormen', '2026-06-15 15:43:53', NULL),
(52, 'Oda Eiichiro', '2026-06-15 15:46:33', NULL),
(53, 'Masashi Kishimoto', '2026-06-15 15:48:30', NULL),
(54, 'Hajime Isayama', '2026-06-15 15:50:39', NULL),
(55, 'Gege Akutami', '2026-06-15 15:52:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblbooks`
--

CREATE TABLE `tblbooks` (
  `id` int(11) NOT NULL,
  `BookName` varchar(255) DEFAULT NULL,
  `CatId` int(11) DEFAULT NULL,
  `AuthorId` int(11) DEFAULT NULL,
  `ISBNNumber` varchar(25) DEFAULT NULL,
  `BookDetails` varchar(1000) DEFAULT NULL,
  `bookImage` varchar(250) NOT NULL,
  `isIssued` int(1) DEFAULT NULL,
  `RegDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `bookQty` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblbooks`
--

INSERT INTO `tblbooks` (`id`, `BookName`, `CatId`, `AuthorId`, `ISBNNumber`, `BookDetails`, `bookImage`, `isIssued`, `RegDate`, `UpdationDate`, `bookQty`) VALUES
(1, 'PHP And MySql programming', 5, 1, '222333', 'this', '1efecc0ca822e40b7b673c0d79ae943f.jpg', 0, '2024-01-02 01:23:03', '2026-06-13 11:40:22', 3),
(3, 'physics', 6, 4, '1111', '', 'dd8267b57e0e4feee5911cb1e1a03a79.jpg', NULL, '2024-01-02 01:23:03', '2026-06-13 11:40:27', 3),
(5, 'Murach\'s MySQL', 5, 1, '9350237695', '', '5939d64655b4d2ae443830d73abc35b6.jpg', 0, '2024-01-02 01:23:03', '2026-06-16 10:30:00', 3),
(6, 'WordPress for Beginners 2022: A Visual Step-by-Step Guide to Mastering WordPress', 5, 10, 'B019MO3WCM', '', '144ab706ba1cb9f6c23fd6ae9c0502b3.jpg', NULL, '2024-01-02 01:23:03', '2026-06-13 11:40:51', 3),
(7, 'WordPress Mastery Guide:', 5, 11, 'B09NKWH7NP', NULL, '90083a56014186e88ffca10286172e64.jpg', 0, '2024-01-02 01:23:03', '2026-06-13 11:41:05', 3),
(8, 'Rich Dad Poor Dad: What the Rich Teach Their Kids About Money That the Poor and Middle Class Do Not', 8, 12, 'B07C7M8SX9', NULL, '52411b2bd2a6b2e0df3eb10943a5b640.jpg', NULL, '2024-01-02 01:23:03', '2026-06-15 15:55:01', 3),
(9, 'The Girl Who Drank the Moon', 8, 13, '1848126476', NULL, 'f05cd198ac9335245e1fdffa793207a7.jpg', NULL, '2024-01-02 01:23:03', '2025-01-13 11:05:45', 1),
(10, 'C++: The Complete Reference, 4th Edition', 5, 14, '007053246X', NULL, '36af5de9012bf8c804e499dc3c3b33a5.jpg', NULL, '2024-01-02 01:23:03', '2025-01-13 11:11:01', 2),
(11, 'ASP.NET Core 5 for Beginners', 9, 11, 'GBSJ36344563', NULL, 'b1b6788016bbfab12cfd2722604badc9.jpg', NULL, '2024-01-02 01:23:03', '2025-01-13 11:11:01', 5),
(12, 'Python Packages', 9, 16, '0367687771', NULL, 'ba719639def504c64ebac89cdd0d0a85.jpg', NULL, '2025-01-07 06:56:50', NULL, 25),
(13, 'Fire Force (1)', 10, 17, 'SB001', '', '23c32d717a517805bff6ede71397114b.jpg', 1, '2026-06-14 08:27:44', '2026-06-16 08:50:35', 3),
(14, 'Fire Force (2) ', 10, 17, 'CS001', 'Shinra is told about the upcoming Rookie Fire Soldier Games and how he and the new recruit, Arthur Boyle, will represent Special Fire Force Company 8. Shinra explains that he knows Arthur from the Academy, and is heavily annoyed by his tendency of conside', '83c03281897e285df43ccefe30126fe0.jpg', NULL, '2026-06-15 13:05:02', NULL, 3),
(15, 'Pride and Prejudice', 4, 18, 'RT003', 'This article is about the novel. For other uses, see Pride and Prejudice.\r\nPride and Prejudice is a novel by English author Jane Austen. Written when she was aged 20–21, it was her third novel scribed and became the second to see print when it was published in 1813. A novel of manners, it follows the character development of Elizabeth Bennet, the protagonist of the book, who learns about the repercussions of hasty judgments and comes to appreciate the difference between superficial goodness and actual goodness.', '02987f95356c6f264f780346bf83328d.jpg', NULL, '2026-06-15 13:41:23', NULL, 3),
(16, 'Jane Eyre', 4, 20, 'RT004', 'The novel revolutionised prose fiction, being the first to focus on the moral and spiritual development of its protagonist through an intimate first-person narrative, where actions and events are coloured by a psychological intensity. Literary critic Daniel S. Burt has called Charlotte Brontë \"the first historian of the private consciousness and the literary ancestor of writers such as Marcel Proust and James Joyce.', '0d3b9ab4ed44ca33db21940a49bacd95.jpg', NULL, '2026-06-15 13:46:13', NULL, 3),
(17, 'Outlander', 4, 21, 'RT005', 'This article is about the novel. For the TV series, see Outlander (TV series).\r\n\r\nOutlander (published in the United Kingdom as Cross Stitch) is a historical fantasy novel by American writer Diana Gabaldon, first published in 1991. Initially set around the time of the Second World War, it focuses on nurse Claire Beauchamp, who travels through time to 18th-century Scotland, where she finds adventure and romance with the dashing Jamie Fraser. It is the first novel in the Outlander series, which is set to comprise ten books, nine of which have already been published. The television adaptation of the series premiered on Starz in the US on August 9, 2014.\r\n\r\nA mix of several genres, the series has elements of historical fiction, romance, adventure and traditional fantasy. It has sold over 25 million copies. The first book won a Romance Writers of America\'s RITA Award in 1992.', '28844705ad5b0196ef7d3c0088c6b138.jpg', NULL, '2026-06-15 13:48:53', NULL, 3),
(18, 'The Notebook', 4, 22, 'RT006', 'The Notebook was Nicholas Sparks\' first published novel and written over a time period of six months in 1994. Literary agent Theresa Park discovered Sparks by picking the book out of her agency\'s slush pile and reading it. Park offered to represent him. In October 1995, Park secured a $1 million advance for the book from the Time Warner Book Group, and the novel was published in October 1996. It was on The New York Times Best Seller list in its first week of release. The Notebook was a hardcover best seller for more than a year.\r\n\r\nIn interviews, Sparks said he was inspired to write the novel by the story of his wife\'s grandparents, who had been married for more than 60 years when he met them. In The Notebook, he tried to express the long romantic love of that couple.', '47855c72e1de3254a54cfea0e47604cf.jpg', NULL, '2026-06-15 13:56:11', NULL, 3),
(19, 'Beach Read', 4, 23, 'RT007', 'January Andrews is a successful romance novel writer who is struggling after the death of her father and the discovery that he was having an affair. While living in his old beach house to prepare to sell it, she runs into Augustus Everett, her former rival in college and now an acclaimed literary fiction author. They reconnect and bond over struggling with writer’s block; they challenge each other to spend the summer writing a novel in each other’s genres.', '63ac1c8723afc97ecbcfb92d8388f334.jpg', NULL, '2026-06-15 14:02:45', NULL, 3),
(20, 'Red, White & Royal Blue', 4, 24, 'RT008', 'McQuiston first came up with the idea for what would become Red, White & Royal Blue during the 2016 U.S. presidential election. While watching a season of the HBO comedy series Veep and reading a Hillary Clinton biography by Carl Bernstein, A Woman in Charge, and The Royal We by Heather Cocks and Jessica Morgan, McQuiston became intrigued by the extravagant, high-profile lifestyle of the royals and wanted to take on a story featuring a royal family.', '4a9c382a9fa746775855d1e0474e7e5c.jpg', NULL, '2026-06-15 14:07:41', NULL, 3),
(21, 'Seven Days in June', 4, 25, 'RT009', 'Brooklynite Eva Mercy is a single mom and bestselling erotica writer, who is feeling pressed from all sides. Shane Hall is a reclusive, enigmatic, award-winning literary author who, to everyone\'s surprise, shows up in New York.\r\n\r\nWhen Shane and Eva meet unexpectedly at a literary event, sparks fly, raising not only their past buried traumas, but the eyebrows of New York\'s Black literati. What no one knows is that twenty years earlier, teenage Eva and Shane spent one crazy, torrid week madly in love. They may be pretending that everything is fine now, but they can\'t deny their chemistry - or the fact that they\'ve been secretly writing to each other in their books ever since.', 'db0522a85f0783f418ebfc5e8c33a20c.jpg', NULL, '2026-06-15 14:39:04', NULL, 3),
(22, 'People We Meet on Vacation', 4, 23, 'RT010', 'For the novel\'s 2026 film adaptation, see People We Meet on Vacation (film).\r\n\r\nPeople We Meet on Vacation is a romance novel by Emily Henry, published May 11, 2021 by Berkley Books, known as You and Me on Vacation in the UK and Australia. The book is a New York Times best seller. People We Meet on Vacation is told in a nonlinear narrative, interspersing its protagonist\'s present vacation with flashbacks from past trips. It follows Poppy Wright and Alex Nilsen, two best friends who are opposites in every way. She is an outgoing, wanderlust-filled wild child, while he is mild-mannered and introverted. Every summer they come together for a week long vacation, until a trip to Dubrovnik causes them to stop speaking for two years.', '4613b01f7a84bb3b3d7e03590cabb2e7.jpg', NULL, '2026-06-15 14:42:16', NULL, 3),
(23, 'The Kiss Quotient', 4, 27, 'RT011', 'Hoang wrote the first draft of what would become The Kiss Quotient within ten weeks. The manuscript went through several drafts before she entered the online pitch contest Pitch Wars, where she revised it again with the help of her mentor Brighton Walsh, working for eight months.\r\nHoang states that she initially wanted to write a gender-swapped Pretty Woman, but was stuck when examining why a \"successful, beautiful woman would hire an escort. During a meeting with her daughter\'s preschool teacher, Hoang found out that her daughter is on the autism spectrum. She researched autism and realized that she, too, is autistic, and used that as the basis for the book\'s concept.', '3598c909f129004edaeadc3c81cbdbeb.jpg', NULL, '2026-06-15 14:43:47', NULL, 3),
(24, 'Book Lovers', 4, 23, 'RT012', 'New York literary agent Nora Stephens is convinced to take a vacation at Sunshine Falls, North Carolina, for the entire month of August by her younger sister, Libby. Nora and Libby were raised by a single mother, who died when Libby was in high school. Throughout her adult life, Nora has given up everything in order to support Libby.\r\n\r\nShe discovers that Charlie Lastra, a book editor she is not on good terms with, is from Sunshine Falls and also happens to be in town. He\'s also running the local bookstore, which his parents own, in order to help them out.', 'e541c9d569dab559981006a14a93fbf4.jpg', NULL, '2026-06-15 14:45:41', NULL, 3),
(25, 'The Innovators', 5, 28, 'TH001', 'The Innovators: How a Group of Hackers, Geniuses, and Geeks Created the Digital Revolution is an overview of the history of computer science and the Digital Revolution. It was written by Walter Isaacson, and published in 2014 by Simon & Schuster.\r\n\r\nThe book summarizes the contributions of several innovators who have made pivotal breakthroughs in computer technology and its applications—from the world\'s first computer programmer, Ada Lovelace, and Alan Turing\'s work in artificial intelligence, through the Information Age of the present', '0e881b10cecf3d55187a6105eabd15f7.jpg', NULL, '2026-06-15 14:47:51', NULL, 3),
(26, 'Designing Data-Intensive Applications', 5, 29, 'TH002', 'Data is at the center of many challenges in system design today. Difficult issues need to be figured out, such as scalability, consistency, reliability, efficiency, and maintainability. In addition, we have an overwhelming variety of tools, including relational databases, NoSQL datastores, stream or batch processors, and message brokers. What are the right choices for your application? How do you make sense of all these buzzwords?\r\nIn this practical and comprehensive guide, author Martin Kleppmann helps you navigate this diverse landscape by examining the pros and cons of various technologies for processing and storing data. Software keeps changing, but the fundamental principles remain the same. With this book, software engineers and architects will learn how to apply those ideas in practice, and how to make full use of data in modern applications.', 'be4f3d749e89e8c34daab62726249520.jpg', NULL, '2026-06-15 14:53:44', NULL, 3),
(27, 'The Web Application Hacker\'s Handbook: Finding and Exploiting Security Flaws', 5, 30, 'TH003', 'The highly successful security book returns with a new edition, completely updated Web applications are the front door to most organizations, exposing them to attacks that may disclose personal information, execute fraudulent transactions, or compromise ordinary users. This practical book has been completely updated and revised to discuss the latest step-by-step techniques for attacking and defending the range of ever-evolving web applications. You\'ll explore the various new technologies employed in web applications that have appeared since the first edition and review the new attack techniques that have been developed, particularly in relation to the client side.', 'c7e52263ad0004cad50256b9dc0e7b43.jpg', NULL, '2026-06-15 14:56:38', NULL, 3),
(28, 'Clean Architecture', 5, 31, 'TH004', 'Building upon the success of best-sellers The Clean Coder and Clean Code, legendary software craftsman Robert C. \"Uncle Bob\" Martin shows how to bring greater professionalism and discipline to application architecture and design.\r\n\r\nAs with his other books, Martin\'s Clean Architecture doesn\'t merely present multiple choices and options, and say \"use your best judgment\": it tells you what choices to make, and why those choices are critical to your success. Martin offers direct, no-nonsense answers to key architecture and design questions like:\r\n\r\nWhat are the best high level structures for different kinds of applications, including web, database, thick-client, console, and embedded apps?\r\n\r\nWhat are the core principles of software architecture?\r\n\r\nWhat is the role of the architect, and what is he/she really trying to achieve?\r\n\r\nWhat are the core principles of software design?\r\n\r\nHow do designs and architectures go wrong, and what can you do about it?\r\n\r\nWhat are the disciplines and pra', '57ecb6361bff349fe69e4a3b292f4aa3.jpg', NULL, '2026-06-15 14:58:35', NULL, 3),
(29, 'Building LLMs for Production: Enhancing LLM Abilities and Reliability with Prompting, Fine-Tuning, and RAG', 5, 32, 'TH005', 'With amazing feedback from industry leaders, this book is an end-to-end resource for anyone looking to enhance their skills or dive into the world of AI and develop their understanding of Generative AI and Large Language Models (LLMs). It explores various methods to adapt \"foundational\" LLMs to specific use cases with enhanced accuracy, reliability, and scalability. Written by over 10 people on our Team at Towards AI and curated by experts from Activeloop, LlamaIndex, Mila, and more, it is a roadmap to the tech stack of the future.', 'caa5b9e37b879b80c7909cff5dcbee85.jpg', NULL, '2026-06-15 15:01:09', NULL, 3),
(30, 'A Brief History of Time', 6, 33, 'SC001', 'A Brief History of Time: From the Big Bang to Black Holes is a book on cosmology by the physicist Stephen Hawking, first published in 1988.\r\n\r\nHawking writes in non-technical terms about the structure, origin, development and eventual fate of the universe. He talks about basic concepts like space and time, building blocks that make up the universe (such as quarks) and the fundamental forces that govern it (such as gravity). He discusses two theories, general relativity and quantum mechanics that form the foundation of modern physics. Finally, he talks about the search for a unified theory that consistently describes everything in the universe.', '54d44b65bc477a5e0e7d626a609cd018.jpg', NULL, '2026-06-15 15:03:32', NULL, 3),
(31, 'Cosmos (Sagan book)', 6, 34, 'SC003', 'Cosmos is a popular science book written by astronomer and Pulitzer Prize-winning author Carl Sagan. It was published in 1980 as a companion piece to the PBS mini-series Cosmos: A Personal Voyage with which it was co-developed and intended to complement. Each of the book\'s 13 illustrated chapters corresponds to one of the 13 episodes of the television series.', 'd47b5262eefcc57fd4f4efc8e190a985.jpg', NULL, '2026-06-15 15:05:39', NULL, 3),
(32, 'The Selfish Gene', 6, 35, 'SC004', 'The Selfish Gene is a 1976 popular science book by Richard Dawkins that espouses the gene-centred view of evolution. It builds upon the thesis of George Christopher Williams\'s Adaptation and Natural Selection (1966) and W. D. Hamilton\'s work on kin selection. From the gene-centred view, it follows that the more genes two individuals share, the more sense it makes for them to co-operate.', 'fbd822a03451676c59e8a3f09dec34d0.jpg', NULL, '2026-06-15 15:09:03', NULL, 3),
(33, 'The Gene: An Intimate History', 6, 36, 'SC005', 'The Gene: An Intimate History is a book written by Siddhartha Mukherjee, an Indian-born American physician and oncologist. It was published on 17 May 2016 by Scribner. The book chronicles the history of the gene and genetic research, all the way from Aristotle to Crick, Watson and Franklin and then the 21st century scientists who mapped the human genome. The book discusses the power of genetics in determining people\'s well-being and traits. It delves into the personal genetic history of Siddhartha Mukherjee\'s family, including mental illness. However, it is also a cautionary message toward not letting genetic predispositions define a person or their fate, a mentality that the author says led to the rise of eugenics in history.', '84af9889718652f3231ed598901651dd.jpg', NULL, '2026-06-15 15:10:39', NULL, 3),
(34, 'The Elegant Universe', 6, 37, 'SC006', 'The Elegant Universe: Superstrings, Hidden Dimensions, and the Quest for the Ultimate Theory is a book by Brian Greene published in 1999, which introduces string and superstring theory, and provides a comprehensive though non-technical assessment of the theory and some of its shortcomings. In 2000, it won the Royal Society Prize for Science Books and was a finalist for the Pulitzer Prize for General Nonfiction. A new edition was released in 2003, with an updated preface.', '4c9c87835d1464ed6f58e85720f0541a.jpg', NULL, '2026-06-15 15:12:40', NULL, 3),
(35, 'Good to Great', 7, 38, 'MN001', 'Good to Great: Why Some Companies Make the Leap... and Others Don\'t is a management book by Jim C. Collins that describes how companies transition from being good companies to great companies, and how most companies fail to make the transition. The book was a bestseller, selling four million copies and going far beyond the traditional audience of business books. The book was published on October 16, 2001.', '4e93e4b32468322305d27eae0c5b891f.jpg', NULL, '2026-06-15 15:15:41', NULL, 3),
(36, 'The Lean Startup', 7, 39, 'MN002', 'The Lean Startup: How Today\'s Entrepreneurs Use Continuous Innovation to Create Radically Successful Businesses is a 2011 book by American entrepreneur Eric Ries. It outlines the lean startup methodology, a framework for startup development that prioritizes rapid prototyping, validated learning, and iterative product releases. The goal of this methodology is to shorten product development cycles.', 'f7f6376820857b5d731aa77f4c387cbd.png', NULL, '2026-06-15 15:17:54', NULL, 3),
(37, 'Radical Candor', 7, 40, 'MN004', 'Radical Candor: Be a Kick-Ass Boss Without Losing Your Humanity is a business leadership book written by former Apple and Google executive Kim Malone Scott. In the book, Scott defines the term radical candor as feedback that incorporates both praise and criticism.[3] Unlike radical transparency or radical honesty, Scott says the management principle of radical candor involves “caring personally while challenging directly. The book was first published in 2017 by St. Martin\'s Press. A fully revised and updated version was released in 2019.', '79a8a2e61f070c8ba000be91aae249da.jpg', NULL, '2026-06-15 15:20:37', NULL, 3),
(38, 'The Five Dysfunctions of a Team', 7, 41, 'MN005', 'The Five Dysfunctions of a Team is a business book by consultant and speaker Patrick Lencioni first published in 2002. It describes many pitfalls that teams face as they seek to \"grow together\". This book explores the fundamental causes of organizational politics and team failure. Like most of Lencioni\'s books, the bulk of it is written as a business fable.\r\n\r\nThe issues it describes were considered especially important to team sports. The book\'s lessons were applied by several coaches to their teams in the National Football League in the United States.', '7f4098c7ff7b00917959daccb883c6ea.png', NULL, '2026-06-15 15:22:27', NULL, 3),
(39, 'To Kill a Mockingbird', 8, 42, 'GN001', 'To Kill a Mockingbird is a 1960 Southern Gothic novel by American author Harper Lee. It became instantly successful after its release; in the United States, it is widely read in high schools and middle schools. To Kill a Mockingbird won the Pulitzer Prize a year after its release, and it has become a classic of modern American literature. The plot and characters are loosely based on Lee\'s observations of her family, her neighbors and an event that occurred near her hometown of Monroeville, Alabama, in 1936, when she was ten.', 'ea5ee06cb967516e022c9411ef8f5adf.jpg', NULL, '2026-06-15 15:24:09', NULL, 3),
(40, 'Nineteen Eighty-Four', 8, 43, 'MN003', 'For the year, see 1984. For other uses, see 1984 (disambiguation).\r\nNineteen Eighty-Four (also published as 1984) is a dystopian speculative fiction novel by the English writer George Orwell. It was published on 8 June 1949 by Secker & Warburg as Orwell\'s ninth and final completed book. Thematically, it centres on totalitarianism, mass surveillance and repressive regimentation of people and behaviours. Nineteen Eighty-Four has been often regarded as a classic and has been acknowledged for its impact on twentieth-century literature.', 'f453eef43591548c986cad52917c1771.jpg', NULL, '2026-06-15 15:25:58', NULL, 3),
(41, 'The Alchemist (novel)', 8, 44, 'MN006', 'The Alchemist (Portuguese: O Alquimista) is a novel by Brazilian author Paulo Coelho which was first published in 1988. Originally written in Portuguese, it became a widely translated international bestseller. The story follows Santiago, a shepherd boy, in his journey across North Africa to the Egyptian pyramids after he dreams of finding treasure there. It has since been translated into more than 65 languages and has sold more than 150 million copies worldwide. In 2009, Paulo Coelho was recognized by the Guinness World Records as the world’s most translated living author.', '7405381b8b83557a13d036a6f340ead9.jpg', NULL, '2026-06-15 15:27:56', NULL, 3),
(42, 'The Kite Runner', 8, 45, 'MN007', 'The Kite Runner is the debut novel of Afghan-American author Khaled Hosseini. Published in 2003 by Riverhead Books, it tells the story of Amir, a young Afghan boy from Wazir Akbar Khan, Kabul. The story is set against a backdrop of tumultuous events, beginning with the collapse of Afghanistan\'s monarchy and the Afghan conflict that sparked shortly thereafter, with a particular focus on the Soviet–Afghan War and the exodus of Afghan refugees, as well as the rise of the Taliban regime.', 'cf67e96f463f7663e94c0c0e7ff073d1.jpg', NULL, '2026-06-15 15:29:17', NULL, 3),
(43, 'The Book Thief', 8, 46, 'MN008', 'For additional editions see The Book Thief\r\nThe Book Thief is a historical fiction novel by the Australian author Markus Zusak, set in Nazi Germany during World War II. Published in 2005, The Book Thief became an international bestseller and was translated into 63 languages and sold 17 million copies. It was adapted into the 2013 feature film, The Book Thief.\r\n\r\nThe novel follows the adventures of a young girl, Liesel Meminger. Narrated by Death, the novel presents the lives and viewpoints of the many victims of the ongoing war. Themes throughout the story include death, literature, and love.', '410794da6024931ef7f6880fe3f3a982.jpg', NULL, '2026-06-15 15:31:49', NULL, 3),
(44, 'Robert C. Martin', 9, 47, 'PG001', 'Robert Cecil Martin (born 5 December 1952), colloquially called \"Uncle Bob\", is an American software engineer, instructor, and author. He is most recognized for promoting many software design principles and for being an author and signatory of the influential Agile Manifesto.\r\n\r\nMartin has authored many books and magazine articles. He was the editor-in-chief of C++ Report magazine and served as the first chairman of the Agile Alliance.', '5a913a88024d0418d6efb550d66585e5.jpg', NULL, '2026-06-15 15:35:53', NULL, 3),
(45, 'The Pragmatic Programmer', 9, 48, 'PG002', 'The Pragmatic Programmer: From Journeyman to Master is a book about computer programming and software engineering, written by Andrew Hunt and David Thomas and published in October 1999. It is used as a textbook in related university courses. It was the first in a series of books under the label The Pragmatic Bookshelf. A second edition, The Pragmatic Programmer: Your Journey to Mastery was released in 2019 for the book\'s 20th anniversary, with major revisions and new material reflecting new technology and other changes in the software engineering industry over the preceding twenty years.', 'dd2246cfb63038d2fa5e03c1f4a949eb.jpg', NULL, '2026-06-15 15:37:52', NULL, 3),
(46, 'Code Complete', 9, 49, 'PG004', 'Code Complete is a software development book, written by Steve McConnell and published in 1993 by Microsoft Press, encouraging developers to continue past code-and-fix programming and the big design up front and waterfall models. It is also a compendium of software construction techniques, which include techniques from naming variables to deciding when to write a subroutine.', '217eadaa17c90817ec6c3da3b852022a.jpg', NULL, '2026-06-15 15:40:38', NULL, 3),
(47, 'Head First (book series)', 9, 50, 'PG006', 'Head First is a series of introductory instructional books to many topics, published by O\'Reilly Media. It stresses an unorthodox, visually intensive, reader-involving combination of puzzles, jokes, nonstandard design and layout, and an engaging, conversational style to immerse the reader in a given topic.\r\n\r\nOriginally, the series covered programming and software engineering, but is now expanding to other topics in science, mathematics and business, due to success. The series was created by Bert Bates and Kathy Sierra, and began with Head First Java in 2003.', 'e628f053708d5b782433202aa9188488.jpg', NULL, '2026-06-15 15:42:14', NULL, 3),
(48, 'Introduction to Algorithms', 9, 51, 'PG007', 'Introduction to Algorithms is a book on computer programming by Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, and Clifford Stein. The book is described by its publisher as \"the leading algorithms text in universities worldwide as well as the standard reference for professionals\". It is commonly cited as a reference for algorithms in published papers, with over 10,000 citations documented on CiteSeerX, and over 70,000 citations on Google Scholar as of 2024. The book sold half a million copies during its first 20 years, and surpassed a million copies sold in 2022. Its fame has led to the common use of the abbreviation \"CLRS\" (Cormen, Leiserson, Rivest, Stein), or, in the first edition, \"CLR\" (Cormen, Leiserson, Rivest).', '0fb74bfadb45ac465a7f2107d1bacd65.jpeg', NULL, '2026-06-15 15:45:46', NULL, 3),
(49, 'One Piece (1)', 10, 52, 'MG001', 'One Piece (stylized in all caps) is a Japanese manga series written and illustrated by Eiichiro Oda. It follows the adventures of Monkey D. Luffy and his crew, the Straw Hats, as he searches for the legendary treasure known as the \"One Piece\" to become the next King of the Pirates. The manga has been serialized in Shueisha\'s sh?nen manga magazine Weekly Sh?nen Jump since July 1997, with its chapters compiled in 114 tank?bon volumes as of March 2026. It was licensed for an English-language release in North America and the United Kingdom by Viz Media and in Australia by Madman Entertainment.', '62ac27288ff58899f05d7e026a1b0b48.jpg', NULL, '2026-06-15 15:48:03', NULL, 3),
(50, 'Naruto (1)', 10, 53, 'MG002', 'Naruto is a Japanese manga series written and illustrated by Masashi Kishimoto. It tells the story of Naruto Uzumaki, a young, socially isolated ninja who seeks recognition from his peers and dreams of becoming the Hokage, the leader of his village. The story is told in two parts: the first is set in Naruto\'s pre-teen years (volumes 1–27), and the second in his teens (volumes 28–72). The series is based on two one-shot manga by Kishimoto: Karakuri (1995), which earned Kishimoto an honorable mention in Shueisha\'s monthly Hop Step Award the following year, and Naruto (1997).', '4cfa55cfda3357b2fae57d731ec35dce.jpg', NULL, '2026-06-15 15:50:03', NULL, 3),
(51, 'Attack On Titan (1)', 10, 54, 'MG004', 'Attack on Titan (Japanese: ?????, Hepburn: Shingeki no Kyojin; lit.?\'The Advancing Giant\') is a Japanese manga series written and illustrated by Hajime Isayama. Set in a world where humanity is forced to live in cities surrounded by three enormous walls that protect them from gigantic man-eating humanoids referred to as Titans, the story follows Eren Yeager, an adolescent boy who vows to exterminate the Titans after they bring about the destruction of his hometown and the death of his mother. It was serialized in Kodansha\'s monthly magazine Bessatsu Sh?nen Magazine from September 2009 to April 2021, with its chapters collected in 34 tank?bon volumes.', '5732d9164a178d049b41e7d81abc5f0b.jpg', NULL, '2026-06-15 15:51:47', NULL, 3),
(52, 'Jujutsu Kaisen (1)', 10, 55, 'MG007', 'Jujutsu Kaisen (????; rgh. \'Sorcery Battle\') is a Japanese manga series written and illustrated by Gege Akutami. It was serialized in Shueisha\'s sh?nen manga magazine Weekly Sh?nen Jump from March 2018 to September 2024, with its chapters collected in 30 tank?bon volumes. The story follows high school student Yuji Itadori as he joins a secret organization of Jujutsu Sorcerers to eliminate a powerful Curse named Ryomen Sukuna, of whom Yuji becomes the host. Jujutsu Kaisen is a sequel to Akutami\'s previous work, Tokyo Metropolitan Curse Technical School, which was retroactively titled Jujutsu Kaisen 0 following the release of Jujutsu Kaisen.', '64b5d976c14d3217cc69853959f3c7d4.jpg', NULL, '2026-06-15 15:53:21', NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `tblcategory`
--

CREATE TABLE `tblcategory` (
  `id` int(11) NOT NULL,
  `CategoryName` varchar(150) DEFAULT NULL,
  `Status` int(1) DEFAULT NULL,
  `CreationDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblcategory`
--

INSERT INTO `tblcategory` (`id`, `CategoryName`, `Status`, `CreationDate`, `UpdationDate`) VALUES
(4, 'Romantic', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:11'),
(5, 'Technology', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:21'),
(6, 'Science', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:21'),
(7, 'Management', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:21'),
(8, 'General', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:21'),
(9, 'Programming', 1, '2025-01-01 07:23:03', '2025-01-07 06:19:21'),
(10, 'Manga', 1, '2026-06-14 08:16:19', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tblissuedbookdetails`
--

CREATE TABLE `tblissuedbookdetails` (
  `id` int(11) NOT NULL,
  `BookId` int(11) DEFAULT NULL,
  `StudentID` varchar(150) DEFAULT NULL,
  `IssuesDate` timestamp NULL DEFAULT current_timestamp(),
  `ReturnDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `RetrunStatus` int(1) DEFAULT NULL,
  `fine` int(11) DEFAULT NULL,
  `remark` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblissuedbookdetails`
--

INSERT INTO `tblissuedbookdetails` (`id`, `BookId`, `StudentID`, `IssuesDate`, `ReturnDate`, `RetrunStatus`, `fine`, `remark`) VALUES
(1, 1, 'SID002', '2025-01-13 11:12:40', '2025-01-14 06:00:56', 1, 0, 'NA'),
(2, 7, 'SID010', '2025-01-14 05:55:25', '2026-06-09 08:32:16', 1, 0, 'NA'),
(3, 1, 'SID010', '2025-01-14 05:55:39', '2026-05-13 16:57:43', 1, 0, 'NA'),
(5, 1, 'SID002', '2025-01-14 06:02:14', '2025-01-14 06:03:36', 1, 0, 'ds'),
(6, 12, 'SID014', '2026-05-10 09:31:37', '2026-05-11 16:00:00', 1, 0, 'NA'),
(7, 8, 'SID014', '2026-05-12 06:48:05', '2026-05-11 16:00:00', 1, 0, 'NA'),
(8, 11, 'SID015', '2026-06-09 15:40:31', NULL, 0, NULL, 'NA'),
(9, 5, 'SID015', '2026-06-10 14:42:33', '2026-06-15 07:08:10', 1, 30, 'NA'),
(10, 13, 'SID016', '2026-06-15 12:55:12', NULL, 0, NULL, 'NA'),
(11, 5, 'SID016', '2026-06-15 12:55:38', '2026-06-15 12:59:17', 1, 0, 'NA'),
(12, 3, 'SID016', '2026-06-15 12:56:14', NULL, 0, NULL, 'NA'),
(13, 5, 'SID016', '2026-06-15 13:00:49', '2026-06-16 10:30:00', 1, 0, 'issue at ciounter'),
(14, 13, 'SID017', '2026-06-16 08:50:35', NULL, NULL, NULL, 'issued at counter'),
(15, 13, 'SID014', '2026-06-16 08:50:58', NULL, NULL, NULL, 'issued  at counter'),
(16, 51, 'SID017', '2026-06-16 08:51:56', NULL, 0, NULL, 'NA');

-- --------------------------------------------------------

--
-- Table structure for table `tblstudents`
--

CREATE TABLE `tblstudents` (
  `id` int(11) NOT NULL,
  `StudentId` varchar(100) DEFAULT NULL,
  `FullName` varchar(120) DEFAULT NULL,
  `EmailId` varchar(120) DEFAULT NULL,
  `MobileNumber` char(12) DEFAULT NULL,
  `Password` varchar(120) DEFAULT NULL,
  `Status` int(1) DEFAULT NULL,
  `RegDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblstudents`
--

INSERT INTO `tblstudents` (`id`, `StudentId`, `FullName`, `EmailId`, `MobileNumber`, `Password`, `Status`, `RegDate`, `UpdationDate`) VALUES
(1, 'SID002', 'Anuj kumar', 'anujk@gmail.com', '9865472555', 'f925916e2754e5e03f75dd58a5733251', 0, '2024-01-03 07:23:03', '2026-06-16 12:21:14'),
(4, 'SID005', 'sdfsd', 'csfsd@dfsfks.com', '8569710025', '92228410fc8b872914e023160cf4ae8f', 1, '2024-01-03 07:23:03', '2025-01-07 06:20:36'),
(8, 'SID009', 'test', 'test@gmail.com', '2359874527', 'f925916e2754e5e03f75dd58a5733251', 1, '2024-01-03 07:23:03', '2025-01-07 06:20:36'),
(9, 'SID010', 'Amit', 'amit@gmail.com', '8585856224', 'f925916e2754e5e03f75dd58a5733251', 1, '2024-01-03 07:23:03', '2025-01-07 06:20:36'),
(10, 'SID011', 'Sarita Pandey', 'sarita@gmail.com', '4672423754', 'f925916e2754e5e03f75dd58a5733251', 1, '2024-01-03 07:23:03', '2025-01-07 06:20:36'),
(11, 'SID012', 'John Doe', 'john@test.com', '1234569870', 'f925916e2754e5e03f75dd58a5733251', 1, '2024-01-03 07:23:03', '2025-01-07 06:20:36'),
(12, 'SID014', 'peishuang', 'peishuang818@gmail.com', '0111652040', '2d03af16ee59761036f6aae8ce739247', 1, '2026-05-09 15:30:48', NULL),
(13, 'SID015', 'Lim Liang Yea', 'liangyeal227@gmail.com', '0127219252', '3d3de6855ae64e2b0b24d473cf9c0278', 1, '2026-05-12 15:27:40', '2026-06-15 07:24:42'),
(14, 'SID016', 'DEW YIN CUN', 'yincundew25@gmail.com', '0183778982', '6134951babb98cb34f7f789e80c5a630', 1, '2026-06-15 12:53:02', '2026-06-15 13:24:00'),
(15, 'SID017', 'liang yea', 'liangyeal44@gmail.com', '0127219252', 'c3a8928a92fabe27f779378b9da5cd6e', 1, '2026-06-16 08:17:00', '2026-06-16 09:48:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblauthors`
--
ALTER TABLE `tblauthors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblbooks`
--
ALTER TABLE `tblbooks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblcategory`
--
ALTER TABLE `tblcategory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblissuedbookdetails`
--
ALTER TABLE `tblissuedbookdetails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblstudents`
--
ALTER TABLE `tblstudents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `StudentId` (`StudentId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblauthors`
--
ALTER TABLE `tblauthors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `tblbooks`
--
ALTER TABLE `tblbooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `tblcategory`
--
ALTER TABLE `tblcategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tblissuedbookdetails`
--
ALTER TABLE `tblissuedbookdetails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tblstudents`
--
ALTER TABLE `tblstudents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
