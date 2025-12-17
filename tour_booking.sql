-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 13, 2024 lúc 10:31 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `tour_booking`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tour_id` int(11) DEFAULT NULL,
  `booking_date` datetime NOT NULL,
  `departure_date` date NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `booking_status` enum('pending','complete','cancel') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `tour_id`, `booking_date`, `departure_date`, `quantity`, `total_price`, `booking_status`) VALUES
(7, 5, 2, '2024-09-10 17:56:05', '2024-09-10', 1, 4000000.00, 'complete'),
(8, 5, 2, '2024-09-10 23:46:55', '2024-09-12', 2, 8000000.00, 'complete'),
(11, 5, 2, '2024-08-01 00:24:57', '2024-08-14', 2, 8000000.00, 'complete'),
(12, 5, 16, '2024-07-02 00:37:03', '2024-07-25', 1, 3500000.00, 'complete'),
(13, 2, 17, '2024-09-12 00:38:28', '2024-09-25', 1, 4000000.00, 'pending'),
(14, 2, 17, '2024-09-12 01:01:14', '2024-09-17', 1, 4000000.00, 'cancel'),
(15, 11, 19, '2024-09-12 01:05:12', '2024-10-10', 1, 2500000.00, 'cancel'),
(16, 11, 19, '2024-09-12 05:05:52', '2024-10-01', 1, 2500000.00, 'pending'),
(17, 2, 18, '2024-09-12 12:49:51', '2024-09-14', 1, 3000000.00, 'pending'),
(18, 2, 20, '2024-09-08 12:54:30', '2024-09-11', 1, 5000000.00, 'complete'),
(19, 5, 2, '2024-09-12 15:06:21', '2024-09-25', 2, 8000000.00, 'cancel');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cities`
--

CREATE TABLE `cities` (
  `city_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `region_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cities`
--

INSERT INTO `cities` (`city_id`, `name`, `region_id`) VALUES
(1, 'Hà Nội', 1),
(2, 'Lào Cai', 1),
(3, 'Yên Bái', 1),
(4, 'Điện Biên', 1),
(5, 'Lai Châu', 1),
(6, 'Sơn La', 1),
(7, 'Hòa Bình', 1),
(8, 'Hà Giang', 1),
(9, 'Cao Bằng', 1),
(10, 'Bắc Kạn', 1),
(11, 'Lạng Sơn', 1),
(12, 'Tuyên Quang', 1),
(13, 'Thái Nguyên', 1),
(14, 'Phú Thọ', 1),
(15, 'Bắc Giang', 1),
(16, 'Quảng Ninh', 1),
(17, 'Bắc Ninh', 1),
(18, 'Hà Nam', 1),
(19, 'Nam Định', 1),
(20, 'Ninh Bình', 1),
(21, 'Thái Bình', 1),
(22, 'Vĩnh Phúc', 1),
(23, 'Hải Dương', 1),
(24, 'Hưng Yên', 1),
(25, 'Hải Phòng', 1),
(26, 'Thanh Hoá', 2),
(27, 'Nghệ An', 2),
(28, 'Hà Tĩnh', 2),
(29, 'Quảng Bình', 2),
(30, 'Quảng Trị', 2),
(31, 'Thừa Thiên-Huế', 2),
(32, 'Đà Nẵng', 2),
(33, 'Quảng Nam', 2),
(34, 'Quảng Ngãi', 2),
(35, 'Bình Định', 2),
(36, 'Phú Yên', 2),
(37, 'Khánh Hòa', 2),
(38, 'Ninh Thuận', 2),
(39, 'Bình Thuận', 2),
(40, 'Kon Tum', 2),
(41, 'Gia Lai', 2),
(42, 'Đắc Lắc', 2),
(43, 'Đắc Nông', 2),
(44, 'Lâm Đồng', 2),
(45, 'Bình Phước', 3),
(46, 'Bình Dương', 3),
(47, 'Đồng Nai', 3),
(48, 'Tây Ninh', 3),
(49, 'Bà Rịa-Vũng Tàu', 3),
(50, 'Thành phố Hồ Chí Minh', 3),
(51, 'Long An', 3),
(52, 'Đồng Tháp', 3),
(53, 'Tiền Giang', 3),
(54, 'An Giang', 3),
(55, 'Bến Tre', 3),
(56, 'Vĩnh Long', 3),
(57, 'Trà Vinh', 3),
(58, 'Hậu Giang', 3),
(59, 'Kiên Giang', 3),
(60, 'Sóc Trăng', 3),
(61, 'Bạc Liêu', 3),
(62, 'Cà Mau', 3),
(63, 'Cần Thơ', 3),
(64, 'Sa Pa', 1),
(65, 'Đà Lạt', 2),
(66, 'Phú Quốc', 3),
(67, 'Nha Trang', 2),
(68, 'Mộc Châu', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `id_card_number` varchar(20) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `id_card_number`, `phone`, `booking_id`) VALUES
(10, '123', '23425', '2345', '1345', 7),
(11, 'Nguyễn Duy ', 'Chiến', '0123456789', '033334444', 8),
(12, 'Vũ Tiến', 'Thành', '03030934552', '012333444', 8),
(16, 'Nguyễn Duy', 'Chiến', '123456789', '123456789', 11),
(17, 'Vũ Tiến', 'Thành', '456789', '456789', 11),
(18, 'Nguyễn Duy ', 'Chiến', '030202004567', '0123456789', 12),
(19, 'Vũ Tiến ', 'Thành', '7531597453', '1234567890', 13),
(20, 'Vũ Tiến ', 'Thành', '123456789', '456789123', 14),
(21, 'Vũ Tiến ', 'Thành', '45617852642', '0254536542', 15),
(22, 'Nguyễn Văn', 'Thành', '0212151315656', '0218562846', 16),
(23, 'Nguyễn Duy', 'Chiến', '043274683424', '0345674383', 17),
(24, 'Nguyễn Văn', 'Chiến', '030202004567', '0123456789', 18),
(25, 'Nguyễn Duy', 'Chiến', '14124124', '235235235', 19),
(26, 'Vũ Tiến', 'Thành', '7824525', '25254234', 19);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customertours`
--

CREATE TABLE `customertours` (
  `customer_tour_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `tour_id` int(11) DEFAULT NULL,
  `departure_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `customertours`
--

INSERT INTO `customertours` (`customer_tour_id`, `customer_id`, `tour_id`, `departure_date`) VALUES
(7, 10, 2, '2024-09-12'),
(8, 11, 2, '2024-09-12'),
(9, 12, 2, '2024-09-12'),
(13, 16, 2, '2024-09-18'),
(14, 17, 2, '2024-09-18'),
(15, 18, 16, '2024-09-16'),
(16, 19, 17, '2024-09-25'),
(17, 20, 17, '2024-09-17'),
(18, 21, 19, '2024-10-10'),
(19, 22, 19, '2024-10-01'),
(20, 23, 18, '2024-09-14'),
(21, 24, 20, '2024-09-14'),
(22, 25, 2, '2024-09-25'),
(23, 26, 2, '2024-09-25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `respond` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`message_id`, `user_id`, `content`, `created_at`, `respond`) VALUES
(1, 5, 'không thể đặt tour', '2024-09-10 18:15:13', 'bạn có thể cung cấp rõ vấn đề của bạn lại cho chúng tôi để có thể giải quyết vấn đề sớm cho bạn ạ.'),
(3, 5, 'Yêu cầu hoàn tiền', '2024-09-11 23:37:42', 'Bên công ty sẽ hoàn lại tiền sớm nhất cho quý khách'),
(4, 5, 'Hỗ trợ đặt tour', '2024-09-11 23:41:07', 'Bạn hãy cung cấp thông tin chi tiết cho bên mình để được hỗ trọ sớm nhất'),
(5, 2, 'Không thể đặt tour', '2024-09-12 12:56:57', NULL),
(6, 2, 'Yêu cầu hoàn tiền', '2024-09-12 12:57:09', 'Chúng tôi sẽ hoàn tiền cho bạn trong thời gian ngắn nhất'),
(7, 5, 'Khách sạn không tốt', '2024-09-12 15:08:26', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','paypal','bank_transfer') NOT NULL,
  `payment_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `payments`
--

INSERT INTO `payments` (`payment_id`, `booking_id`, `amount`, `payment_method`, `payment_date`) VALUES
(4, 7, 4000000.00, 'bank_transfer', '2024-09-10 17:56:05'),
(5, 8, 8000000.00, 'bank_transfer', '2024-09-10 23:46:55'),
(8, 11, 8000000.00, 'bank_transfer', '2024-09-12 00:24:57'),
(9, 12, 3500000.00, 'bank_transfer', '2024-09-12 00:37:03'),
(10, 13, 4000000.00, 'bank_transfer', '2024-09-12 00:38:28'),
(11, 14, 4000000.00, 'paypal', '2024-09-12 01:01:14'),
(12, 15, 2500000.00, 'credit_card', '2024-09-12 01:05:12'),
(13, 16, 2500000.00, 'credit_card', '2024-09-12 05:05:52'),
(14, 17, 3000000.00, 'paypal', '2024-09-12 12:49:51'),
(15, 18, 5000000.00, 'bank_transfer', '2024-09-12 12:54:30'),
(16, 19, 8000000.00, 'credit_card', '2024-09-12 15:06:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `regions`
--

CREATE TABLE `regions` (
  `region_id` int(11) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `regions`
--

INSERT INTO `regions` (`region_id`, `name`) VALUES
(1, 'Miền Bắc'),
(2, 'Miền Trung'),
(3, 'Miền Nam');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tour_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `tour_id`, `rating`, `comment`, `created_at`) VALUES
(2, 2, 2, 3, 'rất là vui', '2024-09-08 20:21:27'),
(4, 5, 2, 4, 'Chu đáo, hướng dẫn viên nhiệt tình', '2024-09-12 00:33:34'),
(5, 5, 16, 4, 'Hướng dẫn viên nhiệt tình', '2024-09-12 15:07:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tours`
--

CREATE TABLE `tours` (
  `tour_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `url_img` text DEFAULT NULL,
  `itinerary` longtext DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `city_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tours`
--

INSERT INTO `tours` (`tour_id`, `title`, `description`, `url_img`, `itinerary`, `duration`, `price`, `city_id`) VALUES
(2, 'Tour Khám Phá Hà Nội Nghìn năm văn hiến', 'Tour Khám Phá Hà Nội là cơ hội tuyệt vời để bạn tìm hiểu về lịch sử, văn hóa và con người của thủ đô Việt Nam. Chuyến đi này bao gồm tham quan các địa danh nổi tiếng như Hồ Gươm, Văn Miếu, Lăng Chủ Tịch Hồ Chí Minh, và nhiều địa điểm hấp dẫn khác.', '/img/hanoi-hoankiem.jpg', '- Ngày 1: Đón khách tại khách sạn, tham quan Hồ Gươm và Văn Miếu.\r\n- Ngày 2: Thăm Lăng Chủ Tịch Hồ Chí Minh, Chùa Một Cột và Bảo tàng Hồ Chí Minh.\r\n- Ngày 3: Tham quan Phố Cổ Hà Nội, mua sắm tại chợ Đồng Xuân, trả khách tại khách sạn.', 3, 4000000, 1),
(16, 'Du lịch Sapa - Chinh phục đỉnh Fansipan', 'Khám phá vẻ đẹp núi rừng Tây Bắc, chinh phục đỉnh Fansipan, tham quan các bản làng dân tộc và thưởng thức đặc sản Sapa.', '/img/anh_2.jpg', '- Ngày 1: Tham quan bản Cát Cát\r\n- Ngày 2: Chinh phục đỉnh Fansipan \r\n- Ngày 3: Thưởng thức đặc sản Sapa', 3, 3500000, 64),
(17, 'Hành trình di sản miền Trung - Huế, Đà Nẵng, Hội An', 'Tham quan các di sản thế giới như Cố đô Huế, phố cổ Hội An, và khám phá thành phố Đà Nẵng.', '/img/anh_3.jpg', '- Ngày 1: Tham quan Cố đô Huế\r\n- Ngày 2: Thăm Đà Nẵng\r\n- Ngày 3: Thăm phố cổ Hội An', 3, 4000000, 31),
(18, 'Khám phá Đà Lạt - Thành phố ngàn hoa', 'Thưởng thức không khí trong lành của thành phố cao nguyên, tham quan các địa điểm nổi tiếng như Hồ Xuân Hương, Thung lũng Tình Yêu, và vườn hoa thành phố.', '/img/anh_4.jpg', '- Ngày 1: Tham quan Hồ Xuân Hương\r\n- Ngày 2: Thăm Thung lũng Tình Yêu\r\n- Ngày 3: Tham quan vườn hoa thành phố', 3, 3000000, 65),
(19, 'Trải nghiệm miền Tây sông nước', 'Tham quan chợ nổi Cái Răng, khám phá đời sống dân dã của người dân miền Tây và thưởng thức ẩm thực miệt vườn.', '/img/anh_5.jpg', '- Ngày 1: Tham quan chợ nổi Cái Răng\r\n- Ngày 2: Khám phá miệt vườn\r\n- Ngày 3: Thưởng thức ẩm thực miền Tây', 3, 2500000, 63),
(20, 'Khám phá Phú Quốc - Thiên đường biển đảo', 'Tham quan các bãi biển đẹp nhất Phú Quốc, tham gia các hoạt động lặn biển, câu cá và thưởng thức hải sản tươi ngon.', '/img/anh_6.jpg', '- Ngày 1: Tham quan Bãi Sao\r\n- Ngày 2: Lặn biển và câu cá\r\n- Ngày 3: Thưởng thức hải sản tươi ngon', 3, 5000000, 66),
(21, 'Hành trình khám phá Ninh Bình - Hạ Long trên cạn', 'Tham quan Tràng An, Tam Cốc, Bích Động và chùa Bái Đính. Thưởng ngoạn phong cảnh hùng vĩ của non nước Ninh Bình.', '/img/anh_7.jpg', '- Ngày 1: Tham quan Tràng An\r\n- Ngày 2: Thăm Tam Cốc, Bích Động\r\n-Ngày 3: Thăm chùa Bái Đính', 3, 2200000, 20),
(22, 'Tour du lịch Cần Thơ - Thủ phủ miền Tây', 'Tham quan nhà cổ Bình Thủy, bến Ninh Kiều và thưởng thức đặc sản miền Tây.', '/img/anh_8.jpg', '- Ngày 1: Tham quan nhà cổ Bình Thủy\r\n- Ngày 2: Thăm bến Ninh Kiều\r\n- Ngày 3: Thưởng thức đặc sản miền Tây', 3, 2000000, 63),
(23, 'Khám phá Nha Trang - Thiên đường du lịch biển', 'Tham quan Vinpearl Land, tham gia các hoạt động dưới nước như lặn biển, dù lượn và thưởng thức hải sản Nha Trang.', '/img/anh_9.jpg', '- Ngày 1: Tham quan Vinpearl Land\r\n- Ngày 2: Lặn biển và dù lượn\r\n- Ngày 3: Thưởng thức hải sản Nha Trang', 3, 4500000, 67),
(24, 'Khám phá Mộc Châu - Thiên đường hoa mận', 'Tham quan đồi chè, rừng thông bản Áng và các vườn hoa mận tuyệt đẹp.', '/img/anh_10.jpg', '- Ngày 1: Tham quan đồi chè\r\n- Ngày 2: Thăm rừng thông bản Áng\r\n- Ngày 3: Tham quan vườn hoa mận', 3, 2400000, 68);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_type` enum('user','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `phone`, `user_type`) VALUES
(2, 'chien1', '123', 'adada@gmail.com', '1234', 'user'),
(5, 'chien', '123', '12345@gmail.com', '123', 'user'),
(8, 'admin1', '$2y$10$S3ogd5gRXjRFGuRtYldguuwGxMP2NcsmQZUjnDWPRfN2E2W/Gl7wK', 'qfdaf@gmail.com', '234', 'admin'),
(11, 'thanhcon', '123', 'thanh@gmail.com', '032145795', 'user'),
(13, 'admin', '$2y$10$YS698MerOrrvXYeUW79/o.9reywjJXeZMPj0QxGuHEUhi1lXGESKm', 'admin@gmail.com', '0321456889', 'admin'),
(15, 'admin2', '$2y$10$7EgJmrjHoczz6JW7h6olDuitesgQ3WK8.fa.LOrr7OpwXckXJ1ABy', 'admin2@gmail.com', '1234', 'admin');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tour_id` (`tour_id`);

--
-- Chỉ mục cho bảng `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`city_id`),
  ADD KEY `region_id` (`region_id`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `customers_ibfk_1` (`booking_id`);

--
-- Chỉ mục cho bảng `customertours`
--
ALTER TABLE `customertours`
  ADD PRIMARY KEY (`customer_tour_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `tour_id` (`tour_id`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Chỉ mục cho bảng `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`region_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tour_id` (`tour_id`);

--
-- Chỉ mục cho bảng `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`tour_id`),
  ADD KEY `city_id` (`city_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `cities`
--
ALTER TABLE `cities`
  MODIFY `city_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `customertours`
--
ALTER TABLE `customertours`
  MODIFY `customer_tour_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `regions`
--
ALTER TABLE `regions`
  MODIFY `region_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `tours`
--
ALTER TABLE `tours`
  MODIFY `tour_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`tour_id`);

--
-- Các ràng buộc cho bảng `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`region_id`);

--
-- Các ràng buộc cho bảng `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Các ràng buộc cho bảng `customertours`
--
ALTER TABLE `customertours`
  ADD CONSTRAINT `customertours_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `customertours_ibfk_2` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`tour_id`);

--
-- Các ràng buộc cho bảng `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`tour_id`);

--
-- Các ràng buộc cho bảng `tours`
--
ALTER TABLE `tours`
  ADD CONSTRAINT `tours_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`city_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
