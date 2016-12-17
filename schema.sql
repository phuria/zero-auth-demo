CREATE TABLE IF NOT EXISTS user (
  id VARCHAR(255) PRIMARY KEY,
  verifier VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS product (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  price INT NOT NULL,
  currency VARCHAR(3) NOT NULL
);

INSERT IGNORE INTO product (title, price, currency) VALUES
  ('Fallout', 199, 'USD'),
  ('Don\'t Starve', 299, 'USD'),
  ('Baldur\'s Gate', 399, 'USD'),
  ('Icewind Dale', 499, 'USD'),
  ('Bloodborne', 599, 'USD');