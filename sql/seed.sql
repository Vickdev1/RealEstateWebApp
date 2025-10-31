-- Sample seed data for the real estate application
-- Properties
INSERT INTO properties (title, description, price, location, property_type, status, bedrooms, bathrooms) VALUES
('Modern Downtown Apartment', 'Luxurious 2-bedroom apartment with city views', 350000, 'Downtown', 'sale', 'available', 2, 2),
('Suburban Family Home', 'Spacious 4-bedroom house with garden', 500000, 'Suburbs', 'sale', 'available', 4, 3),
('Beach View Condo', 'Beautiful 3-bedroom condo with ocean views', 450000, 'Beachfront', 'sale', 'available', 3, 2);

-- Services
INSERT INTO services (title, description, display_order) VALUES
('Property Valuation', 'Professional property value assessment', 4),
('Home Inspection', 'Detailed home inspection service', 5),
('Legal Consultation', 'Real estate legal consultation', 6);

-- Testimonials
INSERT INTO testimonials (client_name, testimonial, rating, display_order) VALUES
('John Smith', 'Excellent service and professional staff', 5, 4),
('Mary Johnson', 'Found my dream home quickly', 5, 5),
('David Wilson', 'Very helpful throughout the process', 4, 6);
