-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3307
-- Thời gian đã tạo: Th10 20, 2025 lúc 08:12 AM
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
-- Cơ sở dữ liệu: `library`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bao_cao_thong_ke`
--

CREATE TABLE `bao_cao_thong_ke` (
  `Ma_bao_cao` int(11) NOT NULL,
  `Loai_bao_cao` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Thoi_gian_tao` datetime DEFAULT current_timestamp(),
  `Nguoi_tao` varchar(20) DEFAULT NULL,
  `Du_lieu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_muon`
--

CREATE TABLE `chi_tiet_muon` (
  `Ma_phieu_muon` varchar(20) NOT NULL,
  `Ma_sach` varchar(20) NOT NULL,
  `Ngay_tra_thuc_te` date DEFAULT NULL,
  `So_ngay_qua_han` int(11) DEFAULT 0,
  `Tien_phat` decimal(10,2) DEFAULT 0.00,
  `Ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_nhap`
--

CREATE TABLE `chi_tiet_nhap` (
  `Ma_phieu_nhap` varchar(20) NOT NULL,
  `Ma_sach` varchar(20) NOT NULL,
  `So_luong` int(11) NOT NULL,
  `Don_gia` decimal(10,2) NOT NULL,
  `Thanh_tien` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `doc_gia`
--

CREATE TABLE `doc_gia` (
  `Ma_doc_gia` varchar(20) NOT NULL,
  `Ho_ten` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Ngay_sinh` date DEFAULT NULL,
  `Gioi_tinh` varchar(10) DEFAULT NULL CHECK (`Gioi_tinh` in ('Nam','Nữ','Khác')),
  `Dia_chi` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SDT` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_muon_tra`
--

CREATE TABLE `lich_su_muon_tra` (
  `Ma_lich_su` int(11) NOT NULL,
  `Ma_doc_gia` varchar(20) DEFAULT NULL,
  `Ma_sach` varchar(20) DEFAULT NULL,
  `Ngay_muon` date DEFAULT NULL,
  `Ngay_tra` date DEFAULT NULL,
  `So_ngay_muon` int(11) DEFAULT NULL,
  `Tien_phat` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhan_vien`
--

CREATE TABLE `nhan_vien` (
  `Ma_nhan_vien` varchar(20) NOT NULL,
  `Ho_ten` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `SDT` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Quyen_quan_tri` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nha_cung_cap`
--

CREATE TABLE `nha_cung_cap` (
  `Ten_nha_cung_cap` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Dia_chi` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SDT` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Nguoi_lien_he` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_muon`
--

CREATE TABLE `phieu_muon` (
  `Ma_phieu_muon` varchar(20) NOT NULL,
  `Ma_doc_gia` varchar(20) NOT NULL,
  `Ma_nhan_vien` varchar(20) NOT NULL,
  `Ngay_muon` date NOT NULL,
  `Ngay_hen_tra` date NOT NULL,
  `Ngay_tra_du_kien` date DEFAULT NULL,
  `Tong_so_sach` int(11) NOT NULL,
  `Hinh_thuc_muon` varchar(20) DEFAULT NULL,
  `Trang_thai` varchar(20) NOT NULL,
  `Ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_nhap_sach`
--

CREATE TABLE `phieu_nhap_sach` (
  `Ma_phieu_nhap` varchar(20) NOT NULL,
  `Ten_nha_cung_cap` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Ma_nhan_vien` varchar(20) NOT NULL,
  `Ngay_nhap` date NOT NULL,
  `Tong_tien` decimal(12,2) NOT NULL,
  `Ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sach`
--

CREATE TABLE `sach` (
  `Ma_sach` varchar(20) NOT NULL,
  `Ten_sach` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Nam_xuat_ban` int(11) DEFAULT NULL,
  `Nha_xuat_ban` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Gia_tien` decimal(10,2) DEFAULT NULL,
  `So_ban` int(11) NOT NULL,
  `So_ban_dang_muon` int(11) NOT NULL,
  `Trang_thai` varchar(20) NOT NULL CHECK (`Trang_thai` in ('Còn','Hết','Ngưng sử dụng')),
  `Nguon_cung_cap` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sach_tac_gia`
--

CREATE TABLE `sach_tac_gia` (
  `Ma_sach` varchar(20) NOT NULL,
  `Ten_tac_gia` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sach_the_loai`
--

CREATE TABLE `sach_the_loai` (
  `Ma_sach` varchar(20) NOT NULL,
  `Ma_the_loai` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tac_gia`
--

CREATE TABLE `tac_gia` (
  `Ten_tac_gia` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('nhanvien','docgia') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tham_so`
--

CREATE TABLE `tham_so` (
  `Ma_tham_so` varchar(20) NOT NULL,
  `Gia_tri` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Mo_ta` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `the_loai`
--

CREATE TABLE `the_loai` (
  `Ma_the_loai` varchar(20) NOT NULL,
  `Ten_the_loai` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Mo_ta` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tra_cuu`
--

CREATE TABLE `tra_cuu` (
  `Ma_tra_cuu` int(11) NOT NULL,
  `Ma_doc_gia` varchar(20) DEFAULT NULL,
  `Tu_khoa` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Thoi_gian_tra_cuu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bao_cao_thong_ke`
--
ALTER TABLE `bao_cao_thong_ke`
  ADD PRIMARY KEY (`Ma_bao_cao`),
  ADD KEY `Nguoi_tao` (`Nguoi_tao`);

--
-- Chỉ mục cho bảng `chi_tiet_muon`
--
ALTER TABLE `chi_tiet_muon`
  ADD PRIMARY KEY (`Ma_phieu_muon`,`Ma_sach`),
  ADD KEY `Ma_sach` (`Ma_sach`);

--
-- Chỉ mục cho bảng `chi_tiet_nhap`
--
ALTER TABLE `chi_tiet_nhap`
  ADD PRIMARY KEY (`Ma_phieu_nhap`,`Ma_sach`),
  ADD KEY `Ma_sach` (`Ma_sach`);

--
-- Chỉ mục cho bảng `doc_gia`
--
ALTER TABLE `doc_gia`
  ADD PRIMARY KEY (`Ma_doc_gia`);

--
-- Chỉ mục cho bảng `lich_su_muon_tra`
--
ALTER TABLE `lich_su_muon_tra`
  ADD PRIMARY KEY (`Ma_lich_su`),
  ADD KEY `Ma_doc_gia` (`Ma_doc_gia`),
  ADD KEY `Ma_sach` (`Ma_sach`);

--
-- Chỉ mục cho bảng `nhan_vien`
--
ALTER TABLE `nhan_vien`
  ADD PRIMARY KEY (`Ma_nhan_vien`);

--
-- Chỉ mục cho bảng `nha_cung_cap`
--
ALTER TABLE `nha_cung_cap`
  ADD PRIMARY KEY (`Ten_nha_cung_cap`);

--
-- Chỉ mục cho bảng `phieu_muon`
--
ALTER TABLE `phieu_muon`
  ADD PRIMARY KEY (`Ma_phieu_muon`),
  ADD KEY `Ma_doc_gia` (`Ma_doc_gia`),
  ADD KEY `Ma_nhan_vien` (`Ma_nhan_vien`);

--
-- Chỉ mục cho bảng `phieu_nhap_sach`
--
ALTER TABLE `phieu_nhap_sach`
  ADD PRIMARY KEY (`Ma_phieu_nhap`),
  ADD KEY `Ten_nha_cung_cap` (`Ten_nha_cung_cap`),
  ADD KEY `Ma_nhan_vien` (`Ma_nhan_vien`);

--
-- Chỉ mục cho bảng `sach`
--
ALTER TABLE `sach`
  ADD PRIMARY KEY (`Ma_sach`),
  ADD KEY `Nguon_cung_cap` (`Nguon_cung_cap`);

--
-- Chỉ mục cho bảng `sach_tac_gia`
--
ALTER TABLE `sach_tac_gia`
  ADD PRIMARY KEY (`Ma_sach`,`Ten_tac_gia`),
  ADD KEY `Ten_tac_gia` (`Ten_tac_gia`);

--
-- Chỉ mục cho bảng `sach_the_loai`
--
ALTER TABLE `sach_the_loai`
  ADD PRIMARY KEY (`Ma_sach`,`Ma_the_loai`),
  ADD KEY `Ma_the_loai` (`Ma_the_loai`);

--
-- Chỉ mục cho bảng `tac_gia`
--
ALTER TABLE `tac_gia`
  ADD PRIMARY KEY (`Ten_tac_gia`);

--
-- Chỉ mục cho bảng `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `tham_so`
--
ALTER TABLE `tham_so`
  ADD PRIMARY KEY (`Ma_tham_so`);

--
-- Chỉ mục cho bảng `the_loai`
--
ALTER TABLE `the_loai`
  ADD PRIMARY KEY (`Ma_the_loai`);

--
-- Chỉ mục cho bảng `tra_cuu`
--
ALTER TABLE `tra_cuu`
  ADD PRIMARY KEY (`Ma_tra_cuu`),
  ADD KEY `Ma_doc_gia` (`Ma_doc_gia`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bao_cao_thong_ke`
--
ALTER TABLE `bao_cao_thong_ke`
  MODIFY `Ma_bao_cao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lich_su_muon_tra`
--
ALTER TABLE `lich_su_muon_tra`
  MODIFY `Ma_lich_su` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tai_khoan`
--
ALTER TABLE `tai_khoan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `tra_cuu`
--
ALTER TABLE `tra_cuu`
  MODIFY `Ma_tra_cuu` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bao_cao_thong_ke`
--
ALTER TABLE `bao_cao_thong_ke`
  ADD CONSTRAINT `bao_cao_thong_ke_ibfk_1` FOREIGN KEY (`Nguoi_tao`) REFERENCES `nhan_vien` (`Ma_nhan_vien`);

--
-- Các ràng buộc cho bảng `chi_tiet_muon`
--
ALTER TABLE `chi_tiet_muon`
  ADD CONSTRAINT `chi_tiet_muon_ibfk_1` FOREIGN KEY (`Ma_phieu_muon`) REFERENCES `phieu_muon` (`Ma_phieu_muon`),
  ADD CONSTRAINT `chi_tiet_muon_ibfk_2` FOREIGN KEY (`Ma_sach`) REFERENCES `sach` (`Ma_sach`);

--
-- Các ràng buộc cho bảng `chi_tiet_nhap`
--
ALTER TABLE `chi_tiet_nhap`
  ADD CONSTRAINT `chi_tiet_nhap_ibfk_1` FOREIGN KEY (`Ma_phieu_nhap`) REFERENCES `phieu_nhap_sach` (`Ma_phieu_nhap`),
  ADD CONSTRAINT `chi_tiet_nhap_ibfk_2` FOREIGN KEY (`Ma_sach`) REFERENCES `sach` (`Ma_sach`);

--
-- Các ràng buộc cho bảng `lich_su_muon_tra`
--
ALTER TABLE `lich_su_muon_tra`
  ADD CONSTRAINT `lich_su_muon_tra_ibfk_1` FOREIGN KEY (`Ma_doc_gia`) REFERENCES `doc_gia` (`Ma_doc_gia`),
  ADD CONSTRAINT `lich_su_muon_tra_ibfk_2` FOREIGN KEY (`Ma_sach`) REFERENCES `sach` (`Ma_sach`);

--
-- Các ràng buộc cho bảng `phieu_muon`
--
ALTER TABLE `phieu_muon`
  ADD CONSTRAINT `phieu_muon_ibfk_1` FOREIGN KEY (`Ma_doc_gia`) REFERENCES `doc_gia` (`Ma_doc_gia`),
  ADD CONSTRAINT `phieu_muon_ibfk_2` FOREIGN KEY (`Ma_nhan_vien`) REFERENCES `nhan_vien` (`Ma_nhan_vien`);

--
-- Các ràng buộc cho bảng `phieu_nhap_sach`
--
ALTER TABLE `phieu_nhap_sach`
  ADD CONSTRAINT `phieu_nhap_sach_ibfk_1` FOREIGN KEY (`Ten_nha_cung_cap`) REFERENCES `nha_cung_cap` (`Ten_nha_cung_cap`),
  ADD CONSTRAINT `phieu_nhap_sach_ibfk_2` FOREIGN KEY (`Ma_nhan_vien`) REFERENCES `nhan_vien` (`Ma_nhan_vien`);

--
-- Các ràng buộc cho bảng `sach`
--
ALTER TABLE `sach`
  ADD CONSTRAINT `sach_ibfk_1` FOREIGN KEY (`Nguon_cung_cap`) REFERENCES `nha_cung_cap` (`Ten_nha_cung_cap`);

--
-- Các ràng buộc cho bảng `sach_tac_gia`
--
ALTER TABLE `sach_tac_gia`
  ADD CONSTRAINT `sach_tac_gia_ibfk_1` FOREIGN KEY (`Ma_sach`) REFERENCES `sach` (`Ma_sach`),
  ADD CONSTRAINT `sach_tac_gia_ibfk_2` FOREIGN KEY (`Ten_tac_gia`) REFERENCES `tac_gia` (`Ten_tac_gia`);

--
-- Các ràng buộc cho bảng `sach_the_loai`
--
ALTER TABLE `sach_the_loai`
  ADD CONSTRAINT `sach_the_loai_ibfk_1` FOREIGN KEY (`Ma_sach`) REFERENCES `sach` (`Ma_sach`),
  ADD CONSTRAINT `sach_the_loai_ibfk_2` FOREIGN KEY (`Ma_the_loai`) REFERENCES `the_loai` (`Ma_the_loai`);

--
-- Các ràng buộc cho bảng `tra_cuu`
--
ALTER TABLE `tra_cuu`
  ADD CONSTRAINT `tra_cuu_ibfk_1` FOREIGN KEY (`Ma_doc_gia`) REFERENCES `doc_gia` (`Ma_doc_gia`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
