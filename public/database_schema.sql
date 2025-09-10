CREATE DATABASE salon_services_db;
USE salon_services_db;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Services table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(150) NOT NULL,..
    description TEXT,
    benefits TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    is_available BOOLEAN DEFAULT TRUE,
    stylist_qualifications TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Stylists table
CREATE TABLE stylists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    title VARCHAR(100),
    specializations TEXT,
    experience_years INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    phone VARCHAR(20),
    email VARCHAR(100),
    bio TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Stylist Services junction table (many-to-many relationship)
CREATE TABLE stylist_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stylist_id INT,
    service_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stylist_id) REFERENCES stylists(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY unique_stylist_service (stylist_id, service_id)
);

-- Stylist availability table
CREATE TABLE stylist_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stylist_id INT,
    date DATE NOT NULL,
    time_slot TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (stylist_id) REFERENCES stylists(id) ON DELETE CASCADE,
    UNIQUE KEY unique_stylist_datetime (stylist_id, date, time_slot)
);

-- Bookings table (basic structure for integration with booking module)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT,
    stylist_id INT,
    customer_name VARCHAR(100),
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    end_time TIME,
    total_price DECIMAL(10,2),
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (stylist_id) REFERENCES stylists(id) ON DELETE SET NULL
);

-- Insert sample data
INSERT INTO categories (name, description) VALUES
('Hair Services', 'Professional hair cutting, styling, and treatment services'),
('Nail Services', 'Manicure, pedicure, and nail art services'),
('Facial Treatments', 'Skincare and facial beauty treatments'),
('Massage Therapy', 'Relaxation and therapeutic massage services'),
('Makeup Services', 'Professional makeup application for special occasions');

INSERT INTO services (category_id, name, description, benefits, price, duration, stylist_qualifications) VALUES
(1, 'Premium Hair Cut', 'Professional hair cutting with style consultation', 'Fresh new look, personalized styling advice', 75.00, 60, 'Certified hair stylists with 3+ years experience'),
(1, 'Hair Coloring', 'Full hair coloring service with premium products', 'Long-lasting color, hair health protection', 120.00, 120, 'Color specialists with advanced training'),
(1, 'Hair Treatment', 'Deep conditioning and repair treatment', 'Restored hair health, increased shine and softness', 85.00, 90, 'Licensed cosmetologists'),
(2, 'Classic Manicure', 'Traditional manicure with polish application', 'Healthy nails, beautiful appearance', 35.00, 45, 'Certified nail technicians'),
(2, 'Gel Manicure', 'Long-lasting gel polish manicure', 'Chip-resistant, 2-week lasting polish', 50.00, 60, 'Gel certified technicians'),
(3, 'Deep Cleansing Facial', 'Thorough facial cleansing and treatment', 'Clear, healthy, glowing skin', 95.00, 75, 'Licensed estheticians'),
(3, 'Anti-Aging Facial', 'Specialized treatment for mature skin', 'Reduced fine lines, improved skin texture', 125.00, 90, 'Advanced skincare specialists'),
(4, 'Swedish Massage', 'Relaxing full-body massage therapy', 'Stress relief, muscle relaxation', 100.00, 60, 'Licensed massage therapists'),
(5, 'Bridal Makeup', 'Complete makeup service for weddings', 'Picture-perfect look for your special day', 150.00, 90, 'Professional makeup artists');

INSERT INTO stylists (name, title, specializations, experience_years, rating, review_count) VALUES
('Sarah Johnson', 'Senior Hair Stylist', 'Hair Cutting, Hair Coloring, Styling', 8, 4.9, 245),
('Maria Rodriguez', 'Color Specialist', 'Hair Coloring, Highlights, Color Correction', 6, 4.8, 189),
('Emily Chen', 'Master Esthetician', 'Facial Treatments, Skincare Consultation', 10, 4.9, 312),
('Jessica Williams', 'Nail Technician', 'Manicure, Pedicure, Nail Art', 4, 4.7, 156),
('Amanda Thompson', 'Massage Therapist', 'Swedish Massage, Deep Tissue, Hot Stone', 7, 4.8, 198),
('Rachel Davis', 'Makeup Artist', 'Bridal Makeup, Special Event Makeup', 5, 4.9, 167);

-- Link stylists to services they can perform
INSERT INTO stylist_services (stylist_id, service_id) VALUES
(1, 1), (1, 2), (1, 3),  -- Sarah: Hair services
(2, 1), (2, 2), (2, 3),  -- Maria: Hair services
(3, 6), (3, 7),          -- Emily: Facial services
(4, 4), (4, 5),          -- Jessica: Nail services
(5, 8),                  -- Amanda: Massage services
(6, 9);                  -- Rachel: Makeup services