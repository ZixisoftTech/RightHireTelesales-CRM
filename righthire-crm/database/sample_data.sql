-- Sample data for Right Hire CRM

USE `righthire_crm`;

-- Sample states
INSERT INTO `states` (`name`, `status`, `created_by`, `created_at`) VALUES
('California', 1, 1, NOW()),
('Texas', 1, 1, NOW()),
('Florida', 1, 1, NOW()),
('New York', 1, 1, NOW()),
('Illinois', 1, 1, NOW());

-- Sample cities
INSERT INTO `cities` (`state_id`, `name`, `status`, `created_by`, `created_at`) VALUES
(1, 'Los Angeles', 1, 1, NOW()),
(1, 'San Francisco', 1, 1, NOW()),
(1, 'San Diego', 1, 1, NOW()),
(2, 'Houston', 1, 1, NOW()),
(2, 'Dallas', 1, 1, NOW()),
(2, 'Austin', 1, 1, NOW()),
(3, 'Miami', 1, 1, NOW()),
(3, 'Orlando', 1, 1, NOW()),
(3, 'Tampa', 1, 1, NOW()),
(4, 'New York City', 1, 1, NOW()),
(4, 'Buffalo', 1, 1, NOW()),
(4, 'Rochester', 1, 1, NOW()),
(5, 'Chicago', 1, 1, NOW()),
(5, 'Springfield', 1, 1, NOW()),
(5, 'Peoria', 1, 1, NOW());

-- Sample employees
INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`, `created_by`, `created_at`) VALUES
('John Smith', 'john.smith@example.com', '$2y$12$1WtUipmLkkmRQo0.Y9.8eeXaMZxXRh0zw1v.XNLxHECPQhhO9iMVu', 'employee', 1, 1, NOW()),
('Jane Doe', 'jane.doe@example.com', '$2y$12$1WtUipmLkkmRQo0.Y9.8eeXaMZxXRh0zw1v.XNLxHECPQhhO9iMVu', 'employee', 1, 1, NOW()),
('Michael Johnson', 'michael.johnson@example.com', '$2y$12$1WtUipmLkkmRQo0.Y9.8eeXaMZxXRh0zw1v.XNLxHECPQhhO9iMVu', 'employee', 1, 1, NOW());
-- Password: Sales@112233

-- Sample employee territories
INSERT INTO `employee_territories` (`user_id`, `state_id`, `city_id`, `created_by`, `created_at`) VALUES
(2, 1, NULL, 1, NOW()),
(2, 2, 4, 1, NOW()),
(2, 2, 5, 1, NOW()),
(3, 3, NULL, 1, NOW()),
(4, 4, 10, 1, NOW()),
(4, 5, 13, 1, NOW());

-- Sample leads
INSERT INTO `leads` (`name`, `email`, `phone`, `address`, `state_id`, `city_id`, `status`, `assigned_to`, `created_by`, `created_at`) VALUES
('Robert Brown', 'robert.brown@example.com', '555-123-4567', '123 Main St', 1, 1, 'new', 2, 1, NOW()),
('Sarah Wilson', 'sarah.wilson@example.com', '555-234-5678', '456 Oak Ave', 1, 2, 'follow_up', 2, 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('David Lee', 'david.lee@example.com', '555-345-6789', '789 Pine Rd', 1, 3, 'interested', 2, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Emily Clark', 'emily.clark@example.com', '555-456-7890', '101 Elm St', 2, 4, 'new', 2, 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('James Taylor', 'james.taylor@example.com', '555-567-8901', '202 Maple Dr', 2, 5, 'not_attend', 2, 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Jennifer Adams', 'jennifer.adams@example.com', '555-678-9012', '303 Cedar Ln', 2, 6, 'wrong_number', 2, 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Thomas Moore', 'thomas.moore@example.com', '555-789-0123', '404 Birch Blvd', 3, 7, 'new', 3, 1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
('Lisa Garcia', 'lisa.garcia@example.com', '555-890-1234', '505 Walnut Ct', 3, 8, 'follow_up', 3, 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Daniel Martinez', 'daniel.martinez@example.com', '555-901-2345', '606 Spruce Pl', 3, 9, 'win', 3, 1, DATE_SUB(NOW(), INTERVAL 8 DAY)),
('Michelle Robinson', 'michelle.robinson@example.com', '555-012-3456', '707 Fir Way', 4, 10, 'new', 4, 1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
('Kevin Wright', 'kevin.wright@example.com', '555-123-4567', '808 Redwood Rd', 4, 11, 'dead', 4, 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
('Amanda Lopez', 'amanda.lopez@example.com', '555-234-5678', '909 Sequoia Ave', 4, 12, 'other', 4, 1, DATE_SUB(NOW(), INTERVAL 11 DAY)),
('Christopher Hill', 'christopher.hill@example.com', '555-345-6789', '111 Aspen Dr', 5, 13, 'new', 4, 1, DATE_SUB(NOW(), INTERVAL 12 DAY)),
('Stephanie Scott', 'stephanie.scott@example.com', '555-456-7890', '222 Willow St', 5, 14, 'interested', 4, 1, DATE_SUB(NOW(), INTERVAL 13 DAY)),
('Matthew Green', 'matthew.green@example.com', '555-567-8901', '333 Poplar Ln', 5, 15, 'win', 4, 1, DATE_SUB(NOW(), INTERVAL 14 DAY));

-- Sample call logs
INSERT INTO `call_logs` (`lead_id`, `status`, `remarks`, `follow_up_date`, `created_by`, `created_at`) VALUES
(1, 'new', 'Initial contact made', NULL, 1, NOW()),
(2, 'follow_up', 'Customer requested more information', DATE_ADD(NOW(), INTERVAL 2 DAY), 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'interested', 'Customer showed interest in our services', NULL, 1, NOW()),
(3, 'new', 'Left voicemail', NULL, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 'follow_up', 'Customer called back, scheduled follow-up', DATE_ADD(NOW(), INTERVAL 3 DAY), 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'interested', 'Customer is interested in premium package', NULL, 1, NOW()),
(4, 'new', 'Sent introduction email', NULL, 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 'new', 'Initial call made', NULL, 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(5, 'not_attend', 'Customer did not attend scheduled call', NULL, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(6, 'new', 'Initial contact attempt', NULL, 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 'wrong_number', 'Phone number is incorrect', NULL, 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 'new', 'Initial contact made', NULL, 1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(8, 'new', 'Left voicemail', NULL, 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(8, 'follow_up', 'Customer requested callback', DATE_ADD(NOW(), INTERVAL 1 DAY), 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(9, 'new', 'Initial contact made', NULL, 1, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(9, 'interested', 'Customer showed high interest', NULL, 1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(9, 'win', 'Customer signed contract', NULL, 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(10, 'new', 'Sent introduction email', NULL, 1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(11, 'new', 'Initial call made', NULL, 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(11, 'follow_up', 'Customer requested more information', DATE_SUB(NOW(), INTERVAL 7 DAY), 1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(11, 'dead', 'Customer not interested anymore', NULL, 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(12, 'new', 'Initial contact attempt', NULL, 1, DATE_SUB(NOW(), INTERVAL 11 DAY)),
(12, 'other', 'Customer moved to another state', NULL, 1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(13, 'new', 'Initial contact made', NULL, 1, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(14, 'new', 'Left voicemail', NULL, 1, DATE_SUB(NOW(), INTERVAL 13 DAY)),
(14, 'follow_up', 'Customer called back', DATE_SUB(NOW(), INTERVAL 10 DAY), 1, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(14, 'interested', 'Customer interested in basic package', NULL, 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(15, 'new', 'Initial contact made', NULL, 1, DATE_SUB(NOW(), INTERVAL 14 DAY)),
(15, 'interested', 'Customer showed high interest', NULL, 1, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(15, 'win', 'Customer signed contract', NULL, 1, DATE_SUB(NOW(), INTERVAL 10 DAY));

