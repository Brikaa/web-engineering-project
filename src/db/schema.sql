DROP DATABASE IF EXISTS app;
CREATE DATABASE IF NOT EXISTS app;
USE app;

CREATE TABLE User (
  `id` varchar(36) DEFAULT (UUID()) PRIMARY KEY,
  `name` varchar(512) UNIQUE NOT NULL,
  `email` varchar(256) UNIQUE NOT NULL,
  `password` varchar(256) NOT NULL,
  `telephone` varchar(128) NOT NULL,
  `photo_url` varchar(256) NOT NULL,
  `money` FLOAT NOT NULL
);

CREATE TABLE Passenger (
  `id` varchar(36) DEFAULT (UUID()) PRIMARY KEY,
  `user_id` varchar(36) UNIQUE NOT NULL,
  `passport_image_url` varchar(256) NOT NULL,
  CONSTRAINT FK_PASSENGER_USER FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);

CREATE TABLE Company (
  `id` varchar(36) DEFAULT (UUID()) PRIMARY KEY,
  `user_id` varchar(36) UNIQUE NOT NULL,
  `bio` TEXT NOT NULL,
  `address` varchar(1024) NOT NULL,
  CONSTRAINT FK_COMPANY_USER FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);

CREATE TABLE Flight (
  `id` varchar(36) DEFAULT (UUID()) PRIMARY KEY,
  `company_user_id` varchar(36) NOT NULL,
  `name` varchar(1024) NOT NULL,
  `max_passengers` INT NOT NULL,
  `price` FLOAT NOT NULL,
  CONSTRAINT FK_FLIGHT_COMPANY FOREIGN KEY (company_user_id) REFERENCES User(id) ON DELETE CASCADE
);

CREATE TABLE FlightReservation (
  `id` varchar(36) DEFAULT (UUID()) PRIMARY KEY,
  `passenger_user_id` varchar(36) NOT NULL,
  `flight_id` varchar(36) NOT NULL,
  UNIQUE KEY UQ_FLIGHT_PASSENGER (passenger_user_id, flight_id),
  CONSTRAINT FK_FLIGHT_RESERVATION_PASSENGER
    FOREIGN KEY (passenger_user_id) REFERENCES User(id)
    ON DELETE CASCADE,
  CONSTRAINT FK_FLIGHT_RESERVATION_FLIGHT FOREIGN KEY (flight_id) REFERENCES Flight(id) ON DELETE CASCADE
);

CREATE TABLE FlightCity (
  `id` varchar(36) DEFAULT (UUID()) PRIMARY KEY,
  `flight_id` varchar(36) NOT NULL,
  `name` varchar(256) NOT NULL,
  `date_in_city` TIMESTAMP NOT NULL,
  UNIQUE KEY UQ_FLIGHT_CITY (`flight_id`, `name`),
  CONSTRAINT FK_FLIGHT_CITY FOREIGN KEY (flight_id) REFERENCES Flight(id) ON DELETE CASCADE
);

CREATE TABLE Message {
  `id` varchar(36) DEFAULT (UUID()) PRIMARY KEY,
  `message` TEXT NOT NULL,
  `sender_user_id` varchar(36) NOT NULL,
  `receiver_user_id` varchar(36) NOT NULL,
  `message_replied_to_id` varchar(36),
  CONSTRAINT FK_MESSAGE_SENDER FOREIGN KEY (sender_user_id) REFERENCES User(id) ON DELETE CASCADE,
  CONSTRAINT FK_MESSAGE_RECEIVER FOREIGN KEY (receiver_user_id) REFERENCES User(id) ON DELETE CASCADE,
  CONSTRAINT FK_MESSAGE_MESSAGE FOREIGN KEY (message_replied_to_id) REFERENCES `Message`(id)
}
