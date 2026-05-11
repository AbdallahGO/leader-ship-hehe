const fs = require('fs');
const path = require('path');
const { Client } = require('pg');
require('dotenv').config();
const { validateEnv } = require('../config/env');

validateEnv();

const force = process.argv.includes('--force');
const seedFile = path.join(__dirname, 'seed.sql');
const sql = fs.readFileSync(seedFile, 'utf-8');

async function runSeed() {
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
        throw new Error('Force seed is disabled in production');
      }
      console.log('⚠️  Force seed enabled: dropping existing tables and recreating schema');
      await client.query(`DROP TABLE IF EXISTS tickets, orders, host_applications, promo_codes, sponsors, speakers, inquiries, plans, users CASCADE;`);
      const schemaSql = fs.readFileSync(path.join(__dirname, 'schema.sql'), 'utf-8');
      await client.query(schemaSql);
    }

    await client.query(sql);
    console.log('✓ Seed complete — plans, speakers, sponsors, promo codes loaded');
    process.exit(0);
  } catch (error) {
    console.error('Seed failed:', error.message || error);
    process.exit(1);
  } finally {
    await client.end();
  }
}

runSeed();
