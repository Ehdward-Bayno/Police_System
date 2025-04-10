-- PostgreSQL schema for Police System
-- Run this file to initialize your database structure

-- Drop tables if they exist (for clean reinstallation)
DROP TABLE IF EXISTS case_access_logs CASCADE;
DROP TABLE IF EXISTS documents CASCADE;
DROP TABLE IF EXISTS respondents CASCADE;
DROP TABLE IF EXISTS cases CASCADE;
DROP TABLE IF EXISTS officers CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Create users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    badge_number TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create officers table
CREATE TABLE officers (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    rank TEXT,
    badge_number TEXT UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create cases table
CREATE TABLE cases (
    id SERIAL PRIMARY KEY,
    case_number TEXT UNIQUE NOT NULL,
    case_title TEXT NOT NULL,
    officer_id INTEGER REFERENCES officers(id),
    description TEXT,
    status TEXT DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create respondents table
CREATE TABLE respondents (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    rank TEXT,
    unit TEXT,
    justification TEXT,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create documents table
CREATE TABLE documents (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    file_name TEXT NOT NULL,
    file_path TEXT NOT NULL,
    document_type TEXT,
    uploaded_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create case_access_logs table
CREATE TABLE case_access_logs (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id),
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_cases_officer_id ON cases(officer_id);
CREATE INDEX idx_respondents_case_id ON respondents(case_id);
CREATE INDEX idx_documents_case_id ON documents(case_id);
CREATE INDEX idx_documents_uploaded_by ON documents(uploaded_by);
CREATE INDEX idx_case_access_logs_case_id ON case_access_logs(case_id);
CREATE INDEX idx_case_access_logs_user_id ON case_access_logs(user_id);
CREATE INDEX idx_case_access_logs_accessed_at ON case_access_logs(accessed_at);

-- Add some helpful comments
COMMENT ON TABLE users IS 'Stores user accounts for the system';
COMMENT ON TABLE officers IS 'Stores information about police officers';
COMMENT ON TABLE cases IS 'Stores case information';
COMMENT ON TABLE respondents IS 'Stores information about case respondents';
COMMENT ON TABLE documents IS 'Stores documents related to cases';
COMMENT ON TABLE case_access_logs IS 'Logs access to case information';

