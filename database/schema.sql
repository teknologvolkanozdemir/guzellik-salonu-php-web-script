CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(50) PRIMARY KEY,
  `value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  description TEXT,
  duration_min INT NOT NULL DEFAULT 30,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  title VARCHAR(100) NOT NULL,
  bio TEXT,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  service_id INT NOT NULL,
  staff_id INT NULL,
  customer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(120) NULL,
  appt_date DATE NOT NULL,
  appt_time TIME NOT NULL,
  note TEXT,
  status ENUM('beklemede','onaylandi','iptal') NOT NULL DEFAULT 'beklemede',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (service_id) REFERENCES services(id),
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL,
  INDEX (appt_date, appt_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL,
  subject VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (`key`,`value`) VALUES
('site_name','Güzellik Salonu'),
('tagline','Saç, cilt ve bakım hizmetlerinde profesyonel çözümler'),
('about','Yılların deneyimi ile kadın ve erkek kuaförlüğü, cilt bakımı, manikür ve pedikür hizmetleri sunuyoruz.'),
('address','Örnek Mah. Güzel Sok. No:1, İstanbul'),
('phone','0212 000 00 00'),
('email','info@example.com'),
('hours','Pazartesi–Cumartesi 09:00–19:00, Pazar kapalı'),
('open_hour','9'),
('close_hour','19')
ON DUPLICATE KEY UPDATE `value`=`value`;

INSERT INTO services (category,name,description,duration_min,price) VALUES
('Saç','Kadın Saç Kesimi','Yıkama, kesim ve fön dahil.',60,400),
('Saç','Erkek Saç Kesimi','Saç kesimi ve şekillendirme.',30,250),
('Saç','Saç Boyama','Kök veya tüm saç boyama.',120,900),
('Cilt','Cilt Bakımı','Temizleme, peeling ve maske.',60,700),
('El ve Ayak','Manikür','El bakımı ve oje.',45,300),
('El ve Ayak','Pedikür','Ayak bakımı ve oje.',60,400);

INSERT INTO staff (name,title,bio) VALUES
('Ayşe Yılmaz','Kuaför','15 yıllık deneyimli saç tasarım uzmanı.'),
('Mehmet Demir','Erkek Kuaförü','Klasik ve modern erkek kesimleri.'),
('Elif Kaya','Güzellik Uzmanı','Cilt bakımı ve manikür-pedikür uzmanı.');

CREATE TABLE IF NOT EXISTS campaigns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  discount VARCHAR(50) NULL,
  starts_on DATE NULL,
  ends_on DATE NULL,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (`key`,`value`) VALUES
('site_url',''),('meta_description','Saç kesimi, boyama, cilt bakımı, manikür ve pedikür. Online randevu alın.'),
('whatsapp','905000000000'),('instagram',''),('facebook',''),('x',''),('youtube',''),('tiktok',''),
('admin_path','yonetim')
ON DUPLICATE KEY UPDATE `value`=`value`;

INSERT INTO campaigns (title,description,discount) VALUES
('Yeni müşteri indirimi','İlk randevunuzda tüm saç hizmetlerinde geçerlidir.','%15');
