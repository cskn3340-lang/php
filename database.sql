-- =====================================================
-- TÜRK FİLMLERİ PLATFORMU - VERİTABANI ŞEMASI
-- Veritabanı: turk_filmleri
-- Karakter Seti: utf8mb4
-- =====================================================

CREATE DATABASE IF NOT EXISTS `turk_filmleri` 
  DEFAULT CHARACTER SET utf8mb4 
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `turk_filmleri`;

-- =====================================================
-- 1. KULLANICILAR TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. FİLMLER TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS `movies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `year` YEAR NOT NULL,
  `description` TEXT NOT NULL,
  `poster_url` VARCHAR(500) DEFAULT NULL,
  `cover_url` VARCHAR(500) DEFAULT NULL,
  `director` VARCHAR(100) DEFAULT NULL,
  `genre` VARCHAR(100) DEFAULT NULL,
  `duration` INT DEFAULT NULL COMMENT 'Dakika cinsinden süre',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. OYUNCULAR TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS `actors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `photo_url` VARCHAR(500) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. FİLM-OYUNCU İLİŞKİ TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS `movie_actors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `movie_id` INT NOT NULL,
  `actor_id` INT NOT NULL,
  `role_name` VARCHAR(100) DEFAULT NULL COMMENT 'Filmde canlandırdığı karakter',
  UNIQUE KEY `unique_movie_actor` (`movie_id`, `actor_id`),
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`actor_id`) REFERENCES `actors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. BEĞENİLER TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS `likes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `movie_id` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_like` (`user_id`, `movie_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. İZLEME LİSTESİ TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS `watchlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `movie_id` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_watchlist` (`user_id`, `movie_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. YORUMLAR TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `movie_id` INT NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. FİLM VİDEOLARI TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS `movie_videos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `movie_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `youtube_url` VARCHAR(500) NOT NULL,
  `video_type` ENUM('trailer','sahne','roportaj','ekstra') NOT NULL DEFAULT 'trailer',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. FİLM GÖRSELLERİ TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS `movie_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `movie_id` INT NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEED VERİSİ: KULLANICILAR
-- Admin şifre: Admin123!
-- Kullanıcı şifre: User123!
-- =====================================================
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@turkfilmleri.com', '$2y$10$YF1JdOx5r0kUE3WnMJZMq.DZrAxqrGVIMf4gJKFNHhE7zRvJH0H8e', 'admin'),
('kullanici', 'kullanici@turkfilmleri.com', '$2y$10$lBxQDE0PxnOAhFg.5BZxnuSvOGh8m3JXo9rtNqDRF7r4LdGmsT5Vi', 'user'),
('sinemaci', 'sinemaci@turkfilmleri.com', '$2y$10$lBxQDE0PxnOAhFg.5BZxnuSvOGh8m3JXo9rtNqDRF7r4LdGmsT5Vi', 'user');

-- =====================================================
-- SEED VERİSİ: 10 TÜRK FİLMİ
-- =====================================================
INSERT INTO `movies` (`id`, `title`, `year`, `description`, `poster_url`, `cover_url`, `director`, `genre`, `duration`) VALUES
(1, 'Kış Uykusu', 2014, 'Kapadokya''da küçük bir otel işleten emekli tiyatro oyuncusu Aydın, genç eşi Nihal ve kız kardeşi Necla ile birlikte yaşamaktadır. Kış geldiğinde kar altında kalan bu küçük kasabada, üç kişi arasındaki gerilim tırmanmaya başlar. Nuri Bilge Ceylan''ın Altın Palmiye ödüllü başyapıtı, insanın iç dünyasındaki çatışmaları derinlemesine inceler.', 'https://m.media-amazon.com/images/M/MV5BOTkxNTc2NDQ4Nl5BMl5BanBnXkFtZTgwOTU4Mjg0MjE@._V1_.jpg', 'https://m.media-amazon.com/images/M/MV5BOTkxNTc2NDQ4Nl5BMl5BanBnXkFtZTgwOTU4Mjg0MjE@._V1_.jpg', 'Nuri Bilge Ceylan', 'Drama', 196),

(2, 'Bir Zamanlar Anadolu''da', 2011, 'Bir cinayet soruşturması için Anadolu bozkırlarında gece boyunca süren bir arayış. Savcı, doktor, komiser ve şüphelinin cesedin gömüldüğü yeri bulmak için çıktıkları bu yolculukta, her bir durağı yeni bir insani keşfe dönüşür. Film, sıradan bir polisiye formatının ötesinde derin bir Anadolu portresi çizer.', 'https://m.media-amazon.com/images/M/MV5BODc1MTdhMWUtNjUxMi00YjQ4LTk2NjktYTI2ODE1NmFiNzBlXkEyXkFqcGc@._V1_.jpg', 'https://m.media-amazon.com/images/M/MV5BODc1MTdhMWUtNjUxMi00YjQ4LTk2NjktYTI2ODE1NmFiNzBlXkEyXkFqcGc@._V1_.jpg', 'Nuri Bilge Ceylan', 'Drama, Suç', 157),

(3, 'Babam ve Oğlum', 2005, 'Sadık, 12 Eylül askeri darbesinin ardından ailesinden kopmuş bir gazeteciydir. Yıllar sonra ölümcül bir hastalığa yakalandığında, küçük oğlu Deniz''i babasının yanına götürmeye karar verir. Bu film, Türkiye''nin yakın tarihinin acılarını bir baba-oğul ilişkisi üzerinden anlatır ve izleyiciyi derinden etkiler.', 'https://m.media-amazon.com/images/M/MV5BNjM2NWQ1OTUtYjc3NS00NjMyLTkzOTctM2YwNzBkNTFkMjRjXkEyXkFqcGc@._V1_.jpg', 'https://m.media-amazon.com/images/M/MV5BNjM2NWQ1OTUtYjc3NS00NjMyLTkzOTctM2YwNzBkNTFkMjRjXkEyXkFqcGc@._V1_.jpg', 'Çağan Irmak', 'Drama', 112),

(4, 'Ayla', 2017, 'Kore Savaşı sırasında Türk askeri Süleyman, savaşın ortasında ailesini kaybetmiş küçük bir Koreli kız çocuğu bulur. Ona "Ayla" adını verir ve aralarında güçlü bir bağ oluşur. Gerçek bir hikâyeye dayanan bu film, savaşın ortasında filizlenen bir baba-kız sevgisini anlatır.', 'https://m.media-amazon.com/images/M/MV5BZjA0OWFiMGEtMTEzNS00ZDYwLThmZTMtMGEzNTMzMDU2YjczXkEyXkFqcGc@._V1_.jpg', 'https://m.media-amazon.com/images/M/MV5BZjA0OWFiMGEtMTEzNS00ZDYwLThmZTMtMGEzNTMzMDU2YjczXkEyXkFqcGc@._V1_.jpg', 'Can Ulkay', 'Drama, Savaş', 125),

(5, 'Mucize', 2015, 'Doğu Anadolu''nun küçük bir köyünde görev yapan idealist öğretmen Mahir, köy halkının önyargılarıyla mücadele ederken, engelli bir çocuğun hayatını değiştirir. Film, eğitimin ve sevginin gücünü Anadolu''nun sert ama sıcak coğrafyasında anlatır.', 'https://m.media-amazon.com/images/M/MV5BNTM3NTAzMDAtMWQ0Yi00MjQ4LTlhNmItODBiN2NlNmQzYjI0XkEyXkFqcGc@._V1_.jpg', 'https://m.media-amazon.com/images/M/MV5BNTM3NTAzMDAtMWQ0Yi00MjQ4LTlhNmItODBiN2NlNmQzYjI0XkEyXkFqcGc@._V1_.jpg', 'Mahsun Kırmızıgül', 'Drama', 130),

(6, 'Hababam Sınıfı', 1975, 'Özel bir yatılı lisedeki yaramaz ve asi öğrencilerin komik maceralarını anlatan efsanevi Türk filmi. Rıfat Ilgaz''ın aynı adlı romanından uyarlanan film, Türk sinemasının en sevilen komedisi olmuştur. Damat Ferit, İnek Şaban ve diğer unutulmaz karakterler nesiller boyu izleyicilerin kalbinde yaşamaya devam eder.', 'https://m.media-amazon.com/images/M/MV5BMjEzMzc2NDAyOV5BMl5BanBnXkFtZTcwNTY4OTQyMQ@@._V1_.jpg', 'https://m.media-amazon.com/images/M/MV5BMjEzMzc2NDAyOV5BMl5BanBnXkFtZTcwNTY4OTQyMQ@@._V1_.jpg', 'Ertem Eğilmez', 'Komedi', 87),

(7, 'Eşkıya', 1996, 'Otuz beş yıl hapis yatan Baran, sevdiği kadın Keje''yi bulmak için İstanbul''a gelir. Ancak İstanbul artık tanıdığı şehir değildir. Eski dostunun ihaneti ve büyük şehrin acımasızlığıyla yüzleşen Baran''ın hikâyesi, Türk sinemasının en güçlü dramlarından birini oluşturur.', 'https://m.media-amazon.com/images/M/MV5BN2QwYzdhNTktYjU1NS00NWFjLWI1MWYtMDdmMTkyMWJhZTk4XkEyXkFqcGc@._V1_.jpg', 'https://m.media-amazon.com/images/M/MV5BN2QwYzdhNTktYjU1NS00NWFjLWI1MWYtMDdmMTkyMWJhZTk4XkEyXkFqcGc@._V1_.jpg', 'Yavuz Turgul', 'Drama, Suç', 128),

(8, 'Organize İşler', 2005, 'İstanbul''un göbeğinde bir apartman dairesinde yaşayan küçük çaplı dolandırıcıların dünyasına dalıyoruz. Asım, Maho ve ekibi her gün yeni bir "iş" peşinde koşarken, olaylar beklenmedik bir şekilde gelişir. Yılmaz Erdoğan''ın yazıp yönettiği bu film, Türk komedisinin en akıllı örneklerinden biridir.', 'https://m.media-amazon.com/images/M/MV5BODA4NjM1NjYzN15BMl5BanBnXkFtZTcwMTcxMTYyMQ@@._V1_.jpg', 'https://m.media-amazon.com/images/M/MV5BODA4NjM1NjYzN15BMl5BanBnXkFtZTcwMTcxMTYyMQ@@._V1_.jpg', 'Yılmaz Erdoğan', 'Komedi, Suç', 107),

(9, 'Nefes: Vatan Sağolsun', 2009, 'Güneydoğu''da bir dağ karakolunda görev yapan Türk askerlerinin gerçek hikâyesine dayanan bu film, vatanını savunan gençlerin cesaretini ve fedakârlığını anlatır. Savaşın acımasızlığı karşısında insan kalabilmenin mücadelesi, izleyiciyi derinden etkileyen sahnelerle aktarılır.', 'https://m.media-amazon.com/images/M/MV5BYTg5MzY0NDItMDdkOC00MjFmLTkzOTgtYjYyZWY5MzJmZmE0XkEyXkFqcGc@._V1_.jpg', 'https://m.media-amazon.com/images/M/MV5BYTg5MzY0NDItMDdkOC00MjFmLTkzOTgtYjYyZWY5MzJmZmE0XkEyXkFqcGc@._V1_.jpg', 'Levent Semerci', 'Savaş, Drama', 128),

(10, 'Recep İvedik', 2008, 'Kendi halinde, görgüsüz ama sevimli Recep İvedik''in hayatından kesitler sunan bu komedi, Türk halkının geniş kesimlerinde büyük yankı uyandırmıştır. İlkokul aşkını bulmak için çıktığı yolculukta başına gelen absürt olaylar, izleyiciyi kahkahaya boğar.', 'https://m.media-amazon.com/images/M/MV5BY2E3NjIzOWEtYjdmNy00NjRhLTk3NWQtY2JjZGQ1MmEyZDZjXkEyXkFqcGc@._V1_.jpg', 'https://m.media-amazon.com/images/M/MV5BY2E3NjIzOWEtYjdmNy00NjRhLTk3NWQtY2JjZGQ1MmEyZDZjXkEyXkFqcGc@._V1_.jpg', 'Togan Gökbakar', 'Komedi', 93);

-- =====================================================
-- SEED VERİSİ: OYUNCULAR
-- =====================================================
INSERT INTO `actors` (`id`, `name`, `photo_url`, `bio`) VALUES
(1, 'Haluk Bilginer', 'https://m.media-amazon.com/images/M/MV5BMTU4MTk0MjA5OF5BMl5BanBnXkFtZTgwMDIyODgwMDE@._V1_.jpg', 'Uluslararası Emmy ödüllü Türk oyuncu. Kış Uykusu filmindeki performansıyla büyük beğeni toplamıştır.'),
(2, 'Melisa Sözen', 'https://m.media-amazon.com/images/M/MV5BMjI3NTkxNTYyN15BMl5BanBnXkFtZTgwODgxMjUxMDE@._V1_.jpg', 'Türk sinema ve tiyatro oyuncusu. Kış Uykusu filminde Nihal karakterini canlandırmıştır.'),
(3, 'Demet Akbağ', 'https://m.media-amazon.com/images/M/MV5BNWZhNjQxYTAtZjA1NC00OTlkLTg2YzAtMjFiODA0NTgyMDA0XkEyXkFqcGc@._V1_.jpg', 'Türk sinemasının en başarılı kadın oyuncularından biri. Komediden dramaya geniş bir yelpazede rol almıştır.'),
(4, 'Muhammet Uzuner', 'https://m.media-amazon.com/images/M/MV5BNGE1YmUxZWQtMjBhYi00OWIxLThiODctMzdjMTI5ZjczNTlhXkEyXkFqcGc@._V1_.jpg', 'Bir Zamanlar Anadolu''da filmindeki Doktor Cemal rolüyle tanınan Türk oyuncu.'),
(5, 'Yılmaz Erdoğan', 'https://m.media-amazon.com/images/M/MV5BODc5NjMzMDQxMl5BMl5BanBnXkFtZTcwMjg2MTcxOA@@._V1_.jpg', 'Oyuncu, yönetmen ve senarist olarak Türk sinemasının en önemli isimlerinden biri.'),
(6, 'Çetin Tekindor', 'https://m.media-amazon.com/images/M/MV5BNDkxNDIwMjAtMzUzMS00NDJhLWJkNTItZGM1NTQ5Yjg4OGZiXkEyXkFqcGc@._V1_.jpg', 'Babam ve Oğlum filmindeki baba rolüyle hafızalara kazınan usta oyuncu.'),
(7, 'Fikret Kuşkan', 'https://m.media-amazon.com/images/M/MV5BMjQ5YjkxOTAtYmM4Ny00NThlLTllMjktOTYwZWZjMGFmMjdlXkEyXkFqcGc@._V1_.jpg', 'Babam ve Oğlum filminde Sadık karakterini canlandıran başarılı Türk oyuncu.'),
(8, 'İsmail Hacıoğlu', 'https://m.media-amazon.com/images/M/MV5BYTY1YjQ3NmItYzA4Mi00OTM3LTkxZjQtZTQzN2FjOWRjYjU1XkEyXkFqcGc@._V1_.jpg', 'Ayla filminde Süleyman karakterini canlandıran genç nesil Türk oyuncu.'),
(9, 'Kim Seol', NULL, 'Ayla filminde küçük Ayla''yı canlandıran Koreli çocuk oyuncu.'),
(10, 'Mahsun Kırmızıgül', 'https://m.media-amazon.com/images/M/MV5BN2E5MjE5MTQtNDA3NC00MWQwLWFkMDAtZWYyNjcxOGExZDU2XkEyXkFqcGc@._V1_.jpg', 'Şarkıcı, oyuncu ve yönetmen. Mucize filminin hem yönetmeni hem de başrol oyuncusu.'),
(11, 'Kemal Sunal', 'https://m.media-amazon.com/images/M/MV5BMjE2MjI2ODc3MF5BMl5BanBnXkFtZTcwNjg1OTQyMQ@@._V1_.jpg', 'Türk sinemasının efsanevi komedi oyuncusu. Hababam Sınıfı serisindeki İnek Şaban rolüyle tanınır.'),
(12, 'Tarık Akan', 'https://m.media-amazon.com/images/M/MV5BMGI5Mjc0ZTUtNjY5Yi00OTJkLTg4M2UtMmMxYWQzYjYyNzFjXkEyXkFqcGc@._V1_.jpg', 'Cannes ödüllü Türk sinema oyuncusu. Hababam Sınıfı ve Sürü gibi başyapıtlarda rol almıştır.'),
(13, 'Şener Şen', 'https://m.media-amazon.com/images/M/MV5BMjI0NjUwODc1Nl5BMl5BanBnXkFtZTcwNjc4MTQyMQ@@._V1_.jpg', 'Türk sinemasının en büyük oyuncularından biri. Eşkıya filmindeki Baran rolüyle efsaneleşmiştir.'),
(14, 'Uğur Yücel', 'https://m.media-amazon.com/images/M/MV5BMjE1MjI4NzE0NV5BMl5BanBnXkFtZTcwMTc4MTQyMQ@@._V1_.jpg', 'Çok yönlü Türk oyuncu ve yönetmen. Eşkıya filmindeki Cumali karakteriyle hafızalara kazınmıştır.'),
(15, 'Ata Demirer', 'https://m.media-amazon.com/images/M/MV5BMTQzMDA0MTY2M15BMl5BanBnXkFtZTcwMDUyMjI0OQ@@._V1_.jpg', 'Komedyen ve oyuncu. Organize İşler filminde unutulmaz bir performans sergilemiştir.'),
(16, 'Mete Horozoğlu', 'https://m.media-amazon.com/images/M/MV5BMjA5MjQ2NjA0Ml5BMl5BanBnXkFtZTgwNjQ4Mjk5NjE@._V1_.jpg', 'Nefes filmindeki komutan rolüyle tanınan başarılı Türk oyuncu.'),
(17, 'Şahan Gökbakar', 'https://m.media-amazon.com/images/M/MV5BOGI4NjIyNjktNjc2MC00OGQ3LWFkNjYtMjU0OGY3M2IwNjRkXkEyXkFqcGc@._V1_.jpg', 'Recep İvedik serisinin yaratıcısı ve başrol oyuncusu. Türkiye gişe rekorlarını elinde tutar.');

-- =====================================================
-- SEED VERİSİ: FİLM-OYUNCU İLİŞKİLERİ
-- =====================================================
INSERT INTO `movie_actors` (`movie_id`, `actor_id`, `role_name`) VALUES
-- Kış Uykusu
(1, 1, 'Aydın'),
(1, 2, 'Nihal'),
(1, 3, 'Necla'),
-- Bir Zamanlar Anadolu'da
(2, 4, 'Doktor Cemal'),
(2, 5, 'Komiser Naci'),
-- Babam ve Oğlum
(3, 6, 'Hüseyin (Baba)'),
(3, 7, 'Sadık'),
-- Ayla
(4, 8, 'Süleyman'),
(4, 9, 'Ayla (küçük)'),
-- Mucize
(5, 10, 'Mahir Öğretmen'),
-- Hababam Sınıfı
(6, 11, 'İnek Şaban'),
(6, 12, 'Damat Ferit'),
-- Eşkıya
(7, 13, 'Baran'),
(7, 14, 'Cumali'),
-- Organize İşler
(8, 5, 'Asım'),
(8, 15, 'Maho'),
-- Nefes
(9, 16, 'Komutan'),
-- Recep İvedik
(10, 17, 'Recep İvedik');

-- =====================================================
-- SEED VERİSİ: FİLM VİDEOLARI (YouTube)
-- =====================================================
INSERT INTO `movie_videos` (`movie_id`, `title`, `youtube_url`, `video_type`) VALUES
-- Kış Uykusu
(1, 'Kış Uykusu - Resmi Fragman', 'https://www.youtube.com/watch?v=LpGVgHfMbQg', 'trailer'),
(1, 'Kış Uykusu - Cannes Ödül Töreni', 'https://www.youtube.com/watch?v=kfNCMBJe8JE', 'ekstra'),
-- Bir Zamanlar Anadolu'da
(2, 'Bir Zamanlar Anadolu''da - Fragman', 'https://www.youtube.com/watch?v=my4kZbU7VdE', 'trailer'),
-- Babam ve Oğlum
(3, 'Babam ve Oğlum - Fragman', 'https://www.youtube.com/watch?v=2mT5BrDBlWc', 'trailer'),
(3, 'Babam ve Oğlum - En Duygusal Sahne', 'https://www.youtube.com/watch?v=u-SH4Jn8JfE', 'sahne'),
-- Ayla
(4, 'Ayla - Resmi Fragman', 'https://www.youtube.com/watch?v=icCxgMNjmTI', 'trailer'),
-- Mucize
(5, 'Mucize - Fragman', 'https://www.youtube.com/watch?v=_TJaqJ_0wHs', 'trailer'),
-- Hababam Sınıfı
(6, 'Hababam Sınıfı - Nostalji', 'https://www.youtube.com/watch?v=AHfqj-2-bTo', 'trailer'),
-- Eşkıya
(7, 'Eşkıya - Fragman', 'https://www.youtube.com/watch?v=dX3CxCcsPWg', 'trailer'),
(7, 'Eşkıya - Baran Sahnesi', 'https://www.youtube.com/watch?v=FtGxVV5bfpM', 'sahne'),
-- Organize İşler
(8, 'Organize İşler - Fragman', 'https://www.youtube.com/watch?v=fVYFo3ngDgg', 'trailer'),
-- Nefes
(9, 'Nefes: Vatan Sağolsun - Fragman', 'https://www.youtube.com/watch?v=_5H3XszLmzE', 'trailer'),
-- Recep İvedik
(10, 'Recep İvedik - Fragman', 'https://www.youtube.com/watch?v=4DF_iAPNves', 'trailer');

-- =====================================================
-- SEED VERİSİ: FİLM GÖRSELLERİ
-- =====================================================
INSERT INTO `movie_images` (`movie_id`, `image_url`, `caption`) VALUES
(1, 'https://m.media-amazon.com/images/M/MV5BOTkxNTc2NDQ4Nl5BMl5BanBnXkFtZTgwOTU4Mjg0MjE@._V1_.jpg', 'Kış Uykusu - Film Karesi'),
(3, 'https://m.media-amazon.com/images/M/MV5BNjM2NWQ1OTUtYjc3NS00NjMyLTkzOTctM2YwNzBkNTFkMjRjXkEyXkFqcGc@._V1_.jpg', 'Babam ve Oğlum - Baba Oğul'),
(4, 'https://m.media-amazon.com/images/M/MV5BZjA0OWFiMGEtMTEzNS00ZDYwLThmZTMtMGEzNTMzMDU2YjczXkEyXkFqcGc@._V1_.jpg', 'Ayla - Film Afişi'),
(7, 'https://m.media-amazon.com/images/M/MV5BN2QwYzdhNTktYjU1NS00NWFjLWI1MWYtMDdmMTkyMWJhZTk4XkEyXkFqcGc@._V1_.jpg', 'Eşkıya - Baran'),
(10, 'https://m.media-amazon.com/images/M/MV5BY2E3NjIzOWEtYjdmNy00NjRhLTk3NWQtY2JjZGQ1MmEyZDZjXkEyXkFqcGc@._V1_.jpg', 'Recep İvedik');

-- =====================================================
-- SEED VERİSİ: ÖRNEK BEĞENİLER
-- =====================================================
INSERT INTO `likes` (`user_id`, `movie_id`) VALUES
(2, 1), (2, 3), (2, 7), (2, 10),
(3, 1), (3, 4), (3, 5), (3, 6), (3, 8);

-- =====================================================
-- SEED VERİSİ: ÖRNEK İZLEME LİSTESİ
-- =====================================================
INSERT INTO `watchlist` (`user_id`, `movie_id`) VALUES
(2, 1), (2, 2), (2, 5),
(3, 3), (3, 7), (3, 9);

-- =====================================================
-- SEED VERİSİ: ÖRNEK YORUMLAR
-- =====================================================
INSERT INTO `comments` (`user_id`, `movie_id`, `content`) VALUES
(2, 1, 'Nuri Bilge Ceylan''ın en iyi filmi bence. Haluk Bilginer muhteşem oynamış!'),
(3, 1, 'Altın Palmiye''yi hak eden bir başyapıt. Diyaloglar inanılmaz güçlü.'),
(2, 3, 'Her izlediğimde ağlıyorum. Türk sinemasının en duygusal filmi.'),
(3, 3, 'Çetin Tekindor''un oyunculuğu olağanüstü. Mutlaka izleyin!'),
(2, 7, 'Şener Şen efsanedir. Bu film Türk sinemasının dönüm noktalarından biri.'),
(3, 4, 'Gerçek bir hikâye olması çok etkili. Çok duygulandım.'),
(2, 10, 'Güldüren bir film, Şahan çok başarılı bu rolde!'),
(3, 8, 'Yılmaz Erdoğan''ın hem yazıp hem oynaması harika. Diyaloglar çok akıcı.');
