CREATE TABLE payment_keys (
  id              TINYINT PRIMARY KEY,
  gateway         VARCHAR(20) NOT NULL,
  public_key      TEXT,
  secret_key      TEXT,
  encrypt_key     TEXT,
  sandbox_mode    TINYINT(1) DEFAULT 0,
  active          TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;
-- seed with empty rows
INSERT INTO payment_keys (id,gateway) VALUES (1,'paystack'),(2,'paypal'),(3,'flutterwave');
