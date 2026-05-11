const fs = require('fs');
const path = require('path');
const { Client } = require('pg');
require('dotenv').config();
const { validateEnv } = require('../config/env');

validateEnv();

const force = process.argv.includes('--force');
const schemaFile = path.join(__dirname, 'schema.sql');
const sql = fs.readFileSync(schemaFile, 'utf-8');

async function runMigration() {
  const client = new Client({
    host: process.env.DB_HOST,
    port: Number(process.env.DB_PORT || 5432),
    database: process.env.DB_NAME,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
  });

  try {
    await client.connect();

    if (force) {
      if (process.env.NODE_ENV === 'production') {
        throw new Error('Force migrate is disabled in production');
      }
      console.log('⚠️  Force migrate enabled: dropping existing tables');
      await client.query(`DROP TABLE IF EXISTS tickets, orders, host_applications, promo_codes, sponsors, speakers, inquiries, plans, users CASCADE;`);
    }

    await client.query(sql);
    console.log('✓ Migration complete');
    process.exit(0);
  } catch (error) {
    console.error('Migration failed:', error.message || error);
    process.exit(1);
  } finally {
    await client.end();
  }
}

runMigration();
