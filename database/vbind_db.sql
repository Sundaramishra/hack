-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS vbind_db;

-- Use the database
USE vbind_db;

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    icon VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    sub_services TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Team members table
CREATE TABLE IF NOT EXISTS team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    bio TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    linkedin VARCHAR(255),
    instagram VARCHAR(255),
    facebook VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Portfolio items table
CREATE TABLE IF NOT EXISTS portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    brand_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    categories VARCHAR(255) NOT NULL,
    services_provided TEXT NOT NULL,
    timeline VARCHAR(100),
    brand_essence TEXT,
    brand_agenda TEXT,
    thumbnail VARCHAR(255) NOT NULL,
    video_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Portfolio images table
CREATE TABLE IF NOT EXISTS portfolio_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    position INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (portfolio_id) REFERENCES portfolio(id) ON DELETE CASCADE
);

-- Brand logos table
CREATE TABLE IF NOT EXISTS brand_logos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(255) NOT NULL,
    logo_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hero reels table
CREATE TABLE IF NOT EXISTS hero_reels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact form submissions table
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT 0
);

-- Insert default services
INSERT INTO services (title, icon, description, sub_services) VALUES
('Branding', 'fas fa-paint-brush', 'We help businesses create a strong and memorable brand identity that resonates with their target audience.', 'Brand Name Curation\nBranded Identity\nPackaging Design\nBrand Story Development\nBrand Collaterals'),
('Social Media Marketing', 'fas fa-hashtag', 'We help you build a strong social media presence that engages your audience and drives conversions.', 'Social Media Management\nSocial Media Strategy\nPlatform Optimization\nContent Creation\nPaid Ads Campaigns'),
('Video & Designing', 'fas fa-video', 'We create high-quality videos and designs that captivate your audience and effectively communicate your message.', 'Short/Long Form Videos\nSocial Media Posts\nCampaign Videos\nMotion Graphics\nProduct/Lifestyle Videos'),
('CGI Ads', 'fas fa-cube', 'We create stunning CGI animations and 3D videos that make your brand stand out in the digital landscape.', 'Campaign Videos\nCorporate 3D Videos\nCGI Animation Videos');

-- Insert default team members
INSERT INTO team (name, position, bio, image, linkedin, instagram) VALUES
('Prashant Katalia', 'Creative Director', 'With over 10 years of experience in creative design and branding, Prashant leads our creative team with vision and expertise.', 'uploads/team/default-prashant.jpg', 'https://www.linkedin.com/', 'https://www.instagram.com/'),
('Parshwa Panchal', 'Marketing Strategist', 'Parshwa specializes in developing comprehensive marketing strategies that drive growth and enhance brand presence.', 'uploads/team/default-parshwa.jpg', 'https://www.linkedin.com/', 'https://www.instagram.com/'),
('Om Vishwakarma', 'CGI Specialist', 'Om brings digital creations to life with his expertise in 3D modeling, animation, and visual effects.', 'uploads/team/default-om.jpg', 'https://www.linkedin.com/', 'https://www.instagram.com/'),
('Rishi Rathod', 'Brand Designer', 'Rishi combines artistic talent with strategic thinking to create memorable brand identities and visual assets.', 'uploads/team/default-rishi.jpg', 'https://www.linkedin.com/', 'https://www.instagram.com/');

-- Insert sample brand logos
INSERT INTO brand_logos (brand_name, logo_path) VALUES
('Somesa Modular Kitchen', 'uploads/brand-logos/68106f1c49f9c.png'),
('Rental Space', 'uploads/brand-logos/68106f7d12509.png'),
('Vaibhav Hair & Beyond', 'uploads/brand-logos/68106fd465ca7.png'),
('Gravityy Motors', 'uploads/brand-logos/6812781080b8a.png'),
('Crystal Detailing Studio', 'uploads/brand-logos/681cb36b03a77.png'),
('Vartak Competitive Academy', 'uploads/brand-logos/681cb3838011d.png');

-- Insert sample portfolio items
INSERT INTO portfolio (title, brand_name, description, categories, services_provided, thumbnail) VALUES
('Ishwar Motors', 'Ishwar Motors', 'Complete branding and digital marketing for automotive dealership', 'Branding, Digital Marketing', 'Brand Identity, Social Media, Website Design', 'uploads/portfolio/thumbnails/6811d54113cd0.png'),
('Vaibhav Hair & Beyond', 'Vaibhav Hair & Beyond', 'Salon branding and social media management', 'Branding, Social Media', 'Brand Identity, Social Media Management, Photography', 'uploads/portfolio/thumbnails/6811f6b8ed516.png'),
('Dum de Biryani', 'Dum de Biryani', 'Restaurant branding and marketing campaigns', 'Branding, Food Marketing', 'Brand Identity, Menu Design, Social Media', 'uploads/portfolio/thumbnails/6811f6ef30b34.jpg'),
('Somesa Modular Kitchen', 'Somesa Modular Kitchen', 'Kitchen showroom branding and digital presence', 'Branding, Interior Design', 'Brand Identity, Showroom Design, Digital Marketing', 'uploads/portfolio/thumbnails/68123a644d6de.jpg'),
('Crystal Detailing Studio', 'Crystal Detailing Studio', 'Car detailing service complete branding', 'Branding, Automotive', 'Brand Identity, Service Packaging, Marketing Materials', 'uploads/portfolio/thumbnails/6811d54113cd0.png');

-- Create necessary directories
-- Note: These commands should be executed by PHP scripts, not SQL
-- mkdir -p uploads/team
-- mkdir -p uploads/portfolio/thumbnails
-- mkdir -p uploads/portfolio/videos
-- mkdir -p uploads/portfolio/images
-- mkdir -p uploads/brand-logos
-- mkdir -p uploads/hero-reels
