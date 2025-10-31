CREATE TABLE IF NOT EXISTS properties (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(12, 2) NOT NULL,
    property_type VARCHAR(50) NOT NULL, -- 'sale', 'rent', 'plot'
    bedrooms INTEGER,
    bathrooms INTEGER,
    square_feet INTEGER,
    location VARCHAR(255),
    features TEXT[], -- Array of features
    image_url TEXT,
    gallery_urls TEXT[], -- Array of image URLs
    status VARCHAR(20) DEFAULT 'available', -- 'available', 'sold', 'rented'
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    image_url TEXT,
    display_order INTEGER DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id SERIAL PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL,
    testimonial TEXT NOT NULL,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    client_image TEXT,
    display_order INTEGER DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact submissions table
CREATE TABLE IF NOT EXISTS contact_submissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'new', -- 'new', 'read', 'replied'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX idx_properties_status ON properties(status);
CREATE INDEX idx_properties_featured ON properties(featured);
CREATE INDEX idx_properties_type ON properties(property_type);
CREATE INDEX idx_testimonials_active ON testimonials(active);
CREATE INDEX idx_contact_status ON contact_submissions(status);

-- Insert sample data
INSERT INTO services (title, description, icon, display_order) VALUES
('Residential Listings', 'Find your dream home with our extensive residential property listings.', 'home', 1),
('Sales and Leasing', 'Professional sales and leasing services tailored to your needs.', 'key', 2),
('Property Management', 'Comprehensive property management solutions for owners.', 'building', 3);

INSERT INTO testimonials (client_name, testimonial, rating, display_order) VALUES
('Happy Client', 'My real estate agent was with me and my wife every step of the way. She held us together throughout the deal that I''m grateful.', 5, 1),
('Satisfied Buyer', 'The broker I worked with was very patient with my real estate needs. No matter the location they all inquired together! My budget.', 5, 2),
('Grateful Customer', 'Our agent went above and beyond what was expected of her. You''re thankful for her excellent service and assistance in the searching.', 5, 3);

INSERT INTO properties (title, description, price, property_type, bedrooms, bathrooms, location, features, status, featured) VALUES
('Modern Family Home', '3 Bedroom with SQ in a Gated setting with CCTV Installed', 2500000.00, 'sale', 3, 2, 'Double Tree Estate, Kangundo Road', ARRAY['Gated Community', 'CCTV', 'Servants Quarter'], 'available', true),
('Luxury Apartment', '3 Bedroom with DSQ in Covered setting with CCTV Installed', 48000.00, 'rent', 3, 2, 'Double Tree Estate, Kangundo Road', ARRAY['Covered Parking', 'CCTV', 'DSQ'], 'available', true),
('Prime Plot', '50x100 plot with title deed in prime location', 1500000.00, 'plot', NULL, NULL, 'Double Tree Estate, Kangundo Road', ARRAY['Title Deed', 'Prime Location', 'Ready to Build'], 'available', true);
