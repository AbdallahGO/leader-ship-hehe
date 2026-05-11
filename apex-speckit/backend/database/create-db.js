const { Client } = require('pg');
require('dotenv').config();
const { validateEnv } = require('../config/env');

validateEnv();

async function createDatabase() {
  const client = new Client({
    host: process.env.DB_HOST,
    port: Number(process.env.DB_PORT || 5432),
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
  });

  try {
    await client.connect();
    await client.query(`CREATE DATABASE ${process.env.DB_NAME};`);
    console.log(`✓ Database ${process.env.DB_NAME} created`);
    process.exit(0);
  } catch (error) {
    if (error.message.includes('already exists')) {
      console.log(`✓ Database ${process.env.DB_NAME} already exists`);
      process.exit(0);
    }
    console.error('Database creation failed:', error.message || error);
    process.exit(1);
  } finally {
    await client.end();
  }
}

createDatabase();
