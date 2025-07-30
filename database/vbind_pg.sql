    -- Services table
    CREATE TABLE IF NOT EXISTS services (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        icon VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        sub_services TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Team members table
    CREATE TABLE IF NOT EXISTS team (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        position VARCHAR(255) NOT NULL,
        bio TEXT NOT NULL,
        image VARCHAR(255) NOT NULL,
        linkedin VARCHAR(255),
        instagram VARCHAR(255),
        facebook VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Portfolio items table
    CREATE TABLE IF NOT EXISTS portfolio (
        id SERIAL PRIMARY KEY,
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Portfolio images table
    CREATE TABLE IF NOT EXISTS portfolio_images (
        id SERIAL PRIMARY KEY,
        portfolio_id INTEGER NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        position INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (portfolio_id) REFERENCES portfolio(id) ON DELETE CASCADE
    );

    -- Brand logos table
    CREATE TABLE IF NOT EXISTS brand_logos (
        id SERIAL PRIMARY KEY,
        brand_name VARCHAR(255) NOT NULL,
        logo_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Hero reels table
    CREATE TABLE IF NOT EXISTS hero_reels (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        video_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Contact form submissions table
    CREATE TABLE IF NOT EXISTS contact_submissions (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        subject VARCHAR(255),
        message TEXT NOT NULL,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT FALSE
    );

    -- Insert default services if not already exists
    DO $$
    BEGIN
        IF NOT EXISTS (SELECT 1 FROM services LIMIT 1) THEN
            INSERT INTO services (title, icon, description, sub_services) VALUES
            ('Branding', 'fas fa-paint-brush', 'We help businesses create a strong and memorable brand identity that resonates with their target audience.', 'Brand Name Curation
    Branded Identity
    Packaging Design
    Brand Story Development
    Brand Collaterals'),
            ('Social Media Marketing', 'fas fa-hashtag', 'We help you build a strong social media presence that engages your audience and drives conversions.', 'Social Media Management
    Social Media Strategy
    Platform Optimization
    Content Creation
    Paid Ads Campaigns'),
            ('Video & Designing', 'fas fa-video', 'We create high-quality videos and designs that captivate your audience and effectively communicate your message.', 'Short/Long Form Videos
    Social Media Posts
    Campaign Videos
    Motion Graphics
    Product/Lifestyle Videos'),
            ('CGI Ads', 'fas fa-cube', 'We create stunning CGI animations and 3D videos that make your brand stand out in the digital landscape.', 'Campaign Videos
    Corporate 3D Videos
    CGI Animation Videos');
        END IF;
    END $$;

    -- Insert default team members if not already exists
    DO $$
    BEGIN
        IF NOT EXISTS (SELECT 1 FROM team LIMIT 1) THEN
            INSERT INTO team (name, position, bio, image, linkedin, instagram) VALUES
            ('Prashant Katalia', 'Creative Director', 'With over 10 years of experience in creative design and branding, Prashant leads our creative team with vision and expertise.', 'uploads/team/default-prashant.svg', 'https://www.linkedin.com/', 'https://www.instagram.com/'),
            ('Parshwa Panchal', 'Marketing Strategist', 'Parshwa specializes in developing comprehensive marketing strategies that drive growth and enhance brand presence.', 'uploads/team/default-parshwa.svg', 'https://www.linkedin.com/', 'https://www.instagram.com/'),
            ('Om Vishwakarma', 'CGI Specialist', 'Om brings digital creations to life with his expertise in 3D modeling, animation, and visual effects.', 'uploads/team/default-om.svg', 'https://www.linkedin.com/', 'https://www.instagram.com/'),
            ('Rishi Rathod', 'Brand Designer', 'Rishi combines artistic talent with strategic thinking to create memorable brand identities and visual assets.', 'uploads/team/default-rishi.svg', 'https://www.linkedin.com/', 'https://www.instagram.com/');
        END IF;
    END $$;