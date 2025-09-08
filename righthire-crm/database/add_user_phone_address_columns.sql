-- Add phone and address columns to users table
-- Migration: Add phone and address columns to users table
-- Date: 2025-09-08

ALTER TABLE `users` 
ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `email`,
ADD COLUMN `address` text DEFAULT NULL AFTER `phone`;

