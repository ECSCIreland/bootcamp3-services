CREATE TABLE users (
    role TEXT NOT NULL,
    username TEXT PRIMARY KEY NOT NULL,
    password TEXT NOT NULL,
    n TEXT NOT NULL,
    e TEXT NOT NULL,
    d TEXT NOT NULL
);


CREATE TABLE doctor_attributes (
    id INT PRIMARY KEY,
    username TEXT NOT NULL,
    fname TEXT NOT NULL,
    lname TEXT NOT NULL,
    specialty TEXT NOT NULL,
    message TEXT NOT NULL,
    is_private BOOLEAN NOT NULL,
    UNIQUE (username)
    
);